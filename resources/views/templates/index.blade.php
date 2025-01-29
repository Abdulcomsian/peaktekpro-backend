@extends('layouts.template-layout')

@section('title', 'Templates')

@section('content')
    <section>
        <div class=" mx-auto p-4">
            <!-- Header with Title and Create Button -->
            <div class="flex items-center justify-between mb-4">

                <h1 class="text-2xl font-bold text-gray-700">
                    Templates</h1>
                <button onclick="openModal()" class="btn-gradient text-white px-4 py-2 rounded hover:bg-blue-600">
                    Create Templates
                </button>
            </div>

            <!-- Responsive Table -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">S.No</th>
                            <th class="py-3 px-6 text-left">Title</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm font-light">

                        @forelse ($templates as $template)
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left w-1">{{ $loop->iteration }}</td>
                                <td class="py-3 px-6 text-left">{{ $template->title }}</td>
                                <td class="py-3 px-6 text-center">
                                    <a href="{{ route('templates.edit', ['id' => $template->id]) }}"
                                        class="text-blue-500 hover:text-blue-600">Edit</a>
                                    <button onclick="openDeleteModal({{ $template->id }})"
                                        class="text-red-500 hover:text-red-600 ml-4">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-3 px-6 text-center">No templates found.</td>
                            </tr>
                        @endforelse
                        <!-- Repeat rows as needed -->
                    </tbody>
                </table>
            </div>
            <div class="bg-white shadow-md rounded-lg">
                {!! $templates->links('vendor.pagination.tailwind') !!}
            </div>

            <!-- Card Grid -->
            <div id="cardGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-3">
                <!-- Cards will be dynamically inserted here -->
            </div>
        </div>
    </section>

    <!-- create template modal -->
    <div id="modal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 w-1/2 max-w-md">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Create Template</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form action="{{ route('templates.store') }}" method="post" id="storeTemplateForm" method="post">
                @csrf
                <!-- Modal Body -->
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 mb-2">Title</label>
                    <input type="text" id="title" name="title" class="w-full border border-gray-300 rounded p-2" />
                    <!-- Error messages will be appended here dynamically -->
                    <div class="text-red-500 text-sm mt-1 error-message" data-error="title"></div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2">Cancel</button>
                    <button class="bg-blue-500 text-white px-4 py-2 rounded">Submit</button>
                </div>

            </form>
        </div>
    </div>

    <!--Template Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Delete Template</h2>
            <p class="text-gray-700 mb-4">Are you sure you want to delete this template?</p>
            <div class="flex justify-end">
                <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded mr-2"
                    onclick="closeDeleteModal()">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded"
                    onclick="confirmDelete()">
                    Delete
                </button>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        // show create modal
        function openModal() {

            $('#storeTemplateForm')[0].reset();
            $('button[type="submit"]', '#storeTemplateForm').prop('disabled', false);

            $('#modal').removeClass('hidden');
        }
        // hide create modal
        function closeModal() {

            $('#storeTemplateForm')[0].reset();
            $('button[type="submit"]', '#storeTemplateForm').prop('disabled', false);

            $('#modal').addClass('hidden');

        }
        $(document).ready(function() {
            const dummyData = [{
                    "image": "https://picsum.photos/536/354",
                    "reportName": "Test1",
                    "siteAddress": "316 Country Run Circle, Powell, Tennessee",
                    "description": "Created in the future",
                    "price": "$41,282.00",
                    "tag": "OPEN"
                },
                {
                    "image": "https://picsum.photos/536/354",
                    "reportName": "Test2",
                    "siteAddress": "102 Heritage Place, Mt. Juliet, Tennessee",
                    "description": "Created today",
                    "price": "$25,723.54",
                    "tag": "WON"

                },
                {
                    "image": "https://picsum.photos/536/354",
                    "reportName": "Test3",
                    "siteAddress": "7128 Grizzly Creek Lane, Powell, Tennessee",
                    "description": "Created 2 days ago",
                    "price": "$30,000.00",
                    "tag": "LOST"

                }
            ];

            const cardGrid = $('#cardGrid');

            $.each(dummyData, function(index, item) {
                const card = $('<div>').addClass('bg-white shadow-md rounded-lg p-4 relative');

                // Image with status tag
                const imageContainer = $('<div>').addClass('relative');
                const image = $('<img>').attr('src', item.image).attr('alt', item.reportName).addClass(
                    'w-full h-32 object-cover mb-4 rounded-lg'
                );
                const statusTag = $('<div>').addClass(
                    'absolute top-2 left-2 text-white px-2 py-1 rounded text-sm'
                );

                // Set tag color based on status
                switch (item.tag) {
                    case 'OPEN':
                        statusTag.addClass('bg-blue-500').text('OPEN');
                        break;
                    case 'WON':
                        statusTag.addClass('bg-green-500').text('WON');
                        break;
                    case 'LOST':
                        statusTag.addClass('bg-red-500').text('LOST');
                        break;
                    default:
                        statusTag.addClass('bg-gray-500').text(item.status);
                }

                imageContainer.append(image).append(statusTag);

                // Three Dots Menu
                const menuContainer = $('<div>').addClass('absolute top-2 right-6 cursor-pointer');
                const threeDots = $('<div>').html('...').addClass(
                    'text-3xl text-white');

                // Dropdown Menu
                const dropdownMenu = $('<div>')
                    .addClass('absolute right-0 mt-2 w-32 bg-white shadow-lg rounded-lg hidden z-10')
                    .append(
                        $('<ul>').addClass('text-sm text-gray-700').html(`
                <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer edit-report">Edit Report</li>
                <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer view-report">View Report</li>
            `)
                    );

                // Toggle menu on click
                menuContainer.append(threeDots).append(dropdownMenu);
                menuContainer.on('click', function(event) {
                    event.stopPropagation(); // Prevent event from bubbling
                    $('.absolute.right-0.mt-2').not(dropdownMenu).hide(); // Hide other dropdowns
                    dropdownMenu.toggle();
                });

                // Click outside to close dropdown
                $(document).on('click', function() {
                    dropdownMenu.hide();
                });

                // Handle menu actions
                dropdownMenu.find('.edit-report').on('click', function() {
                    alert(`Edit Report: ${item.reportName}`);
                });

                dropdownMenu.find('.view-report').on('click', function() {
                    alert(`View Report: ${item.reportName}`);
                });

                // Card content
                const content = $('<div>').html(`
        <h3 class="text-xl font-bold text-gray-700">${item.reportName}</h3>
        <p class="text-gray-600">${item.siteAddress}</p>
        <p class="text-gray-600">${item.description}</p>
        <p class="text-gray-800 font-bold mt-2">${item.price}</p>
    `);

                // Append image container, menu, and content to card
                card.append(imageContainer).append(menuContainer).append(content);

                // Append card to card grid
                cardGrid.append(card);
            });


            $('#storeTemplateForm').submit(function(e) {

                e.preventDefault();

                // Disable the submit button within the form
                $(this).find('button[type="submit"]').prop('disabled', true);

                // Clear previous error messages
                $('.error-message').text('');

                $.ajax({
                    url: $(this).attr('action'),
                    type: $(this).attr('method'),
                    data: $(this).serialize(),
                    success: async function(response) {
                        if (response.status) {

                            closeModal();

                            await showSuccessNotification('Template created successfully!');

                            window.location.href = response.redirect_to;

                        } else {
                            showErrorNotification('Error creating template!');
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

                            await showErrorNotification('An error occurred. Please try again.');
                            $('button[type="submit"]', '#storeTemplateForm').prop('disabled',
                                false);

                        }
                    }
                })
            })
        })

        // delete template start

        let deleteTemplateId = null;

        // Open the delete modal and set the template ID
        function openDeleteModal(templateId) {
            deleteTemplateId = templateId;
            $('#deleteModal').removeClass('hidden');
        }

        // Close the delete modal
        function closeDeleteModal() {
            $('#deleteModal').addClass('hidden');
            deleteTemplateId = null;
        }

        // Confirm deletion via AJAX
        function confirmDelete() {
            if (!deleteTemplateId) {
                showErrorNotification('Template not found');
                return;
            }

            $.ajax({
                url: `{{ route('templates.destroy', ['id' => ':id']) }}`.replace(':id', deleteTemplateId),
                type: 'DELETE',
                success: function(response) {

                    if (response.status) {

                        closeDeleteModal();

                        showSuccessNotification(response.message);

                        window.location.reload();

                    }

                },
                error: function(error) {
                    showErrorNotification('An error occurred. Please try again.');
                }
            });
        }

        // delete template end
    </script>
@endpush
