<?php

// Include the Autoloader class to automatically load required classes.
require_once __DIR__ . '/app/core/Autoloader.php';

// Register the Autoloader to enable automatic loading of class files.
core\Autoloader::register();

session_start();

try {
    // Include the routes configuration file to get the route mappings.
    $routes = include 'config/routes.php';

    // Create a new Router instance with the loaded routes.
    $router = new core\Router($routes);

    // Get the current URI
    $uri = trim($_SERVER['REQUEST_URI'], '/');

    // Get the controller and action based on the current URI.
    $controllerAction = $router->getControllerAction($uri);

    if ($controllerAction) {
        // Separate the controller and action from the URI route.
        list($controller, $action) = $controllerAction;

        $controller = 'controllers\\' . ucfirst($controller) . 'Controller';

        // Check if the controller class exists.
        if (!class_exists($controller)) {
            throw new Exception("Controller class $controller not found.");
        }

          // Instantiate the controller class.
        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $action)) {
            throw new Exception("Action $action not found in controller $controller.");
        }

         // Call the specified action method on the controller.
        $controllerInstance->$action();
    } else {
        throw new Exception("Error 404. Page not found");
    }
} catch (Exception $e) {
    // Catch any exceptions and display the error message.
    echo $e->getMessage();
}
// Close the session after processing the request.
session_write_close();
