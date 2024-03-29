<?php

/**
 * From config.php
 *
 * @var Monolog\Logger $log
 */

use FPDEV\Images\NoFilesException;

require_once __DIR__ . '/include/setup/config.php';

try {
    ini_set('memory_limit', '512M');
    $log->info("**** START ****");

    // check mandatory params in env
    $mandatory_params = [
        'INPUT',
        'OUTPUT',
        'QUALITY',
        'EXTENSION',
    ];
    foreach ($mandatory_params as $mandatory_param) {
        if (!isset($_ENV[$mandatory_param])) {
            throw new Exception(
                'Mandatory param missed in env: ' .
                    $mandatory_param
            );
        }
    }
    $fl_zip = getFlagZip();

    // run procedure
    $procedure = new ProcedureCompressAndConvertImages(
        $log,
        __DIR__ . '/' . $_ENV['INPUT'],
        __DIR__ . '/' . $_ENV['OUTPUT'],
        $fl_zip
    );
    $procedure->run(
        $_ENV['EXTENSION'],
        intval($_ENV['QUALITY']),
    );

    $log->info("**** END ****");
} catch (NoFilesException | Exception $e) {
    $log->error($e->getMessage());
    $log->warning("**** INTERRUPTED ****");
}
exit();

/**
 * Check FL_ZIP in env
 *
 * @return bool
 * @throws Exception
 */
function getFlagZip(): bool
{
    if (!isset($_ENV['FL_ZIP']) || strtolower($_ENV['FL_ZIP']) === 'false') {
        $fl_zip = false;
    } elseif (isset($_ENV['FL_ZIP']) && strtolower($_ENV['FL_ZIP']) === 'true') {
        $fl_zip = true;
    } else {
        throw new Exception(
            "Env var FL_ZIP value not valid. Set it to 'true' or 'false'"
        );
    }
    return $fl_zip;
}
