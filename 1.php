<?php
/**
 * Created by PhpStorm.
 * User: dage
 * Date: 2018/2/9
 * Time: 13:46
 */
$config=file('.config');
foreach($config as $key =>$value){
    putenv($value);
}
echo "test";
var_dump(getenv('ALL_NAME'));
