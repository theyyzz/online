<?php
/**
 * Created by PhpStorm.
 * User: dage
 * Date: 2018/1/22
 * Time: 20:16
 */

namespace App;

class CmdType_Handler
{
    public function __construct($code)
    {
        $route= include_once '../Route/Route.php';
        explode('@',$route[$code]);
    }

}