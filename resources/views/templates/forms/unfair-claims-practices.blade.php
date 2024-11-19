<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <!-- First Card with Radio Buttons -->
    {{-- <div class="mb-6">
        <div class="flex flex-col justify-start">
            <div>
                <input type="radio" id="unfair-claims-single-pdf" name="unfair_claims_type" value="single_pdf" class="mr-2">
                <label for="unfair-claims-single-pdf" class="text-gray-700 text-md">Single Use PDF</label>
            </div>
            <div class="mb-1">
                <input type="radio" id="unfair-claims-shared-pdf" name="unfair_claims_type" value="shared_pdf" class="mr-2">
                <label for="unfair-claims-shared-pdf" class="text-gray-700 text-md">Shared PDFs</label>
            </div>
        </div>
    </div> --}}

    <!-- Form for PDF Upload (Dropzone) -->
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone" id="unfair-claims-form-single-pdf" >
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload PDF</span>
            <small class="text-gray-500">Only PDF file are allowed</small>
        </div>
    </form>

    <!-- Shared PDFs -->
    <div id="unfair-claims-form-shared-pdf" class="hidden">
        <p>shared pdfs</p>
    </div>
</div>
