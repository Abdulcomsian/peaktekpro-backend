<div class="w-full">
    <!-- Section Container -->
    <div id="sections-container">
        <!-- First Section -->
        <div class="section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="section_1">
            <!-- Section Header -->
            <div class="flex justify-between items-center mb-4">
                <div>
                    <input type="text"
                        class="section-title text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                        placeholder="Section Title" />
                        <button class="remove-section-btn text-red-500 hover:text-red-700 font-medium text-sm">
                            X
                        </button>
                        <span class="section-drag-handle cursor-pointer">⇄ Drag</span>
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
                <div class="row flex flex-wrap items-center space-x-4" data-id="row_1">
                    <span class="row-drag-handle cursor-pointer">⇄ Drag</span>
                    <!-- Item Description -->
                    <input type="text"
                        class="item-description flex-grow border border-gray-300 rounded-md px-2 py-1"
                        placeholder="Item Description">

                    <!-- Quantity -->
                    <input type="number"
                        class="item-qty w-20 border border-gray-300 rounded-md px-2 py-1"
                        placeholder="Qty" min="0" step="0.01">

                    <!-- Unit Price -->
                    <input type="number"
                        class="item-price w-20 border border-gray-300 rounded-md px-2 py-1"
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
