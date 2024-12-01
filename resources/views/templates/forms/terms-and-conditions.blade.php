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
        // quill
        const termsAndConditionsQuillOptions = [
            ['bold', 'italic', 'underline', 'strike'], // toggled buttons
            ['blockquote', 'code-block'],
            ['link'],
            [{
                'header': 1
            }, {
                'header': 2
            }], // custom button values
            [{
                'list': 'ordered'
            }, {
                'list': 'bullet'
            }, {
                'list': 'check'
            }],
            [{
                'script': 'sub'
            }, {
                'script': 'super'
            }], // superscript/subscript
            [{
                'header': [1, 2, 3, 4, 5, 6, false]
            }],

            [{
                'color': []
            }, {
                'background': []
            }], // dropdown with defaults from theme
            [{
                'font': []
            }],
            [{
                'align': []
            }],
            ['clean'] // remove formatting button
        ];
        var termsAndConditionsQuill = new Quill('#terms-and-conditions-quill', {
            theme: 'snow',
            modules: {
                toolbar: termsAndConditionsQuillOptions
            }
        });
        // Set the height dynamically via JavaScript
        termsAndConditionsQuill.root.style.height = '200px';

        // old value
        let oldTermsAndConditionsTextValue = "{!! $pageData->json_data['terms_and_conditions_text'] ?? '' !!}";

        // Load the saved content into the editor
        termsAndConditionsQuill.clipboard.dangerouslyPasteHTML(oldTermsAndConditionsTextValue);
        termsAndConditionsQuill.on('text-change', function() {
            $('#terms-and-conditions-text').val(termsAndConditionsQuill.root.innerHTML);

            //save textarea data
            saveTemplatePageTextareaData('#terms-and-conditions-text');

        });
    </script>
@endpush
