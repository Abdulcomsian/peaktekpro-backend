<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <!-- First Card with Radio Buttons -->
    <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div class="mb-1">
                <input type="radio" id="product-compatibility-upload-pdf" name="product_compatibility_type" value="pdf"
                    class="mr-2">
                <label for="product-compatibility-upload-pdf" class="text-gray-700 text-md">Upload PDFs</label>
            </div>
            <div>
                <input type="radio" id="product-compatibility-text-page" name="product_compatibility_type"
                    value="text" class="mr-2">
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

@push('scripts')
    <script type="text/javascript">
        // Show the appropriate form when the radio button is changed
        $("input[name='product_compatibility_type']").on("change", function() {
            var selectedValue = $("input[name='product_compatibility_type']:checked").val();

            if (selectedValue === 'pdf') {
                $('#product-compatibility-form-upload-pdf').removeClass('hidden');
                $('#product-compatibility-form-text-page').hasClass('hidden') ? '' : $(
                    '#product-compatibility-form-text-page').addClass('hidden');
            } else if (selectedValue === 'text') {
                $('#product-compatibility-form-text-page').removeClass('hidden');
                $('#product-compatibility-form-upload-pdf').hasClass('hidden') ? '' : $(
                    '#product-compatibility-form-upload-pdf').addClass('hidden');
            }
        });


        // drop zone
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
            createImageThumbnails: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            init: function() {
                let productCompatibilityFiles = {
                    files : JSON.parse(`{!! json_encode($pageData->json_data['product_compatibility_files'] ?? []) !!}`),
                    file_url : "{{ $pageData->file_url ?? '' }}",
                    filesType : "pdf"
                }

                // Show images on load
                showMultipleFilesOnLoadInDropzone(this, productCompatibilityFiles, 'product_compatibility_files');

                // When a file is added, check if it's valid based on accepted file types
                this.on("addedfile", function(file) {
                    if (!file.type.match('application/pdf')) {
                        // If the file type doesn't match, remove the file from preview
                        this.removeFile(file);
                        showErrorNotification('Only PDFs are allowed.')
                    }
                    else{
                        let thumnailUrl = "{{ asset('assets/images/pdf.png') }}"
                        this.emit("thumbnail", file, thumnailUrl);
                    }
                });

                this.on("sending", function(file, xhr, formData) {
                    formData.append('type', 'product_compatibility_files');
                    formData.append('page_id', pageId);
                    formData.append('folder', 'product_compatibility');
                });

                this.on("successmultiple", function(files, response) {
                    if (response.status && response.file_details.length === files.length) {
                        // Iterate through each uploaded file and its corresponding server response
                        files.forEach((file, index) => {
                            const fileData = response.file_details[index];  // Match file with its response data

                            // Add custom keys from the server response to the file object
                            file.file_id = fileData.file_id;

                        });
                        showSuccessNotification(response.message);
                    } else {
                        showErrorNotification("Mismatch between uploaded files and server response.");
                    }
                });

                this.on("removedfile", function(file) {

                    // delete file from dropzone
                    deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                        page_id: pageId,
                        file_key: 'product_compatibility_files',
                        file_id: file.file_id,
                    });

                });
            }
        });


        // quill

        const productCompatibilityQuillOptions = [
            ['bold', 'italic', 'underline', 'strike'], // toggled buttons
            ['blockquote', 'code-block'],
            ['link'],
            [{
                'header': 1
            }, {
                'header': 2
            }], // custom button values
            [{
                'list': 'ordered'
            }, {
                'list': 'bullet'
            }, {
                'list': 'check'
            }],
            [{
                'script': 'sub'
            }, {
                'script': 'super'
            }], // superscript/subscript
            [{
                'header': [1, 2, 3, 4, 5, 6, false]
            }],

            [{
                'color': []
            }, {
                'background': []
            }], // dropdown with defaults from theme
            [{
                'font': []
            }],
            [{
                'align': []
            }],
            ['clean'] // remove formatting button
        ];
        var productCompatibilityQuill = new Quill('#product-compatibility-quill', {
            theme: 'snow',
            modules: {
                toolbar: productCompatibilityQuillOptions
            }
        });
        // Set the height dynamically via JavaScript
        productCompatibilityQuill.root.style.height = '200px';

        // old intro text value
        let oldProductCompatibilityValue = "{!! $pageData->json_data['product_compatibility_text'] ?? '' !!}";

        // Load the saved content into the editor
        productCompatibilityQuill.clipboard.dangerouslyPasteHTML(oldProductCompatibilityValue);
        productCompatibilityQuill.on('text-change', function() {
            $('#product-compatibility-text').val(productCompatibilityQuill.root.innerHTML);

            //save textarea data
            saveReportPageTextareaData('#product-compatibility-text');

        });
    </script>
@endpush
