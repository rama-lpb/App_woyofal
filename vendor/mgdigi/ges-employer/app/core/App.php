<?php

// 


namespace App\Core;

class App
{
    private static ?DIContainer $container = null;

    public static function init(): void
    {
        self::$container = DIContainer::getInstance();
    }

    public static function get(string $identifier): object
    {
        if (self::$container === null) {
            self::init();
        }

        return self::$container->get($identifier);
    }

    // public static function getContainer(): DIContainer
    // {
    //     if (self::$container === null) {
    //         self::init();
    //     }

    //     return self::$container;
    // }

    // public static function getDependency(string $key): object
    // {
    //     return self::get($key);
    // }

    // public static function bind(string $abstract, string $concrete): void
    // {
    //     if (self::$container === null) {
    //         self::init();
    //     }

    //     self::$container->bind($abstract, $concrete);
    // }

    // public static function reload(): void
    // {
    //     if (self::$container !== null) {
    //         self::$container->reload();
    //     }
    // }
}

