@extends('layouts.report-layout')

@section('title', 'Create Report')
<div class="flex justify-end mt-6 mr-12">
    <a href="{{ route('reports.index') }}" class="hover:text-gray-300 text-white btn-gradient p-2 rounded">
        Reports
    </a>
</div>



@push('styles')
    {{-- load quill css --}}
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    {{-- load dropzone --}}
    <link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />

    <style>
        .dropzone .dz-preview .dz-details .dz-filename {
            display: none;
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield; /* for Firefox */
        }
    </style>

   

@endpush


@push('scripts')
    {{-- load quill --}}
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    {{-- load drop zone --}}
    <script src="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js"></script>

    <script>
        var pageId = null
        var deleteFileFromDropZoneRoute = "{{ route('reports.page.delete-file') }}"
        var deleteFileFromRepairablityDropZoneRoute = "{{ route('reports.page.repairability.delete-file') }}"
        var saveFileFromDropZoneRoute = "{{ route('reports.page.save-file') }}"
        var saveMultipleFilesFromDropZoneRoute = "{{ route('reports.page.save-multiple-files') }}"

        // Initialize Quill editor
        const customPageInitializeQuill = () => {

            const customPageTextQuillOptions = [
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
            // Re-initialize Quill for the newly added content
            $('.custom-page-quill-editor').each(function() {
                if (!$(this).hasClass('quill-initialized')) {
                    var customQuill = new Quill($(this)[0], {
                        theme: 'snow',
                        modules: {
                            toolbar: customPageTextQuillOptions
                        }
                    });
                    $(this).addClass('quill-initialized'); // Mark as initialized
                    customQuill.root.style.height = '200px';

                    // Reference to the associated textarea (assumes it's the next sibling)
                    var $textarea = $(this).next('textarea');

                    // Parse the JSON data passed from the backend
                    var existingContent = $textarea.val();
                    if (existingContent) {
                        customQuill.clipboard.dangerouslyPasteHTML(existingContent);
                    }

                    // If no content exists, initialize the Quill editor and set up the 'text-change' event
                    customQuill.on('text-change', function() {
                        // Sync content with the associated textarea
                        $textarea.val(customQuill.root.innerHTML);

                        // Optionally trigger change event on the textarea if needed
                        // $textarea.trigger('change');
                        saveReportPageTextareaData($textarea);
                    });

                }
            });
        }

        const customPageInitializeDropzone = () => {
            // Re-initialize Dropzone for the newly added elements
            $('.custom-page-dropzone').each(function() {

                let customPageDropzoneJsonData = '';
                let dataJson = $(this).attr('data-json');

                if (dataJson && dataJson !== '') {
                    try {
                        // Attempt to parse the JSON data
                        customPageDropzoneJsonData = JSON.parse(dataJson);
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                    }
                }

                // Ensure Dropzone is not re-initialized
                if (!$(this).hasClass('dropzone-initialized')) {
                    new Dropzone($(this)[0], {
                        url: saveFileFromDropZoneRoute,
                        paramName: 'file',
                        maxFiles: 1,
                        acceptedFiles: '.pdf',
                        addRemoveLinks: true,
                        dictRemoveFile: "Remove",
                        dictDefaultMessage: "Drag & Drop or Click to Upload",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        init: function() {
                            // Initialize customPageFileData outside of the if block
                            let customPageFileData = null; // Start with null as default value

                            // Ensure jsonData is available and correctly formatted
                            let jsonData = customPageDropzoneJsonData;
                            if (jsonData) {
                                customPageFileData = {
                                    name: jsonData.file_name ?? '',
                                    size: jsonData.size ?? '',
                                    url: jsonData.path ? "{{ asset('storage') }}/" + jsonData
                                        .path : '',
                                    path: jsonData.path ?? '',
                                    type: 'custom_page_file'
                                };
                            }

                            // Check if the file name exists and show the file in the Dropzone
                            if (customPageFileData && customPageFileData.name) {
                                // If there is an existing file, show it in the Dropzone
                                this.emit("addedfile", customPageFileData);
                                let thumbnailUrl = "{{ asset('assets/images/pdf.png') }}"
                                // Emitting the correct full path for the thumbnail
                                this.emit("thumbnail", customPageFileData, thumbnailUrl); // Use the URL from jsonData
                                // this.emit("thumbnail", customPageFileData, customPageFileData.url); // Use the URL from jsonData
                                this.emit("complete", customPageFileData);
                                this.files.push(
                                    customPageFileData); // Add to the Dropzone files array
                            }

                            // When a file is sent, add additional form data
                            this.on("sending", function(file, xhr, formData) {
                                formData.append('type', 'custom_page_file');
                                formData.append('page_id', pageId);
                                formData.append('folder', 'custom_page_file');
                            });

                            // When a file is added, check if it's valid based on accepted file types
                            this.on("addedfile", function(file) {
                                // Ensure only PDF files are allowed
                                if (!file.type.match('application/pdf')) {
                                    // If the file type doesn't match, remove the file from preview
                                    this.removeFile(file);
                                    showErrorNotification('Only PDF files are allowed.');
                                }
                                else
                                {
                                    let thumbnailUrl = "{{ asset('assets/images/pdf.png') }}"
                                    this.emit("thumbnail", file, thumbnailUrl);
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
                    $(this).addClass('dropzone-initialized'); // Mark as initialized
                }
            });
        }

        // Initialize Dropzone for each page dynamically
        customPageInitializeDropzone();

        // show file on load in dropzone
        function showFileOnLoadInDropzone(dropzoneInstance, fileData) {
            if (fileData.name && fileData.size && fileData.url && fileData.path && fileData.type) {
                // Simulate adding the existing file
                dropzoneInstance.emit("addedfile", fileData);
                let fileUrl = `${fileData.url}/${fileData.path}`;
                let pdfThumbnail = "{{ asset('assets/images/pdf.png') }}"
                let thumbnailUrl = fileData.fileExtension === 'pdf' ? pdfThumbnail : fileUrl
                dropzoneInstance.emit("thumbnail", fileData, thumbnailUrl); // Set the thumbnail

                // Set the file as already uploaded
                fileData.status = Dropzone.SUCCESS;
                dropzoneInstance.emit("complete", fileData);

                // Add file to Dropzone's internal files array
                dropzoneInstance.files.push(fileData);
            }
        }
        // show files on load in dropzone
        function showMultipleFilesOnLoadInDropzone(dropzoneInstance, filesData, type) {
            // Check if files exist and add them to Dropzone
            if (filesData.files.length > 0) {
                filesData.files.forEach(function(fileData) {
                    let mockFile = {
                        name: fileData.file_name,
                        size: fileData.size,
                        url: fileData.path,
                        type: type,
                        file_id: fileData.file_id,
                    };

                    dropzoneInstance.emit("addedfile", mockFile); // Add the file to Dropzone
                    let fileUrl = `${filesData.file_url}/${fileData.path}`;
                    let pdfThumbnail = "{{ asset('assets/images/pdf.png') }}"
                    let thumbnailUrl = filesData.filesExtension === 'pdf' ? pdfThumbnail : fileUrl
                    dropzoneInstance.emit("thumbnail", mockFile, thumbnailUrl); // Set the thumbnail

                    mockFile.status = Dropzone.SUCCESS;
                    dropzoneInstance.emit("complete", mockFile); // Mark as complete

                    dropzoneInstance.files.push(mockFile); // Add to Dropzone's file list
                });
            }
        }
        // remove file from dropzone
        function deleteFileFromDropzone(fileData, deleteUrl, params) {
            if (fileData.name && fileData.type) {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: params,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showSuccessNotification(response.message);
                    },
                    error: function() {
                        showErrorNotification('Error deleting the file.');
                    }
                });
            }
        }

        function deleteFileFromRepairablityDropzone(deleteUrl, params) {
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: params,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Call the success callback to update the UI
                        if (typeof successCallback === "function") {
                            successCallback();
                        }
                        showSuccessNotification('File deleted successfully');
                    } else {
                        console.error('Error:', response.message || 'An error occurred');
                    }

                },
                error: function() {
                    showErrorNotification('Error deleting the file.');
                }
            });
        }

        $(document).ready(function() {
            // edit report title start
            // Show input field for editing when edit button is clicked
            $('#editTitleBtn').on('click', function() {
                // Hide the title text, show the input field for editing
                $('#reportTitleText').addClass('hidden');
                $('#editTitleContainer').removeClass('hidden');
            });

            // Cancel editing and hide input field
            $('#cancelEditBtn').on('click', function() {
                $('#reportTitleText').removeClass('hidden');
                $('#editTitleContainer').addClass('hidden');
            });

            // Save the new title using AJAX
            $('#saveTitleBtn').on('click', function() {
                var newTitle = $('#reportTitleInput').val();

                // Clear previous error messages
                $('.error-message').text('');

                // Check if the new title is not empty
                if (newTitle.trim() === '') {

                    $('.error-message[data-error="title"]').text('Title cannot be empty!');

                    return;
                }
                let actionUrl = "{{ route('reports.update.title', $report->id) }}"
                // Make the AJAX call to update the report title
                $.ajax({
                    url: actionUrl,
                    method: 'PUT',
                    data: {
                        title: newTitle,
                    },
                    success: function(response) {
                        if (response.status) {
                            // Update the title on the page and hide the input
                            $('#reportTitleText').text(newTitle).removeClass('hidden');
                            $('#editTitleContainer').addClass('hidden');

                            // show a success message
                            showSuccessNotification(response.message);
                        } else {
                            showErrorNotification('Error updating report title!');
                        }
                    },
                    error: async function(xhr) {
                        // Handle validation errors
                        if (xhr.status ===
                            422) { // Laravel returns 422 status code for validation errors
                            let errors = xhr.responseJSON.errors;
                            // Loop through each error field and display messages
                            $.each(errors, function(field, messages) {
                                // Find the error container with data attribute matching the field name
                                let errorContainer = $(`[data-error="${field}"]`);
                                // Append each error message
                                messages.forEach(function(message) {
                                    errorContainer.append(
                                        `<div>${message}</div>`);
                                });
                            });
                        } else {

                            await showErrorNotification(
                                'An error occurred while updating title');

                        }
                    }
                });
            });

           

            // Show the first tab content by default
            $(".tab-content").hide();
            $("#content1").show();

            // Tab click handler
            $(document).on('click', '.tab-item', function() {
             
                $(".tab-item").removeClass("bg-blue-400").addClass("bg-blue-200");

                // Remove any existing arrow-indicator from all tab items
                $(".arrow-indicator").remove();

                // Add bg-blue-400 to the clicked tab
                $(this).addClass("bg-blue-400");

                // Append arrow-indicator div only to the active tab
                $(this).append(
                    '<div class="absolute -right-7 top-0 h-full w-7 bg-blue-400 clip-path-arrow arrow-indicator"></div>'
                );

                // Show the related content
                $(".tab-content").hide();
                $($(this).data("target")).fadeIn();

                pageId = $(this).data('id');
            });

            // Enable draggable tabs
            $("#tabsList").sortable({
                opacity: 0.5,
                start: function(event, ui) {
                    ui.item.css("background-color",
                        "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
                },
                stop: function(event, ui) {
                    ui.item.css("background-color", ""); // Reset color on drag stop

                    // Update order via AJAX after drag stop
                    const order = $("#tabsList .tab-item").map(function() {
                        return $(this).data("id");
                    }).get();

                    $.ajax({
                        url: "{{ route('reports.page-ordering.update', $report->id) }}",
                        method: 'POST',
                        data: {
                            order: order,
                        },
                        success: function(response) {
                            if (response.status) {

                                // show a success message
                                showSuccessNotification(response.message);
                            } else {
                                showErrorNotification(response.message);
                            }
                        },
                        error: async function(xhr) {
                            // Handle validation errors
                            if (xhr.status ===
                                422
                            ) { // Laravel returns 422 status code for validation errors
                                let errors = xhr.responseJSON.errors;
                                // Loop through each error field and display messages
                                $.each(errors, function(field, messages) {
                                    // Find the error container with data attribute matching the field name
                                    let errorContainer = $(
                                        `[data-error="${field}"]`);
                                    // Append each error message
                                    messages.forEach(function(message) {
                                        errorContainer.append(
                                            `<div>${message}</div>`);
                                    });
                                });
                            } else {

                                await showErrorNotification(
                                    'An error occurred while updating pages order');

                            }
                        }
                    });
                }
            });

            // Handle "Create Page" button click
            $('#createPageBtn').on('click', function() {
                $.ajax({
                    url: "{{ route('reports.create-page', $report->id) }}",
                    method: 'POST',
                    data: {
                        title: 'Custom Page',
                        _token: $('meta[name=csrf-token]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            // Generate unique random values for new IDs
                            let firstrandom = Math.random().toString(36).substr(2, 8);
                            let secondRandom = Math.random().toString(36).substr(2, 8);
                            let thirdRandom = Math.random().toString(36).substr(2, 8);

                            // Append the new page to the list dynamically
                            $('#tabsList').append(`
<li class="tab-item bg-blue-200 p-2 rounded cursor-pointer relative flex justify-between items-center"
    data-target="#tab${response.page.id}" data-id="${response.page.id}">
    <div class="flex gap-2">
        <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M1 1h15M1 7h15M1 13h15"></path>
        </svg>
        <span id="leftMenuPageName-${response.page.id}" class="text-sm">${response.page.name}</span>
    </div>
   <label for="toggle-${response.page.id}" class="inline-flex relative items-center cursor-pointer">
        <input type="checkbox" id="toggle-${response.page.id}" class="sr-only toggle" data-page-id="${response.page.id}"
            ${response.page.is_active === 1 ? 'checked' : ''} />
        <span class="w-10 h-4 bg-gray-300 rounded-full flex items-center relative">
            <span class="w-4 h-4 bg-white rounded-full shadow transform transition-transform absolute left-0"></span>
        </span>
        
    </label>

</li>
`);

                            // Append the new page content
                            $('#tabContent').append(`
<div id="tab${response.page.id}" class="tab-content hidden bg-blue-50 p-4 rounded shadow mb-4">
    <div class="flex items-center justify-between">
        <h3 id="pageName-${response.page.id}" class="text-lg font-medium mb-2">${response.page.name}</h3>
        <button class="text-blue-500 hover:text-blue-600 edit-button"
            data-id="${response.page.id}" data-name="${response.page.name}">Edit</button>
    </div>
    <div id="editPageForm-${response.page.id}" class="edit-form hidden mb-2">
        <input type="text" id="editInput-${response.page.id}"
            class="border rounded p-2 w-full" value="${response.page.name}" />
        <div class="flex space-x-2 mt-2">
            <button class="bg-blue-500 text-white text-sm px-4 py-2 rounded update-button"
                data-id="${response.page.id}">Update</button>
            <button class="bg-gray-500 text-white text-sm px-4 py-2 rounded cancel-button"
                data-id="${response.page.id}">Cancel</button>
        </div>
    </div>
    <div class="w-full mx-auto p-6 bg-white shadow rounded-lg custom-page-container">
        <div class="mb-6">
            <div class="flex flex-col justify-start">
                <div>
                    <input type="radio" id="custom-page-single-pdf-${firstrandom}" name="custom_page_type_${thirdRandom}" value="single_pdf" class="mr-2 custom_page_type">
                    <label for="custom-page-single-pdf-${firstrandom}" class="text-gray-700 text-md cursor-pointer">Single Use PDF</label>
                </div>
                <div>
                    <input type="radio" id="custom-page-text-${secondRandom}" name="custom_page_type_${thirdRandom}" value="single_text" class="mr-2 custom_page_type">
                    <label for="custom-page-text-${secondRandom}" class="text-gray-700 text-md cursor-pointer">Text Page</label>
                </div>
            </div>
        </div>
        <div id="custom-page-single-pdf-section-${firstrandom}" class="hidden" data-selected="single_pdf">
            <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone custom-page-dropzone">
                <div class="dz-message text-gray-600">
                    <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDF</span>
                    <small class="text-gray-500">Only PDF file are allowed</small>
                </div>
            </form>
        </div>
        <div id="custom-page-text-section-${secondRandom}" class="hidden" data-selected="single_text">
            <div class="bg-white custom-page-quill-editor" style="position: static"></div>
            <textarea class="custom-page-text hidden" name="custom_page_text" required>{{ $pageData->json_data['custom_page_text'] ?? '' }}</textarea>
        </div>
    </div>
</div>
`);

                            customPageInitializeQuill();
                            customPageInitializeDropzone();


                            showSuccessNotification(response.message);

                        } else {
                            showErrorNotification('Error creating page.');
                        }
                    },
                    error: function() {
                        showErrorNotification('An error occurred while creating the page.');
                    }
                });
            });


            // Handle toggle change (to update page status)
            $(document).on('change', 'input[type="checkbox"][data-page-id]', function(e) {
                e.stopPropagation();
                var pageId = $(this).data('page-id');
                var status = $(this).prop('checked') ? 1 : 0;

                // Hide the tab content when toggle is clicked
                $(`#tab${pageId}`).addClass('hidden');

                let actionUrl = "{{ route('reports.update-page.status', ['pageId' => ':pageId']) }}"
                actionUrl = actionUrl.replace(':pageId', pageId)
                // Send AJAX request to update page status
                $.ajax({
                    url: actionUrl,
                    method: 'PATCH',
                    data: {
                        status: status,
                    },
                    success: function(response) {
                        if (response.status) {

                            showSuccessNotification(response.message);

                        } else {
                            showErrorNotification(
                                'Error updating page status');
                        }
                    },
                    error: function() {
                        showErrorNotification(
                            'An error occurred while updating page status');
                    }
                });
            });

            // left menu pages end

            // page name edit start

            // Handle the Edit button click
            $(document).on('click', '.edit-button', function() {
                const pageId = $(this).data('id');
                const pageName = $(this).data('name');

                // Show edit form and hide the static name
                $(`#pageName-${pageId}`).addClass('hidden');
                $(`#editPageForm-${pageId}`).removeClass('hidden');
                $(`#editInput-${pageId}`).val(pageName);
            });

            // Handle the Cancel button click
            $(document).on('click', '.cancel-button', function() {
                const pageId = $(this).data('id');

                // Hide edit form and show the static name
                $(`#pageName-${pageId}`).removeClass('hidden');
                $(`#editPageForm-${pageId}`).addClass('hidden');
            });

            // Handle the Update button click with AJAX call
            $(document).on('click', '.update-button', function() {
                const pageId = $(this).data('id');
                const newName = $(`#editInput-${pageId}`).val().trim();

                // Basic validation
                if (!newName) {
                    showErrorNotification('Page name cannot be empty');
                    return;
                }

                // AJAX request to update page name
                $.ajax({
                    url: `{{ route('reports.update.page-title', ':id') }}`.replace(':id',
                        pageId),
                    method: 'PUT',
                    data: {
                        name: newName,
                    },
                    success: function(response) {
                        if (response.status) {

                            // Update the static name and revert the form to original state
                            $(`#pageName-${pageId}`).text(newName).removeClass('hidden');
                            $(`#editPageForm-${pageId}`).addClass('hidden');

                            // let menu page name update
                            $('#leftMenuPageName-' + pageId).text(newName);

                            showSuccessNotification(response.message);

                        } else {
                            showErrorNotification(
                                'Error updating page name. Please try again.');
                        }
                    },
                    error: function() {
                        showErrorNotification('An error occurred. Please try again.');
                    }
                });
            });
            // page name edit end
        });
    </script>

    {{-- repairability or compatibility photosjs js --}}
    <!-- <script src="{{ asset('assets/js/reports/repairability_or_compatibility_photos.js') }}"></script> -->

    {{-- save data --}}
    <script type="text/javascript">
        const saveReportPageData = "{{ route('reports.page.save-data') }}";

        // save inputs data
        const saveReportPageInputData = debounce(function() {
            console.log('there');
            let fieldName = $(this).attr('name');
            let fieldValue = $(this).val();
            console.log("Input Event Triggered -> Field:", fieldName, "Value:", fieldValue);

            $.ajax({
                url: saveReportPageData,
                method: 'POST',
                data: {
                    page_id: pageId,
                    [fieldName]: fieldValue
                },
                success: function(response) {
                    showSuccessNotification(response.message);
                },
                error: function(xhr) {
                    showErrorNotification('An error occurred. while saving data.');
                }
            });
        }, 500); // Delay in milliseconds

        // Apply debounced function to save data on keyup
        $('.inp-data').on('keyup change', saveReportPageInputData);


        //save all address fields

        const saveReportPageDataAddress = "{{ route('reports.page.save-data') }}";

        // Function to save all address-related fields at once
        function saveAddressData() {
            let addressData = {
                page_id: pageId,
                company_address: $('#company-address').val(),
                company_city: $('#company-city').val(),
                company_province: $('#company-province').val(),
                company_postal_code: $('#company-postal-code').val()
            };

            console.log("Saving Address Data:", addressData);

            $.ajax({
                url: saveReportPageDataAddress,
                method: 'POST',
                data: addressData,
                success: function(response) {
                    showSuccessNotification(response.message);
                },
                error: function(xhr) {
                    showErrorNotification('An error occurred while saving the address data.');
                }
            });
        }

        // save the company address function
        const saveReportPageInputDataAddress = debounce(function() {
            let fieldName = $(this).attr('name');
            let fieldValue = $(this).val();
            console.log("Input Event Triggered -> Field:", fieldName, "Value:", fieldValue);

            // Check if the changed field is part of the address-related fields
            if (['company_address', 'company_city', 'company_province', 'company_postal_code'].includes(
                    fieldName)) {
                saveAddressData();
                return;
            }

            $.ajax({
                url: saveReportPageDataAddress,
                method: 'POST',
                data: {
                    page_id: pageId,
                    [fieldName]: fieldValue
                },
                success: function(response) {
                    showSuccessNotification(response.message);
                },
                error: function(xhr) {
                    showErrorNotification('An error occurred while saving data.');
                }
            });
        }, 500); // Delay in milliseconds

        // Apply debounced function to save data on keyup or change
        $('.inp-data-address').on('keyup change', saveReportPageInputDataAddress);



        //end here


        // save textarea data
        const saveReportPageTextareaData = debounce(function(element) {

            let fieldName = $(element).attr('name');
            let fieldValue = $(element).val();

            $.ajax({
                url: saveReportPageData,
                method: 'POST',
                data: {
                    page_id: pageId,
                    [fieldName]: fieldValue
                },
                success: function(response) {
                    showSuccessNotification(response.message);
                },
                error: function(xhr) {
                    showErrorNotification('An error occurred. while saving data.');
                }
            });

        }, 500)

        // generate key
        function generateBase64Key(length) {
            const array = new Uint8Array(length);
            window.crypto.getRandomValues(array);

            // Convert to base64 and filter out non-alphanumeric characters
            let base64Key = btoa(String.fromCharCode(...array));

            // Remove non-alphanumeric characters (including '+', '/', '=')
            base64Key = base64Key.replace(/[^a-zA-Z0-9]/g, '');

            // If the generated string is shorter than the required length, generate more characters
            while (base64Key.length < length) {
                base64Key += base64Key; // Append more random base64 characters
                base64Key = base64Key.replace(/[^a-zA-Z0-9]/g, ''); // Filter again
            }

            return base64Key.slice(0, length);
        }

        document.getElementById('updateToPublishedBtn').addEventListener('click', function() {
            const reportId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            const newStatus = currentStatus === 'draft' ? 'published' : 'draft';

            // Show the appropriate modal based on the current status
            if (newStatus === 'published') {
                document.getElementById('publishReportModal').classList.remove('hidden');
            } else {
                document.getElementById('draftReportModal').classList.remove('hidden');
            }

            // Handle confirm action for publishing
            document.getElementById('confirmPublishBtn').addEventListener('click', function() {
                updateReportStatus(reportId, newStatus);
                document.getElementById('publishReportModal').classList.add('hidden');
            });

            // Handle confirm action for saving as draft
            document.getElementById('confirmDraftBtn').addEventListener('click', function() {
                updateReportStatus(reportId, newStatus);
                document.getElementById('draftReportModal').classList.add('hidden');
            });

            // Handle cancel actions for both modals
            document.getElementById('cancelPublishBtn').addEventListener('click', function() {
                document.getElementById('publishReportModal').classList.add('hidden');
            });
            document.getElementById('cancelDraftBtn').addEventListener('click', function() {
                document.getElementById('draftReportModal').classList.add('hidden');
            });
        });

        // Function to update report status
        function updateReportStatus(reportId, newStatus) {
            const updateStatusUrl = "{{ route('reports.update-status', ':id') }}".replace(':id', reportId);

            fetch(updateStatusUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    status: newStatus
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Trigger PDF download if published
                    if (newStatus === 'published' && data.file_url) {
                        const link = document.createElement('a');
                        link.href = data.file_url;
                        link.setAttribute('download', 'report.pdf');
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }

                    // Update UI and reload
                    const button = document.getElementById('updateToPublishedBtn');
                    button.setAttribute('data-status', newStatus);
                    button.textContent = newStatus === 'draft' ? 'Publish Report' : 'Save as Draft';
                    showSuccessNotification(data.message);
                    setTimeout(() => {
                        window.location.reload();
                        // window.location.href = response.data.redirect_url;

                    }, 100);
                } else {
                    showErrorNotification(data.message || 'An error occurred.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorNotification('An error occurred. Please try again.');
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const url = "{{ route('reports.copy-template') }}";
            const reportId = @json($report->id ?? '');

            const confirmationModal = document.getElementById('confirmationModal');
            const errorModal = document.getElementById('errorModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmBtn = document.getElementById('confirmBtn');
            const closeErrorModalBtn = document.getElementById('closeErrorModal');

            // Handle button click for template copy
            document.getElementById('templateDropdown').addEventListener('click', function() {
                const selectedTemplateId = document.getElementById('templateDropdownSelect').value;

                // Check if template is selected
                if (!selectedTemplateId) {
                    // Show the error modal if no template is selected
                    errorModal.classList.remove('hidden');
                    return; // Do not proceed if no template is selected
                }

                // Show the confirmation modal
                confirmationModal.classList.remove('hidden');

                // Handle confirmation button click
                confirmBtn.addEventListener('click', () => {
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                template_id: selectedTemplateId,
                                report_id: reportId
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            showSuccessNotification(data
                                .message); // Display success notification
                            setTimeout(() => {
                                document.getElementById('templateDropdownSelect')
                                    .value = ''; // Reset dropdown
                                window.location
                                    .reload(); // Reload the page after a short delay
                            }, 2000); // Delay of 2 seconds
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showErrorNotification(
                                'An error occurred while copying the template.');
                        });

                    // Close the modal after action is taken
                    confirmationModal.classList.add('hidden');
                });

                // Handle cancel button click
                cancelBtn.addEventListener('click', () => {
                    // Close the modal without doing anything
                    confirmationModal.classList.add('hidden');
                });
            });

            // Close the error modal when close button is clicked
            closeErrorModalBtn.addEventListener('click', () => {
                errorModal.classList.add('hidden');
            });
        });
    </script>
@endpush


@section('content')
    <section class="h-screen flex">
        <img id="loadingSpinner" src="{{ asset('assets/images/loader2.gif') }}" alt="Loading"
            style="display: none; position: fixed; top: 50%; left: 60%; transform: translate(-50%, -50%); z-index: 9999; width: 100px; height: 100px;" />
        <!-- Sidebar with Tabs -->

        <aside class="w-auto p-4 bg-white shadow h-full scrollbar-thin scrollbar-thumb-blue-600 scrollbar-track-blue-300">
            <ul id="tabsList" class="space-y-2">
                <!-- Loop through pages -->
                @forelse ($report->reportPages as $page)
                    <li class="tab-item bg-blue-200 p-2 rounded cursor-pointer relative flex justify-between items-center"
                        data-target="#tab{{ $page->id }}" data-id="{{ $page->id }}">
                        <div class="flex gap-2">
                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M1 1h15M1 7h15M1 13h15"></path>
                            </svg>
                            <span id="leftMenuPageName-{{ $page->id }}" class="text-sm">{{ $page->name }}</span>
                            {{-- <div class="absolute right-0 top-0 h-full w-5 bg-green-500 clip-path-arrow"></div> --}}

                        </div>

                        <!-- Toggle switch to update page status -->
                        <label for="toggle-{{ $page->id }}" class="inline-flex relative items-center cursor-pointer">
                            <input type="checkbox" id="toggle-{{ $page->id }}" class="sr-only toggle"
                                data-page-id="{{ $page->id }}" {{ $page->is_active === 1 ? 'checked' : '' }}>
                            <span class="w-10 h-4 bg-gray-300 rounded-full flex items-center">
                                <span class="w-4 h-4 bg-white rounded-full shadow transform transition-transform"></span>
                            </span>
                        </label>
                        {{-- <div class="absolute right-0 top-0 h-full w-5 bg-green-500 clip-path-arrow"></div> --}}

                    </li>
                @empty
                    <p class="text-gray-500">No pages found</p>
                @endforelse
            </ul>

            <!-- Button to create a new page -->
            <button id="createPageBtn" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-lg w-full">
                Create Page
            </button>
        </aside>

        <!-- Main Content Area -->
        <section class="w-3/4 p-6">
            <!-- Template dropdown positioned above the content area -->
            <!-- <div class="flex justify-end w-full">
                <label for="layout-select" class="font-bold lg:w-2/12 md:w-4/12 w-full" style="margin-top: 8px;">Copy
                    Template:</label>
                <select id="templateDropdownSelect" class="layout-select border p-2 lg:w-2/12 md:w-4/12 w-full"
                    style="margin-right: 60px;">
                    <option selected value="">Choose a Template</option>

                    @forelse ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->title }}</option>
                    @empty
                        <option disabled>No templates available</option>
                    @endforelse
                </select>
                <button id="templateDropdown"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg btn-gradient">Submit</button>

                    <div class="flex space-x-4">
                <a href="{{ route('reports.index') }}"
                    class="hover:text-gray-300 text-white btn-gradient p-2 rounded" style="margin-left: 10px;">Reports</a>
                <a href="{{ route('templates.index') }}"
                    class="hover:text-gray-300 text-white btn-gradient p-2 rounded" style="margin-right: 0px;">Templates</a>

            </div> -->
            </div>
            <!-- Modal for confirmation -->
            <!-- Modal for confirmation (small version) -->
            <div id="confirmationModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white p-4 rounded-lg w-1/4 shadow-lg">
                    <h2 class="text-lg font-semibold mb-4">Confirm Template Copy</h2>
                    <p>Are you sure you want to copy the template code?</p>
                    <div class="flex justify-end mt-4">
                        <button id="cancelBtn" class="bg-gray-500 text-white px-4 py-2 rounded-lg mr-2">Cancel</button>
                        <button id="confirmBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Confirm</button>
                    </div>
                    
                </div>
            </div>

            <!-- Error Modal for no template selected -->
            <div id="errorModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white p-4 rounded-lg w-1/4 shadow-lg">
                    <p>Please select a template first.</p>
                    <div class="flex justify-end mt-4">
                        <button id="closeErrorModal" class="bg-gray-500 text-white px-4 py-2 rounded-lg">Close</button>
                    </div>
                  
                    
                </div>
                
               
            </div>

            <!-- Content area with a card-like design for report and actions -->
            <div class="bg-white shadow p-4 rounded-lg mt-4 mb-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold" id="reportTitleText">{{ $report->title }}</h2>
                    <div>
                        
                        <button class="text-blue-500 hover:text-blue-600 update-status-button" id="updateToPublishedBtn"
                            style="margin-right:100px;" data-id="{{ $report->id }}" data-status="{{ $report->status }}">
                            {{ $report->status === 'draft' ? 'Publish Report' : 'Save as Draft' }}
                        </button>
                       
                        <div id="publishReportModal"
                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
                            <div class="bg-white p-4 rounded-lg w-1/4 shadow-lg">
                                <h2 class="text-lg font-semibold mb-4">Publish Report</h2>
                                <p>Are you sure you want to update this report to Published?</p>
                                <div class="flex justify-end mt-4">
                                    <button id="cancelPublishBtn"
                                        class="bg-gray-500 text-white px-4 py-2 rounded-lg mr-2">Cancel</button>
                                    <button id="confirmPublishBtn"
                                        class="bg-blue-500 text-white px-4 py-2 rounded-lg">Confirm</button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for saving report as draft -->
                        <div id="draftReportModal"
                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
                            <div class="bg-white p-4 rounded-lg w-1/4 shadow-lg">
                                <h2 class="text-lg font-semibold mb-4">Draft Report</h2>
                                <p>Are you sure you want to update this report to Draft?</p>
                                <div class="flex justify-end mt-4">
                                    <button id="cancelDraftBtn"
                                        class="bg-gray-500 text-white px-4 py-2 rounded-lg mr-2">Cancel</button>
                                    <button id="confirmDraftBtn"
                                        class="bg-blue-500 text-white px-4 py-2 rounded-lg">Confirm</button>
                                </div>
                            </div>
                        </div>

                        <button class="text-blue-500 hover:text-blue-600 edit-button" id="editTitleBtn">Edit</button>
                    </div>
                </div>

                <!-- Edit input (initially hidden) -->
                <div id="editTitleContainer" class="hidden mt-2">
                    <input type="text" id="reportTitleInput" value="{{ $report->title }}"
                        class="border p-2 rounded w-full" name="title" />
                    <div class="text-red-500 text-sm mt-1 error-message" data-error="title"></div>
                    <button id="saveTitleBtn" class="bg-blue-500 text-white text-sm p-2 rounded mt-2">Save</button>
                    <button id="cancelEditBtn"
                        class="bg-gray-300 text-black text-sm p-2 rounded mt-2 ml-2">Cancel</button>
                </div>
            </div>

            <!-- Content area with tabs and corresponding content -->
            <div class="flex">
                <!-- Right side for tab content -->
                <div class="w-full" id="tabContent">
                    @forelse ($report->reportPages as $page)
                        <div id="tab{{ $page->id }}" class="tab-content hidden bg-blue-50 p-4 rounded shadow mb-4">
                            <div class="flex items-center justify-between">
                                <h3 id="pageName-{{ $page->id }}" class="text-lg font-medium mb-2">
                                    {{ $page->name }}</h3>
                                <button class="text-blue-500 hover:text-blue-600 edit-button"
                                    data-id="{{ $page->id }}" data-name="{{ $page->name }}">Edit</button>
                            </div>

                            <div id="editPageForm-{{ $page->id }}" class="edit-form hidden mb-2">
                                <input type="text" id="editInput-{{ $page->id }}"
                                    class="border rounded p-2 w-full" value="{{ $page->name }}" />
                                <div class="flex space-x-2 mt-2">
                                    <button class="bg-blue-500 text-white text-sm px-4 py-2 rounded update-button"
                                        data-id="{{ $page->id }}">Update</button>
                                    <button class="bg-gray-500 text-white text-sm px-4 py-2 rounded cancel-button"
                                        data-id="{{ $page->id }}">Cancel</button>
                                </div> 
                            </div>

                            @includeIf(
                                'reports_layout.forms.' . (!empty($page->slug) ? $page->slug : 'custom-page'),
                                ['pageData' => $page->pageData, 'address'=> $address,'firstName' => $firstName, 'lastName' => $lastName,'created_At'=> $date]
                            )
                        </div>
                    @empty
                        <p class="text-gray-500">No content available</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- custom page content --}}
        <template id="custom-page-content">
            @includeIf('reports_layout.forms.custom-page')
        </template>

    @endsection
