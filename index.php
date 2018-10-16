<?php

require_once 'vendor/autoload.php';

class Autoloader {
    static public function loader($className) {
        $filename = str_replace("\\", '/', $className) . ".php";
        if (file_exists($filename)) {
            include $filename;
            if (class_exists($className)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}

spl_autoload_register('Autoloader::loader');

//function exception_handler($exception) {
//    echo "Uncaught exception: " , $exception->getMessage(), "\n";
//}
//
//set_exception_handler('exception_handler');

$path = $_SERVER['PATH_INFO'];

$url = explode('/', $path);

if (isset($url[0])) {
    if ($url[0] == 'api') {
        $params = array_slice($url, 3);
        $url = array_slice($url, 0, 3);
    } else {
        $params = array_slice($url, 2);
        $url = array_slice($url, 0, 2);
    }
}

$controller = implode('', $url);
try {
    $finalController = 'Controllers\\' . $controller;

    $method = implode('', $params);
    try {
        if(in_array('get', get_class_methods($finalController)) && empty($method)) {
            call_user_func(array($finalController, 'get'));
        } else {
            call_user_func(array($finalController, $method));
        }
    } catch (\Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
    }

} catch (\Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}