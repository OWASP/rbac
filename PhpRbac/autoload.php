<?php
spl_autoload_register(function ($class) {

    // a partial filename
    $part = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    // directories where we can find classes
    $dirs = array(
        __DIR__ . DIRECTORY_SEPARATOR . 'src',
        __DIR__ . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'src',
    );

    // go through the directories to find classes
    foreach ($dirs as $dir) {
        
        $file = $dir . DIRECTORY_SEPARATOR . $part;
        if (is_readable($file)) {
            require $file;
            return;
        }
    }
});
