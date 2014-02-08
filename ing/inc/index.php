<?php

error_reporting(E_ALL);

define('GZ_PATH', dirname(__FILE__));
define('EC_PATH', dirname(GZ_PATH));

// define('INIT_NO_SMARTY', true);

require GZ_PATH.'/Library/function.php';

spl_autoload_register('gz_autoload');

GZ_Api::init();

$url = _GET('url');

$controller = 'index';

$tmp = $url ? array_filter(explode('/', $url)) : array();

$path = GZ_PATH . '/controller';

$tmp = array_values($tmp);

//reset($tmp);
    
$count = count($tmp);
for ($i = 0; $i < $count; $i++) {
    if (!is_dir($path.'/'.$tmp[$i])) {
        break;
    }
    $path .= '/'.$tmp[$i];
}

if (isset($tmp[$i])) {
    $controller = $tmp[$i];
    $i++;
}

$file = $path.'/'.$controller.'.php';

$i && $tmp = array_slice($tmp, $i);

if (file_exists($file)) {
    define('IN_ECS', true);
    require $file;
} else {
    echo $file;
    echo '<br>';
    echo 'api: '.$url.' 缺失';
    //echo $file;exit;
}