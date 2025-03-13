<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        @csrf
        <div class="mb-4">
            <div id="warranty-text-quill" class="bg-white"></div>
            <textarea class="hidden" id="warranty-text" name="warranty_text" required>
                {{ $pageData->json_data['warranty_text'] ?? '' }}
            </textarea>
        </div>
    </form>
</div>

@push('scripts')
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        // Define Quill toolbar options
        const warrantyTextQuillOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            ['link'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['clean']
        ];

        // Initialize Quill editor
        var warrantyTextQuill = new Quill('#warranty-text-quill', {
            theme: 'snow',
            modules: { toolbar: warrantyTextQuillOptions }
        });

        // Set editor height
        warrantyTextQuill.root.style.height = '200px';

        // Retrieve old content safely
        let oldWarrantyTextValue = @json($pageData->json_data['warranty_text'] ?? '');
        console.log("Loaded content:", oldWarrantyTextValue);

        // Ensure content is inserted correctly
        if (oldWarrantyTextValue) {
            warrantyTextQuill.clipboard.dangerouslyPasteHTML(oldWarrantyTextValue);
        }

        // Sync Quill content to hidden textarea on text change
        warrantyTextQuill.on('text-change', function () {
            document.getElementById('warranty-text').value = warrantyTextQuill.root.innerHTML;

            // Save data dynamically if needed
            saveReportPageTextareaData('#warranty-text');
        });
    });
</script>
@endpush
