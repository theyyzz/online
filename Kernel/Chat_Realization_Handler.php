<?php
/**
 * Created by PhpStorm.
 * User: dage
 * Date: 2018/1/20
 * Time: 16:14
 */

namespace Kernel;

use Underlying\Web_Socket;
require_once __DIR__.'/../Underlying/Web_Socket.php';
class Chat_Realization_Handler
{//the class name need  change
    public $server;
    private $func=array();

    /**
     * construct
     * @param string $address
     * @param int $port
     * @return void No value is returned.
     */
    public function __construct($address, $port)
    {
        $this->server=new Web_Socket($address, $port);

    }

    /**
     * push
     * @param string $fd
     * @param string $message
     * @return void No value is returned.
     */
    public function push( $fd, $message)
    {
        $this->server->push($fd, $message);
    }

    /**
     * close
     * @param string $fd
     * @return void No value is returned.
     */
    public function close( $fd)
    {
       $this->server->close($fd);
    }

    public function on($type,$func)
    {
        $this->func[$type]=$func;
    }

    public function start()
    {
        $this->server->run(function ($data){
            $this->func[array_keys($data)[0]]($data);
        });
    }




}