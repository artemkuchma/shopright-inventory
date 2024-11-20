<?php

namespace helpers;

/**
 * NotificationService class is responsible for managing notifications and flash messages
 * within the application. It provides methods to send, retrieve, and clear notifications,
 * as well as manage flash messages, which are typically displayed once and then removed.
 *
 * Notifications are stored in the session under the `notifications` key and can be used
 * to alert users of important system events, such as low stock for a product. Flash messages,
 * stored under the `flash_messages` key, are temporary messages that are shown to users once
 * and then deleted after being displayed.
 *
 * The class ensures that the session is properly initialized before interacting with session data,
 * and it handles adding, retrieving, and clearing notifications and flash messages securely.
 *
 * @package helpers
 */
class NotificationService
{
    private const NOTIFICATIONS_KEY = 'notifications';
    private const FLASH_MESSAGES_KEY = 'flash_messages';

    /**
     * Ensures the session is initialized and the required keys are set.
     *
     * @return void
     */
    private static function ensureSessionInitialized(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION[self::NOTIFICATIONS_KEY] ??= [];
        $_SESSION[self::FLASH_MESSAGES_KEY] ??= [];
    }

    /**
     * Adds a notification about low stock for a given product ID.
     *
     * @param string $productName The name of the product with low stock.
     * @param int $productId The ID of the product with low stock.
     * @return void
     */
    public static function sendLowStockAlert(int $productId, string $productName): void
    {
        self::ensureSessionInitialized();
        session_start();
        $message = "Product with name <b> {$productName} </b> has low stock. " . date('Y-m-d H:i:s');
        $_SESSION[self::NOTIFICATIONS_KEY][$productId] = $message;
        session_write_close();
    }

    /**
     * Retrieves all notifications from the session.
     *
     * @return array List of all notifications.
     */
    public static function getNotifications(): array
    {
        self::ensureSessionInitialized();
        return $_SESSION[self::NOTIFICATIONS_KEY];
    }

    /**
     * Clears all notifications from the session.
     *
     * @return void
     */
    public static function clearNotifications(): void
    {
        self::ensureSessionInitialized();
        $_SESSION[self::NOTIFICATIONS_KEY] = [];
    }

    /**
     * Adds a flash message to the session.
     * Flash messages are meant to be displayed once and then removed.
     *
     * @param string $message The flash message to add.
     * @return void
     */
    public static function addFlashMessage(string $message): void
    {
        self::ensureSessionInitialized();
        $_SESSION[self::FLASH_MESSAGES_KEY][] = $message;
    }

    /**
     * Retrieves and clears all flash messages from the session.
     *
     * @return array List of flash messages.
     */
    public static function getFlashMessages(): array
    {
        self::ensureSessionInitialized();
        return array_splice($_SESSION[self::FLASH_MESSAGES_KEY], 0);
    }
}
