<?php

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../include/class/NoFilesException.php';

// create a log channel
$log = new Logger('checkImgsLogger');

// Aggiungo un handler per la console
$log->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::DEBUG));

$logRotate = new RotatingFileHandler("./logs/checkImgs.log", 10, Logger::INFO);

$log->pushHandler($logRotate);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/env');
$dotenv->load();
