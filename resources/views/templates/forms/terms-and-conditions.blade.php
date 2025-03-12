<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Description -->
        <div class="mb-4">
            <div id="terms-and-conditions-quill" class="bg-white"></div>
            <textarea class="hidden" id="terms-and-conditions-text" name="terms_and_conditions_text" required>{{ $pageData->json_data['terms_and_conditions_text'] ?? '' }}</textarea>
        </div>
    </form>
</div>

@push('scripts')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill editor
            const termsConditionsQuill = new Quill('#terms-and-conditions-quill', {
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
            termsConditionsQuill.root.style.height = '200px';

            // Load saved content safely
            const initialContent = document.getElementById('terms-and-conditions-text').value;
            termsConditionsQuill.root.innerHTML = initialContent;

            // Update textarea on content change
            termsConditionsQuill.on('text-change', function() {
                const htmlContent = termsConditionsQuill.root.innerHTML;
                document.getElementById('terms-and-conditions-text').value = htmlContent;
                saveTemplatePageTextareaData('#terms-and-conditions-text');
            });

            // Add resize observer
            new ResizeObserver(() => {
                termsConditionsQuill.update();
            }).observe(termsConditionsQuill.root);
        });
    </script>
@endpush