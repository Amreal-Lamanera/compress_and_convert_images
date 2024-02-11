# Compress And Convert Images <a href="https://www.francescopieraccini.it/"><img src="./light_blue_nome.svg"  alt="FrancescoPieraccini Logo" width="75" style="vertical-align:middle; padding-left:1rem;"/></a>
Hello there! This is a simple utility to compress and convert your photos or images.
It uses my library FP\compress_and_convert_images_library, you can check it
[here](https://github.com/Amreal-Lamanera/compress_and_convert_images_library)

If you encounter any issues or have questions, feel free to reach out! And don't forget to check out my website at: [Francesco Pieraccini WebSite](https://www.francescopieraccini.it)

## Requirements
- PHP (>=8.1) installed on your computer (you can check this: https://www.php.net/manual/en/install.php).
- Composer, you can install it in your computer globally or download `composer.phar` and add it to the project
  (check this: https://getcomposer.org/).
- Download this repository.

## Usage
1. Set up your environment by creating a `.env` file based on the `.env_example` provided. 
   Choose the quality and the desired output extension.
2. Install the dependencies of this utility by running `composer install` or `php composer.phar install`.
3. Place the files you want to compress and convert in the `input_files` directory.
4. Run the utility in the terminal with: `php procedureRun.php`.
5. You will find the output in `output_files` directory and the logs of the utility 
   in `logs` and in your terminal window.

### Examples
- To convert your images without compression, set the `EXTENSION` in the .env file to your desired format 
  (e.g., "webp") and leave the `QUALITY` as `100`.
- To convert and compress your images to WEBP format with 20% compression, set the `EXTENSION` in the `.env` 
  file to `webp` and adjust the `QUALITY` to `20`.