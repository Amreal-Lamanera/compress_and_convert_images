<?php

use Monolog\Logger;
use Intervention\Image\ImageManagerStatic as Image;

require_once __DIR__ . '/include/setup/config.php';

class CompressAndConvertImages
{
    public Logger $logger;

    public function __construct(Logger $log)
    {
        $this->logger = $log;
    }

    /**
     * @throws Exception
     * @return array
     */
    public function getFiles()
    {
        // Ottieni un array contenente tutti i file e le cartelle nella directory
        $files = scandir(INPUT);
        $filesToGet = [];

        if (!$files || empty($files)) {
            throw new Exception("Cartella vuota: " . INPUT);
        }

        // Itera attraverso l'array
        foreach ($files as $file) {
            $file_ext = explode('.', $file);
            $file_ext = end($file_ext);
            // Ignora i file speciali . e ..
            if (
                $file !== '.' &&
                $file !== '..' &&
                in_array($file_ext, FILE_EXT_ALLOWED)
            ) {
                $filesToGet[] = [
                    'ext' => $file_ext,
                    'filename' => $file
                ];
            }
        }
        return $filesToGet;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $files = $this->getFiles();

        if (empty($files)) {
            throw new Exception('No files were found');
        }

        foreach ($files as $file) {
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
            $this->logger->debug('FILESIZE ORIGINALE: ' . $filesize);
            $this->logger->debug('FILESIZE COMPRESSO: ' . $compressed_filesize);
        }
    }
}

$log->info("**** Inizio procedura ****");
try {
    if (!is_dir(INPUT) || !is_dir(OUTPUT)) {
        throw new Exception("Error in directories configuration: check your env file");
    }
    $compressor = new CompressAndConvertImages($log);
    $compressor->run();
    $log->info("**** Fine procedura ****");
} catch (Exception $e) {
    $log->error($e->getMessage());
    $log->info("**** Procedura interrotta ****");
}
exit();
