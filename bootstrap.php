<?php

$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once($vendorAutoload);
} else {
    spl_autoload_register(function ($class) {
        $file = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
        if (!file_exists($file)) {
            throw new RuntimeException("Class $class not found");
        }
        require_once($file);
    });
}