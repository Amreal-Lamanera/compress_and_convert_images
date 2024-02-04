# Compress And Convert Images
Hello there! This is a simple utility to compress and convert your photos or images.

## Requirements
- Composer and PHP installed on your computer.
- Download this repository.

## Usage
1. Set up your environment by creating a `.env` file based on the `.env_example` provided. Choose the quality and the desired output extension.
2. Install the dependencies of this utility by running `composer install`.
3. Place the files you want to compress and convert in the `input_files` directory.
4. Run the utility in the terminal with: `php CompressAndConvertImages.php`.
5. You will find the output in `output_files` directory and the logs of the utility in `logs` and in your terminal window.

### Examples
- To convert your images without compression, set the EXTENSION in the .env file to your desired format (e.g., "WEBP") and leave the QUALITY as 100.
- To convert and compress your images to WEBP format with 20% compression, set the EXTENSION in the .env file to WEBP and adjust the QUALITY to 20.

If you encounter any issues or have questions, feel free to reach out!
