<?php

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../include/class/NoFilesException.php';
require_once __DIR__ . '/../../include/class/CompressAndConvertImages.php';
require_once __DIR__ . '/../../include/class/ProcedureCompressAndConvertImages.php';

// create a log channel
$log = new Logger('checkImgsLogger');
// handler to log in console
$log->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM));
// files rotation
$logRotate = new RotatingFileHandler("./logs/checkImgs.log", 10);
// files handler
$log->pushHandler($logRotate);

// dotenv load
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/env');
$dotenv->load();
