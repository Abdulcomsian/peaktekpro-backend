<div class="w-full">
    <!-- Section Container -->
    <div id="sections-container">
        <!-- First Section -->
        @if (isset($pageData->json_data['sections']) && count($pageData->json_data['sections']) > 0)

            @forelse ($pageData->json_data['sections'] as $section)

            <div class="section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200"
                data-id="{{ $section['id'] }}">
                <!-- Section Header -->
                <div class="flex justify-between items-center mb-4 gap-1">
                    <div class="flex items-center space-x-2">
                        <div>
                            <input type="text"
                                class="section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1 quote-section-title"
                                placeholder="Section Title" value="{{ $section['title'] }}"/>
                        </div>
                        <div>
                            <button class="remove-section-btn text-red-500 hover:text-red-700 font-medium text-sm">
                                X
                            </button>
                            <span class="section-drag-handle cursor-pointer">↑↓</span>
                        </div>
                    </div>
                    <div class="relative flex items-center">
                        <span id="toggle-tooltip"
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 text-sm bg-black text-white px-2 py-1 rounded-md hidden">
                            Show or Hide this section, It's total is always included.
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer section-toggle" @checked($section['isActive'] == 'true')>
                            <div
                                class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                            </div>
                        </label>
                    </div>
                </div>
                <!-- Rows Container -->
                <div class="rows-container space-y-4">
                    <!-- Default Row -->
                    @forelse ($section['sectionItems'] as $item)

                    <div class="row flex flex-wrap items-center space-x-4" data-id="{{ $item['rowId'] }}">
                        <span class="row-drag-handle cursor-pointer">↑↓</span>
                        <!-- Item Description -->
                        <input type="text" class="item-description flex-grow border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Item Description" value="{{ $item['description'] }}">

                        <!-- Quantity -->
                        <input type="number" class="item-qty w-20 border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Qty" min="0" step="0.01" value="{{ $item['qty'] }}">

                        <!-- Unit Price -->
                        <input type="number" class="item-price w-20 border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Unit Price" min="0" step="0.01" value="{{ $item['price'] }}">

                        <!-- Line Total -->
                        <div class="line-total-container w-24 text-right flex-1">
                            <span class="line-total block">
                                ${{ number_format($item['lineTotal'] ?? 0, 2, '.', '') }}
                            </span>
                        </div>

                        <!-- Remove Button -->
                        <button class="remove-row-btn text-red-500 hover:text-red-700 font-medium text-sm">
                            X
                        </button>
                    </div>

                    @empty

                    @endforelse

                </div>
                <!-- Add Row Button -->
                <button class="add-row-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">
                    + Add Row
                </button>
                <!-- Section Total -->
                <div class="flex justify-between items-center mt-4">
                    <span class="text-lg font-medium text-gray-700">Section Total:</span>
                    <span class="section-total text-lg font-semibold text-gray-800">
                        ${{ number_format($section['sectionTotal'] ?? 0, 2, '.', '') }}
                    </span>
                </div>
            </div>

            @empty

            @endforelse
            @else

            <div class="section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200"
                data-id="{{ \Str::random(8) }}">
                <!-- Section Header -->
                <div class="flex justify-between items-center mb-4 gap-1">
                    <div class="flex items-center space-x-2">
                        <div>
                            <input type="text"
                                class="section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1 quote-section-title"
                                placeholder="Section Title" />
                        </div>
                        <div>
                            <button class="remove-section-btn text-red-500 hover:text-red-700 font-medium text-sm">
                                X
                            </button>
                            <span class="section-drag-handle cursor-pointer">↑↓</span>
                        </div>
                    </div>
                    <div class="relative flex items-center">
                        <span id="toggle-tooltip"
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 text-sm bg-black text-white px-2 py-1 rounded-md hidden">
                            Show or Hide this section, It's total is always included.
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer section-toggle">
                            <div
                                class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                            </div>
                        </label>
                    </div>
                </div>
                <!-- Rows Container -->
                <div class="rows-container space-y-4">
                    <!-- Default Row -->
                    <div class="row flex flex-wrap items-center space-x-4" data-id="{{ \Str::random(8) }}">
                        <span class="row-drag-handle cursor-pointer">↑↓</span>
                        <!-- Item Description -->
                        <input type="text" class="item-description flex-grow border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Item Description">

                        <!-- Quantity -->
                        <input type="number" class="item-qty w-20 border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Qty" min="0" step="0.01">

                        <!-- Unit Price -->
                        <input type="number" class="item-price w-20 border border-gray-300 rounded-md px-2 py-1"
                            placeholder="Unit Price" min="0" step="0.01">

                        <!-- Line Total -->
                        <div class="line-total-container w-24 text-right flex-1">
                            <span class="line-total block">$0.00</span>
                        </div>

                        <!-- Remove Button -->
                        <button class="remove-row-btn text-red-500 hover:text-red-700 font-medium text-sm">
                            X
                        </button>
                    </div>
                </div>
                <!-- Add Row Button -->
                <button class="add-row-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">
                    + Add Row
                </button>
                <!-- Section Total -->
                <div class="flex justify-between items-center mt-4">
                    <span class="text-lg font-medium text-gray-700">Section Total:</span>
                    <span class="section-total text-lg font-semibold text-gray-800">$0.00</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Grand Total -->
    <div class="flex justify-end items-center mb-6">
        <div>
            <span id="grand-total-label" class="text-lg font-medium text-gray-700">Grand Total:</span>
            <span id="grand-total" class="text-lg font-semibold text-gray-800 ml-2">
                ${{ number_format($pageData->json_data['grand_total'] ?? 0, 2, '.', '') }}
            </span>
        </div>
    </div>

    <!-- Add Section Button -->
    <button id="add-section-btn"
        class="mt-6 text-white text-sm bg-blue-600 hover:bg-blue-700 font-medium rounded-md px-4 py-2">
        + Add Section
    </button>
