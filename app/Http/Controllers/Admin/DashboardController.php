<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Show the Admin Dashboard.
     */
    public function index()
    {
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();

        // Recently created users with their roles
        $recentUsers = User::with('roles')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('totalUsers', 'totalRoles', 'totalPermissions', 'recentUsers'));
    }
}
