@extends('layouts.report-layout')

@section('title', 'Reports')

@section('content')
    <section>
        <div class=" mx-auto p-4">
            <!-- Header with Title and Create Button -->
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-700">Reports</h1>
                <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 btn-gradient">
                    Create Report
                </button>
            </div>

            <!-- Responsive Table -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full border border-gray-300">
                    <!-- <thead>
                                                                                                                                                                                                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                                                                                                                                                                                    <th class="py-3 px-6 text-left">S.No</th>
                                                                                                                                                                                                    <th class="py-3 px-6 text-left">Title</th>
                                                                                                                                                                                                    @if ($reports->where('status', 'published')->count() > 0)
    <th class="py-3 px-6 text-left">File</th>
    @endif
                                                                                                                                                                                                    <th class="py-3 px-6 text-left">Status</th>
                                                                                                                                                                                                    <th class="py-3 px-6 text-center">Actions</th>
                                                                                                                                                                                                </tr>
                                                                                                                                                                                            </thead> -->
                    <tbody class="text-gray-700 text-sm font-light">
                        @forelse ($reports as $report)
                            <!-- <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                                                                                                                                                                        <td class="py-3 px-6 text-left w-1">{{ $loop->iteration }}</td>
                                                                                                                                                                                                        <td class="py-3 px-6 text-left">{{ $report->title }}</td>
                                                                                                                                                                                                        <td class="py-3 px-6 text-left">
                                                                                                                                                                                                            @if ($report->status == 'published')
    <a class="downloadReportPDF text-blue-500 hover:text-blue-600 cursor-pointer"
                                                                                                                                                                                                                    data-id="{{ $report->id }}">
                                                                                                                                                                                                                    Download PDF
                                                                                                                                                                                                                </a>
    @endif
                                                                                                                                                                                                        </td>
                                                                                                                                                                                                        <td class="py-3 px-6 text-left">
                                                                                                                                                                                                            @if ($report->status === 'draft')
    <span
                                                                                                                                                                                                                    class="inline-block px-3 py-1 text-sm font-semibold text-gray-800 bg-gray-200 rounded-full">Draft</span>
