<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/19
 * Time: 21:13
 */

namespace App;


use Kernel\Chat_Realization_Handler;


class App
{
    public $server;

    public function __construct()
    {


    }
    /**
     * start App
     *
     *
     */
    public function start()
    {
        define('ALL_START', microtime(true));//开始时间戳
        require_once  __DIR__.'/../Kernel/Chat_Realization_Handler.php';
        $this->server=new Chat_Realization_Handler('127.0.0.1',9502);
        $this->server->on('open',function ($data){
        });
        $this->server->on('message',function ($data){
            var_dump($data);
        });
        $this->server->on('close',function ($data){

        });
        $this->server->start();
    }

    public function push($fd,$mess)
    {
        $this->server->push($fd,$mess);
    }


}