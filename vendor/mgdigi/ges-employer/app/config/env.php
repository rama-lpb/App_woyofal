<?php
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();


define('DB_USER', $_ENV['DB_USER']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
define('APP_URL', $_ENV['APP_URL']);
define('DSN', $_ENV['DSN'] );