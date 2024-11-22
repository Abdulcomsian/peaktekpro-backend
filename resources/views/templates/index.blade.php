@extends('layouts.template-layout')

@section('title', 'Templates')

@section('content')
    <section>
        <div class="container mx-auto p-4">
            <!-- Header with Title and Create Button -->
            <div class="flex items-center justify-between mb-4">

                <h1 class="text-2xl font-bold text-gray-700">
                    <input type="text" id="_accessToken" value="{{ $accessToken }}"/>
                    Templates</h1>
                <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Create
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
                                    <a href="{{ route('templates.edit', $template->id) }}"
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
                    <button onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2">Cancel</button>
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
                <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded mr-2"
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
                url: `{{ route('templates.destroy', ':id') }}`.replace(':id', deleteTemplateId),
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

        // delete template end

    </script>
@endpush
