<?php

use Intervention\Image\ImageManagerStatic as Image;
use Monolog\Logger;

require_once __DIR__ . '/include/setup/config.php';

class CompressAndConvertImages
{
    public Logger $logger;

    public function __construct(Logger $log)
    {
        $this->logger = $log;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getFiles(): array
    {
        // Ottieni un array contenente tutti i file e le cartelle nella directory
        $files = scandir(INPUT);
        $filesToGet = [];
        $discardedFiles = [];

        if (!$files || empty($files)) {
            throw new Exception("Cartella vuota: " . INPUT);
        }

        // Itera attraverso l'array
        foreach ($files as $file) {
            $file_ext = explode('.', $file);
            $file_ext = end($file_ext);
            if (
                $file !== '.' &&
                $file !== '..' &&
                $file !== '.gitkeep'
            ) {
                if (in_array(strtolower($file_ext), FILE_EXT_ALLOWED)) {
                    $filesToGet[] = [
                        'ext' => $file_ext,
                        'filename' => $file
                    ];
                } else {
                    $discardedFiles[] = $file;
                }
            }
        }
        return [$filesToGet, $discardedFiles];
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        [$files, $discarded] = $this->getFiles();

        if (!empty($discarded)) {
            $this->logger->warning('Discarded files:', $discarded);
        }

        if (empty($files)) {
            throw new Exception('No workable files were found. File extensions allowed are: ', FILE_EXT_ALLOWED);
        }

        foreach ($files as $file) {
            $this->logger->info('Working on: ' . $file['filename']);
            // Ottieni l'istanza di Intervention Image dall'immagine caricata tramite il modulo
            $image = Image::make(INPUT . $file['filename']);
            // Correggi l'orientamento dell'immagine basandoti sul metadata EXIF
            $image->orientate();

            /***************** GESTIONE COMPRESSIONE *****************************/
            $file_name = str_replace(".{$file['ext']}", '', $file['filename']);
            $compressed_filepath = OUTPUT . "$file_name." . EXTENSION;

            // Salva l'immagine compressa su disco
            $image->save($compressed_filepath, QUALITY);

            // Info di DEBUG sui filesizes
            $filesize = filesize(INPUT . $file['filename']);
            $compressed_filesize = filesize($compressed_filepath);
            $this->logger->debug('ORIGINAL FILESIZE: ' . $filesize);
            $this->logger->debug('COMPRESSED FILESIZE: ' . $compressed_filesize);
        }
    }
}

/**
 * From config.php
 *
 * @var $log
 */
$log->info("**** START ****");
try {
    if (!is_dir(INPUT) || !is_dir(OUTPUT)) {
        throw new Exception("Error in directories configuration: check your env file");
    }
    $compressor = new CompressAndConvertImages($log);
    $compressor->run();
    $log->info("**** END ****");
} catch (Exception $e) {
    $log->error($e->getMessage());
    $log->info("**** INTERRUPTED ****");
}
exit();
