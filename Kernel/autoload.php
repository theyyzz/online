<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-8-19 0019
 * Time: 下午 15:39:49
 */

namespace Kernel;


class autoload
{
    public static function getLoader()
    {
        define('ON_LINE', microtime(true));
        spl_autoload_register(function ($class) {
            include  $class . '.php';
        });
    }
}

