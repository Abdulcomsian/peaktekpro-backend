@extends('layouts.template-layout')

@section('title', 'Templates')

@section('content')
    <section>
        <div class=" mx-auto p-4">
            <!-- Header with Title and Create Button -->
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-700">Templates</h1>
                <div class="flex gap-2 ml-auto"> 
        <a href="{{ route('reports.index') }}" class="text-white hover:text-gray-300 btn-gradient p-2 rounded">
            Back To Reports
        </a>
        <button onclick="openModal()" class="btn-gradient text-white px-4 py-2 rounded hover:bg-blue-600">
            Create Templates
        </button>
    </div>
            </div>

            <!-- Responsive Table -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full border border-gray-300">
                    <!-- <thead>
                            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">S.No</th>
                                <th class="py-3 px-6 text-left">Title</th>
                                <th class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead> -->
                    <!-- <tbody class="text-gray-700 text-sm font-light">
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
                        </tbody> -->
                </table>
            </div>


            <!-- Card Grid -->
            <div id="cardGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-3">
                <!-- Cards will be dynamically inserted here -->
            </div>
            <div class="mt-3">
                {!! $templates->links('vendor.pagination.tailwind') !!}
            </div>
        </div>
    </section>

    <!-- Create Template Modal -->
    <div id="modal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 w-1/2 max-w-md">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Create Template</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form action="{{ route('templates.store') }}" method="post" id="storeTemplateForm">
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

    <!-- Delete Confirmation Modal -->
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
        // Pass the $templates data to the frontend and adjust its structure
        const templatesData = @json($templates->items()).map(template => {
            // Fetch the first template page's data (assuming a similar structure to reports)
            // const templatePage = templates.template_pages[0]; // Adjust this based on your actual relationship
            // const templatePageData = templatePage ? templatePage.page_data : null;

            const templatePage = template.template_pages?.[
            0]; // Optional chaining to safely access the first element
            const templatePageData = templatePage ? templatePage.page_data : null; // Safely access page_data



            // Extract required data
            const template_title = templatePageData ? templatePageData.json_data.report_title : 'No title available';
            const title = template.title || template_title;
            console.log(title);

            const siteAddress = templatePageData ? templatePageData.json_data.company_address :
                'No address available';
            const description = templatePageData ? templatePageData.json_data.intro_text :
                'No description available';
            const price = template.price ? `$${template.price.toFixed(2)}` : '$0.00';
            // const tag = template.status === 'published' ? 'PUBLISHED' : 'DRAFT';
            const image = templatePageData && templatePageData.json_data.primary_image ?
                templatePageData.file_url + '/' + templatePageData.json_data.primary_image.path :
                'https://picsum.photos/536/354';
            const companyAddress = @json($address);
            return {
                templateId: template.id, // Map title to reportName
                reportName: title, // Map title to reportName
                siteAddress: companyAddress, // Use company_address
                description: description, // Use intro_text
                price: price, // Format price if available
                // tag: tag, // Map status to tag
                image: image, // Use primary_image path
            };
        });

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
            const cardGrid = $('#cardGrid');

            // Use the adjusted dynamic data
            $.each(templatesData, function(index, item) {
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
                // switch (item.tag) {
                //     case 'DRAFT':
                //         statusTag.addClass('bg-blue-500').text('DRAFT');
                //         break;
                //     case 'PUBLISHED':
                //         statusTag.addClass('bg-green-500').text('PUBLISHED');
                //         break;
                //     default:
                //         statusTag.addClass('bg-gray-500').text(item.tag);
                // }

                imageContainer.append(image).append(statusTag);

                // Three Dots Menu
                const menuContainer = $('<div>').addClass('absolute top-2 right-6 cursor-pointer');
                const threeDots = $('<div>').html('...').addClass(
                    'text-3xl text-white');

                let editTemplateRoute = "{{ route('templates.edit', ['id' => ':id']) }}"
                editTemplateRoute = editTemplateRoute.replace(':id', item.templateId)


                // Dropdown Menu
                const dropdownMenu = $('<div>')
                    .addClass('absolute right-0 mt-2 w-32 bg-white shadow-lg rounded-lg hidden z-10')
                    .append(
                        $('<ul>').addClass('text-sm text-gray-700').html(`
                            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer edit-template">
                                <a href="${editTemplateRoute}">Edit Template</a>
                            </li>

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
                // dropdownMenu.find('.edit-template').on('click', function() {
                //     alert(`Edit Template: ${item.reportName}`);
                // });

                dropdownMenu.find('.view-template').on('click', function() {
                    alert(`View Template: ${item.reportName}`);
                });

                // Card content
                const content = $('<div>').html(`
                    <h3 class="text-xl font-bold text-gray-700">${item.reportName}</h3>
                    <p class="text-gray-600">${item.siteAddress}</p>
                     <div class="text-gray-600 break-words">${item.description}</div>
                    <p class="text-gray-800 font-bold mt-2">${item.price}</p>
                `);

                // Append image container, menu, and content to card
                card.append(imageContainer).append(menuContainer).append(content);

                // Append card to card grid
                cardGrid.append(card);
            });

            // Form submission handling
            $('#storeTemplateForm').submit(function(e) {
                e.preventDefault();
                $(this).find('button[type="submit"]').prop('disabled', true);
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
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                let errorContainer = $(`[data-error="${field}"]`);
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
                });
            });
        });

        // Delete template functionality
        let deleteTemplateId = null;

        function openDeleteModal(templateId) {
            deleteTemplateId = templateId;
            $('#deleteModal').removeClass('hidden');
        }

        function closeDeleteModal() {
            $('#deleteModal').addClass('hidden');
            deleteTemplateId = null;
        }

        function confirmDelete() {
            if (!deleteTemplateId) {
                showErrorNotification('Template not found');
                return;
            }

            $.ajax({
                url: `{{ route('templates.destroy', ['id' => ':id']) }}`.replace(':id', deleteTemplateId),
                type: 'DELETE',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content')
                },
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
    </script>
@endpush
