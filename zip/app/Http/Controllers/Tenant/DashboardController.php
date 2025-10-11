<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Facades\Tenancy;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // If File, Folder, ActivityLog models do not exist, skip stats and recent data
        return view('dashboard', compact('user'));
    }
}