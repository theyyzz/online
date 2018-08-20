<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-8-20 0020
 * Time: 下午 22:17:00
 */

namespace Kernel;


class Application
{
    public function __construct()
    {
        require __DIR__.'/autoload.php';//启动自动加载
        autoload::getLoader();
    }
}