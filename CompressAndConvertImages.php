<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
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
     * @throws NoFilesException
     */
    public function getFiles(): array
    {
        // Ottieni un array contenente tutti i file e le cartelle nella directory
        $files = scandir(INPUT);
        $filesToGet = [];
        $discardedFiles = [];

        if (!$files || empty($files)) {
            throw new NoFilesException("Cartella vuota: " . INPUT);
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

    public function handleFileAndSave($file)
    {
        $this->logger->info('Working on: ' . $file['filename']);

        // create new manager instance with desired driver
        $manager = new ImageManager(Driver::class);

        // read image from file system
        $image = $manager->read(INPUT . $file['filename']);

        // encode as the originally read image format
//        $encoded = $image->encode(); // Intervention\Image\EncodedImage

        $file_name = str_replace(".{$file['ext']}", '', $file['filename']);
        $compressed_filepath = OUTPUT . "$file_name." . EXTENSION;

        // encode jpeg as webp format
        $encoded = $image->encodeByPath($compressed_filepath, quality: intval(QUALITY)); // Intervention\Image\EncodedImage
        $encoded->save($compressed_filepath);

        // Info di DEBUG sui filesizes
        $filesize = filesize(INPUT . $file['filename']);
        $compressed_filesize = filesize($compressed_filepath);
        $this->logger->debug('ORIGINAL FILESIZE: ' . $filesize);
        $this->logger->debug('COMPRESSED FILESIZE: ' . $compressed_filesize);
    }

    /**
     * @throws Exception
     * @throws NoFilesException
     */
    public function run()
    {
        [$files, $discarded] = $this->getFiles();

        if (!empty($discarded)) {
            $this->logger->warning('Discarded files:', $discarded);
        }

        if (empty($files)) {
            $extensions = implode(',', FILE_EXT_ALLOWED);
            throw new NoFilesException('No workable files were found. File extensions allowed are: ' . $extensions);
        }

        foreach ($files as $key => $file) {
            $this->logger->info("Step: " . $key + 1 . "/" . count($files));
            $this->handleFileAndSave($file);
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
} catch (NoFilesException | Exception $e) {
    $log->error($e->getMessage());
    $log->warning("**** INTERRUPTED ****");
}
exit();
