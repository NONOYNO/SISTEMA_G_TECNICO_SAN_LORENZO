<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\FileUploadService;
use App\Services\NotificationService;
use RuntimeException;
use Throwable;

final class NotificationController extends Controller
{
    private NotificationService $notifications;
    private FileUploadService $files;

    public function __construct()
    {
        $this->notifications = new NotificationService();
        $this->files = new FileUploadService();
    }

    public function index(): void
    {
        $userId = auth_id();
        if ($userId === null) {
            abort(403);
        }

        $items = $this->notifications->listForUser($userId);

        foreach ($items as &$item) {
            $item['attachments'] = [];
            if (!empty($item['announcement_id'])) {
                $item['attachments'] = $this->files->listFor('announcement', (int) $item['announcement_id']);
            }
        }
        unset($item);

        $this->view('notifications.index', [
            'title' => 'Notificaciones',
            'notifications' => $items,
            'unreadCount' => $this->notifications->countUnread($userId),
        ]);
    }

    public function markRead(string $id): void
    {
        $userId = auth_id();
        if ($userId === null) {
            abort(403);
        }

        $wantsJson = $this->wantsJson();

        try {
            $this->notifications->markRead((int) $id, $userId);
            $unread = $this->notifications->countUnread($userId);

            if ($wantsJson) {
                $this->json([
                    'success' => true,
                    'message' => 'Notificación marcada como leída.',
                    'data' => [
                        'id' => (int) $id,
                        'unread_count' => $unread,
                    ],
                ]);
            }

            flash('success', 'Notificación marcada como leída.');
            $this->redirect('/notifications');
        } catch (RuntimeException $e) {
            if ($wantsJson) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => null,
                ], 404);
            }
            flash('error', $e->getMessage());
            $this->redirect('/notifications');
        } catch (Throwable) {
            if ($wantsJson) {
                $this->json([
                    'success' => false,
                    'message' => 'No se pudo marcar como leída.',
                    'data' => null,
                ], 500);
            }
            flash('error', 'No se pudo marcar como leída.');
            $this->redirect('/notifications');
        }
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return str_contains($accept, 'application/json')
            || strcasecmp($requestedWith, 'XMLHttpRequest') === 0;
    }
}
