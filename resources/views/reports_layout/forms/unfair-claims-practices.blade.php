<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <!-- First Card with Radio Buttons -->
    {{-- <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div>
                <input type="radio" id="unfair-claims-single-pdf" name="unfair_claims_type" value="single_pdf" class="mr-2">
                <label for="unfair-claims-single-pdf" class="text-gray-700 text-md">Single Use PDF</label>
            </div>
            <div class="mb-1">
                <input type="radio" id="unfair-claims-shared-pdf" name="unfair_claims_type" value="shared_pdf" class="mr-2">
                <label for="unfair-claims-shared-pdf" class="text-gray-700 text-md">Shared PDFs</label>
            </div>
        </div>
    </div> --}}

    <!-- Form for PDF Upload (Dropzone) -->
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone" id="unfair-claims-form-single-pdf">
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDF</span>
            <small class="text-gray-500">Only PDF file are allowed</small>
        </div>
    </form>

    <!-- Shared PDFs -->
    <div id="unfair-claims-form-shared-pdf" class="hidden">
        <p>shared pdfs</p>
    </div>
</div>

@push('scripts')
    <script type="text/javascript">
        // drop zone
        Dropzone.autoDiscover = false;

        const unfairClaimsSinglePdfDropZone = new Dropzone("#unfair-claims-form-single-pdf", {
            url: saveFileFromDropZoneRoute,
            maxFiles: 1,
            acceptedFiles: ".pdf",
            addRemoveLinks: true,
            dictRemoveFile: "Remove",
            dictDefaultMessage: "Drag & Drop or Click to Upload",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            init: function() {

                let unfairClaimFileData = {
                    name: "{{ $pageData->json_data['unfair_claim_file']['file_name'] ?? '' }}",
                    size: "{{ $pageData->json_data['unfair_claim_file']['size'] ?? '' }}",
                    url: "{{ $pageData->file_url ?? '' }}",
                    path : "{{ $pageData->json_data['unfair_claim_file']['path'] ?? '' }}",
                    type : 'unfair_claim_file'
                }

                // Show image on load
                showFileOnLoadInDropzone(this, unfairClaimFileData);

                this.on("sending", function(file, xhr, formData) {
                    formData.append('type', 'unfair_claim_file');
                    formData.append('page_id', pageId);
                    formData.append('folder', 'unfair_claim_file');
                });
                // When a file is added, check if it's valid based on accepted file types
                this.on("addedfile", function(file) {
                    if (!file.type.match('application/pdf')) {
                        // If the file type doesn't match, remove the file from preview
                        this.removeFile(file);
                        showErrorNotification('Only PDF files are allowed.')
                    }
                });

                this.on("success", function(file, response) {
                    showSuccessNotification(response.message);
                });

                this.on("removedfile", function(file) {
                    // delete file from dropzone
                    deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                        page_id: pageId,
                        file_key: 'unfair_claim_file',
                    });
                });
            }
        });
    </script>
@endpush
