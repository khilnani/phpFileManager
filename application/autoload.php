<?php

spl_autoload_register( function ($class_name) {
    include './application/'.$class_name . '.php';
});

?>