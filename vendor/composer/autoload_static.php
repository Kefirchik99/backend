<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2f5e1f4e85df18b6a929a6731d0ee264
{
    public static $files = array (
        '253c157292f75eb38082b5acb06f3f01' => __DIR__ . '/..' . '/nikic/fast-route/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'Y' => 
        array (
            'Yaro\\EcommerceProject\\' => 22,
        ),
        'G' => 
        array (
            'GraphQL\\' => 8,
        ),
        'F' => 
        array (
            'FastRoute\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Yaro\\EcommerceProject\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'GraphQL\\' => 
        array (
            0 => __DIR__ . '/..' . '/webonyx/graphql-php/src',
        ),
        'FastRoute\\' => 
        array (
            0 => __DIR__ . '/..' . '/nikic/fast-route/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2f5e1f4e85df18b6a929a6731d0ee264::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2f5e1f4e85df18b6a929a6731d0ee264::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2f5e1f4e85df18b6a929a6731d0ee264::$classMap;

        }, null, ClassLoader::class);
    }
}
