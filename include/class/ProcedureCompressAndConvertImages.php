<?php

use Monolog\Logger;

/**
 * Class ProcedureCompressAndConvertImages
 * @author Francesco Pieraccini
 */
class ProcedureCompressAndConvertImages
{
    private Logger $logger;
    private string $input_dir;
    private string $output_dir;
    private bool $fl_zip;

    /**
     * ProcedureCompressAndConvertImages constructor.
     *
     * @param Logger $log   - logger
     * @param $input_dir    - path/to/dir with files to manage
     * @param $output_dir   - path/to/dir where to save outputs
     * @param $fl_zip       - flag zip. If true save only a zip file in
     *                          the output directory, if false save all
     *                          compressed and converted files in the output directory.
     *
     * @throws Exception
     */
    public function __construct(
        Logger $log,
        $input_dir,
        $output_dir,
        $fl_zip
    ) {
        $this->logger = $log;
        $this->fl_zip = $fl_zip;
        $this->input_dir = $input_dir;
        $this->output_dir = $output_dir;

        if (!is_dir($this->input_dir) || !is_dir($this->output_dir)) {
            throw new Exception(
                "Error in directories configuration: check your env file"
            );
        }
    }

    /**
     * Procedure run function.
     *
     * @param string $extension - extension to convert
     * @param int $quality      - quality to compress
     *
     * @throws NoFilesException
     * @throws Exception
     */
    public function run(
        string $extension,
        int $quality,
    ) {
        $compressor = new CompressAndConvertImages(
            $extension,
            $quality
        );
        [$files, $discarded] = $compressor->getFiles($this->input_dir);

        if (!empty($discarded)) {
            $this->logger->warning('Discarded files:', $discarded);
        }

        if (empty($files)) {
            $extensions = implode(', ', $compressor->getFileExtAllowed());
            throw new NoFilesException(
                'No workable files were found. ' .
                "File extensions allowed are: $extensions");
        }

        foreach ($files as $key => $file) {
            $this->logger->info(
                "Step: " . $key + 1 . "/" . count($files)
            );
            $this->logger->info(
                "Working on: {$file['filename']}"
            );
            $compressor->handleFileAndSave(
                $file,
                $this->input_dir,
                $this->output_dir
            );
        }
        $this->logger->info(
            "All files are converted and compressed!"
        );

        if ($this->fl_zip) {
            $this->logger->info("Zipping files...");
            $this->logger->info(
                "Zip file created: " .
                $compressor->zipFiles($this->output_dir)
            );
            $compressor->removeFilesFromDir($this->output_dir);
        }

    }
}
