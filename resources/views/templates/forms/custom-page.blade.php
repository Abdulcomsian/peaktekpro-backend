<div class="w-full mx-auto p-6 bg-white shadow rounded-lg custom-page-container">
    <!-- First Card with Radio Buttons -->
    <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div>
                <input type="radio" id="custom-page-single-pdf" name="custom_page_type" value="single_pdf"
                    class="mr-2 custom_page_type">
                <label for="custom-page-single-pdf" class="text-gray-700 text-md">Single Use PDF</label>
            </div>
            <div>
                <input type="radio" id="custom-page-text" name="custom_page_type" value="single_text"
                    class="mr-2 custom_page_type">
                <label for="custom-page-text" class="text-gray-700 text-md">Text Page</label>
            </div>
        </div>
    </div>

    <div class="hidden" data-selected="single_pdf">

        <!-- Form for PDF Upload (Dropzone) -->
        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone custom-page-dropzone">
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDF</span>
                <small class="text-gray-500">Only PDF file are allowed</small>
            </div>
        </form>

    </div>

    <div class="hidden" data-selected="single_text">
        <div class="bg-white custom-page-quill-editor"></div>
        <textarea class="custom-page-text" name="custom_page_text" required>{{ $pageData->json_data['terms_and_conditions_text'] ?? '' }}</textarea>
    </div>
</div>
@push('scripts')
    <script type="text/javascript">
        // Show the appropriate form when the radio button is changed
        $(document).on("change", ".custom_page_type", function() {
            let selectedValue = $(this).val();
            let element = $(this).closest(".custom-page-container").find("div[data-selected]")
            // Toggle visibility of elements based on `data-selected`
            element.each(function() {
                if ($(this).data("selected") === selectedValue) {
                    $(this).removeClass("hidden");
                } else {
                    $(this).hasClass("hidden") ? '' : $(this).addClass("hidden");
                }
            });

        });

        // initialize Quill and Dropzone after appending content
        customPageInitializeQuill();
        customPageInitializeDropzone();
    </script>
@endpush