</div>

@push('scripts')
    <script>
        // save section data
        const saveQuoteSectionData = debounce(function(element) {
            let quoteSectionGrandTotal = $('#grand-total').text().replace('$', '');
            let sectionContainer = element.closest('.section')
            let quoteSectionId = sectionContainer.data('id');
            let sectionTitle = sectionContainer.find('.quote-section-title').val()
            let sectionOrder = sectionContainer.index();

            let quoteSection = {
                id: quoteSectionId,
                title: sectionTitle,
                order: sectionOrder,
                isActive: sectionContainer.find('.section-toggle').is(':checked'),
                sectionTotal: +sectionContainer.find('.section-total').text().replace('$', ''),
                sectionItems: []
            }

            sectionContainer.find('.rows-container').find('.row').each(function(index) {
                let rowId = $(this).data('id');

                quoteSection.sectionItems.push({
                    rowId: rowId,
                    order: index, // Add order for the row
                    description: $(this).find('.item-description').val(),
                    qty: +$(this).find('.item-qty').val(),
                    price: +$(this).find('.item-price').val(),
                    lineTotal: +$(this).find('.line-total').text().replace('$', ''),
                })

            })

            $.ajax({
                url: "{{ route('templates.quote-section.update') }}",
                method: 'POST',
                data: {
                    page_id: pageId,
                    quoteSection: quoteSection,
                    grandTotal: quoteSectionGrandTotal
                },
                success: function(response) {
                    showSuccessNotification(response.message);
                },
                error: function(xhr) {
                    showErrorNotification(xhr.responseJSON.message);
                }
            })

        }, 500); // Delay in milliseconds

        // update on section title and toggle
        $(document).on('keyup change', '.quote-section-title , .section-toggle', function() {
            saveQuoteSectionData($(this))
        });

        // Update Grand Total
        function updateQuoteGrandTotal() {
            let grandTotal = 0;
            $(".section-total").each(function() {
                grandTotal += parseFloat($(this).text().replace("$", "")) || 0;
            });
            $("#grand-total").text(`$${grandTotal.toFixed(2)}`);
        }

        // Function to Create a New Row
        function createQuoteRow() {
            return `
    <div class="row flex flex-wrap items-center space-x-4" data-id="${ generateBase64Key(8) }">
        <span class="row-drag-handle cursor-pointer">↑↓</span>
        <input type="text" class="item-description flex-grow border border-gray-300 rounded-md px-2 py-1"
            placeholder="Item Description">
        <input type="number" class="item-qty w-20 border border-gray-300 rounded-md px-2 py-1"
            placeholder="Qty" min="0" step="0.01">
        <input type="number" class="item-price w-20 border border-gray-300 rounded-md px-2 py-1"
            placeholder="Unit Price" min="0" step="0.01">
        <div class="line-total-container w-24 text-right flex-1">
            <span class="line-total block">$0.00</span>
        </div>
        <button class="remove-row-btn text-red-500 hover:text-red-700 font-medium text-sm">
            X
        </button>
    </div>`;
        }

        // Add New Section with Default Row
        $("#add-section-btn").click(function() {
            const newSection = `
    <div class="section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="${ generateBase64Key(8) }">
        <div class="flex justify-between items-center mb-4 gap-1">
            <div class="flex items-center space-x-2">
                <div>
                    <input type="text"
                    class="section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1 quote-section-title"
                    placeholder="Section Title" />
                </div>
                <div>
                    <button class="remove-section-btn text-red-500 hover:text-red-700 font-medium text-sm">
                    X
                    </button>
                    <span class="section-drag-handle cursor-pointer">↑↓</span>
                </div>
            </div>
          <div class="relative flex items-center">
                <span id="toggle-tooltip"
                    class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 text-sm bg-black text-white px-2 py-1 rounded-md hidden">
                    Show or Hide this section, It's total is always included.
                </span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer section-toggle">
                    <div
                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                    </div>
                </label>
            </div>
        </div>
        <div class="rows-container space-y-4">
            ${createQuoteRow()}
        </div>
        <button class="add-row-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">
            + Add Row
        </button>
        <div class="flex justify-between items-center mt-4">
            <span class="text-lg font-medium text-gray-700">Section Total:</span>
            <span class="section-total text-lg font-semibold text-gray-800">$0.00</span>
        </div>
    </div>`;
            $("#sections-container").append(newSection);

            // Reinitialize the sortable functionality after adding a new section
            makeQuoteSectionRowContainerSortable()

        });

        // Add Row in Section
        $(document).on("click", ".add-row-btn", function() {
            const newRow = createQuoteRow();
            $(this).siblings(".rows-container").append(newRow);
        });

        // Update Row details, Section Total and Grand Total
        $(document).on("input", ".item-qty, .item-price, .item-description", function() {

            const row = $(this).closest(".row");
            const rowId = row.data("id");
            const description = row.find(".item-description").val();
            const qty = parseFloat(row.find(".item-qty").val()) || 0;
            const price = parseFloat(row.find(".item-price").val()) || 0;

            const lineTotal = qty * price;
            row.find(".line-total").text(`$${lineTotal.toFixed(2)}`);

            // Update Section Total
            const section = $(this).closest(".section");
            let sectionTotal = 0;
            section.find(".line-total").each(function() {
                sectionTotal += parseFloat($(this).text().replace("$", "")) || 0;
            });
            section.find(".section-total").text(`$${sectionTotal.toFixed(2)}`);

            updateQuoteGrandTotal();

            // save / update section data with items
            saveQuoteSectionData($(this));

        });

        // Remove Section
        $(document).on("click", ".remove-section-btn", function() {
            const section = $(this).closest(".section");
            const sectionId = section.data("id");

            $.ajax({
                url: "{{ route('template.quote.remove-section') }}",
                method: "DELETE",
                data: {
                    page_id: pageId,
                    section_id: sectionId
                },
                success: function(response) {

                    // Remove section
                    section.remove();

                    // Update the Grand Total
                    updateQuoteGrandTotal();

                    showSuccessNotification(response.message);
                },
                error: function(xhr) {
                    showErrorNotification(xhr.responseJSON.message);
                },
            });
        });

        // Remove Row
        $(document).on("click", ".remove-row-btn", function() {
            const row = $(this).closest(".row");
            const rowId = row.data("id");
            const section = $(this).closest(".section");

            // Remove the row
            row.remove();

            // Update the Section Total
            let sectionTotal = 0;
            section.find(".line-total").each(function() {
                sectionTotal += parseFloat($(this).text().replace("$", "")) || 0;
            });
            section.find(".section-total").text(`$${sectionTotal.toFixed(2)}`);

            // Update the Grand Total
            updateQuoteGrandTotal();

            // save / update section data with items
            saveQuoteSectionData($(this));
        });

        // Make sections sortable (drag to reorder sections)
        $("#sections-container").sortable({
            items: ".section", // Only sections can be dragged
            handle: ".section-drag-handle", // Drag handle element
            opacity: 0.5,
            start: function(event, ui) {
                ui.item.css("background-color", "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
            },
            stop: function(event, ui) {
                ui.item.css("background-color", "white"); // Reset opacity
            },
            update: function(event, ui) {
                // Update order via AJAX after drag stop
                const quoteSectionsOrder = $("#sections-container .section").map(function() {
                        return $(this).data("id");
                    }).get();
                    $.ajax({
                        url: "{{ route('templates.page.quote-sections-ordering.update') }}",
                        method: 'POST',
                        data: {
                            page_id: pageId,
                            sections_order: quoteSectionsOrder,
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
            cancel: ".remove-section-btn, input, button" // Prevent drag interference
        });

        // Function to make rows sortable (drag to reorder rows)
        function makeQuoteSectionRowContainerSortable() {
            $(".rows-container").sortable({
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
                    saveQuoteSectionData($(this));

                }
            });
        }

        // Initially apply sortable to the rowscontainer
        makeQuoteSectionRowContainerSortable();
    </script>
@endpush
