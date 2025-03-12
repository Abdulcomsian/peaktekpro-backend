<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <div class="mb-4">
            <div id="warranty-text-quill" class="bg-white"></div>
            <textarea class="hidden" id="warranty-text" name="warranty_text" required>{{ $pageData->json_data['warranty_text'] ?? '' }}</textarea>
        </div>
    </form>
</div>

@push('scripts')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill editor
            const warrantyQuill = new Quill('#warranty-text-quill', {
                theme: 'snow',
                modules: {
                    toolbar: [
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
                    ]
                }
            });

            // Set initial height
            warrantyQuill.root.style.height = '200px';

            // Load saved content safely
            const initialContent = document.getElementById('warranty-text').value;
            warrantyQuill.root.innerHTML = initialContent;

            // Update textarea on content change
            warrantyQuill.on('text-change', function() {
                const htmlContent = warrantyQuill.root.innerHTML;
                document.getElementById('warranty-text').value = htmlContent;
                saveTemplatePageTextareaData('#warranty-text');
            });

            // Add resize observer for layout stability
            new ResizeObserver(() => {
                warrantyQuill.update();
            }).observe(warrantyQuill.root);
        });
    </script>
@endpush