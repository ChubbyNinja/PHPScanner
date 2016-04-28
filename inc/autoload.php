<?php

function my_autoloader($class)
{
    $file = trim($class, '\\');
    $file_array = explode('\\', $file);
    array_shift($file_array);

    $path = BASE_PATH.strtolower(implode('/', $file_array).'.class.php');

    if(is_file($path)) {
        return require_once($path);
    }

    return false;
}

spl_autoload_register('my_autoloader');