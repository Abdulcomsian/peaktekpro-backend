<div class="w-full">
    <!-- Section Container -->
    <div id="sections-container">
        <!-- First Section -->
        <div class="section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="{{ \Str::random(8) }}">
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
    </div>

    <!-- Grand Total -->
    <div class="flex justify-end items-center mb-6">
        <div>
            <span id="grand-total-label" class="text-lg font-medium text-gray-700">Grand Total:</span>
            <span id="grand-total" class="text-lg font-semibold text-gray-800 ml-2">$0.00</span>
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

        // save section title
        const saveQuoteSectionTitle = debounce(function() {
            let sectionContainer = $(this).closest('.section')
            let quoteSectionId = sectionContainer.data('id');
            let sectionTitle = sectionContainer.find('.quote-section-title').val()

            let quoteSection = {
                id: quoteSectionId,
                title: sectionTitle,
                isActive: sectionContainer.find('.section-toggle').is(':checked'),
                sectionItems: []
            }

            sectionContainer.find('.rows-container').find('.row').each(function() {
                let rowId = $(this).data('id');

                quoteSection.sectionItems.push({
                    row_id: rowId,
                    description: $(this).find('.item-description').val(),
                    qty: $(this).find('.item-qty').val(),
                    price: $(this).find('.item-price').val(),
                    line_total: $(this).find('.line-total').text().replace('$', ''),
                })

            })

            console.log(quoteSection)

        }, 500); // Delay in milliseconds


        $(document).on('keyup change','.quote-section-title', saveQuoteSectionTitle);


        let sectionCounter = 1;



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

            // Send AJAX request to update the row
            // $.ajax({
            //     url: "/update-row",
            //     method: "POST",
            //     data: {
            //         id: rowId,
            //         description: description,
            //         qty: qty,
            //         price: price,
            //     },
            //     success: function (response) {
            //         console.log("Row updated:", response);
            //     },
            //     error: function (xhr) {
            //         console.error("Failed to update row:", xhr.responseText);
            //     },
            // });
        });

        // Update Section Title
        $(document).on("input", ".section-title", function() {
            const sectionId = $(this).closest(".section").data("id");
            const title = $(this).val();

            // $.ajax({
            //     url: "/update-section",
            //     method: "POST",
            //     data: { id: sectionId, title: title },
            //     success: function (response) {
            //         console.log("Section updated:", response);
            //     },
            //     error: function (xhr) {
            //         console.error("Failed to update section:", xhr.responseText);
            //     },
            // });
        });

        // Remove Section
        $(document).on("click", ".remove-section-btn", function() {
            const section = $(this).closest(".section");
            const sectionId = section.data("id");

            // Remove the section
            section.remove();

            // Update the Grand Total
            updateQuoteGrandTotal();

            // $.ajax({
            //     url: "/delete-section",
            //     method: "DELETE",
            //     data: { id: sectionId },
            //     success: function (response) {
            //         section.remove(); // Remove section from DOM
            //         console.log("Section removed:", response);
            //     },
            //     error: function (xhr) {
            //         console.error("Failed to remove section:", xhr.responseText);
            //     },
            // });
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

            // $.ajax({
            //     url: "/delete-row",
            //     method: "DELETE",
            //     data: { id: rowId },
            //     success: function (response) {
            //         row.remove(); // Remove row from DOM
            //         console.log("Row removed:", response);
            //     },
            //     error: function (xhr) {
            //         console.error("Failed to remove row:", xhr.responseText);
            //     },
            // });
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
                console.log('Sections reordered');
                // Trigger any updates or reordering here if needed
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
                    console.log('Rows reordered');
                    // Trigger any updates or reordering here if needed
                }
            });
        }

        // Initially apply sortable to the rowscontainer
        makeQuoteSectionRowContainerSortable();
    </script>
@endpush
