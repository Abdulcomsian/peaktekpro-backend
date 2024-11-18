<div class="w-full mx-auto p-6 bg-white shadow rounded-lg custom-page-container">
    <!-- First Card with Radio Buttons -->
    <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div>
                <input type="radio" id="cutom-page-single-pdf" name="custom_page_type" value="single_pdf" class="mr-2">
                <label for="cutom-page-single-pdf" class="text-gray-700 text-md">Single Use PDF</label>
            </div>
            <div>
                <input type="radio" id="cutom-page-text" name="custom_page_type" value="single_text" class="mr-2">
                <label for="cutom-page-text" class="text-gray-700 text-md">Text Page</label>
            </div>
        </div>
    </div>

    <div class="hidden" data-selected="single_pdf">

        <!-- Form for PDF Upload (Dropzone) -->
        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone" id="custom-page-single-pdf-dropzone" >
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDF</span>
                <small class="text-gray-500">Only PDF file are allowed</small>
            </div>
        </form>

    </div>

    <div class="hidden" data-selected="single_text">
        <div id="custom-page-text-quill" class="bg-white"></div>
        <textarea class="hidden" id="custom-page-text" name="custom_page_text" required>{{ '' }}</textarea>
    </div>
</div>
