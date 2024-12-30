<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Report Title -->
        <div class="mb-4">
            <label for="disclaimer" class="block text-gray-700 text-sm font-medium">Disclaimer</label>
            <small>For example, the terms of an estimate, or a direction to the insurer.</small>
            <input type="text" id="disclaimer" name="authorization_disclaimer" placeholder="Enter disclaimer"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                required value="{{ $pageData->json_data['authorization_disclaimer'] ?? '' }}" />
        </div>

    </form>
</div>

<div class="w-full my-3">
    <!-- Section Container -->
    <div id="authorization-sections-container">
        <!-- First Section -->
        @if (isset($pageData->json_data['sections']) && count($pageData->json_data['sections']) > 0)
            @forelse ($pageData->json_data['sections'] as $section)
                <div class="authorization-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200"
                data-id="{{ $section['id'] }}">
                    <!-- Section Header -->
                    <div class="flex justify-start items-center mb-4 gap-1">
                        <div>
                            <input type="text"
                                class="authorization-section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                                placeholder="Section Title" value="{{ $section['title'] }}"/>
                        </div>
                        <div>
                            <button
                                class="remove-authorization-section-btn text-red-500 hover:text-red-700 font-medium text-sm">
                                X
                            </button>
                            <span class="authorization-section-drag-handle cursor-pointer">↑↓</span>
                        </div>
                    </div>
                    <!-- Rows Container -->
                    <div class="rows-container space-y-4 authorization-rows-container">
                        <!-- Default Row -->
                        @forelse ($section['sectionItems'] as $item)
                        <div class="row flex flex-wrap items-center space-x-4" data-id="{{ $item['rowId'] }}">
                            <span class="row-drag-handle cursor-pointer">↑↓</span>
                            <!-- Item Description -->
                            <input type="text"
                                class="auth-item-description flex-grow border border-gray-300 rounded-md px-2 py-1"
                                placeholder="Item Description" value="{{ $item['description'] }}">

                            <!-- Quantity -->
                            <input type="number" class="auth-item-qty w-20 border border-gray-300 rounded-md px-2 py-1"
                                placeholder="Qty" min="0" step="0.01" value="{{ $item['qty'] }}">

                            <!-- Unit Price -->
                            <input type="number" class="auth-item-price w-20 border border-gray-300 rounded-md px-2 py-1"
                                placeholder="Unit Price" min="0" step="0.01" value="{{ $item['price'] }}">

                            <!-- Line Total -->
                            <div class="line-total-container w-24 text-right flex-1">
                                <span class="line-total block">
                                    ${{ number_format($item['lineTotal'] ?? 0, 2, '.', '') }}
                                </span>
                            </div>

                            <!-- Remove Button -->
                            <button
                                class="remove-authorization-row-btn text-red-500 hover:text-red-700 font-medium text-sm">
                                X
                            </button>
                        </div>

                        @empty

                        @endforelse
                    </div>
                    <!-- Add Row Button -->
                    <button
                        class="add-authorization-row-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">
                        + Add Row
                    </button>
                    <!-- Section Total -->
                    <div class="flex justify-between items-center mt-4">
                        <span class="text-lg font-medium text-gray-700">Section Total:</span>
                        <span class="authorization-section-total text-lg font-semibold text-gray-800">
                            ${{ number_format($section['sectionTotal'] ?? 0, 2, '.', '') }}
                        </span>
                    </div>
                </div>
            @empty
            @endforelse
        @else
            <div class="authorization-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200"
                data-id="{{ \Str::random(8) }}">
                <!-- Section Header -->
                <div class="flex justify-start items-center mb-4 gap-1">
                    <div>
                        <input type="text"
                            class="authorization-section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Section Title" />
                    </div>
                    <div>
                        <button
                            class="remove-authorization-section-btn text-red-500 hover:text-red-700 font-medium text-sm">
                            X
                        </button>
                        <span class="authorization-section-drag-handle cursor-pointer">↑↓</span>
                    </div>
                </div>
                <!-- Rows Container -->
                <div class="rows-container space-y-4 authorization-rows-container">
                    <!-- Default Row -->
                    <div class="row flex flex-wrap items-center space-x-4" data-id="{{ \Str::random(8) }}">
                        <span class="row-drag-handle cursor-pointer">↑↓</span>
                        <!-- Item Description -->
                        <input type="text"
                            class="auth-item-description flex-grow border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Item Description">

                        <!-- Quantity -->
                        <input type="number" class="auth-item-qty w-20 border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Qty" min="0" step="0.01">

                        <!-- Unit Price -->
                        <input type="number" class="auth-item-price w-20 border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Unit Price" min="0" step="0.01">

                        <!-- Line Total -->
                        <div class="line-total-container w-24 text-right flex-1">
                            <span class="line-total block">$0.00</span>
                        </div>

                        <!-- Remove Button -->
                        <button
                            class="remove-authorization-row-btn text-red-500 hover:text-red-700 font-medium text-sm">
                            X
                        </button>
                    </div>
                </div>
                <!-- Add Row Button -->
                <button class="add-authorization-row-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">
                    + Add Row
                </button>
                <!-- Section Total -->
                <div class="flex justify-between items-center mt-4">
                    <span class="text-lg font-medium text-gray-700">Section Total:</span>
                    <span class="authorization-section-total text-lg font-semibold text-gray-800">$0.00</span>
                </div>
            </div>
        @endif

    </div>

    <!-- Grand Total -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <button id="add-authorization-section-btn"
                class=" text-white text-sm bg-blue-600 hover:bg-blue-700 font-medium rounded-md px-4 py-2">
                + Add Section
            </button>
        </div>
        <div>
            <span id="grand-total-label" class="pt-5 text-lg font-medium text-gray-700">Grand Total:</span>
            <span id="authorization-grand-total" class="pt-5 text-lg font-semibold text-gray-800 ml-2">
                ${{ number_format($pageData->json_data['authorization_sections_grand_total'] ?? 0, 2, '.', '') }}
            </span>
        </div>
    </div>
