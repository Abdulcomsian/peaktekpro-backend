<div class="w-full p-6 bg-white shadow rounded-lg">
    <form action="/submit" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Dynamic Fields Container -->
        <div id="dynamic-photos-container">
            <!-- Initial Row (Empty for Now) -->
            <div class="photo-row flex space-x-6 mb-6" id="photo-row-1">
                <div class="flex-1">
                    <label for="photo1" class="block text-gray-700 text-sm font-medium mb-2">Upload Photo 1</label>
                    <input type="file" id="photo1" name="photos[]" accept="image/*"
                        class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex-1">
                    <label for="caption1" class="block text-gray-700 text-sm font-medium mb-2">Caption for Photo
                        1</label>
                    <input type="text" id="caption1" name="captions[]"
                        class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter caption" required>
                </div>

                <button type="button" class="remove-row text-red-500 hover:text-red-600 mt-6">Remove</button>
            </div>
        </div>

        <!-- Add New Row Button -->
        <div class="flex my-2">
            <button type="button" id="add-photo-row"
                class=" px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Add Row</button>
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
