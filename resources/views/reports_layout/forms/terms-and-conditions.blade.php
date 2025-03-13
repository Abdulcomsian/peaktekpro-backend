<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">

        <!-- Description -->
        <div class="mb-4">
            <div id="terms-and-conditions-quill" class="bg-white" style="position: static"></div>
            <textarea class="hidden" id="terms-and-conditions-text" name="terms_and_conditions_text" required>{{ $pageData->json_data['terms_and_conditions_text'] ?? '' }}</textarea>
        </div>

    </form>
</div>
@push('scripts')
<script type="text/javascript">
    const termsAndConditionsQuillOptions = [
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

    var termsAndConditionsQuill = new Quill('#terms-and-conditions-quill', {
        theme: 'snow',
        modules: {
            toolbar: termsAndConditionsQuillOptions
        }
    });

    // Set the height dynamically via JavaScript
    termsAndConditionsQuill.root.style.height = '200px';

    // Load old value safely
    let oldTermsAndConditionsTextValue = @json($pageData->json_data['terms_and_conditions_text'] ?? '');
    console.log("Loaded content:", oldTermsAndConditionsTextValue);

    if (oldTermsAndConditionsTextValue) {
        termsAndConditionsQuill.clipboard.dangerouslyPasteHTML(oldTermsAndConditionsTextValue);
    }

    termsAndConditionsQuill.on('text-change', function() {
        $('#terms-and-conditions-text').val(termsAndConditionsQuill.root.innerHTML);
        saveReportPageTextareaData('#terms-and-conditions-text');
    });
</script>

@endpush