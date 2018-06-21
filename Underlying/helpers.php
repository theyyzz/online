<?php
/**
 * Created by PhpStorm.
 * User: dage
 * Date: 2018/2/9
 * Time: 19:28
 */
if (!function_exists('config')){
    /**
     * 系统配置调用函数
     * @param string $key
     * @param string $default
     * @return array | bool
     */
    function config($key, $default = null){
        $value=getenv($key);
        if ($value === false) {
            return $default;
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        return $value;
    }
}
if (!function_exists('set_config')){
    /**
     * set env
     * @param string $path
     */
    function set_config($path){
        $config=file($path.'/.config');
        foreach($config as $key =>$value){
            putenv($value);
        }
    }
}

