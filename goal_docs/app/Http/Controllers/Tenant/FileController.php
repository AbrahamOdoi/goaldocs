<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\File;
use App\Models\Tenant\Folder;
use App\Models\Tenant\User;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\FileShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stancl\Tenancy\Facades\Tenancy;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = File::with(['uploader', 'folder', 'tags']);
        
        // Filter by folder
        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('original_name', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filter by file type
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'images':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'documents':
                    $query->whereIn('mime_type', [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ]);
                    break;
                case 'videos':
                    $query->where('mime_type', 'like', 'video/%');
                    break;
            }
        }
        
        $files = $query->latest()->paginate(20);
        $folders = Folder::select('id', 'name')->get();
        
        return view('tenant.files.index', compact('files', 'folders'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:' . config('app.max_file_size', 102400), // 100MB default
            'folder_id' => 'nullable|exists:folders,id',
        ]);
        
        $tenant = Tenancy::tenant();
        $user = Auth::user();
        $tenantUser = User::where('central_user_id', $user->id)->first();
        
        $uploadedFiles = [];
        
        foreach ($request->file('files') as $uploadedFile) {
            // Check storage limit
            if (!$tenant->canUpload($uploadedFile->getSize())) {
                return back()->withErrors(['files' => 'Storage limit exceeded.']);
            }
            
            // Store file
            $path = Storage::disk('tenant')->put('files', $uploadedFile);
            $hash = hash_file('md5', $uploadedFile->getRealPath());
            
            // Create file record
            $file = File::create([
                'name' => Str::slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $uploadedFile->getClientOriginalExtension(),
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'path' => $path,
                'disk' => 'tenant',
                'folder_id' => $request->folder_id,
                'uploaded_by' => $tenantUser->id,
                'hash' => $hash,
            ]);
            
            // Log activity
            ActivityLog::create([
                'action' => 'upload',
                'subject_type' => File::class,
                'subject_id' => $file->id,
                'user_id' => $tenantUser->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => [
                    'file_name' => $file->original_name,
                    'file_size' => $file->size,
                    'folder_id' => $file->folder_id,
                ],
            ]);
            
            $uploadedFiles[] = $file;
        }
        
        return back()->with('success', count($uploadedFiles) . ' file(s) uploaded successfully.');
    }
    
    public function download(Request $request, File $file)
    {
        $user = Auth::user();
        $tenantUser = User::where('central_user_id', $user->id)->first();
        
        // Check permissions (implement your permission logic here)
        
        // Log download activity
        ActivityLog::create([
            'action' => 'download',
            'subject_type' => File::class,
            'subject_id' => $file->id,
            'user_id' => $tenantUser->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
    
    public function share(Request $request, File $file)
    {
        $request->validate([
            'shared_with_email' => 'nullable|email',
            'expires_at' => 'nullable|date|after:now',
            'permissions' => 'required|array',
            'permissions.*' => 'in:view,download',
            'password' => 'nullable|string|min:6',
        ]);
        
        $user = Auth::user();
        $tenantUser = User::where('central_user_id', $user->id)->first();
        
        $share = FileShare::create([
            'file_id' => $file->id,
            'shared_by' => $tenantUser->id,
            'shared_with_email' => $request->shared_with_email,
            'token' => Str::random(32),
            'permissions' => $request->permissions,
            'expires_at' => $request->expires_at,
            'is_password_protected' => !empty($request->password),
            'password' => $request->password ? bcrypt($request->password) : null,
        ]);
        
        // Log sharing activity
        ActivityLog::create([
            'action' => 'share',
            'subject_type' => File::class,
            'subject_id' => $file->id,
            'user_id' => $tenantUser->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'properties' => [
                'shared_with' => $request->shared_with_email,
                'expires_at' => $request->expires_at,
            ],
        ]);
        
        $shareUrl = route('files.public.share', ['token' => $share->token]);
        
        return response()->json([
            'success' => true,
            'share_url' => $shareUrl,
            'message' => 'File shared successfully.',
        ]);
    }
}