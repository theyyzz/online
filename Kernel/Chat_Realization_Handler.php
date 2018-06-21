<?php
/**
 * Created by PhpStorm.
 * User: dage
 * Date: 2018/1/20
 * Time: 16:14
 */

namespace Kernel;
use Underlying\Web_Socket;

class Chat_Realization_Handler
{//the class name need  change
    public $server;

    /**
     * construct
     * @param string $address
     * @param int $port
     * @return void No value is returned.
     */
    public function __construct(string $address,int $port)
    {
        $this->server=new Web_Socket($address, $port);

    }

    /**
     * push
     * @param string $fd
     * @param string $message
     * @return void No value is returned.
     */
    public function push(string $fd,string $message)
    {
        $this->server->push($fd, $message);
    }

    /**
     * close
     * @param string $fd
     * @return void No value is returned.
     */
    public function close(string $fd)
    {
       $this->server->close($fd);
    }

    public function on(string $type,callable $func)
    {
        $this->server->run(function ($data)use ($type,$func){
            if (array_keys($data)[0]=='open'&& $type== 'open'){
                $func($data['open']);
            }elseif (array_keys($data)[0]=='message'&&$type== 'message' ){
                $func($data['message']);
            }elseif (array_keys($data)[0]=='close'&&$type== 'close'){
                $func($data['close']);
            }
        });
    }




}