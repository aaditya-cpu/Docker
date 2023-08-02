<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit68102189be1151f09da7a088b842b5bf
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'AweBooking\\Gallery\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'AweBooking\\Gallery\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit68102189be1151f09da7a088b842b5bf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit68102189be1151f09da7a088b842b5bf::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
