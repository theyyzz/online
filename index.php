<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/19
 * Time: 20:57
 */
use  Underlying\Redis as Redis;
class test extends  Redis
{
    public function index()
    {
        Redis::set('test',11111);
        $opo=Redis::get('test');
        var_dump($opo);
    }

}



