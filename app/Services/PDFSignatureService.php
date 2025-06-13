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
        $this->pythonExecutable = '/var/www/html/backend/scripts/venv/bin/python'; // or 'python3' depending on your system
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
        'output_format' => 'json',
        'quiet' => true,
        'strict_mode' => true,  // Default to strict mode for fewer false positives
        'debug' => false
    ];

    $options = array_merge($defaultOptions, $options);

    // Build command for the new Python script
    $command = $this->buildCommand($pdfPath, $options);

    Log::info("Executing PDF signature extraction", [
        'command' => $this->sanitizeCommandForLog($command),
        'pdf_path' => basename($pdfPath),
        'mode' => $options['strict_mode'] ? 'strict' : 'lenient'
    ]);

    // Execute command
    $output = [];
    $returnCode = 0;
    //exec($command . ' 2>&1', $output, $returnCode);
$command = escapeshellcmd($this->pythonExecutable) . ' ' . escapeshellarg($this->pythonScriptPath) . ' ' . escapeshellarg($pdfPath);

    // Execute command
    $output = shell_exec($command);
    // $jsonOutput = implode("\n", $output);
     $result = json_decode($output, true);
     return $result;
    // Check if command executed successfully
    if ($returnCode !== 0) {
        $errorMessage = "Python script execution failed with return code: {$returnCode}";
        if (!empty($output)) {
            $errorMessage .= ". Output: " . implode("\n", $output);
        }
        Log::error("PDF signature extraction failed", [
            'return_code' => $returnCode,
            'output' => $output,
            'pdf_path' => basename($pdfPath)
        ]);
        throw new Exception($errorMessage);
    }

    // Parse JSON output
    $jsonOutput = implode("\n", $output);
    
    // Try to extract JSON from output (in case there are extra messages)
    if (preg_match('/\{.*\}/s', $jsonOutput, $matches)) {
        $jsonOutput = $matches[0];
    }

    $result = json_decode($jsonOutput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        Log::error("Failed to parse JSON output", [
            'json_error' => json_last_error_msg(),
            'output' => $jsonOutput
        ]);
        throw new Exception("Failed to parse JSON output: " . json_last_error_msg());
    }

    if (!$result['success']) {
        throw new Exception("Signature extraction failed: " . ($result['error'] ?? 'Unknown error'));
    }

    // Transform the result to match your expected format
    return $this->transformResult($result);
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

        $options = [
    'strict_mode' => true,   // Only high-confidence signatures
    'quiet' => true,         // No console output
    'include_base64' => true, // Don't generate base64 images
    'save_images' => false    // Don't save images to disk
];

