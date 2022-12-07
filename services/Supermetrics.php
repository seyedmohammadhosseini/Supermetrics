<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

spl_autoload_register(function ($class) {
    $filePath = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($filePath)) {
        require_once($filePath);
    }
});

class Supermetrics {

    private static $instances = [];

    public static function get($name) {

        if (!isset(self::$instances[$name])) {
            $reflectionClass = new \ReflectionClass('\\Supermetrics\\' . $name);
            self::$instances[$name] = $reflectionClass->newInstance();
        }

        return self::$instances[$name];
    }

    public static function set($name, $instance) {
        self::$instances[$name] = $instance;
    }
}
