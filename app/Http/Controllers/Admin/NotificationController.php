<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = StaffNotification::forOwner()
            ->latest('id')
            ->take(15)
            ->get();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function latest()
    {
        $notifications = StaffNotification::forOwner()
            ->latest('id')
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'time' => $n->created_at?->diffForHumans(),
                'read' => ! is_null($n->read_at),
            ]);

        return response()->json([
            'status' => true,
            'unread' => StaffNotification::forOwner()->unread()->count(),
            'items' => $notifications,
        ]);
    }

    public function readAll()
    {
        StaffNotification::forOwner()->unread()->update(['read_at' => now()]);

        return response()->json(['status' => true]);
    }
}
