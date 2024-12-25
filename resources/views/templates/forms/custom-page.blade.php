<div class="w-full mx-auto p-6 bg-white shadow rounded-lg custom-page-container">
    <!-- First Card with Radio Buttons -->
    @php
        $uniquePageId = \Str::uuid(); // Unique identifier for this page
        $firstrandom = \Str::random(8);
        $secondRandom = \Str::random(8);
        $thirdRandom = \Str::random(8);
    @endphp
    <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div>
                <input type="radio" id="custom-page-single-pdf-{{ $uniquePageId }}-{{ $firstrandom }}"
                       name="custom_page_type_{{ $uniquePageId }}-{{ $thirdRandom }}"
                       value="single_pdf_{{ $uniquePageId }}-{{ $firstrandom }}"
                       class="mr-2 custom_page_type">
                <label for="custom-page-single-pdf-{{ $uniquePageId }}-{{ $firstrandom }}"
                       class="text-gray-700 text-md cursor-pointer">Single Use PDF</label>
            </div>
            <div>
                <input type="radio" id="custom-page-text-{{ $uniquePageId }}-{{ $secondRandom }}"
                       name="custom_page_type_{{ $uniquePageId }}-{{ $thirdRandom }}"
                       value="single_text_{{ $uniquePageId }}-{{ $secondRandom }}"
                       class="mr-2 custom_page_type">
                <label for="custom-page-text-{{ $uniquePageId }}-{{ $secondRandom }}"
                       class="text-gray-700 text-md cursor-pointer">Text Page</label>
            </div>
        </div>
    </div>

    <div id="custom-page-single-pdf-section-{{ $uniquePageId }}-{{ $firstrandom }}"
         class="hidden"
         data-selected="single_pdf_{{ $uniquePageId }}-{{ $firstrandom }}">
        <!-- Form for PDF Upload (Dropzone) -->
        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone custom-page-dropzone">
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDF</span>
                <small class="text-gray-500">Only PDF file are allowed</small>
            </div>
        </form>
    </div>

    <div id="custom-page-text-section-{{ $uniquePageId }}-{{ $secondRandom }}"
         class="hidden"
         data-selected="single_text_{{ $uniquePageId }}-{{ $secondRandom }}">
        <div class="bg-white custom-page-quill-editor"></div>
        <textarea class="custom-page-text hidden" name="custom_page_text" required>{{ $pageData->json_data['custom_page_text'] ?? '' }}</textarea>
    </div>
</div>


@push('scripts')
    <script type="text/javascript">
       $(document).on("change", ".custom_page_type", function () {
        let selectedValue = $(this).val();
console.log('selectedValue',selectedValue);
        // Find the closest container to this radio button
        let container = $(this).closest(".custom-page-container");

        // Iterate over all sections in this container
        container.find("div[data-selected]").each(function () {
            // Check if the data-selected matches the selected radio value
            if ($(this).data("selected") === selectedValue) {
                $(this).removeClass("hidden");
            } else {
                $(this).addClass("hidden");
            }
        });
    });


        // initialize Quill and Dropzone after appending content
        customPageInitializeQuill();
        customPageInitializeDropzone();
    </script>
@endpush
