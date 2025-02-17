<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">

        <!-- Text -->
        <div class="mb-4">
            <div id="applicable-code-guidelines-text-quill" class="bg-white" style="position: static"></div>
            <textarea class="hidden" id="applicable-code-guidelines-text" name="applicable_code_guidelines_text" required>{{ $pageData->json_data['applicable_code_guidelines_text'] ?? '' }}</textarea>
        </div>

    </form>
</div>
@push('scripts')
    <script type="text/javascript">
        // quill
        const applicableCodeGuidelinesQuillOptions = [
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
        var applicableCodeGuidelinesQuill = new Quill('#applicable-code-guidelines-text-quill', {
            theme: 'snow',
            modules: {
                toolbar: applicableCodeGuidelinesQuillOptions
            }
        });
        // Set the height dynamically via JavaScript
        applicableCodeGuidelinesQuill.root.style.height = '200px';

        // old text value
        let oldApplicableCodeGuidelinesValue = "{!! $pageData->json_data['applicable_code_guidelines_text'] ?? '' !!}";

        // Load the saved content into the editor
        applicableCodeGuidelinesQuill.clipboard.dangerouslyPasteHTML(oldApplicableCodeGuidelinesValue);
        applicableCodeGuidelinesQuill.on('text-change', function() {
            $('#applicable-code-guidelines-text').val(applicableCodeGuidelinesQuill.root.innerHTML);

            //save textarea data
            saveReportPageTextareaData('#applicable-code-guidelines-text');
        })
    </script>
@endpush
