<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd6558e0da51f5e789b7ff9a187ac4c53
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Ahc\\Jwt\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ahc\\Jwt\\' => 
        array (
            0 => __DIR__ . '/..' . '/adhocore/jwt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd6558e0da51f5e789b7ff9a187ac4c53::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd6558e0da51f5e789b7ff9a187ac4c53::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
