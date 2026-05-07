<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Policies\NotificationPolicy;
use App\Models\NotificationModel;

final class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->requireAuth();
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 15;

        return $this->view('notifications/index', [
            'title' => 'Notifications',
            'notifications' => NotificationModel::listForUser((int) $user['user_id'], $page, $perPage),
            'total' => NotificationModel::countForUser((int) $user['user_id']),
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }

    public function markRead(Request $request, string $id): Response
    {
        $user = $this->requireAuth();
        $notification = NotificationModel::find((int) $id);
        if (! $notification) {
            throw new HttpException(404, 'Notification not found.');
        }
        if (! (new NotificationPolicy())->markRead($user, $notification)) {
            throw new HttpException(403, 'You cannot update this notification.');
        }

        NotificationModel::markRead((int) $id, (int) $user['user_id']);
        Session::flash('status', 'Notification marked as read.');

        return $this->redirect(url('notifications.index'));
    }

    public function markAllRead(Request $request): Response
    {
        $user = $this->requireAuth();
        NotificationModel::markAllRead((int) $user['user_id']);
        Session::flash('status', 'All notifications marked as read.');

        return $this->redirect(url('notifications.index'));
    }
}
