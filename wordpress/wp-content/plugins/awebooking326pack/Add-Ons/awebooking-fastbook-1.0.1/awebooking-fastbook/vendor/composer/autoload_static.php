<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb6d887be92367d18b2177c3118658b45
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'AweBooking\\FastBook\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'AweBooking\\FastBook\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb6d887be92367d18b2177c3118658b45::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb6d887be92367d18b2177c3118658b45::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
