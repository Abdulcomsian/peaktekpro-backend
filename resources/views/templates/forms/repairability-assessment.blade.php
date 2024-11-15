<div class="w-full p-6 bg-white shadow rounded-lg">
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone" id="repairabilityAssessmentDropzone">
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Image</span>
            <small class="text-gray-500">Only image files are allowed</small>
        </div>
    </form>

    <form action="/upload" method="POST">
        <!-- Descriptive Text Field -->
        <div class="my-6">
            <label for="roof-repair-limitations-text" class="block text-gray-700 text-sm font-medium mb-2">Descriptive
                Text for Roof Repair Limitations</label>
            <div id="roof-repair-limitations-quill" class="bg-white"></div>
            <textarea class="hidden" id="roof-repair-limitations-text" name="roof_repair_limitations" required>{{ '' }}</textarea>
        </div>
    </form>
</div>

