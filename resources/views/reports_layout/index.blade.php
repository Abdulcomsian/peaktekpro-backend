@extends('layouts.report-layout')

@section('title', 'Reports')

@section('content')
    <section>
        <div class=" mx-auto p-4">
            <!-- Header with Title and Create Button -->
            <div class="flex items-center justify-between mb-4 mr-4">
                <h1 class="text-2xl font-bold text-gray-700">Design Packet</h1>
                <!-- <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 btn-gradient">
                    Manage Report
                </button> -->
            </div>

            <!-- Responsive Table -->
            <div class="overflow-x-auto bg-white">
                <table class="min-w-full border border-gray-500">
                    <thead>
                        <tr class="bg-gray-100 text-gray-1000 text-sm ">
                            <th class="py-1 px-4 text-left font-normal">File Name:</th>
                            <th class="py-1 px-4 text-left font-normal">Link:</th>
                            <th class="py-1 px-4 text-left font-normal">Action:</th>
                        </tr>

                    </thead>
                    <tbody class="text-gray-500 text-sm font-light divide-y divide-gray-100">
                        @forelse ($reports as $report)
                            <tr class="border-b border-gray-100 hover:bg-gray-100">
                            <td class="py-1 px-4 text-left">{{ $report->reportPages->first()?->pageData?->json_data['report_title'] ?? $report->template->title }}</td>

                            @if ($report->status == 'published')
                                    <td class="py-1 px-4 text-left">
                                    <a class="downloadReportPDF text-blue-500 hover:text-blue-600 cursor-pointer"
                                        data-url="{{ route('reports.download-pdf', $report->id) }}">
                                        View PDF
                                    </a>
                                    </td>
                                    @elseif($report->status == 'draft')
                                    <td class="py-1 px-4 text-left">
                                    <a class="text-blue-500 hover:text-blue-600 cursor-pointer"
                                        data-url="#">
                                        Not Yet
                                    </a>
                                    </td>
                                @endif

                                <td class="py-1 px-4 text-left">
                                    <a href="{{ route('reports.edit', ['id' => $report->id]) }}"
                                        class="text-blue-500 hover:text-blue-600">Edit</a>
                                     
                                    <!-- <button onclick="openDeleteModal({{ $report->id }})"
                                        class="text-red-500 hover:text-red-600 ml-4">Delete</button> -->
                                </td>
                            
                             
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-3 px-6 text-center">No reports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {!! $reports->links('vendor.pagination.tailwind') !!}
            </div>
            <div class="flex justify-end mt-2 mb-4">
                <button onclick="openModal()" class="bg-gray-400 text-white px-6 py-1 rounded-full hover:bg-gray-600">
                    Add Report
                </button>
            </div>

        </div>
    </section>

    <!-- Create Report Modal -->
    <div id="modal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 w-1/2 max-w-md">
            <form action="{{ route('reports.store') }}" method="post" id="storeReportLayoutForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Select Template</label>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Choose from existing templates</span>
                        <!-- <a href="{{ route('templates.index') }}"
                            class="bg-blue-500 btn-gradient text-white px-4 py-2 rounded text-sm">
                            Manage Templates
                        </a> -->
                    </div>
                    <select name="template_id" class="w-full border border-gray-300 rounded p-2 mt-2" required>
                        <option value="">Select a Template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->title }}</option>
                        @endforeach
                    </select>
                    @error('template_id')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Delete Report</h2>
            <p class="text-gray-700 mb-4">Are you sure you want to delete this report?</p>
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
        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        let deleteReportId = null;

        function openDeleteModal(reportId) {
            deleteReportId = reportId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            deleteReportId = null;
        }

        function confirmDelete() {
            if (!deleteReportId) return;

            fetch(`/reports/${deleteReportId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to delete report');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the report');
            });
        }

        // Download PDF handling
     

        document.querySelectorAll('.downloadReportPDF').forEach(button => {
            button.addEventListener('click', function() {
                window.location.href = this.dataset.url;
            });
        });
      


        // Form submission handling
        $('#storeReportLayoutForm').submit(function(e) {
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
                            await showSuccessNotification(
                                'Report Layout created successfully!');
                            window.location.href = response.redirect_to;
                        } else {
                            showErrorNotification('Please Select a Template!');
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
                            $('button[type="submit"]', '#storeReportLayoutForm').prop(
                                'disabled', false);
                        }
                    }
                });
            });

       

    </script>
@endpush
