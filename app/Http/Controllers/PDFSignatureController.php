<?php

namespace App\Http\Controllers;

use App\Services\PDFSignatureService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class PDFSignatureController extends Controller
{
    private $pdfSignatureService;

    public function __construct(PDFSignatureService $pdfSignatureService)
    {
        $this->pdfSignatureService = $pdfSignatureService;
    }

    /**
     * Extract signatures from uploaded PDF
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function extractSignatures(Request $request): JsonResponse
    {
        try {
            // Validate request
            // $request->validate([
            //     'pdf_file' => 'required|file|mimes:pdf|max:10240', // max 10MB
            // ]);

            $file = $request->file('pdf_file');
            // $file = public_path('assets/test.pdf');

            // Extract signatures
            $result = $this->pdfSignatureService->extractSignaturesFromUpload($file, [
                'include_base64' => $request->get('include_base64', true),
                'save_images' => $request->get('save_images', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Signatures extracted successfully',
                'data' => $result
            ]);

        } catch (Exception $e) {
            Log::error('PDF signature extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to extract signatures',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract signatures from existing PDF file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function extractSignaturesFromFile(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'pdf_path' => 'required|string',
            ]);

            $pdfPath = $request->get('pdf_path');

            // Extract signatures
            $result = $this->pdfSignatureService->extractSignatures($pdfPath, [
                'include_base64' => $request->get('include_base64', true),
                'save_images' => $request->get('save_images', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Signatures extracted successfully',
                'data' => $result
            ]);

        } catch (Exception $e) {
            Log::error('PDF signature extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to extract signatures',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get signature image
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function getSignatureImage($filename)
    {
        try {
            $path = storage_path('app/signatures/' . $filename);
            
            if (!file_exists($path)) {
                abort(404, 'Signature image not found');
            }

            return response()->file($path);

        } catch (Exception $e) {
            Log::error('Failed to retrieve signature image', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);

            abort(500, 'Failed to retrieve signature image');
        }
    }
}