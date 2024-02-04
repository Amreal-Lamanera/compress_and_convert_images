<?php

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
//use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../../vendor/autoload.php';

// create a log channel
$log = new Logger('checkImgsLogger');
//$log->pushHandler(new StreamHandler('./logs/checkImgs.log', Logger::DEBUG));

// Aggiungo un handler per la console
$log->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::DEBUG));

$logRotate = new RotatingFileHandler("./logs/checkImgs.log", 10, Logger::INFO);

$log->pushHandler($logRotate);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/env');
$dotenv->load();

define('QUALITY', $_ENV['QUALITY']);
define('EXTENSION', $_ENV['EXTENSION']);
define('INPUT', __DIR__ . '/../../' . $_ENV['INPUT'] . '/');
define('OUTPUT', __DIR__ . '/../../' . $_ENV['OUTPUT'] . '/');
define('FILE_EXT_ALLOWED', array('jpg', 'jpeg', 'png', 'webp'));
