<?php
/* Simple admin for small shop system */
error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);
if ( !defined('MITH') ){  define('MITH', 'temp'); }

session_start();

require 'src/flight/Flight.php';
include 'src/Twig/Autoloader.php';
Twig_Autoloader::register();

include_once 'src/medoo.php';
include_once 'incl/common.php';
include_once 'incl/functions.php';

Flight::route('/', function(){
    echo 'hello world!';
});

Flight::route('/roboteyes/*', function(){
    require_once 'roboteye/index.php';
});

Flight::start();