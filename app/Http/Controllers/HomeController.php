<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth'); // Applied at route level for dashboardRedirect
    }

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.redirect');
        }
        return view('auth.login'); // Or your landing page
    }

    public function dashboardRedirect()
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->isReception()) {
            return redirect()->route('reception.dashboard');
        } elseif ($user->isWaiter()) {
            return redirect()->route('waiter.dashboard');
        }
        Auth::logout(); // Should not happen if roles are set
        return redirect('/login')->withErrors('Invalid role assignment.');
    }

    public function superAdminDashboard()
    {
        // Add data for superadmin dashboard
        return view('superadmin.dashboard');
    }
}