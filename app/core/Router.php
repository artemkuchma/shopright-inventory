<?php

namespace core;

/**
 * The Router class is responsible for mapping incoming URIs to corresponding controller actions.
 * It holds a list of predefined routes and provides a method to retrieve the controller and action
 * for a given URI.
 *
 * The class is initialized with an array of routes, where the keys are URIs and the values are
 * corresponding controller action strings in the format "controller/action".
 */
class Router
{
    private array $routes;

    /**
     * Constructor for the Router class.
     * Initializes the router with a given set of routes.
     *
     * @param array $routes An associative array where keys are URIs and values are controller actions.
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Retrieves the controller and action for a given URI.
     * If a match is found, the method returns an array with the controller and action name.
     * Otherwise, it returns null.
     *
     * @param string $uri The URI for which to find the corresponding controller action.
     *
     * @return array|null An array with the controller and action names, or null if no route matches the URI.
     */
    public function getControllerAction(string $uri): ?array
    {
        return $this->routes[$uri] ? explode('/', $this->routes[$uri]) : null;
    }
}
