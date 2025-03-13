<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Text Editor Container -->
        <div class="mb-4 h-[200px]">
            <div id="applicable-code-guidelines-text-quill" class="bg-white h-full"></div>
            <textarea class="hidden" id="applicable-code-guidelines-text" name="applicable_code_guidelines_text" required>
                {{ $pageData->json_data['applicable_code_guidelines_text'] ?? '' }}
            </textarea>
        </div>
    </form>
</div>

@push('scripts')
<script type="text/javascript">
    // Initialize Quill with proper content handling
    const initializeQuillEditor = () => {
        const quillOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            ['link'],
            [{ header: 1 }, { header: 2 }],
            [{ list: 'ordered' }, { list: 'bullet' }, { list: 'check' }],
            [{ script: 'sub' }, { script: 'super' }],
            [{ header: [1, 2, 3, 4, 5, 6, false] }],
            [{ color: [] }, { background: [] }],
            [{ font: [] }],
            [{ align: [] }],
            ['clean']
        ];

        const quill = new Quill('#applicable-code-guidelines-text-quill', {
            theme: 'snow',
            modules: { toolbar: quillOptions }
        });

        // Set initial content safely
        const initialContent = @json($pageData->json_data['applicable_code_guidelines_text'] ?? '');
        if(initialContent.trim() === '') {
            quill.root.innerHTML = '<p><br></p>'; // Maintain editor visibility
        } else {
            quill.clipboard.dangerouslyPasteHTML(initialContent);
        }

        // Sync with textarea
        quill.on('text-change', () => {
            const content = quill.root.innerHTML;
            document.getElementById('applicable-code-guidelines-text').value = 
                content === '<p><br></p>' ? '' : content;
            saveTemplatePageTextareaData('#applicable-code-guidelines-text');
        });

        // Force height maintenance
        quill.root.style.height = '200px';
        new ResizeObserver(() => quill.update()).observe(quill.root);
    };

    // Initialize when ready
    document.addEventListener('DOMContentLoaded', initializeQuillEditor);
</script>
@endpush