<div class="w-full mx-auto p-6 bg-white shadow rounded-lg custom-page-container" data-id="{{ $page->id }}">
    <!-- First Card with Radio Buttons -->
    @php
        $uniquePageId = \Str::uuid(); // Unique identifier for this page
        $firstrandom = \Str::random(8);
        $secondRandom = \Str::random(8);
        $thirdRandom = \Str::random(8);
    @endphp
    <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div>
                <input type="radio" id="custom-page-single-pdf-{{ $uniquePageId }}-{{ $firstrandom }}"
                       name="custom_page_type_{{ $uniquePageId }}-{{ $thirdRandom }}"
                       value="single_pdf_{{ $uniquePageId }}-{{ $firstrandom }}"
                       class="mr-2 custom_page_type">
                <label for="custom-page-single-pdf-{{ $uniquePageId }}-{{ $firstrandom }}"
                       class="text-gray-700 text-md cursor-pointer">Single Use PDF</label>
            </div>
            <div>
                <input type="radio" id="custom-page-text-{{ $uniquePageId }}-{{ $secondRandom }}"
                       name="custom_page_type_{{ $uniquePageId }}-{{ $thirdRandom }}"
                       value="single_text_{{ $uniquePageId }}-{{ $secondRandom }}"
                       class="mr-2 custom_page_type">
                <label for="custom-page-text-{{ $uniquePageId }}-{{ $secondRandom }}"
                       class="text-gray-700 text-md cursor-pointer">Text Page</label>
            </div>
        </div>
    </div>

    <div id="custom-page-single-pdf-section-{{ $page->id }}"
         class="hidden"
         data-selected="single_pdf_{{ $uniquePageId }}-{{ $firstrandom }}">
        <!-- Form for PDF Upload (Dropzone) -->
        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone custom-page-dropzone-{{ $page->id }}">
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDF</span>
                <small class="text-gray-500">Only PDF file are allowed</small>
            </div>
        </form>
    </div>

    <div id="custom-page-text-section-{{ $uniquePageId }}-{{ $secondRandom }}"
         class="hidden"
         data-selected="single_text_{{ $uniquePageId }}-{{ $secondRandom }}">
        <div class="bg-white custom-page-quill-editor"></div>
        <textarea class="custom-page-text hidden" name="custom_page_text" required>{{ $pageData->json_data['custom_page_text'] ?? '' }}</textarea>
    </div>
</div>


@push('scripts')
    <script type="text/javascript">
       $(document).on("change", ".custom_page_type", function () {
        let selectedValue = $(this).val();
        // Find the closest container to this radio button
        let container = $(this).closest(".custom-page-container");

        // Iterate over all sections in this container
        container.find("div[data-selected]").each(function () {
            // Check if the data-selected matches the selected radio value
            if ($(this).data("selected") === selectedValue) {
                $(this).removeClass("hidden");
            } else {
                $(this).addClass("hidden");
            }
        });
    });

        // initialize Quill and Dropzone after appending content
        customPageInitializeQuill();

            // Initialize Dropzone for each page dynamically
    const customPageInitializeDropzone = (pageId) => {
        console.log('pageId',pageId)
        const dropzoneElement = $('#custom-page-dropzone-' + pageId);

        if (dropzoneElement.length && !dropzoneElement.hasClass('dropzone-initialized')) {
            new Dropzone(dropzoneElement[0], {
                url: saveFileFromDropZoneRoute, // Replace with your route for saving files
                paramName: 'file',
                maxFiles: 1,
                acceptedFiles: '.pdf',
                addRemoveLinks: true,
                dictRemoveFile: "Remove",
                dictDefaultMessage: "Drag & Drop or Click to Upload",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                init: function () {
                    let jsonData = JSON.parse(`{!! json_encode($pageData->json_data ?? []) !!}`);

                    let customPageFileData = {
                        name: jsonData['custom_page_file']?.file_name ?? '',
                        size: jsonData['custom_page_file']?.size ?? '',
                        url: jsonData['custom_page_file']?.path ? "{{ asset('storage') }}/" + jsonData['custom_page_file'].path : '',
                        path: jsonData['custom_page_file']?.path ?? '',
                        type: 'custom_page_file'
                    };

                    if (customPageFileData.name) {
                        // Show existing file in Dropzone if it exists
                        this.emit("addedfile", customPageFileData);
                        this.emit("thumbnail", customPageFileData, customPageFileData.url);
                        this.emit("complete", customPageFileData);
                        this.files.push(customPageFileData);
                    }

                    // Add additional form data when a file is sent
                    this.on("sending", function (file, xhr, formData) {
                        formData.append('type', 'custom_page_file');
                        formData.append('page_id', pageId);
                        formData.append('folder', 'custom_page_file');
                    });

                    // File type validation
                    this.on("addedfile", function (file) {
                        if (!file.type.match('application/pdf')) {
                            this.removeFile(file);
                            showErrorNotification('Only PDF files are allowed.');
                        }
                    });

                    // Success notification
                    this.on("success", function (file, response) {
                        showSuccessNotification(response.message);
                    });

                    // Delete file when removed
                    this.on("removedfile", function (file) {
                        deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                            page_id: pageId,
                            file_key: 'custom_page_file',
                        });
                    });
                }
            });

            dropzoneElement.addClass('dropzone-initialized'); // Mark as initialized
        }
    };

    // Initialize Dropzone for each dynamically created page
    $(".custom-page-container").each(function() {
        let pageId = $(this).data('id');  // Assuming each container has a data-page-id
        customPageInitializeDropzone(pageId);   // Initialize Dropzone for each page
    });
    </script>
@endpush
