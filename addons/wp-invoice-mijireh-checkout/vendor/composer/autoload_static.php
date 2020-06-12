<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit78a35fff1c8f31bdb842da72ccc8179d
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $classMap = array (
        'UsabilityDynamics\\MijirehClient\\Address' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Address.php',
        'UsabilityDynamics\\MijirehClient\\BadRequest' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Mijireh.php',
        'UsabilityDynamics\\MijirehClient\\ClientError' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Mijireh.php',
        'UsabilityDynamics\\MijirehClient\\Exception' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Mijireh.php',
        'UsabilityDynamics\\MijirehClient\\InternalError' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Mijireh.php',
        'UsabilityDynamics\\MijirehClient\\Item' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Item.php',
        'UsabilityDynamics\\MijirehClient\\Mijireh' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Mijireh.php',
        'UsabilityDynamics\\MijirehClient\\Model' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Model.php',
        'UsabilityDynamics\\MijirehClient\\NotFound' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Mijireh.php',
        'UsabilityDynamics\\MijirehClient\\Order' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Order.php',
        'UsabilityDynamics\\MijirehClient\\Rest' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\RestJSON' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/RestJSON.php',
        'UsabilityDynamics\\MijirehClient\\Rest_BadRequest' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_ClientError' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_Conflict' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_Exception' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_Forbidden' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_Gone' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_InvalidRecord' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_MethodNotAllowed' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_NotFound' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_ServerError' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_Unauthorized' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\Rest_UnknownResponse' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Rest.php',
        'UsabilityDynamics\\MijirehClient\\ServerError' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Mijireh.php',
        'UsabilityDynamics\\MijirehClient\\Unauthorized' => __DIR__ . '/..' . '/usabilitydynamics/mijireh-php/lib/classes/Mijireh.php',
        'UsabilityDynamics\\WPI_MC\\Bootstrap' => __DIR__ . '/../..' . '/lib/classes/class-bootstrap.php',
        'UsabilityDynamics\\WPI_MC\\Gateway' => __DIR__ . '/../..' . '/lib/classes/class-gateway.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit78a35fff1c8f31bdb842da72ccc8179d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit78a35fff1c8f31bdb842da72ccc8179d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit78a35fff1c8f31bdb842da72ccc8179d::$classMap;

        }, null, ClassLoader::class);
    }
}
