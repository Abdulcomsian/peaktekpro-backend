<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <!-- First Card with Radio Buttons -->
    <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div class="mb-1">
                <input type="radio" id="product-compatibility-upload-pdf" 
                       name="product_compatibility_type" value="pdf"
                       class="mr-2"
                       {{ ($pageData->json_data['product_compatibility_type'] ?? '') == 'pdf' ? 'checked' : '' }}>
                <label for="product-compatibility-upload-pdf" class="text-gray-700 text-md">Upload PDFs</label>
            </div>
            <div>
                <input type="radio" id="product-compatibility-text-page" 
                       name="product_compatibility_type" value="text" 
                       class="mr-2"
                       {{ ($pageData->json_data['product_compatibility_type'] ?? '') == 'text' ? 'checked' : '' }}>
                <label for="product-compatibility-text-page" class="text-gray-700 text-md">Text Page</label>
            </div>
        </div>
    </div>

    <!-- Form for PDF Upload (Dropzone) -->
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone hidden"
        id="product-compatibility-form-upload-pdf">
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDFs</span>
            <small class="text-gray-500">Only PDF files are allowed</small>
        </div>
    </form>

    <!-- Form for Text Page -->
    <form id="product-compatibility-form-text-page" action="/submit-text" method="POST" class="hidden">
        <!-- Descriptive Text Field -->
        <div class="my-6">
            <div id="product-compatibility-quill" class="bg-white"></div>
            <textarea class="hidden" id="product-compatibility-text" name="product_compatibility_text" required>{{ $pageData->json_data['product_compatibility_text'] ?? '' }}</textarea>
        </div>
    </form>
</div>
<div id="pdf-upload-loader" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
</div>

@push('scripts')
    <script type="text/javascript">
        // Show the appropriate form when the radio button is changed
        $("input[name='product_compatibility_type']").on("change", function() {
            const selectedValue = $("input[name='product_compatibility_type']:checked").val();
            $('#product-compatibility-form-upload-pdf').toggleClass('hidden', selectedValue !== 'pdf');
            $('#product-compatibility-form-text-page').toggleClass('hidden', selectedValue !== 'text');
        });

        // Initialize visibility on page load
        $(document).ready(function() {
            // Trigger change event for initially checked radio
            $("input[name='product_compatibility_type']:checked").trigger('change');
        });

        // Dropzone initialization
        Dropzone.autoDiscover = false;

        const productCompatibilityDropzone = new Dropzone("#product-compatibility-form-upload-pdf", {
            url: saveMultipleFilesFromDropZoneRoute,
            uploadMultiple: true,
            parallelUploads: 100,
            maxFiles: 100,
            acceptedFiles: ".pdf",
            addRemoveLinks: true,
            dictRemoveFile: "Remove",
            dictDefaultMessage: "Drag & Drop or Click to Upload",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            init: function() {
                let productCompatibilityFiles = {
                    files: JSON.parse(`{!! json_encode($pageData->json_data['product_compatibility_files'] ?? []) !!}`),
                    file_url: "{{ $pageData->file_url ?? '' }}"
                };
                
                // Show existing files
                showMultipleFilesOnLoadInDropzone(this, productCompatibilityFiles, 'product_compatibility_files');

                this.on("addedfile", function(file) {
                    if (!file.type.match('application/pdf')) {
                        this.removeFile(file);
                        showErrorNotification('Only PDFs are allowed.')
                    }
                });

                this.on("sending", function(file, xhr, formData) {
                    $("#pdf-upload-loader").removeClass("hidden"); // Show loader

                    formData.append('type', 'product_compatibility_files');
                    formData.append('page_id', pageId);
                    formData.append('folder', 'product_compatibility');
                });

                this.on("successmultiple", function(files, response) {
                    $("#pdf-upload-loader").addClass("hidden"); // Hide loader

                    if (response.status && response.file_details.length === files.length) {
                        files.forEach((file, index) => {
                            const fileData = response.file_details[index];
                            file.file_id = fileData.file_id;
                        });
                        showSuccessNotification(response.message);
                    } else {
                        showErrorNotification("Upload error.");
                    }
                });

                this.on("error", function(file, message) {
                    $("#pdf-upload-loader").addClass("hidden"); // Hide loader on error
                    showErrorNotification(message);
                    this.removeFile(file);
                });

                this.on("removedfile", function(file) {
                    deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                        page_id: pageId,
                        file_key: 'product_compatibility_files',
                        file_id: file.file_id,
                    });
                });
            }
        });

        // Quill Editor initialization
        const productCompatibilityQuill = new Quill('#product-compatibility-quill', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    ['link'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
                    [{ 'script': 'sub' }, { 'script': 'super' }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean']
                ]
            }
        });

        productCompatibilityQuill.root.style.height = '200px';
        productCompatibilityQuill.clipboard.dangerouslyPasteHTML(
            `{!! $pageData->json_data['product_compatibility_text'] ?? '' !!}`
        );

        productCompatibilityQuill.on('text-change', function() {
            $('#product-compatibility-text').val(productCompatibilityQuill.root.innerHTML);
            saveReportPageTextareaData('#product-compatibility-text');
        });
    </script>
@endpush