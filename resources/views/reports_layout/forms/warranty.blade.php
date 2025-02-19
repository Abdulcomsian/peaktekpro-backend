<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <div class="mb-4">
            <div id="warranty-text-quill" class="bg-white" style="position: static"></div>
            <textarea class="hidden" id="warranty-text" name="warranty_text" required>{{ $pageData->json_data['warranty_text'] ?? '' }}</textarea>
        </div>

    </form>
</div>
@push('scripts')
    <script type="text/javascript">
        // quill
        const warrantyTextQuillOptions = [
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
        var warrantyTextQuill = new Quill('#warranty-text-quill', {
            theme: 'snow',
            modules: {
                toolbar: warrantyTextQuillOptions
            }
        });
        // Set the height dynamically via JavaScript
        warrantyTextQuill.root.style.height = '200px';

        // old text value
        let oldWarrantyTextValue = "{!! $pageData->json_data['warranty_text'] ?? '' !!}";

        // Load the saved content into the editor
        warrantyTextQuill.clipboard.dangerouslyPasteHTML(oldWarrantyTextValue);
        warrantyTextQuill.on('text-change', function() {
            $('#warranty-text').val(warrantyTextQuill.root.innerHTML);

            //save textarea data
            saveReportPageTextareaData('#warranty-text');

        });
    </script>
@endpush
