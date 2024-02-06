<?php

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Class CompressAndConvertImages
 * @author Francesco Pieraccini
 */
class CompressAndConvertImages
{
    private int $quality;
    private string $extension;

    private const FILE_EXT_ALLOWED = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    /**
     * CompressAndConvertImages constructor.
     *
     * @param string $extension     - extension to convert
     * @param int $quality          - quality to compress
     *
     * @throws Exception
     */
    public function __construct(
        string $extension,
        int $quality
    )
    {
        $this->quality = $quality;
        $this->extension = $extension;
    }

    /**
     * Getter for FILE_EXT_ALLOWED.
     *
     * @return string[]
     */
    public function getFileExtAllowed(): array
    {
        return self::FILE_EXT_ALLOWED;
    }

    /**
     * Scan the $dir and return an array with acceptedFiles
     * and discardedFiles.
     *
     * @param string $dir       - path/to/dir to scan
     *
     * @return array
     * @throws NoFilesException
     */
    public function getFiles(string $dir): array
    {
        $files = scandir($dir);
        $acceptedFiles = [];
        $discardedFiles = [];

        if (!$files || empty($files)) {
            throw new NoFilesException(
                "Empty directory: {$dir}");
        }

        foreach ($files as $file) {
            $file_ext = explode('.', $file);
            $file_ext = end($file_ext);
            if (
                $file !== '.' &&
                $file !== '..' &&
                $file !== '.gitkeep'
            ) {
                if (
                in_array(strtolower($file_ext), self::FILE_EXT_ALLOWED)
                ) {
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
     * @param array $file           - [filename, ext]
     * @param string $input_dir     - path/to/dir with original files
     * @param string $output_dir    - path/to/dir where to put output files
     */
    public function handleFileAndSave(
        array $file,
        string $input_dir,
        string $output_dir
    ) {
        // create new manager instance with desired driver
        $manager = new ImageManager(Driver::class);

        // read image from file system
        $image = $manager->read(
            $input_dir . '/' . $file['filename']
        );

        $file_name =
            str_replace(".{$file['ext']}", '', $file['filename']);
        $compressed_filepath =
            $output_dir . "/$file_name." . $this->extension;

        // encode img by path
        $encoded = $image->encodeByPath(
            $compressed_filepath,
            quality: $this->quality
        );
        $encoded->save($compressed_filepath);
    }

    /**
     * Make the zip file that contains all the images in $dirWithFiles.
     * Zip will be saved in the same directory.
     *
     * @param string $dirWithFiles  - path/to/dir with files to zip
     *
     * @return string               - zip filename
     * @throws Exception
     */
    public function zipFiles(string $dirWithFiles): string
    {
        $zip = new ZipArchive();
        $zip_filename = 'IMGS_' . date("Ymd_His") . ".zip";
        $zip_filepath = $dirWithFiles . "/$zip_filename";

        if (
        !$zip->open(
            $zip_filepath,
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        )
        ) {
            throw new Exception(
                "ZIP file creation failed."
            );
        }

        $files = scandir($dirWithFiles);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
                $zip->addFile($dirWithFiles . "/$file", $file);
            }
        }

        $zip->close();

        return $zip_filename;
    }

    /**
     * Delete all the files in $dir excepted zip files.
     * Call this if you want to clean the output from all compressed images,
     * but maintain the zip file.
     *
     * @param $dir
     */
    public function removeFilesFromDir($dir)
    {
        $files = scandir($dir);

        foreach ($files as $file) {
            if (
                $file !== '.' &&
                $file !== '..' &&
                $file !== '.gitkeep' &&
                !str_contains($file, '.zip')
            ) {
                unlink($dir . '/' . $file);
            }
        }
    }
}
