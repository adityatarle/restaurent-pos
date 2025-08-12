<?php

namespace App\Http\Controllers;

use App\Models\Notification; // Make sure this is imported
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Make sure this is imported

class NotificationController extends Controller
{
    public function indexForReception()
    {
        // Ensure the user is authenticated and has notifications relationship
        $notifications = Auth::user()
                            ->notifications() // Access the relationship
                            ->latest()        // Order by latest
                            ->paginate(15);    // Paginate results

        return view('reception.notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        $notification->is_read = true;
        $notification->save();

        return back()->with('success', 'Notification marked as read.');
    }

    // You might want a method to mark all as read too
    public function markAllAsRead(Request $request)
    {
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}