@extends('layouts.template-layout')

@section('title', 'Create Template')

@section('content')
    <section class="h-screen flex">
        <!-- Sidebar with Tabs -->
        <aside
            class="w-1/4 p-4 bg-white shadow overflow-y-auto h-full scrollbar-thin scrollbar-thumb-blue-600 scrollbar-track-blue-300">
            <ul id="tabsList" class="space-y-2">
                <!-- Loop through pages -->
                @forelse ($template->templatePages as $page)
                    <li class="tab-item bg-blue-200 p-2 rounded cursor-pointer flex justify-between items-center"
                        data-target="#tab{{ $page->id }}" data-id="{{ $page->id }}">
                        <span>{{ $page->name }}</span>
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
                    <p class="text-gray-500">No tabs available</p>
                @endforelse
            </ul>

            <!-- Button to create a new page -->
            <button id="createPageBtn" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-lg w-full">
                Create Page
            </button>
        </aside>



        <!-- Main Content Area -->
        <section class="w-3/4 p-6">
            <!-- Heading at the top of the right side -->
            <div class="bg-white shadow p-4 rounded-lg mb-4">
                <h2 class="text-xl font-semibold" id="templateTitleText">{{ $template->title }}</h2>

                <!-- Edit icon (clickable) -->
                <button id="editTitleBtn" class="text-blue-500 hover:text-blue-700 ml-2">
                    <i class="fas fa-edit"></i> Edit
                </button>

                <!-- Edit input (initially hidden) -->
                <div id="editTitleContainer" class="hidden mt-2">
                    <input type="text" id="templateTitleInput" value="{{ $template->title }}"
                        class="border p-2 rounded w-full" name="title" />
                    <div class="text-red-500 text-sm mt-1 error-message" data-error="title"></div>
                    <button id="saveTitleBtn" class="bg-blue-500 text-white p-2 rounded mt-2">Save</button>
                    <button id="cancelEditBtn" class="bg-gray-300 text-black p-2 rounded mt-2 ml-2">Cancel</button>
                </div>
            </div>


            <!-- Content area with tabs and corresponding content -->
            <div class="flex">
                <!-- Right side for tab content -->
                <div class="w-full" id="tabContent">
                    @forelse ($template->templatePages as $page)
                        <div id="tab{{ $page->id }}" class="tab-content hidden bg-blue-50 p-4 rounded shadow mb-4">
                            <h3 class="text-lg font-medium mb-2">{{ $page->name }}</h3>
                            <p>Content for {{ $page->name }}</p>
                            @includeIf('templates.forms.' . $page->slug)

                        </div>
                    @empty
                        <p class="text-gray-500">No content available</p>
                    @endforelse
                </div>
            </div>
        </section>
    </section>


@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // edit template title start
            // Show input field for editing when edit button is clicked
            $('#editTitleBtn').on('click', function() {
                // Hide the title text, show the input field for editing
                $('#templateTitleText').addClass('hidden');
                $('#editTitleContainer').removeClass('hidden');
            });

            // Cancel editing and hide input field
            $('#cancelEditBtn').on('click', function() {
                $('#templateTitleText').removeClass('hidden');
                $('#editTitleContainer').addClass('hidden');
            });

            // Save the new title using AJAX
            $('#saveTitleBtn').on('click', function() {
                var newTitle = $('#templateTitleInput').val();

                // Clear previous error messages
                $('.error-message').text('');

                // Check if the new title is not empty
                if (newTitle.trim() === '') {

                    $('.error-message[data-error="title"]').text('Title cannot be empty!');

                    return;
                }
                let actionUrl = "{{ route('templates.update.title', $template->id) }}"
                // Make the AJAX call to update the template title
                $.ajax({
                    url: actionUrl,
                    method: 'PUT',
                    data: {
                        title: newTitle,
                        _token: $('meta[name=csrf-token]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            // Update the title on the page and hide the input
                            $('#templateTitleText').text(newTitle).removeClass('hidden');
                            $('#editTitleContainer').addClass('hidden');

                            // show a success message
                            showSuccessNotification(response.message);
                        } else {
                            showErrorNotification('Error updating template title!');
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

            // edit template title end

            // pages start

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
                        url: "{{ route('templates.page-ordering.update', $template->id) }}",
                        method: 'POST',
                        data: {
                            order: order,
                            _token: $('meta[name=csrf-token]').attr('content')
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
                    url: "{{ route('templates.create-page', $template->id) }}",
                    method: 'POST',
                    data: {
                        title: 'Custom Page',
                        _token: $('meta[name=csrf-token]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            // Append the new page to the list dynamically
                            $('#tabsList').append(`
                                <li class="tab-item bg-blue-200 p-2 rounded cursor-pointer flex justify-between items-center"
                                    data-target="#tab${response.page.id}" data-id="${response.page.id}">
                                    <span>${response.page.name}</span>
                                    <label for="toggle-${response.page.id}" class="inline-flex relative items-center cursor-pointer">
                                        <input type="checkbox" id="toggle-${response.page.id}" class="sr-only toggle" data-page-id="${response.page.id}" ${ response.page.is_active === 1 ? 'checked' : '' } />
                                        <span class="w-10 h-4 bg-gray-300 rounded-full flex items-center">
                                            <span class="w-6 h-6 bg-white rounded-full shadow transform transition-transform"></span>
                                        </span>
                                    </label>
                                </li>
                            `);

                            // Append the new page content
                            $('#tabContent').append(`<div id="tab${response.page.id}" class="tab-content hidden bg-blue-50 p-4 rounded shadow mb-4">
                                <h3 class="text-lg font-medium mb-2">${response.page.name}</h3>
                                <p>Content for ${response.page.name}</p>
                            </div>`);

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
            $(document).on('change', 'input[type="checkbox"][data-page-id]', function() {
                var pageId = $(this).data('page-id');
                var status = $(this).prop('checked') ? 1 : 0;

                // Hide the tab content when toggle is clicked
                $(`#tab${pageId}`).addClass('hidden');

                let actionUrl = "{{ route('templates.update-page.status', ['pageId' => ':pageId']) }}"
                actionUrl = actionUrl.replace(':pageId', pageId)
                // Send AJAX request to update page status
                $.ajax({
                    url: actionUrl,
                    method: 'PATCH',
                    data: {
                        status: status,
                        _token: $('meta[name=csrf-token]').attr('content')
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

            // pages end
        });
    </script>
@endpush
