<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Report Title -->
        <div class="mb-4">
            <label for="disclaimer" class="block text-gray-700 text-sm font-medium">Disclaimer</label>
            <small>For example, the terms of an estimate, or a direction to the insurer.</small>
            <input type="text" id="disclaimer" name="authorization_disclaimer" placeholder="Enter disclaimer"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                required />
        </div>

    </form>
</div>

<div class="w-full my-3">
    <!-- Section Container -->
    <div id="authorization-sections-container">
        <!-- First Section -->
        <div class="authorization-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="section_1">
            <!-- Section Header -->
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
            <!-- Rows Container -->
            <div class="rows-container space-y-4">
                <!-- Default Row -->
                <div class="row flex flex-wrap items-center space-x-4" data-id="row_1">
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
                    <button class="remove-authorization-row-btn text-red-500 hover:text-red-700 font-medium text-sm">
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
            <span id="authorization-grand-total" class="pt-5 text-lg font-semibold text-gray-800 ml-2">$0.00</span>
        </div>
    </div>
</div>

<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">

        <!-- Text -->
        <div class="mb-4">
            <label for="authorization-footer-text" class="block text-gray-700 text-sm font-medium">Footer</label>
            <div id="authorization-footer-text-quill" class="bg-white"></div>
            <textarea class="hidden" id="authorization-footer-text" name="authorization_footer_text" required>{{ '' }}</textarea>
        </div>

    </form>
</div>