// For detailed analysis
// $options = [
//     'strict_mode' => false,  // Include all possible signatures
//     'debug' => true,         // Show detailed info
//     'include_base64' => true,
//     'save_images' => true
// ];
        // For test mode files (created from existing files), validation is different
        $isTestMode = $file->getError() === UPLOAD_ERR_OK || $file->getPathname();
        
        // Validate file exists
        if (!$isTestMode && !$file->isValid()) {
            throw new Exception("Invalid file upload");
        }

        // Check if file exists on filesystem (for both uploaded and existing files)
        $filePath = $file->getPathname() ?: $file->getRealPath();
        if (!file_exists($filePath)) {
            throw new Exception("File not found: " . $filePath);
        }

        // Validate PDF extension
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'pdf') {
            throw new Exception("Only PDF files are supported. Got: " . $extension);
        }

        // For existing files, use the file directly
        if (file_exists($filePath) && is_readable($filePath)) {
            try {
                // Extract signatures directly from the file path
                $result = $this->extractSignatures($filePath, $options);
                
                // Add file metadata to result
                $result['original_filename'] = $file->getClientOriginalName();
                $result['file_size'] = filesize($filePath);
                $result['processed_at'] = now()->toISOString();
                $result['file_path'] = basename($filePath); // Only basename for security
                
                return $result;
            } catch (Exception $e) {
                throw new Exception("Failed to process PDF: " . $e->getMessage());
            }
        }

        // Fallback: Store file temporarily (for actual uploads)
        $tempPath = $file->store('temp', 'local');
        $fullPath = Storage::path($tempPath);

        try {
            // Extract signatures
            $result = $this->extractSignatures($fullPath, $options);
            
            // Add original filename to result
            $result['original_filename'] = $file->getClientOriginalName();
            $result['file_size'] = $file->getSize();
            $result['processed_at'] = now()->toISOString();
            
            return $result;
        } finally {
            // Clean up temporary file
            if (Storage::exists($tempPath)) {
                Storage::delete($tempPath);
            }
        }
    }

    /**
     * Check if PDF has signatures (simple boolean check)
     *
     * @param string $pdfPath
     * @return bool
     * @throws Exception
     */
    public function hasSignatures($pdfPath)
    {
        $result = $this->extractSignatures($pdfPath, ['quiet' => true]);
        return $result['count'] > 0;
    }

    /**
     * Check if uploaded file has signatures
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool
     * @throws Exception
     */
    public function uploadHasSignatures($file)
    {
        $result = $this->extractSignaturesFromUpload($file, ['quiet' => true]);
        return $result['count'] > 0;
    }

    /**
     * Get signature summary (count and basic info only)
     *
     * @param string $pdfPath
     * @return array
     * @throws Exception
     */
    public function getSignatureSummary($pdfPath)
    {
        $result = $this->extractSignatures($pdfPath);
        
        return [
            'has_signatures' => $result['count'] > 0,
            'signature_count' => $result['count'],
            'signatures' => array_keys($result['signatures']),
            'message' => $result['message']
        ];
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
    // Use the updated script with --file argument
    $command = escapeshellcmd($this->pythonExecutable) . ' ' . escapeshellarg($this->pythonScriptPath);
    $command .= ' --file ' . escapeshellarg($pdfPath);

    // Add quiet flag if specified
    if (isset($options['quiet']) && $options['quiet']) {
        $command .= ' --quiet';
    }

    // Add strict/lenient mode
    if (isset($options['strict_mode']) && $options['strict_mode'] === false) {
        $command .= ' --lenient';
    } else {
        // Default to strict mode
        $command .= ' --strict';
    }

    // Add debug flag if needed
    if (isset($options['debug']) && $options['debug']) {
        $command .= ' --debug';
    }

    // Add output directory if specified
    if (!empty($options['output_dir'])) {
        $command .= ' --output-dir ' . escapeshellarg($options['output_dir']);
    }

    // Add other flags based on options
    if (isset($options['save_images']) && !$options['save_images']) {
        $command .= ' --no-save';
    }

    if (isset($options['include_base64']) && !$options['include_base64']) {
        $command .= ' --no-base64';
    }
    
    return $command;
}

    /**
     * Transform the Python script result to match expected format
     *
     * @param array $result
     * @return array
     */
    private function transformResult($result)
    {
        return [
            'success' => $result['success'],
            'count' => $result['count'],
            'signatures' => $result['signatures'],
            'message' => $result['message'],
            'error' => $result['error'] ?? null
        ];
    }

    /**
     * Sanitize command for logging (remove sensitive paths)
     *
     * @param string $command
     * @return string
     */
    private function sanitizeCommandForLog($command)
    {
        // Replace full paths with just filenames for security
        $sanitized = preg_replace('/\/[^\s]*\/([^\/\s]+\.pdf)/', '***/$1', $command);
        $sanitized = preg_replace('/\/[^\s]*\/([^\/\s]+\.py)/', '***/$1', $sanitized);
        return $sanitized;
    }

    /**
     * Test the service configuration
     *
     * @return array
     */
    public function testConfiguration()
    {
        $status = [
            'python_executable' => $this->pythonExecutable,
            'python_script' => $this->pythonScriptPath,
            'output_directory' => $this->outputDirectory,
            'python_exists' => false,
            'script_exists' => false,
            'output_dir_writable' => false,
            'test_execution' => false,
            'errors' => []
        ];

        // Check if Python executable exists
        $output = [];
        $returnCode = 0;
        exec(escapeshellcmd($this->pythonExecutable) . ' --version 2>&1', $output, $returnCode);
        $status['python_exists'] = ($returnCode === 0);
        $status['python_version'] = implode(' ', $output);

        if (!$status['python_exists']) {
            $status['errors'][] = 'Python executable not found or not working';
        }

        // Check if script exists
        $status['script_exists'] = file_exists($this->pythonScriptPath);
        if (!$status['script_exists']) {
            $status['errors'][] = 'Python script not found: ' . $this->pythonScriptPath;
        }

        // Check if output directory is writable
        $status['output_dir_writable'] = is_writable($this->outputDirectory);
        if (!$status['output_dir_writable']) {
            $status['errors'][] = 'Output directory not writable: ' . $this->outputDirectory;
        }

        // Test basic script execution if everything else is OK
        if ($status['python_exists'] && $status['script_exists']) {
            try {
                $output = [];
                $returnCode = 0;
                $testCommand = escapeshellcmd($this->pythonExecutable) . ' ' . escapeshellarg($this->pythonScriptPath) . ' --help 2>&1';
                exec($testCommand, $output, $returnCode);
                $status['test_execution'] = ($returnCode === 0);
                $status['test_output'] = implode("\n", $output);
                
                if (!$status['test_execution']) {
                    $status['errors'][] = 'Script execution test failed';
                }
            } catch (Exception $e) {
                $status['test_execution'] = false;
                $status['errors'][] = 'Script execution test error: ' . $e->getMessage();
            }
        }

        return $status;
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
     * Get the output directory
     *
     * @return string
     */
    public function getOutputDirectory()
    {
        return $this->outputDirectory;
    }

    /**
     * Set the output directory
     *
     * @param string $directory
     * @return void
     */
    public function setOutputDirectory($directory)
    {
        $this->outputDirectory = $directory;
        
        // Create directory if it doesn't exist
        if (!file_exists($this->outputDirectory)) {
            mkdir($this->outputDirectory, 0755, true);
        }
    }
}