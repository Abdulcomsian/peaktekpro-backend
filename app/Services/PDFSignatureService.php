<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PDFSignatureService
{
    private $pythonScriptPath;
    private $pythonExecutable;
    private $outputDirectory;

    public function __construct()
    {
        // Configure these paths according to your setup
        $this->pythonScriptPath = base_path('scripts/app.py');
        $this->pythonExecutable = 'python3'; // or 'python' depending on your system
        $this->outputDirectory = storage_path('app/signatures');
        
        // Create output directory if it doesn't exist
        if (!file_exists($this->outputDirectory)) {
            mkdir($this->outputDirectory, 0755, true);
        }
    }

    /**
     * Extract signatures from a PDF file
     *
     * @param string $pdfPath Path to the PDF file
     * @param array $options Options for extraction
     * @return array
     * @throws Exception
     */
    public function extractSignatures($pdfPath, $options = [])
    {
        // Validate PDF file exists
        if (!file_exists($pdfPath)) {
            throw new Exception("PDF file not found: {$pdfPath}");
        }

        // Default options
        $defaultOptions = [
            'include_base64' => true,
            'save_images' => true,
            'output_dir' => $this->outputDirectory,
            'quiet' => false
        ];

        $options = array_merge($defaultOptions, $options);

        // Build command
        $command = $this->buildCommand($pdfPath, $options);

        Log::info("Executing PDF signature extraction", [
            'command' => $command,
            'pdf_path' => $pdfPath
        ]);

        // Execute command
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        // Check if command executed successfully
        if ($returnCode !== 0) {
            $errorMessage = "Python script execution failed with return code: {$returnCode}";
            if (!empty($output)) {
                $errorMessage .= ". Output: " . implode("\n", $output);
            }
            throw new Exception($errorMessage);
        }

        // Parse JSON output
        $jsonOutput = implode("\n", $output);
        $result = json_decode($jsonOutput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse JSON output: " . json_last_error_msg());
        }

        if (!$result['success']) {
            throw new Exception("Signature extraction failed: " . ($result['error'] ?? 'Unknown error'));
        }

        return $result;
    }

    /**
     * Extract signatures from uploaded file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function extractSignaturesFromUpload($file, $options = [])
    {
        // Validate file
        if (!$file->isValid()) {
            throw new Exception("Invalid file upload");
        }

        if ($file->getClientOriginalExtension() !== 'pdf') {
            throw new Exception("Only PDF files are supported");
        }

        // Store file temporarily
        $tempPath = $file->store('temp', 'local');
        $fullPath = Storage::path($tempPath);

        try {
            // Extract signatures
            $result = $this->extractSignatures($fullPath, $options);
            
            // Add original filename to result
            $result['original_filename'] = $file->getClientOriginalName();
            
            return $result;
        } finally {
            // Clean up temporary file
            Storage::delete($tempPath);
        }
    }

    /**
     * Build the command to execute the Python script
     *
     * @param string $pdfPath
     * @param array $options
     * @return string
     */
    private function buildCommand($pdfPath, $options)
    {
        $command = escapeshellcmd($this->pythonExecutable) . ' ' . escapeshellarg($this->pythonScriptPath);
        $command .= ' ' . escapeshellarg($pdfPath);

        if (!empty($options['output_dir'])) {
            $command .= ' --output-dir ' . escapeshellarg($options['output_dir']);
        }

        if (!$options['include_base64']) {
            $command .= ' --no-base64';
        }

        if (!$options['save_images']) {
            $command .= ' --no-save';
        }

        if ($options['quiet']) {
            $command .= ' --quiet';
        }

        return $command;
    }

    /**
     * Get the path to the Python script
     *
     * @return string
     */
    public function getPythonScriptPath()
    {
        return $this->pythonScriptPath;
    }

    /**
     * Set the path to the Python script
     *
     * @param string $path
     * @return void
     */
    public function setPythonScriptPath($path)
    {
        $this->pythonScriptPath = $path;
    }

    /**
     * Get the Python executable path
     *
     * @return string
     */
    public function getPythonExecutable()
    {
        return $this->pythonExecutable;
    }

    /**
     * Set the Python executable path
     *
     * @param string $executable
     * @return void
     */
    public function setPythonExecutable($executable)
    {
        $this->pythonExecutable = $executable;
    }

    /**
     * Extract signatures from a PDF file downloaded from URL
     *
     * @param string $url URL to the PDF file
     * @param array $options Options for extraction
     * @return array
     * @throws Exception
     */
    public function extractSignaturesFromUrl($url, $options = [])
    {
        // Download the file
        $fileContent = file_get_contents($url);
        
        if ($fileContent === false) {
            throw new Exception("Failed to download PDF from URL: {$url}");
        }

        // Create a temporary file
        $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_signature_') . '.pdf';
        
        if (file_put_contents($tempFilePath, $fileContent) === false) {
            throw new Exception("Failed to create temporary file");
        }

        try {
            // Extract signatures
            $result = $this->extractSignatures($tempFilePath, $options);
            
            // Add URL info to result
            $result['source_url'] = $url;
            
            return $result;
            
        } finally {
            // Clean up temporary file
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }
}