<?php

namespace helpers;

use models\Products;
use models\Sse;

/**
 * SseHelper class provides functionality to handle Server-Sent Events (SSE),
 * allowing real-time updates of product data and messages to clients.
 * It includes methods to continuously send updates for products and notifications
 * through SSE, as well as methods for triggering new updates when data changes.
 *
 * @package helpers
 */
class SseHelper
{
    /**
     * Sends real-time updates via SSE to the client.
     * Continuously sends product data and notifications at regular intervals.
     * The data is sent as JSON, which includes updated product information
     * and any new messages in the system.
     *
     * The method runs in an infinite loop, sending data every 5 seconds.
     *
     * @return void
     */
    public static function sse(): void
    {
        while (true) {
            session_start();
           // $model = new Sse();
           // $triger_data = $model->getById(0);
          //  if ($triger_data['products'] ?? false) {

                echo "data: " . json_encode([
                    'products' => static::productsOnTimeUpdate(),
                    'messages' => static::messagesOnTimeUpdate()
                        ]) . "\n\n";
                   // $model->attributes['products'] = false;
                   // $model->update(0);
          //  }
            ob_flush();
            flush();
            session_write_close();
            sleep(5);
        }
    }

    /**
     * Triggers an update for a specified data type (e.g., products or messages).
     * Marks the given name as true in the Sse model and updates the data.
     *
     * This can be used to notify the SSE loop to push new data updates to the client.
     *
     * @param string $name The name of the data to trigger as updated.
     * @return void
     */
    public static function newUpdates(string $name): void
    {
        $model = new Sse();
        $model->attributes[$name] = true;
        $model->update(0);
    }

    /**
     * Retrieves the most up-to-date list of products from the Products model.
     *
     * @return array List of all products.
     */
    private static function productsOnTimeUpdate(): array
    {
        return Products::getAll();
    }

    /**
     * Retrieves the most recent notifications from the NotificationService.
     *
     * @return array List of notifications.
     */
    private static function messagesOnTimeUpdate(): array
    {
        return NotificationService::getNotifications();
    }
}