</div>

<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">

        <!-- Text -->
        <div class="mb-4">
            <label for="authorization-footer-text" class="block text-gray-700 text-sm font-medium">Footer</label>
            <div id="authorization-footer-text-quill" class="bg-white"></div>
            <textarea class="hidden" id="authorization-footer-text" name="authorization_footer_text" required>{{ $pageData->json_data['authorization_footer_text'] ?? '' }}</textarea>
        </div>

    </form>
</div>

@push('scripts')
    <script type="text/javascript">
        // save section data
        const saveAuthorizationSectionData = debounce(function(element) {
            let authorizationSectionGrandTotal = $('#authorization-grand-total').text().replace('$', '');
            let sectionContainer = element.closest('.authorization-section')
            let authorizationSectionId = sectionContainer.data('id');
            let sectionTitle = sectionContainer.find('.authorization-section-title').val()
            let sectionOrder = sectionContainer.index();

            let authorizationSection = {
                id: authorizationSectionId,
                title: sectionTitle,
                order: sectionOrder,
                sectionTotal: +sectionContainer.find('.authorization-section-total').text().replace('$', ''),
                sectionItems: []
            }

            sectionContainer.find('.authorization-rows-container').find('.row').each(function(index) {
                let rowId = $(this).data('id');

                authorizationSection.sectionItems.push({
                    rowId: rowId,
                    order: index, // Add order for the row
                    description: $(this).find('.auth-item-description').val(),
                    qty: +$(this).find('.auth-item-qty').val(),
                    price: +$(this).find('.auth-item-price').val(),
                    lineTotal: +$(this).find('.line-total').text().replace('$', ''),
                })

            })

            $.ajax({
                url: "{{ route('reports.authorization-section.update') }}",
                method: 'POST',
                data: {
                    page_id: pageId,
                    authorizationSection: authorizationSection,
                    grandTotal: authorizationSectionGrandTotal
                },
                success: function(response) {
                    showSuccessNotification(response.message);
                },
                error: function(xhr) {
                    showErrorNotification(xhr.responseJSON.message);
                }
            })

        }, 500); // Delay in milliseconds

        // quill
        const authorizationFooterTextQuillOptions = [
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
        var authorizationFooterTextQuill = new Quill('#authorization-footer-text-quill', {
            theme: 'snow',
            modules: {
                toolbar: authorizationFooterTextQuillOptions
            }
        });
        // Set the height dynamically via JavaScript
        authorizationFooterTextQuill.root.style.height = '200px';

        // old text value
        let oldAuthorizationlFooterTextValue = "{!! $pageData->json_data['authorization_footer_text'] ?? '' !!}";

        // Load the saved content into the editor
        authorizationFooterTextQuill.clipboard.dangerouslyPasteHTML(oldAuthorizationlFooterTextValue);
        authorizationFooterTextQuill.on('text-change', function() {
            $('#authorization-footer-text').val(authorizationFooterTextQuill.root.innerHTML);

            //save textarea data
            saveReportPageTextareaData('#authorization-footer-text');
        });

        // Update Grand Total
        function updateAuthorizationGrandTotal() {
            let grandTotal = 0;
            $(".authorization-section-total").each(function() {
                grandTotal += parseFloat($(this).text().replace("$", "")) || 0;
            });
            $("#authorization-grand-total").text(`$${grandTotal.toFixed(2)}`);
        }

        // Function to Create a New Row
        function createRow() {
            return `
    <div class="row flex flex-wrap items-center space-x-4" data-id="${ generateBase64Key(8) }">
        <span class="row-drag-handle cursor-pointer">↑↓</span>
        <input type="text" class="auth-item-description flex-grow border border-gray-300 rounded-md px-2 py-1"
            placeholder="Item Description">
        <input type="number" class="auth-item-qty w-20 border border-gray-300 rounded-md px-2 py-1"
            placeholder="Qty" min="0" step="0.01">
        <input type="number" class="auth-item-price w-20 border border-gray-300 rounded-md px-2 py-1"
            placeholder="Unit Price" min="0" step="0.01">
        <div class="line-total-container w-24 text-right flex-1">
            <span class="line-total block">$0.00</span>
        </div>
        <button class="remove-authorization-row-btn text-red-500 hover:text-red-700 font-medium text-sm">
            X
        </button>
    </div>`;
        }

        // Add New Section with Default Row
        $("#add-authorization-section-btn").click(function() {
            const newSection = `
    <div class="authorization-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="${ generateBase64Key(8) }">
        <div class="flex justify-start items-center mb-4 gap-1">
            <div>
                <input type="text"
                class="authorization-section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                placeholder="Section Title" />
            </div>
            <div>
                <button class="remove-authorization-section-btn text-red-500 hover:text-red-700 font-medium text-sm">
                X
                </button>
                <span class="authorization-section-drag-handle cursor-pointer">↑↓</span>
            </div>
        </div>
        <div class="rows-container space-y-4 authorization-rows-container">
            ${createRow()}
        </div>
        <button class="add-authorization-row-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">
            + Add Row
        </button>
        <div class="flex justify-between items-center mt-4">
            <span class="text-lg font-medium text-gray-700">Section Total:</span>
            <span class="authorization-section-total text-lg font-semibold text-gray-800">$0.00</span>
        </div>
    </div>`;
            $("#authorization-sections-container").append(newSection);

            // Reinitialize the sortable functionality after adding a new section
            makeSectionRowContainerSortable()

        });

        // Add Row in Section
        $(document).on("click", ".add-authorization-row-btn", function() {
            const newRow = createRow();
            $(this).siblings(".authorization-rows-container").append(newRow);
        });

        // Update Row details, Section Total and Grand Total
        $(document).on("input", ".auth-item-qty, .auth-item-price, .auth-item-description", function() {

            const row = $(this).closest(".row");
            const rowId = row.data("id");
            const description = row.find(".auth-item-description").val();
            const qty = parseFloat(row.find(".auth-item-qty").val()) || 0;
            const price = parseFloat(row.find(".auth-item-price").val()) || 0;

            const lineTotal = qty * price;
            row.find(".line-total").text(`$${lineTotal.toFixed(2)}`);

            // Update Section Total
            const section = $(this).closest(".authorization-section");
            let sectionTotal = 0;
            section.find(".line-total").each(function() {
                sectionTotal += parseFloat($(this).text().replace("$", "")) || 0;
            });
            section.find(".authorization-section-total").text(`$${sectionTotal.toFixed(2)}`);

            updateAuthorizationGrandTotal();

            // save / update section data with items
            saveAuthorizationSectionData($(this))
        });

        // Update Section Title
        $(document).on("keyup change", ".authorization-section-title", function() {

            // save / update section data with items
            saveAuthorizationSectionData($(this))
        });

        // Remove Section
        $(document).on("click", ".remove-authorization-section-btn", function() {
            const section = $(this).closest(".authorization-section");
            const sectionId = section.data("id");

            $.ajax({
                url: "{{ route('report.authorization.remove-section') }}",
                method: "DELETE",
                data: {
                    page_id: pageId,
                    section_id: sectionId
                },
                success: function(response) {

                    // Remove section
                    section.remove();

                    // Update the Grand Total
                    updateAuthorizationGrandTotal();

                    showSuccessNotification(response.message);
                },
                error: function(xhr) {
                    showErrorNotification(xhr.responseJSON.message);
                },
            });
        });

        // Remove Row
        $(document).on("click", ".remove-authorization-row-btn", function() {
            const row = $(this).closest(".row");
            const rowId = row.data("id");
            const section = $(this).closest(".authorization-section");

            // Remove the row
            row.remove();

            // Update the Section Total
            let sectionTotal = 0;
            section.find(".line-total").each(function() {
                sectionTotal += parseFloat($(this).text().replace("$", "")) || 0;
            });
            section.find(".authorization-section-total").text(`$${sectionTotal.toFixed(2)}`);

            // Update the Grand Total
            updateAuthorizationGrandTotal();

            // save / update section data with items
            saveAuthorizationSectionData($(this))

        });

        // Make sections sortable (drag to reorder sections)
        $("#authorization-sections-container").sortable({
            items: ".authorization-section", // Only sections can be dragged
            handle: ".authorization-section-drag-handle", // Drag handle element
            opacity: 0.5,
            start: function(event, ui) {
                ui.item.css("background-color", "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
            },
            stop: function(event, ui) {
                ui.item.css("background-color", "white"); // Reset opacity
            },
            update: function(event, ui) {
                // Update order via AJAX after drag stop
                const authorizationSectionsOrder = $("#authorization-sections-container .authorization-section")
                    .map(function() {
                        return $(this).data("id");
                    }).get();
                $.ajax({
                    url: "{{ route('reports.page.authorization-sections-ordering.update') }}",
                    method: 'POST',
                    data: {
                        page_id: pageId,
                        sections_order: authorizationSectionsOrder,
                    },
                    success: function(response) {
                        if (response.status) {

                            // show a success message
                            showSuccessNotification(response.message);
                        } else {
                            showErrorNotification(response.message);
                        }
                    },
                    error: function(xhr) {
                        showErrorNotification("Failed to reorder sections:", xhr.responseText);
                    }
                });

            },
            cancel: ".remove-authorization-section-btn, input, button" // Prevent drag interference
        });

        // Function to make rows sortable (drag to reorder rows)
        function makeSectionRowContainerSortable() {
            $(".authorization-rows-container").sortable({
                items: ".row", // Only rows can be dragged
                handle: ".row-drag-handle", // Drag handle element
                opacity: 0.5,
                start: function(event, ui) {
                    ui.item.css("background-color", "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
                },
                stop: function(event, ui) {
                    ui.item.css("background-color", "white"); // Reset opacity
                },
                update: function(event, ui) {
                    // save / update section data with items
                    saveAuthorizationSectionData($(this));
                }
            });
        }

        // Initially apply sortable to the rowscontainer
        makeSectionRowContainerSortable();
    </script>
@endpush
