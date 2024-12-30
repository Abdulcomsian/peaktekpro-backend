@extends('layouts.report-layout')

@section('title', 'Create Report')

@push('styles')
{{-- load quill css --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
{{-- load dropzone --}}
<link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />

<style>
    .dropzone .dz-preview .dz-details .dz-filename {
        display: none;
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

    // show file on load in dropzone
    function showFileOnLoadInDropzone(dropzoneInstance, fileData) {
        if (fileData.name && fileData.size && fileData.url && fileData.path && fileData.type) {
            // Simulate adding the existing file
            dropzoneInstance.emit("addedfile", fileData);
            let fileUrl = `${fileData.url}/${fileData.path}`;
            dropzoneInstance.emit("thumbnail", fileData, fileUrl); // Set the thumbnail

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
                dropzoneInstance.emit("thumbnail", mockFile, fileUrl); // Set the thumbnail

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

        // edit report title end

        // left menu pages start

        // Show the first tab content by default
        $(".tab-content").hide();
        $("#content1").show();

        // Tab click handler
        $(document).on('click', '.tab-item', function() {
            $(".tab-item").removeClass("bg-blue-400").addClass("bg-blue-200");
            $(this).addClass("bg-blue-400");

            // Show the related content
            $(".tab-content").hide();
            $($(this).data("target")).fadeIn();

            // assign active page id
            pageId = $(this).data('id')

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
<li class="tab-item bg-blue-200 p-2 rounded cursor-pointer flex justify-between items-center"
    data-target="#tab${response.page.id}" data-id="${response.page.id}">
    <span>${response.page.name}</span>
    <label for="toggle-${response.page.id}" class="inline-flex relative items-center cursor-pointer">
        <input type="checkbox" id="toggle-${response.page.id}" class="sr-only toggle" data-page-id="${response.page.id}"
            ${response.page.is_active === 1 ? 'checked' : ''} />
        <span class="w-10 h-4 bg-gray-300 rounded-full flex items-center">
            <span class="w-6 h-6 bg-white rounded-full shadow transform transition-transform"></span>
        </span>
    </label>
</li>
`);

                // Append the new page content
                $('#tabContent').append(`
<div id="tab${response.page.id}" class="tab-content hidden bg-blue-50 p-4 rounded shadow mb-4">
    <h3 class="text-lg font-medium mb-2">${response.page.name}</h3>
    <p>Content for ${response.page.name}</p>
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
            <div class="bg-white custom-page-quill-editor"></div>
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
        let fieldName = $(this).attr('name');
        let fieldValue = $(this).val();
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

    document.getElementById('updateToPublishedBtn').addEventListener('click', function () {
    const reportId = this.getAttribute('data-id');
    const currentStatus = this.getAttribute('data-status');
    const newStatus = currentStatus === 'draft' ? 'published' : 'draft';
    const updateStatusUrl = "{{ route('reports.update-status', ':id') }}";

    // Confirm action with the user
    if (!confirm(`Are you sure you want to update this report to ${newStatus === 'published' ? 'Published' : 'Draft'}?`)) return;

    const url = updateStatusUrl.replace(':id', reportId);

    // AJAX Request
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ status: newStatus }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {  // Check for 'status' instead of 'success'
            console.log('data', data);
            // Update the button text and data-status attribute dynamically
            console.log(newStatus)
            this.setAttribute('data-status', newStatus);
            this.textContent = newStatus === 'draft' ? 'Publish Report' : 'Save as Draft';
            showSuccessNotification(data.message);
            // if(newStatus == 'published') {
            //     document.getElementById('downloadReportPDF').style.display = 'block'
            // }
            
        } else {
            showErrorNotification(data.message || 'An error occurred while updating the status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});


document.getElementById('downloadReportPDF').addEventListener('click', function () {
    const reportId = this.getAttribute('data-id');
    const downloadPdfUrl = "{{ route('reports.download-pdf', ':id') }}";
    const url = downloadPdfUrl.replace(':id', reportId);

    // Show the loader before starting the download
    document.getElementById('loadingSpinner').style.display = 'block';

    // Download PDF via Fetch
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.blob(); // Convert the response to a Blob for the file
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `report-${reportId}.pdf`; // Specify the filename
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link); // Clean up
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        })
        .finally(() => {
            // Hide the loader after the download is done
            document.getElementById('loadingSpinner').style.display = 'none';
        });
});


document.addEventListener('DOMContentLoaded', () => {
    const url = "{{ route('reports.copy-template') }}";
    const reportId = @json($report->id ?? '');

    // Handle dropdown change
    document.getElementById('templateDropdown').addEventListener('change', function () {
        const selectedTemplateId = this.value;

        // Show confirmation alert
        if(selectedTemplateId) {
            if (confirm('Are you sure you want to copy the template code?')) {
                // If user confirms, send the AJAX request
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ 
                        template_id: selectedTemplateId, 
                        report_id: reportId 
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                    showSuccessNotification(data.message); // Display success notification
                    setTimeout(() => {
                        document.getElementById('templateDropdown').value = '';
                        window.location.reload(); // Reload the page after a delay
                    }, 2000); // Delay of 2000 milliseconds (2 seconds)
                })
                    .catch(error => {
                        // Handle error
                        console.error('Error:', error);
                        showErrorNotification('Only PDF files are allowed.');
                    });
            }
        }
    });
});
</script>
@endpush


@section('content')
<section class="h-screen flex">
<img id="loadingSpinner" src="{{ asset('assets/images/loader.gif') }}" alt="Loading" 
style="display: none; position: fixed; top: 50%; left: 60%; transform: translate(-50%, -50%); z-index: 9999; width: 250px; height: 200px;" />
    <!-- Sidebar with Tabs -->
    <aside
        class="w-1/4 p-4 bg-white shadow overflow-y-auto h-full scrollbar-thin scrollbar-thumb-blue-600 scrollbar-track-blue-300">
        <ul id="tabsList" class="space-y-2">
            <!-- Loop through pages -->
            @forelse ($report->reportPages as $page)
            <li class="tab-item bg-blue-200 p-2 rounded cursor-pointer flex justify-between items-center"
                data-target="#tab{{ $page->id }}" data-id="{{ $page->id }}">
                <span id="leftMenuPageName-{{ $page->id }}">{{ $page->name }}</span>
                <!-- Toggle switch to update page status -->
                <label for="toggle-{{ $page->id }}" class="inline-flex relative items-center cursor-pointer">
                    <input type="checkbox" id="toggle-{{ $page->id }}" class="sr-only toggle"
                        data-page-id="{{ $page->id }}" {{ $page->is_active === 1 ? 'checked' : '' }}>
                    <span class="w-10 h-4 bg-gray-300 rounded-full flex items-center">
                        <span class="w-6 h-6 bg-white rounded-full shadow transform transition-transform"></span>
                    </span>
                </label>
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
    <div class="flex justify-end w-full">
        <label for="layout-select" class="font-bold lg:w-2/12 md:w-4/12 w-full" style="margin-top: 8px;">Copy Template:</label>
                    <select id="templateDropdown" class="layout-select border p-2 lg:w-2/12 md:w-4/12 w-full">
                    <option selected value="">Choose a Template</option>
            @forelse ($templates as $template)
                <option value="{{ $template->id }}">{{ $template->title }}</option>
            @empty
                <option disabled>No templates available</option>
            @endforelse
                    </select>
    </div>

    <!-- Content area with a card-like design for report and actions -->
    <div class="bg-white shadow p-4 rounded-lg mt-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold" id="reportTitleText">{{ $report->title }}</h2>
            <div>
            <button 
    class="text-blue-500 hover:text-blue-600 update-status-button" 
    id="updateToPublishedBtn" 
    style="margin-right:100px;"
    data-id="{{ $report->id }}"
    data-status="{{ $report->status }}">
    {{ $report->status === 'draft' ? 'Publish Report' : 'Save as Draft' }}
</button>
                @if($report->status === 'published')
                <button 
                    class="text-blue-500 hover:text-blue-600 update-status-button" 
                    id="downloadReportPDF" style="margin-right:100px;"
                    data-id="{{ $report->id }}">
                    Download PDF
                </button>
                @endif
                <button class="text-blue-500 hover:text-blue-600 edit-button" id="editTitleBtn">Edit</button>
            </div>
        </div>

        <!-- Edit input (initially hidden) -->
        <div id="editTitleContainer" class="hidden mt-2">
            <input type="text" id="reportTitleInput" value="{{ $report->title }}"
                class="border p-2 rounded w-full" name="title" />
            <div class="text-red-500 text-sm mt-1 error-message" data-error="title"></div>
            <button id="saveTitleBtn" class="bg-blue-500 text-white text-sm p-2 rounded mt-2">Save</button>
            <button id="cancelEditBtn" class="bg-gray-300 text-black text-sm p-2 rounded mt-2 ml-2">Cancel</button>
        </div>
    </div>

    <!-- Content area with tabs and corresponding content -->
    <div class="flex">
        <!-- Right side for tab content -->
        <div class="w-full" id="tabContent">
            @forelse ($report->reportPages as $page)
                <div id="tab{{ $page->id }}" class="tab-content hidden bg-blue-50 p-4 rounded shadow mb-4">
                    <div class="flex items-center justify-between">
                        <h3 id="pageName-{{ $page->id }}" class="text-lg font-medium mb-2">{{ $page->name }}</h3>
                        <button class="text-blue-500 hover:text-blue-600 edit-button" data-id="{{ $page->id }}"
                                data-name="{{ $page->name }}">Edit</button>
                    </div>

                    <div id="editPageForm-{{ $page->id }}" class="edit-form hidden mb-2">
                        <input type="text" id="editInput-{{ $page->id }}" class="border rounded p-2 w-full"
                               value="{{ $page->name }}" />
                        <div class="flex space-x-2 mt-2">
                            <button class="bg-blue-500 text-white text-sm px-4 py-2 rounded update-button"
                                    data-id="{{ $page->id }}">Update</button>
                            <button class="bg-gray-500 text-white text-sm px-4 py-2 rounded cancel-button"
                                    data-id="{{ $page->id }}">Cancel</button>
                        </div>
                    </div>

                    @includeIf(
                        'reports_layout.forms.' . (!empty($page->slug) ? $page->slug : 'custom-page'),
                        ['pageData' => $page->pageData]
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
