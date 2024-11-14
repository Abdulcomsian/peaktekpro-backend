<div class="w-full p-6 bg-white shadow rounded-lg">
    <form action="/submit" method="POST" enctype="multipart/form-data">
        <!-- Upload Image Field -->
        <div class="mb-6">
            <label for="image" class="block text-gray-700 text-sm font-medium mb-2">Upload Images</label>
            <input type="file" id="image" name="image" accept="image/*"
                class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Descriptive Text Field -->
        <div class="mb-6">
            <label for="roof-repair-limitations-text" class="block text-gray-700 text-sm font-medium mb-2">Descriptive
                Text for Roof Repair Limitations</label>
            <div id="roof-repair-limitations-quill" class="bg-white"></div>
            <textarea class="hidden" id="roof-repair-limitations-text" name="roof_repair_limitations" required>{{ '' }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex">
            <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-200">
                Submit
            </button>
        </div>
    </form>
</div>
