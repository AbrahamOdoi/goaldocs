<?php

namespace App\Http\Controllers;

use App\Models\ExternalShare;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class ExternalShareController extends Controller
{
    /**
     * Create a new external share
     */
    public function createShare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
            'share_type' => 'required|in:link,email,whatsapp',
            'recipient_email' => 'nullable|email|required_if:share_type,email',
            'recipient_phone' => 'nullable|string|required_if:share_type,whatsapp',
            'recipient_name' => 'nullable|string|max:255',
            'password_protected' => 'boolean',
            'password' => 'nullable|string|min:4|required_if:password_protected,true',
            'expires_at' => 'nullable|date|after:now',
            'max_downloads' => 'nullable|integer|min:1',
            'permissions' => 'nullable|array',
            'permissions.view' => 'boolean',
            'permissions.download' => 'boolean',
            'permissions.edit' => 'boolean',
            'permissions.upload' => 'boolean',
            'permissions.delete' => 'boolean',
            'permissions.reshare' => 'boolean',
            'permissions.manage' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $resource = null;

        // Get the resource being shared
        if ($request->resource_type === 'file') {
            $resource = File::forOrganization($user->type, $user->type_name)
                           ->find($request->resource_id);
        } else {
            $resource = Folder::forOrganization($user->type, $user->type_name)
                             ->find($request->resource_id);
        }

        if (!$resource) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        // Check if user has permission to share this resource
        if (!$this->canShareResource($user, $resource)) {
            return response()->json(['error' => 'You do not have permission to share this resource'], 403);
        }

        // Create the share
        $shareData = [
            'share_token' => ExternalShare::generateToken(),
            'share_type' => $request->share_type,
            'recipient_email' => $request->recipient_email,
            'recipient_phone' => $request->recipient_phone,
            'recipient_name' => $request->recipient_name,
            'password_protected' => $request->password_protected ?? false,
            'expires_at' => $request->expires_at,
            'max_downloads' => $request->max_downloads,
            'permissions' => $request->permissions ?? ExternalShare::getDefaultPermissions(),
            'user_type' => $user->type,
            'type_name' => $user->type_name,
            'created_by' => $user->id,
        ];

        if ($request->resource_type === 'file') {
            $shareData['file_id'] = $resource->id;
        } else {
            $shareData['folder_id'] = $resource->id;
        }

        $share = ExternalShare::create($shareData);

        // Set password if provided
        if ($request->password_protected && $request->password) {
            $share->setPassword($request->password);
        }

        // Send notification based on share type
        if ($request->share_type === 'email' && $request->recipient_email) {
            $this->sendEmailShare($share);
        } elseif ($request->share_type === 'whatsapp' && $request->recipient_phone) {
            $this->sendWhatsAppShare($share);
        }

        return response()->json([
            'success' => true,
            'share' => $share,
            'share_url' => $share->share_url,
            'message' => 'Share created successfully'
        ]);
    }

    /**
     * Get all shares for the current user
     */
    public function getMyShares(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $shares = ExternalShare::forOrganization($user->type, $user->type_name)
                              ->where('created_by', $user->id)
                              ->with(['file', 'folder', 'creator'])
                              ->orderBy('created_at', 'desc')
                              ->get();

        return response()->json([
            'success' => true,
            'shares' => $shares
        ]);
    }

    /**
     * Revoke a share
     */
    public function revokeShare(Request $request, ExternalShare $share): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns this share
        if ($share->created_by !== $user->id || 
            $share->user_type !== $user->type || 
            $share->type_name !== $user->type_name) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $share->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Share revoked successfully'
        ]);
    }

    /**
     * Access a shared resource (public endpoint)
     */
    public function accessShared(Request $request, string $token): Response
    {
        $share = ExternalShare::where('share_token', $token)
                             ->with(['file', 'folder'])
                             ->first();

        if (!$share) {
            return response()->view('shared.not-found', [], 404);
        }

        if (!$share->is_valid) {
            return response()->view('shared.expired', [
                'share' => $share
            ], 410);
        }

        // Increment view count
        $share->incrementViewCount();

        // Check if password is required
        if ($share->password_protected) {
            $password = $request->input('password');
            if (!$password || !$share->verifyPassword($password)) {
                return response()->view('shared.password-required', [
                    'share' => $share,
                    'error' => $request->has('password') ? 'Invalid password' : null
                ]);
            }
        }

        // Return the shared resource view
        return response()->view('shared.access', [
            'share' => $share,
            'resource' => $share->resource
        ]);
    }

    /**
     * Download a shared file
     */
    public function downloadShared(Request $request, string $token)
    {
        $share = ExternalShare::where('share_token', $token)
                             ->with(['file'])
                             ->first();

        if (!$share || !$share->file) {
            return response()->view('shared.not-found', [], 404);
        }

        if (!$share->is_valid) {
            return response()->view('shared.expired', [
                'share' => $share
            ], 410);
        }

        if (!$share->hasPermission('download')) {
            abort(403, 'Download not allowed');
        }

        // Check password if required
        if ($share->password_protected) {
            $password = $request->input('password');
            if (!$password || !$share->verifyPassword($password)) {
                abort(403, 'Invalid password');
            }
        }

        // Increment download count
        $share->incrementDownloadCount();

        // Return file download
        $file = $share->file;
        
        if (!Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'File not found');
        }
        
        $fileContent = Storage::disk('local')->get($file->file_path);
        
        return response($fileContent)
            ->header('Content-Type', $file->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $file->original_name . '"');
    }

    /**
     * Check if user can share a resource
     */
    private function canShareResource($user, $resource): bool
    {
        // Admin users can share anything
        if ($user->is_admin) {
            return true;
        }

        // Check if user has reshare permission on the resource
        // This would integrate with the PermissionService
        // For now, allow if user created the resource
        return $resource->created_by === $user->id || $resource->uploaded_by === $user->id;
    }

    /**
     * Send email share notification
     */
    private function sendEmailShare(ExternalShare $share): void
    {
        // This would send an email with the share link
        // Implementation depends on your email service
        Mail::send('emails.share-notification', [
            'share' => $share,
            'resource' => $share->resource
        ], function ($message) use ($share) {
            $message->to($share->recipient_email)
                    ->subject('File shared with you: ' . $share->resource_name);
        });
    }

    /**
     * Send WhatsApp share notification
     */
    private function sendWhatsAppShare(ExternalShare $share): void
    {
        // This would send a WhatsApp message with the share link
        // Implementation depends on your WhatsApp API service
        $message = "You have been shared a file: {$share->resource_name}\n\n";
        $message .= "Access it here: {$share->share_url}\n\n";
        
        if ($share->password_protected) {
            $message .= "This share is password protected.";
        }

        // Example WhatsApp API call (you'll need to implement this)
        // Http::post('your-whatsapp-api-endpoint', [
        //     'phone' => $share->recipient_phone,
        //     'message' => $message
        // ]);
    }
}
