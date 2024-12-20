<div class="w-full mx-auto p-6 bg-white shadow rounded-lg custom-page-container">
    <!-- First Card with Radio Buttons -->
    <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div>
                <input type="radio" id="custom-page-single-pdf" name="custom_page_type" value="single_pdf"
                    class="mr-2 custom_page_type">
                <label for="custom-page-single-pdf" class="text-gray-700 text-md">Single Use PDF</label>
            </div>
            <div>
                <input type="radio" id="custom-page-text" name="custom_page_type" value="single_text"
                    class="mr-2 custom_page_type">
                <label for="custom-page-text" class="text-gray-700 text-md">Text Page</label>
            </div>
        </div>
    </div>

    <div class="hidden" data-selected="single_pdf">

        <!-- Form for PDF Upload (Dropzone) -->
        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone custom-page-dropzone" id="custom-page-dropzone">
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDF</span>
                <small class="text-gray-500">Only PDF file are allowed</small>
            </div>
        </form>

    </div>

    <div class="hidden" data-selected="single_text">
        <div class="bg-white custom-page-quill-editor"></div>
        <textarea class="custom-page-text hidden" name="custom_page_text" required>{{ $pageData->json_data['custom_page_text'] ?? '' }}</textarea>
    </div>
</div>
@push('scripts')
    <script type="text/javascript">
        // Show the appropriate form when the radio button is changed
        $(document).on("change", ".custom_page_type", function() {
            let selectedValue = $(this).val();
            let element = $(this).closest(".custom-page-container").find("div[data-selected]")
            // Toggle visibility of elements based on `data-selected`
            element.each(function() {
                if ($(this).data("selected") === selectedValue) {
                    $(this).removeClass("hidden");
                } else {
                    $(this).addClass("hidden");
                }
            });

        });

        // initialize Quill and Dropzone after appending content
        customPageInitializeQuill();
        customPageInitializeDropzone();


        Dropzone.autoDiscover = false;

            const customPageDropZone = new Dropzone("#custom-page-dropzone", {
                url: saveMultipleFilesFromDropZoneRoute,
                maxFiles: 1,
                acceptedFiles: ".pdf",
                addRemoveLinks: true,
                dictRemoveFile: "Remove",
                dictDefaultMessage: "Drag & Drop or Click to Upload",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                init: function() {
                    // Check if there's an existing file and initialize Dropzone with the file data
                    let jsonData = JSON.parse(`{!! json_encode($pageData->json_data ?? []) !!}`)
            // Check if there's an existing file in the parsed JSON
            let customPageFileData = {
                name: jsonData['custom_page_file']?.file_name ?? '',
                size: jsonData['custom_page_file']?.size ?? '',
                url: jsonData['custom_page_file']?.path ? "{{ asset('storage') }}/" + jsonData['custom_page_file'].path : '',
                path: jsonData['custom_page_file']?.path ?? '',
                type: 'custom_page_file'
            };

            if (customPageFileData.name) {
                // If there is an existing file, show it in the Dropzone
                this.emit("addedfile", customPageFileData);
                // Emitting the correct full path for the thumbnail
                this.emit("thumbnail", customPageFileData, customPageFileData.url); // Use the URL from jsonData
                this.emit("complete", customPageFileData);
                this.files.push(customPageFileData);
            }

                    // When a file is sent, add additional form data
                    this.on("sending", function(file, xhr, formData) {
                        formData.append('type', 'custom_page_file');
                        formData.append('page_id', pageId);
                        formData.append('folder', 'custom_page_file');
                    });

                    // When a file is added, check if it's valid based on accepted file types
                    this.on("addedfile", function(file) {
                        if (!file.type.match('application/pdf')) {
                            // If the file type doesn't match, remove the file from preview
                            this.removeFile(file);
                            showErrorNotification('Only PDF files are allowed.');
                        }
                    });

                    // On success, show a success notification
                    this.on("success", function(file, response) {
                        showSuccessNotification(response.message);
                    });

                    // When a file is removed, delete it from the Dropzone
                    this.on("removedfile", function(file) {
                        deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                            page_id: pageId,
                            file_key: 'custom_page_file',
                        });
                    });
                }
            });

    </script>
@endpush
