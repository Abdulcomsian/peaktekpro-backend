<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Text Editor Container -->
        <div class="mb-4">
            <div id="applicable-code-guidelines-text-quill" class="bg-white"></div>
            <textarea class="hidden" id="applicable-code-guidelines-text" name="applicable_code_guidelines_text" required>{{ $pageData->json_data['applicable_code_guidelines_text'] ?? '' }}</textarea>
        </div>
    </form>
</div>

@push('scripts')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill editor
            const quillOptions = {
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
            };

            const quill = new Quill('#applicable-code-guidelines-text-quill', quillOptions);
            
            // Set initial height
            quill.root.style.height = '200px';

            // Load saved content safely
            const initialContent = document.getElementById('applicable-code-guidelines-text').value;
            quill.root.innerHTML = initialContent;

            // Update textarea on content change
            quill.on('text-change', function() {
                const htmlContent = quill.root.innerHTML;
                document.getElementById('applicable-code-guidelines-text').value = htmlContent;
                saveTemplatePageTextareaData('#applicable-code-guidelines-text');
            });

            // Add resize observer to maintain layout
            new ResizeObserver(() => {
                quill.update();
            }).observe(quill.root);
        });
    </script>
@endpush