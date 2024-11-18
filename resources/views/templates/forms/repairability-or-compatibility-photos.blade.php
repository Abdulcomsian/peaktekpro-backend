<div class="w-full">
    <!-- Section Container -->
    <div id="compatibility-sections-container">
        <!-- Initial Section -->
        <div class="compatibility-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="section_1">
            <!-- Section Header -->
            <div class="flex justify-between items-center mb-4">
                <div>
                    <input type="text"
                        class="section-title text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                        placeholder="Section Title" />
                    <button class="remove-section-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                    <span class="section-drag-handle cursor-pointer">↑↓</span>
                </div>
            </div>

            <!-- Items Container -->
            <div class="compatibility-items-container space-y-4">
                <!-- Initial Item -->
                <div class="item flex flex-wrap items-center space-x-4" data-id="item_1">
                    <!-- Drag Handle -->
                    <div class="mb-2">
                        <span class="item-drag-handle cursor-pointer">↑↓</span>
                    </div>
                    <!-- Image Upload -->
                    <div class="mb-2">
                        <input type="file" class="item-image border border-gray-300 rounded-md px-2 py-1" />
                    </div>
                    <!-- Quill Editor -->
                    <div class="mb-2">
                        <div id="repairability-or-compatibility-text-quill-1" class="item-editor bg-white"></div>
                        <textarea class="hidden" id="repairability-or-compatibility-text-1" name="repairability_or_compatibility_text[]"
                            required>{{ '' }}</textarea>
                    </div>
                    <!-- Remove Button -->
                    <div class="mb-2">
                        <button class="remove-item-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                    </div>
                </div>
            </div>

            <!-- Add Item Button -->
            <button class="add-compatibility-item-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">+ Add Item</button>
        </div>
    </div>

    <!-- Add Section Button -->
    <button id="add-compatibility-section-btn"
        class="mt-6 text-white text-sm bg-blue-600 hover:bg-blue-700 font-medium rounded-md px-4 py-2">
        + Add Section
    </button>
</div>