@else
    <span
                                                                                                                                                                                                                    class="inline-block px-3 py-1 text-sm font-semibold text-green-800 bg-green-200 rounded-full">Published</span>
    @endif
                                                                                                                                                                                                        </td>
                                                                                                                                                                                                        <td class="py-3 px-6 text-center">
                                                                                                                                                                                                            <a href="{{ route('reports.edit', ['id' => $report->id]) }}"
                                                                                                                                                                                                                class="text-blue-500 hover:text-blue-600">Edit</a>
                                                                                                                                                                                                            <button onclick="openDeleteModal({{ $report->id }})"
                                                                                                                                                                                                                class="text-red-500 hover:text-red-600 ml-4">Delete</button>
                                                                                                                                                                                                        </td>
                                                                                                                                                                                                    </tr> -->
                        @empty
                            <tr>
                                <td colspan="4" class="py-3 px-6 text-center">No reports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>


            <div id="cardGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-3">
                <!-- Cards will be dynamically inserted here -->
            </div>
            <div class="mt-3">
                {!! $reports->links('vendor.pagination.tailwind') !!}
            </div>
        </div>
    </section>

    <!-- Create Report Modal -->
    <div id="modal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 w-1/2 max-w-md">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Create Report Layout</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form action="{{ route('reports.store') }}" method="post" id="storeReportLayoutForm">
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
        // Pass the $reports data to the frontend and adjust its structure
        const reportsData = @json($reports->items()).map(report => {
            // Fetch the first report page's data
            // const reportPage = report.report_pages?.[0];
            // const reportPage6 = report.report_pages?.[6];

            const reportPage = report.report_pages.find(page => page.slug === 'introduction');
            const reportPage6 = report.report_pages.find(page => page.slug === 'quote-details');


            // Check if reportPage exists and fetch page data
            const reportPageData = reportPage ? reportPage.page_data : null;
            const reportPageData6 = reportPage6 ? reportPage6.page_data : null;
            console.log("Reports Page Data", reportPageData);

            // Debugging: Log reportPage and reportPageData

            // Extract required data
            const title = reportPageData ? reportPageData.json_data.report_title : 'No company name available';
            const grandTotal = reportPageData6 ? reportPageData6.json_data.grand_total : null;
            const price = grandTotal ? `$${parseFloat(grandTotal).toFixed(2)}` :
                `$${report.price ? report.price.toFixed(2) : '0.00'}`;

            const companyName = reportPageData ? reportPageData.json_data.company_name :
                'No company name available';
            const companyAddress = reportPageData && reportPageData.json_data.company_address ? reportPageData
                .json_data.company_address :
                'No address available';
            const createdAt = reportPageData ? report.created_at :
                'Not yet Created';
            // const primaryImagePath = reportPageData && reportPageData.json_data.primary_image ? reportPageData.json_data.primary_image.path : 'https://picsum.photos/536/354';
            const primaryImagePath = reportPageData && reportPageData.json_data.primary_image ?
                reportPageData.file_url + '/' + reportPageData.json_data.primary_image.path :
                'https://picsum.photos/536/354';

            return {
                id: report.id, // Map ID
                reportName: title, // Map title to reportName
                siteAddress: companyAddress, // Use company_address
                description: reportPageData && reportPageData.json_data.intro_text ? reportPageData.json_data
                    .intro_text : 'No description available', // Use intro_text
                price: price, // Format price if available
                tag: report.status === 'published' ? 'PUBLISHED' : 'DRAFT', // Map status to tag
                image: primaryImagePath, // Use primary_image path
                companyName: companyName, // Company name
                createdAt: timeAgo(report.created_at)
            };
        });

        function timeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const secondsAgo = Math.floor((now - date) / 1000);

            if (secondsAgo < 60) return `${secondsAgo} seconds ago`;
            const minutesAgo = Math.floor(secondsAgo / 60);
            if (minutesAgo < 60) return `${minutesAgo} minutes ago`;
            const hoursAgo = Math.floor(minutesAgo / 60);
            if (hoursAgo < 24) return `${hoursAgo} hours ago`;
            const daysAgo = Math.floor(hoursAgo / 24);
            if (daysAgo < 30) return `${daysAgo} days ago`;
            const monthsAgo = Math.floor(daysAgo / 30);
            if (monthsAgo < 12) return `${monthsAgo} months ago`;
            const yearsAgo = Math.floor(monthsAgo / 12);

            return `${yearsAgo} years ago`;
        }

        function openModal() {

            $('#storeReportLayoutForm')[0].reset();
            $('button[type="submit"]', '#storeReportLayoutForm').prop('disabled', false);

            $('#modal').removeClass('hidden');
        }
        // hide create modal
        function closeModal() {

            $('#storeReportLayoutForm')[0].reset();
            $('button[type="submit"]', '#storeReportLayoutForm').prop('disabled', false);

            $('#modal').addClass('hidden');

        }
        $(document).ready(function() {
            const cardGrid = $('#cardGrid');

            // Use the adjusted dynamic data
            $.each(reportsData, function(index, item) {
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
                    case 'DRAFT':
                        statusTag.addClass('bg-blue-500').text('DRAFT');
                        break;
                    case 'PUBLISHED':
                        statusTag.addClass('bg-green-500').text('PUBLISHED');
                        break;
                    default:
                        statusTag.addClass('bg-gray-500').text(item.tag);
                }

                imageContainer.append(image).append(statusTag);

                // Three Dots Menu
                const menuContainer = $('<div>').addClass('relative ');
                const threeDots = $('<div>').html('...').addClass(
                    'text-5xl text-blue-600 absolute -top-40 right-4 cursor-pointer');

                let editReportRoute = "{{ route('reports.edit', ['id' => ':id']) }}"
                editReportRoute = editReportRoute.replace(':id', item.id)

                let downloadReportPdfRoute = "{{ route('reports.download-pdf', ['id' => ':id']) }}"
                downloadReportPdfRoute = downloadReportPdfRoute.replace(':id', item.id)

                // Dropdown Menu
                const dropdownMenu = $('<div>')
                    .addClass(
                        'absolute right-4 -top-36 mt-2 w-32 bg-white shadow-lg rounded-lg hidden z-10')
                    .append(
                        $('<ul>').addClass('text-sm text-gray-700').html(`<a href="${editReportRoute}" class="block px-4 py-2 hover:bg-gray-100 cursor-pointer edit-report">Edit Report</a>
                                <a href="${downloadReportPdfRoute}" class="block px-4 py-2 hover:bg-gray-100 cursor-pointer view-report">View Report</a>
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
                // dropdownMenu.find('.edit-report').on('click', function() {
                //     alert(`Edit Report: ${item.reportName}`);
                // });

                // dropdownMenu.find('.view-report').on('click', function() {
                //     alert(`View Report: ${item.reportName}`);
                // });

                // Card content
                const content = $('<div class="flex flex-col gap-1">').html(`
                    <h3 class="text-xl font-bold text-gray-700 mb-2">${item.reportName}</h3>
                    <p class="text-gray-600">${item.siteAddress}</p>
                    <div class="text-gray-600 break-words">${item.description}</div>
                    <p class="text-gray-800">${item.createdAt}</p>
                    <p class="text-gray-800 font-bold ">${item.price}</p>
                `);

                // Append image container, menu, and content to card
                card.append(imageContainer).append(menuContainer).append(content);

                // Append card to card grid
                cardGrid.append(card);
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
                            showErrorNotification('Error creating report layout!');
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
        });

        // Delete report functionality
        let deleteReportId = null;

        function openDeleteModal(reportId) {
            deleteReportId = reportId;
            $('#deleteModal').removeClass('hidden');
        }

        function closeDeleteModal() {
            $('#deleteModal').addClass('hidden');
            deleteReportId = null;
        }

        function confirmDelete() {
            if (!deleteReportId) {
                showErrorNotification('Report not found');
                return;
            }

            $.ajax({
                url: `{{ route('reports.destroy', ['id' => ':id']) }}`.replace(':id', deleteReportId),
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

        // Download PDF functionality
        document.querySelectorAll('.downloadReportPDF').forEach(button => {
            button.addEventListener('click', function() {
                const reportId = this.getAttribute('data-id');
                const downloadPdfUrl = "{{ route('reports.download-pdf', ':id') }}";
                const url = downloadPdfUrl.replace(':id', reportId);

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                        },
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.blob();
                    })
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = `report-${reportId}.pdf`;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    })
                    .catch(error => {
                        alert('An error occurred. Please try again.');
                    });
            });
        });
    </script>
@endpush
