<?php

spl_autoload_register( function ($class_name) {
    include './app/'.$class_name . '.php';
});

?>