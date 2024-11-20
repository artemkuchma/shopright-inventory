<?php

namespace core;

/**
 * Class Autoloader
 * This class handles the automatic loading of classes when they are needed.
 * It registers an autoloader function using `spl_autoload_register`.
 */
class Autoloader
{
    /**
     * Registers the autoloader function to be called when a class is instantiated.
     * The function will attempt to locate the class file based on the class name
     * by replacing backslashes with the directory separator and looking for
     * the corresponding file in the project directory.
     *
     * @return void
     * @throws \Exception If the class file cannot be found.
     */
    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            $path = __DIR__ . '/../' . $class . '.php';
            if (file_exists($path)) {
                require_once $path;
            } else {
                throw new \Exception("Class $class not found at path $path.");
            }
        });
    }
}
