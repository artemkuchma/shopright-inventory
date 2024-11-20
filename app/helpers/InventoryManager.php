<?php

namespace helpers;

use models\Orders;
use models\Products;

/**
 * Class OrderProcessor
 *
 * Handles the process of placing an order, including product validation,
 * stock management, order creation, and notification handling.
 */
class InventoryManager
{
    /**
     * Processes an order by checking stock, updating inventory, and creating an order record.
     *
     * @param int $productId The ID of the product to be ordered.
     * @param int $quantity The quantity of the product to be ordered.
     * @return bool True if the order was successfully processed, false otherwise.
     */
    public static function processOrder(int $productId, int $quantity): bool
    {
        $model = new Products();
        $product = $model->getById($productId);

        if (!$product) {
            NotificationService::addFlashMessage('Product not found');
            return false;
        }
        if (!self::checkStock($product, $quantity)) {
            return false;
        }

        if (!self::updateStock($model, $quantity) || !self::createOrder($productId, $quantity)) {
            return false;
        }

        self::handleNotifications($productId, $product);
        return true;
    }

    public static function notificationActualization() :void
    {
        $products = Products::getAll();
        NotificationService::clearNotifications();
        foreach ($products as $product){
            static::checkLowStock($product);
        }
    }

    /**
     * Retrieves a product by its ID.
     *
     * @param int $productId The ID of the product to retrieve.
     * @return array|null The product data as an associative array, or null if not found.
     */
    private static function getProduct(int $productId): ?array
    {
        $model = new Products();
        $product = $model->getById($productId);

        if (!$product) {
            NotificationService::addFlashMessage('Product not found');
        }

        return $product;
    }

    /**
     * Checks if the product has sufficient stock for the requested quantity.
     *
     * @param array $product The product data as an associative array.
     * @param int $quantity The quantity to check.
     * @return bool True if stock is sufficient, false otherwise.
     */
    private static function checkStock(array $product, int $quantity): bool
    {
        if ($product['stock'] < $quantity) {
            NotificationService::addFlashMessage('Not available');
            return false;
        }
        return true;
    }

    /**
     * Updates the stock of the product after the order is processed.
     *
     * @param object $model The model.
     * @param int $quantity The quantity to deduct from the stock.
     * @return bool True if the stock update was successful, false otherwise.
     */
    private static function updateStock(object $model, int $quantity): bool
    {

        $model->attributes['stock'] -= $quantity;

        if (!$model->save()) {
            NotificationService::addFlashMessage('Problems with product reservation');
            return false;
        }
        return true;
    }

    /**
     * Creates an order record.
     *
     * @param int $productId The ID of the product being ordered.
     * @param int $quantity The quantity of the product to be ordered.
     * @return bool True if the order was successfully created, false otherwise.
     */
    private static function createOrder(int $productId, int $quantity): bool
    {
        $order = new Orders();
        if (
            !$order->load(['product_id' => $productId, 'quantity' => $quantity, 'timestamp' => time()])
            || !$order->add()
        ) {
            NotificationService::addFlashMessage('Problems with placing an order');
            return false;
        }
        return true;
    }

    /**
     * Handles notifications and updates related to the order process.
     *
     * @param int $productId The ID of the product being ordered.
     * @param array $product The product data as an associative array.
     * @return void
     */
    private static function handleNotifications(int $productId, array $product): void
    {

        SseHelper::newUpdates('products');
        NotificationService::addFlashMessage('Order added successfully');

        static::checkLowStock($product);
    }

    /**
     * Checks if a product has low stock and triggers a low-stock alert if necessary.
     *
     * This function evaluates the stock level of a given product.
     * If the stock is greater than 0 but less than 5, it sends a notification
     * through the NotificationService.
     *
     * @param array $product The product data, including 'id', 'name', and 'stock'.
     */
    private static function checkLowStock(array $product): void
    {
        if ($product['stock'] < 5 && $product['stock'] > 0) {
            NotificationService::sendLowStockAlert($product['id'], $product['name']);
        }
    }
}
