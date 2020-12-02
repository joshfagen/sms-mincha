<?php
if (!defined('BASE_URL'))
define( 'BASE_URL' , (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/");

//session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$username = getenv('DDEV_TLD') ? "db": "USERNAME" ;
$password = getenv('DDEV_TLD') ? "db": "PASS" ;
$host = getenv('DDEV_TLD') ? "db": "PATH_TO_DB";
$dbname = getenv('DDEV_TLD') ? "db": "DBNAME" ;
$connection = new PDO( 'mysql:host='.$host.';dbname='.$dbname, $username, $password );
