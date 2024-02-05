<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Monolog\Logger;

require_once __DIR__ . '/include/setup/config.php';

class CompressAndConvertImages
{
    private string $input_dir;
    private string $output_dir;
    private string $quality;
    private string $extension;
    private bool $fl_zip;
    private Logger $logger;

    const FILE_EXT_ALLOWED = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    /**
     * CompressAndConvertImages constructor.
     *
     * @param Logger $log
     * @throws Exception
     */
    public function __construct(Logger $log)
    {
        $mandatory_params = [
            'INPUT',
            'OUTPUT',
            'QUALITY',
            'EXTENSION',
        ];

        foreach ($mandatory_params as $mandatory_param) {
            if (!isset($_ENV["$mandatory_param"])) {
                throw new Exception(
                    'Mandatory param missed in env: ' .
                    $mandatory_param
                );
            }
        }

        $this->input_dir = __DIR__ . '/' . $_ENV['INPUT'] . '/';
        $this->output_dir = __DIR__ . '/' . $_ENV['OUTPUT'] . '/';

        if (!is_dir($this->input_dir) || !is_dir($this->output_dir)) {
            throw new Exception("Error in directories configuration: check your env file");
        }

        $this->logger = $log;
        $this->quality = $_ENV['QUALITY'];
        $this->extension = $_ENV['EXTENSION'];
        $this->fl_zip =
            (isset($_ENV['FL_ZIP']) ? boolval($_ENV['FL_ZIP']) : false);
    }

    /**
     * Scan the input dir and return an array with acceptedFiles and discardedFiles.
     *
     * @return array
     * @throws NoFilesException
     */
    private function getFiles(): array
    {
        $files = scandir($this->input_dir);
        $acceptedFiles = [];
        $discardedFiles = [];

        if (!$files || empty($files)) {
            throw new NoFilesException("Cartella vuota: " . $this->input_dir);
        }

        foreach ($files as $file) {
            $file_ext = explode('.', $file);
            $file_ext = end($file_ext);
            if (
                $file !== '.' &&
                $file !== '..' &&
                $file !== '.gitkeep'
            ) {
                if (in_array(strtolower($file_ext), self::FILE_EXT_ALLOWED)) {
                    $acceptedFiles[] = [
                        'ext' => $file_ext,
                        'filename' => $file
                    ];
                } else {
                    $discardedFiles[] = $file;
                }
            }
        }
        return [$acceptedFiles, $discardedFiles];
    }

    /**
     * A function that utilizes ImageManager to convert and compress the file.
     * Once completed, the compressed file will be saved in the OUTPUT directory.
     *
     * @param $file
     */
    private function handleFileAndSave($file)
    {
        $this->logger->info('Working on: ' . $file['filename']);

        // create new manager instance with desired driver
        $manager = new ImageManager(Driver::class);

        // read image from file system
        $image = $manager->read($this->input_dir . $file['filename']);

        // encode as the originally read image format
//        $encoded = $image->encode(); // Intervention\Image\EncodedImage

        $file_name = str_replace(".{$file['ext']}", '', $file['filename']);
        $compressed_filepath = $this->output_dir . "$file_name." . $this->extension;

        // encode img by path
        $encoded = $image->encodeByPath($compressed_filepath, quality: intval($this->quality)); // Intervention\Image\EncodedImage
        $encoded->save($compressed_filepath);

        // Info di DEBUG sui filesizes
        $filesize = filesize($this->input_dir . $file['filename']);
        $compressed_filesize = filesize($compressed_filepath);
        $this->logger->debug('ORIGINAL FILESIZE: ' . $filesize);
        $this->logger->debug('COMPRESSED FILESIZE: ' . $compressed_filesize);
    }

    /**
     * Make the zip file that contains all the images in output folder.
     *
     * @return string
     * @throws Exception
     */
    private function zipFiles(): string
    {
        $zip = new ZipArchive();
        $zip_filename = 'IMGS_' . date("Ymd_His") . ".zip";
        $zip_filepath = $this->output_dir . $zip_filename;

        if (!$zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new Exception("Errore durante la creazione del file ZIP");
        }

        $files = scandir($this->output_dir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
                $zip->addFile($this->output_dir . $file, $file);
            }
        }

        $zip->close();

        return $zip_filename;
    }

    /**
     * Delete all the files in $dir.
     *
     * @param $dir
     */
    private function removeFilesFromDir($dir) {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && $file !== '.gitkeep' && !str_contains($file, '.zip')) {
                unlink($dir . '/' . $file);
            }
        }
    }

    /**
     * Procedure run function.
     *
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
            $extensions = implode(',', self::FILE_EXT_ALLOWED);
            throw new NoFilesException('No workable files were found. File extensions allowed are: ' . $extensions);
        }

        foreach ($files as $key => $file) {
            $this->logger->info("Step: " . $key + 1 . "/" . count($files));
            $this->handleFileAndSave($file);
        }

        if ($this->fl_zip) {
            $this->logger->info("Zipping files...");
            $this->logger->info("Zip file created: " . $this->zipFiles());
            $this->removeFilesFromDir($this->output_dir);
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
    $compressor = new CompressAndConvertImages($log);
    $compressor->run();
    $log->info("**** END ****");
} catch (NoFilesException | Exception $e) {
    $log->error($e->getMessage());
    $log->warning("**** INTERRUPTED ****");
}
exit();
