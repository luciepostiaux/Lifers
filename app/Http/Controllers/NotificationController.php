<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications; // Assurez-vous que votre modèle User peut accéder à ses notifications

        return Inertia::render('App', [
            'notifications' => $notifications,
        ]);
    }
    public function showNotifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->unreadNotifications;

        // Retourner les notifications à la vue
        return view('notifications', compact('notifications'));
    }
}
