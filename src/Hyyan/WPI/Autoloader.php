<?php

namespace Hyyan\WPI;

/**
 * Plugin Namespace Autoloader.
 */
final class Autoloader
{
    private string $base;

    /**
     * @throws \Exception
     */
    public function __construct(string $base)
    {
        $this->base = $base;
        spl_autoload_register([$this, 'handle'], true, true);
    }

    public function handle(string $className): ?bool
    {
        if (stripos($className, "Hyyan\WPI") === false) {
            return null;
        }

        $filename = $this->base . str_replace('\\', '/', $className) . '.php';
        
        if (file_exists($filename)) {
            require_once $filename;
            return class_exists($className) || interface_exists($className);
        }

        return false;
    }
}
