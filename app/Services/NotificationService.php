<?php

namespace App\Services;

class NotificationService
{
    protected $sessionKey = 'notifications';

    /**
     * メッセージを追加
     *
     * @param string $message
     * @param string|null $orderCode
     * @param array $additionalData
     * @return void
     */
    public function addNotification(string $message, ?string $orderCode = null, array $additionalData = []): void
    {
        $notifications = session($this->sessionKey, []);

        $notifications[] = array_merge([
            'message' => $message,
            'order_code' => $orderCode,
        ], $additionalData);

        session([$this->sessionKey => $notifications]);
    }

    /**
     * すべてのメッセージを取得
     *
     * @return array
     */
    public function getNotifications(): array
    {
        return session($this->sessionKey, []);
    }

    /**
     * 明確なメッセージ
     *
     * @return void
     */
    public function clearNotifications(): void
    {
        session()->forget($this->sessionKey);
    }
}
