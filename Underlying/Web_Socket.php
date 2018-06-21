<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/01/09 0009
 * Time: 15:10
 */

namespace Underlying;

class Web_Socket {
    private $master;
    private $sockets = array();
    private $fd=array();

    /**
     * construct
     * @param string $address
     * @param int $port
     * @return void No value is returned.
     */
    public function __construct(string $address,int $port)
    {

        $this->master=socket_create(AF_INET, SOCK_STREAM, SOL_TCP)or die("socket_create() failed");

        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)or die("socket_option() failed");

        socket_bind($this->master, $address, $port)or die("socket_bind() failed");

        socket_listen($this->master,20)or die("socket_listen() failed");

        $this->sockets[] = $this->master;
        $this->log("Server Started : ".date('Y-m-d H:i:s'));
        $this->log("Listening on   : ".$address." port ".$port);
        $this->log("Master socket  : ".$this->master);
    }

    /**
     * web_socket start
     * @param  callable $func
     */
    public function run(callable $func)
    {
        while(true){
            $socketArr = $this->sockets;
            $write =$except= NULL;
            socket_select($socketArr, $write, $except, NULL);
            foreach ($socketArr as $socket){
                if ($socket == $this->master){
                    $client = socket_accept($this->master);
                    if ($client > 0){
                        $array=$this->connect($client);//建立连接，阻塞没有处理
                        $func($array);
                    }
                }else{
                    $bytes = @socket_recv($socket,$buffer,2048,0);
                    if ($bytes == 0){
                        $fd= $this->search($socket);
                        $this->close($fd);//断开连接（超时自动断开）
                        $func($array=array('close'=>$fd));
                    } else{
                        $fd=$this->handshake($socket,$buffer);
                        if (!empty($fd)){
                            $array=$this->message($fd,$buffer);
                            $func($array);
                        }
                    }
                }
            }
        }
    }

    /**
     * connect
     * @param resource $socket
     * @return array $fp
     */
    public function connect($socket): array
    {
        array_push($this->sockets, $socket);
        $fd=uniqid();
        $this->fd[$fd]=[
            'socket'=>$socket,
            'handshake'=>false
        ];
        $array=array('open'=>$fd);
        return $array;
    }

    /**
     * handshake
     * @param resource $socket
     * @param string $buffer
     * @return bool $fd
     * */
    private function handshake($socket,string $buffer)
    {
        $fd= $this->search($socket);
        if ($this->fd[$fd]['handshake']===false){
            list($resource, $host, $origin, $key) = $this->header($buffer);
            $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";
            socket_write($socket, $upgrade, strlen($upgrade));
            $this->fd[$fd]['handshake']=true;
        }else{
            return $fd;
        }
    }

    /**
     * message
     * @param string $fd
     * @param string $buffer
     * @return array $array
     */
    public function message(string $fd,string $buffer):array
    {
        //是否有心跳检测
        $buffer = $this->decode($buffer);
        $array=array('message'=>['fd'=>$fd,'data'=>$buffer]);
        return $array;
    }

    /**
     * push
     * @param string $fd
     * @param string $message
     * @return void No value is returned.
     */
    public function push(string $fd,string $message)
    {
        $msg = $this->frame($message);
        socket_write($this->fd[$fd]['socket'], $message, strlen($msg));
    }

    /**
     * close
     * @param string $fd
     * @return void No value is returned.
     */
    public function close(string $fd)
    {
        socket_close($this->fd[$fd]['socket']);
        unset($this->fd[$fd]);
        $this->sockets=array($this->master);
        foreach($this->fd as $v){
            $this->sockets[]=$v['socket'];
        }
    }

    /*
     *  心跳检测
     * 
     * */
    public function pong()
    {

    }

    /**
     * decode1
     * @param string $buffer
     * @return string $decoded
     */
    private function decode(string $buffer): string
    {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;

        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        }
        else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        }
        else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    /**
     * 发送数据格式处理
     * @param string $message
     * @return string $ns
     */
    private function frame(string $message): int
    {
        $a = str_split($message, 125);
        if (count($a) == 1) {
            return "\x81" . chr(strlen($a[0])) . $a[0];
        }
        $ns = "";
        foreach ($a as $o) {
            $ns .= "\x81" . chr(strlen($o)) . $o;
        }
        return $ns;
    }

    /**
     * 握手头信息
     * @param string $req
     * @return array
     */
    private function header(string $req): array
    {
        $r = $h = $o = $key = null;
        if (preg_match("/GET (.*) HTTP/"              ,$req,$match)) { $r = $match[1]; }
        if (preg_match("/Host: (.*)\r\n/"             ,$req,$match)) { $h = $match[1]; }
        if (preg_match("/Origin: (.*)\r\n/"           ,$req,$match)) { $o = $match[1]; }
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/",$req,$match)) { $key = $match[1]; }
        return array($r, $h, $o, $key);
    }

    /**
     * 加密Key
     * @param string $key
     * @return  string $accept
     */
    private function calcKey(string $key): string
    {
        //基于websocket version 13
        $accept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        return $accept;
    }

    /**
     * log
     * @param string $content
     * @param string $addr
     * @return void No value is returned.
     * */
    private function log(string $content,string $addr='websocket_log.txt')
    {
        echo $content."\n";
        $log=fopen($addr,'a');
        fwrite($log,$content."\n");
        fclose($log);
    }

    /**
     * 根据sock在users里面查找相应的$k
     * @param resource $socket
     * @return string or string
     * */
    private function search($socket): string
    {
        foreach ($this->fd as $k=>$v){
            if($socket==$v['socket'])
                return $k;
        }
        return false;
    }

}
