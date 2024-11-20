<?php

namespace core;

use Exception;
use helpers\InventoryManager;
use helpers\NotificationService;

/**
 * Class Controller
 * This class provides methods for rendering views, loading layouts, managing notifications,
 * and redirecting the user to different pages.
 */
class Controller
{

    public function __construct()
    {
        $this->before();
    }

    /**
     * The before method is called automatically when the controller is initialized.
     * Can be overridden in child classes, but should call parent::before() to preserve base functionality.
     */
    protected function before(): void
    {
        InventoryManager::notificationActualization();
    }

    /**
     * Renders a page with the specified view and data.
     *
     * @param string $view The name of the view file to render.
     * @param array $data Data to pass to the view (default is an empty array).
     *
     * @return void
     * @throws Exception If the layout or view files cannot be found.
     */
    protected function render(string $view, array $data = []): void
    {
        $layout = $this->loadLayout();

        $mainContent = $this->renderView($view, $data);
        $renderedMessages = $this->renderMessages();
        $finalContent = str_replace(
            ['{{content}}', '{{messages}}'],
            [$mainContent, $renderedMessages],
            $layout
        );
        echo $finalContent;
    }

    /**
     * Loads the layout file for the page.
     *
     * @return string The layout content.
     * @throws Exception If the layout file is not found.
     */
    private function loadLayout(): string
    {
        $layoutFile = __DIR__ . '/../views/layout.html';

        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found: $layoutFile");
        }

        return file_get_contents($layoutFile);
    }

    /**
     * Renders the view with the provided data.
     *
     * @param string $view The name of the view file to render.
     * @param array $data Data to pass to the view (default is an empty array).
     *
     * @return string The rendered content of the view.
     * @throws Exception If the view file is not found.
     */
    private function renderView(string $view, array $data = []): string
    {

        $controllerName = $this->getControllerName();
        $file = __DIR__ . "/../views/$controllerName/$view.html";

        if (!file_exists($file)) {
            throw new Exception("View file not found: $file");
        }

        ob_start();
        extract($data);
        include $file;
        return ob_get_clean();
    }

    /**
     * Renders the message section (notifications and flash messages).
     *
     * @return string The rendered messages content.
     * @throws Exception If the messages file is not found.
     */
    private function renderMessages(): string
    {
        $file = __DIR__ . "/../views/messages.html";

        if (!file_exists($file)) {
            throw new Exception("Messages file not found: $file");
        }

        $data['notifications'] = NotificationService::getNotifications();
        $data['flash_messages'] = NotificationService::getFlashMessages();

        ob_start();
        extract($data);
        include $file;
        return ob_get_clean();
    }


    /**
     * Returns the name of the current controller in lowercase, without the "Controller" suffix.
     *
     * @return string The controller name in lowercase.
     */
    private function getControllerName(): string
    {
        $className = static::class;
        $baseName = basename(str_replace('\\', '/', $className));
        return strtolower(str_replace('Controller', '', $baseName));
    }


    /**
     * Redirects the user to a specified URL.
     *
     * @param string $url The URL to redirect to.
     *
     * @return void
     */
    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}
