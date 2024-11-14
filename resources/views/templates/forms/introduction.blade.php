<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Report Title -->
        <div class="mb-4">
            <label for="report-title" class="block text-gray-700 text-sm font-medium mb-2">Report Title</label>
            <input type="text" id="report-title" name="report_title" placeholder="Enter report title"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500"
                required />
        </div>

        <!-- Date -->
        <div class="mb-4">
            <label for="report-date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
            <input type="date" id="report-date" name="report_date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500"
                required />
        </div>

        <!-- Introductory Text -->
        <div class="mb-4">
            <label for="intro-text" class="block text-gray-700 text-sm font-medium mb-2">Introductory Text</label>
            <div id="intro-text-quill" class="bg-white"></div>
            <textarea class="hidden" id="intro-text" name="intro_text" required>{{ '' }}</textarea>
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
