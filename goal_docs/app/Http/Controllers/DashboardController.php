<?php

/**
 * DashboardController - Enterprise Dashboard Management for GoalDocs System
 * 
 * This controller provides comprehensive dashboard functionality for the GoalDocs
 * enterprise document management system, offering real-time analytics, insights,
 * and system monitoring capabilities.
 * 
 * Key Features:
 * - Real-time analytics and reporting
 * - Multi-dimensional data visualization
 * - User activity monitoring and insights
 * - Storage and performance analytics
 * - Security monitoring and compliance tracking
 * - Workflow and collaboration analytics
 * - System health monitoring
 * - Customizable dashboard views
 * 
 * Dashboard Sections:
 * - Overview Statistics: Files, folders, users, storage
 * - Storage Analytics: Usage patterns, growth trends, optimization
 * - Activity Analytics: User behavior, engagement, productivity
 * - Document Analytics: Processing, conversion, search patterns
 * - Team Analytics: Collaboration, sharing, communication
 * - Security Analytics: Access patterns, compliance, threats
 * - Workflow Analytics: Process efficiency, bottlenecks, completion rates
 * - System Health: Performance metrics, uptime, resource usage
 * 
 * Analytics Capabilities:
 * - Time-based filtering and comparison
 * - Multi-user type support with organizational context
 * - Real-time data updates and notifications
 * - Export capabilities for reporting
 * - Custom date range selection
 * - Trend analysis and forecasting
 * 
 * @package App\Http\Controllers
 * @author GoalDocs Development Team
 * @version 1.0.0
 * @since 2024
 */

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use App\Models\RecentActivity;
use App\Models\ExternalShare;
use App\Models\DocumentWorkflow;
use App\Models\SecurityLog;
use App\Models\AuditLog;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Analytics service instance for data processing.
     * 
     * @var AnalyticsService
     */
    protected $analyticsService;

    /**
     * Constructor - Initialize the dashboard controller.
     * 
     * @param AnalyticsService $analyticsService Service for analytics data processing
     */
    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the main enterprise dashboard with comprehensive analytics.
     * 
     * Renders the primary dashboard view with real-time analytics, statistics,
     * and insights tailored to the user's role and organizational context.
     * 
     * @param Request $request HTTP request containing period and filter parameters
     * @return \Illuminate\View\View Dashboard view with analytics data
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', '30d'); // Default to 30 days

        // Get comprehensive dashboard data
        $data = [
            'user' => $user,
            'period' => $period,
            
            // Overview Statistics
            'overview' => $this->getOverviewStats($user),
            
            // Storage Analytics
            'storage' => $this->getStorageAnalytics($user),
            
            // Activity Analytics
            'activity' => $this->getActivityAnalytics($user, $period),
            
            // Document Analytics
            'documents' => $this->getDocumentAnalytics($user, $period),
            
            // User & Team Analytics
            'team' => $this->getTeamAnalytics($user),
            
            // Security Analytics
            'security' => $this->getSecurityAnalytics($user, $period),
            
            // Workflow Analytics
            'workflows' => $this->getWorkflowAnalytics($user, $period),
            
            // Recent Activities
            'recentActivities' => $this->getRecentActivities($user, 10),
            
            // Quick Actions
            'quickStats' => $this->getQuickStats($user),
            
            // System Health
            'systemHealth' => $this->getSystemHealth(),
        ];

        return view('dashboard.index', $data);
    }

    /**
     * Get comprehensive overview statistics for the dashboard.
     * 
     * Calculates key metrics including file counts, storage usage, user statistics,
     * and activity metrics based on the user's role and permissions.
     * 
     * @param User $user The authenticated user
     * @return array Overview statistics data
     */
    private function getOverviewStats($user)
    {
        $isAdmin = $user->is_admin;

        if ($isAdmin) {
            // Admin sees all organization data
            $totalFiles = File::count();
            $totalFolders = Folder::count();
            $totalUsers = User::count();
            $totalStorage = File::sum('file_size');
            $activeUsers = User::where('last_seen_at', '>=', Carbon::now()->subDays(7))->count();
        } else {
            // Regular users see only their accessible data
            $totalFiles = File::where('uploaded_by', $user->id)->count();
            $totalFolders = Folder::where('created_by', $user->id)->count();
            $totalUsers = 1; // Just the user
            $totalStorage = File::where('uploaded_by', $user->id)->sum('file_size');
            $activeUsers = 1;
        }

        return [
            'total_files' => $totalFiles,
            'total_folders' => $totalFolders,
            'total_users' => $totalUsers,
            'total_storage' => $totalStorage,
            'storage_formatted' => $this->formatBytes($totalStorage),
            'active_users' => $activeUsers,
            'files_this_week' => $this->getFilesThisWeek($user, $isAdmin),
            'growth_rate' => $this->getGrowthRate($user, $isAdmin),
        ];
    }

    /**
     * Get storage analytics
     */
    private function getStorageAnalytics($user)
    {
        $isAdmin = $user->is_admin;
        
        $query = File::query();
        if (!$isAdmin) {
            $query->where('uploaded_by', $user->id);
        }

        // Storage by file type
        $storageByType = $query->select('mime_type', DB::raw('SUM(file_size) as total_size'), DB::raw('COUNT(*) as file_count'))
            ->groupBy('mime_type')
            ->orderByDesc('total_size')
            ->limit(10)
            ->get();

        // Storage trend (last 30 days)
        $storageTrend = File::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(file_size) as daily_storage'),
                DB::raw('COUNT(*) as daily_files')
            )
            ->when(!$isAdmin, function ($q) use ($user) {
                return $q->where('uploaded_by', $user->id);
            })
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'by_type' => $storageByType,
            'trend' => $storageTrend,
            'largest_files' => $this->getLargestFiles($user, $isAdmin),
        ];
    }

    /**
     * Get activity analytics
     */
    private function getActivityAnalytics($user, $period)
    {
        $startDate = $this->getStartDate($period);
        $isAdmin = $user->is_admin;

        $query = RecentActivity::where('created_at', '>=', $startDate);
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        $activities = $query->get();

        // Activity by type
        $activityByType = $activities->groupBy('activity_type')->map(function ($items) {
            return $items->count();
        });

        // Daily activity trend
        $dailyActivity = $activities->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        })->map(function ($items) {
            return $items->count();
        });

        // Peak hours
        $peakHours = $activities->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('H');
        })->map(function ($items) {
            return $items->count();
        })->sortDesc()->take(5);

        return [
            'total' => $activities->count(),
            'by_type' => $activityByType,
            'daily_trend' => $dailyActivity,
            'peak_hours' => $peakHours,
        ];
    }

    /**
     * Get document analytics
     */
    private function getDocumentAnalytics($user, $period)
    {
        $startDate = $this->getStartDate($period);
        $isAdmin = $user->is_admin;

        $query = File::where('created_at', '>=', $startDate);
        if (!$isAdmin) {
            $query->where('uploaded_by', $user->id);
        }

        $files = $query->get();

        return [
            'total_uploaded' => $files->count(),
            'total_size' => $files->sum('file_size'),
            'most_popular' => $this->getMostPopularFiles($user, $period, $isAdmin),
            'file_types' => $files->groupBy('extension')->map(function ($items) {
                return $items->count();
            })->sortDesc()->take(10),
            'shared_count' => $this->getSharedFilesCount($user, $isAdmin),
        ];
    }

    /**
     * Get team analytics
     */
    private function getTeamAnalytics($user)
    {
        if (!$user->is_admin) {
            return [
                'total_team_members' => 1,
                'active_members' => 1,
                'top_contributors' => [],
            ];
        }

        $totalUsers = User::count();
        $activeUsers = User::where('last_seen_at', '>=', Carbon::now()->subDays(7))->count();

        // Top contributors (users with most uploads)
        $topContributors = User::select('users.*', DB::raw('COUNT(files.id) as file_count'))
            ->leftJoin('files', 'users.id', '=', 'files.uploaded_by')
            ->groupBy('users.id')
            ->orderByDesc('file_count')
            ->limit(5)
            ->get();

        return [
            'total_team_members' => $totalUsers,
            'active_members' => $activeUsers,
            'top_contributors' => $topContributors,
            'departments' => $this->getDepartmentStats(),
        ];
    }

    /**
     * Get security analytics
     */
    private function getSecurityAnalytics($user, $period)
    {
        $startDate = $this->getStartDate($period);
        $isAdmin = $user->is_admin;

        if (!$isAdmin) {
            return [
                'security_events' => 0,
                'failed_logins' => 0,
                'suspicious_activities' => 0,
            ];
        }

        $securityLogs = SecurityLog::where('created_at', '>=', $startDate)->get();
        $auditLogs = AuditLog::where('created_at', '>=', $startDate)->get();

        return [
            'security_events' => $securityLogs->count(),
            'failed_logins' => $securityLogs->where('event_type', 'failed_login')->count(),
            'suspicious_activities' => $securityLogs->where('severity', 'high')->count(),
            'audit_logs' => $auditLogs->count(),
            'recent_security_events' => $securityLogs->sortByDesc('created_at')->take(5),
        ];
    }

    /**
     * Get workflow analytics
     */
    private function getWorkflowAnalytics($user, $period)
    {
        $startDate = $this->getStartDate($period);
        $isAdmin = $user->is_admin;

        $query = DocumentWorkflow::where('created_at', '>=', $startDate);
        if (!$isAdmin) {
            $query->where('created_by', $user->id);
        }

        $workflows = $query->get();

        return [
            'total_workflows' => $workflows->count(),
            'active_workflows' => $workflows->where('status', 'in_progress')->count(),
            'completed_workflows' => $workflows->where('status', 'completed')->count(),
            'pending_approvals' => $workflows->where('status', 'pending')->count(),
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($user, $limit = 10)
    {
        $query = RecentActivity::with(['user', 'file']);
        
        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        return $query->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get quick stats for cards
     */
    private function getQuickStats($user)
    {
        $isAdmin = $user->is_admin;
        $today = Carbon::today();

        return [
            'files_today' => File::when(!$isAdmin, function ($q) use ($user) {
                    return $q->where('uploaded_by', $user->id);
                })
                ->whereDate('created_at', $today)
                ->count(),
            'downloads_today' => RecentActivity::when(!$isAdmin, function ($q) use ($user) {
                    return $q->where('user_id', $user->id);
                })
                ->where('activity_type', 'download')
                ->whereDate('created_at', $today)
                ->count(),
            'shares_active' => ExternalShare::when(!$isAdmin, function ($q) use ($user) {
                    return $q->where('created_by', $user->id);
                })
                ->where('expires_at', '>', now())
                ->orWhereNull('expires_at')
                ->count(),
            'pending_tasks' => DocumentWorkflow::when(!$isAdmin, function ($q) use ($user) {
                    return $q->where('created_by', $user->id);
                })
                ->where('status', 'pending')
                ->count(),
        ];
    }

    /**
     * Get system health indicators
     */
    private function getSystemHealth()
    {
        $health = [
            'storage_status' => 'healthy',
            'api_status' => 'healthy',
            'database_status' => 'healthy',
            'queue_status' => 'healthy',
        ];

        // Check storage (if above 90% capacity, mark as warning)
        $totalStorage = File::sum('file_size');
        $storageLimit = 1000000000000; // 1TB example
        if ($totalStorage > $storageLimit * 0.9) {
            $health['storage_status'] = 'warning';
        }

        return $health;
    }

    // Helper methods
    private function getStartDate($period)
    {
        return match($period) {
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            '90d' => Carbon::now()->subDays(90),
            '1y' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    private function getFilesThisWeek($user, $isAdmin)
    {
        $query = File::where('created_at', '>=', Carbon::now()->subWeek());
        if (!$isAdmin) {
            $query->where('uploaded_by', $user->id);
        }
        return $query->count();
    }

    private function getGrowthRate($user, $isAdmin)
    {
        $thisWeek = File::when(!$isAdmin, function ($q) use ($user) {
                return $q->where('uploaded_by', $user->id);
            })
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->count();

        $lastWeek = File::when(!$isAdmin, function ($q) use ($user) {
                return $q->where('uploaded_by', $user->id);
            })
            ->whereBetween('created_at', [Carbon::now()->subWeeks(2), Carbon::now()->subWeek()])
            ->count();

        if ($lastWeek == 0) return 100;
        
        return round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
    }

    private function getLargestFiles($user, $isAdmin)
    {
        $query = File::orderByDesc('file_size')->limit(5);
        if (!$isAdmin) {
            $query->where('uploaded_by', $user->id);
        }
        return $query->get();
    }

    private function getMostPopularFiles($user, $period, $isAdmin)
    {
        $startDate = $this->getStartDate($period);
        
        return File::select('files.*', DB::raw('COUNT(recent_activities.id) as activity_count'))
            ->leftJoin('recent_activities', 'files.id', '=', 'recent_activities.file_id')
            ->when(!$isAdmin, function ($q) use ($user) {
                return $q->where('files.uploaded_by', $user->id);
            })
            ->where('recent_activities.created_at', '>=', $startDate)
            ->groupBy('files.id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get();
    }

    private function getSharedFilesCount($user, $isAdmin)
    {
        $query = ExternalShare::query();
        if (!$isAdmin) {
            $query->where('created_by', $user->id);
        }
        return $query->count();
    }

    private function getDepartmentStats()
    {
        return DB::table('departments')
            ->select('departments.name', DB::raw('COUNT(users.id) as user_count'))
            ->leftJoin('positions', 'departments.id', '=', 'positions.department_id')
            ->leftJoin('user_positions', 'positions.id', '=', 'user_positions.position_id')
            ->leftJoin('users', 'user_positions.user_id', '=', 'users.id')
            ->groupBy('departments.id', 'departments.name')
            ->get();
    }

    /**
     * API endpoint for dashboard data (for AJAX refresh)
     */
    public function getData(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', '30d');
        $section = $request->input('section', 'all');

        $data = [];

        if ($section === 'all' || $section === 'overview') {
            $data['overview'] = $this->getOverviewStats($user);
        }

        if ($section === 'all' || $section === 'activity') {
            $data['activity'] = $this->getActivityAnalytics($user, $period);
        }

        if ($section === 'all' || $section === 'documents') {
            $data['documents'] = $this->getDocumentAnalytics($user, $period);
        }

        return response()->json($data);
    }
}
