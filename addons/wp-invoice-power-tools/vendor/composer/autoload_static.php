<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbef0c226c603606d5f137b7ef7773d8a
{
    public static $classMap = array (
        'UsabilityDynamics\\WPI\\WPI_PT_Bootstrap' => __DIR__ . '/../..' . '/lib/classes/class-WPI_PT_Bootstrap.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitbef0c226c603606d5f137b7ef7773d8a::$classMap;

        }, null, ClassLoader::class);
    }
}