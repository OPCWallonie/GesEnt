<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Liste paginée des notifications de l'utilisateur connecté.
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(30);

        auth()->user()->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue et rediriger vers son URL.
     */
    public function redirect(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        $url = $notification->data['url'] ?? route('dashboard');
        return redirect($url);
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function marquerToutesLues()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification.
     */
    public function destroy(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();
        return back();
    }

    /**
     * API : nombre de notifications non lues (pour le badge topbar).
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }
}
