<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <!-- First Card with Radio Buttons -->
    <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div class="mb-1">
                <input type="radio" id="product-compatibility-upload-pdf" name="product_compatibility_type" value="pdf" class="mr-2">
                <label for="product-compatibility-upload-pdf" class="text-gray-700 text-md">Upload PDFs</label>
            </div>
            <div>
                <input type="radio" id="product-compatibility-text-page" name="product_compatibility_type" value="text" class="mr-2">
                <label for="product-compatibility-text-page" class="text-gray-700 text-md">Text Page</label>
            </div>
        </div>
    </div>

    <!-- Form for PDF Upload (Dropzone) -->
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone hidden" id="product-compatibility-form-upload-pdf" >
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDFs</span>
            <small class="text-gray-500">Only PDF files are allowed</small>
        </div>
    </form>

    <!-- Form for Text Page -->
    <form id="product-compatibility-form-text-page" action="/submit-text" method="POST" class="hidden">
        <!-- Descriptive Text Field -->
        <div class="my-6">
            <div id="product-compatibility-quill" class="bg-white"></div>
            <textarea class="hidden" id="product-compatibility-text" name="roof_repair_limitations" required>{{ '' }}</textarea>
        </div>
    </form>
</div>
