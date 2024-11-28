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
let oldApplicableCodeGuidelinesValue = '';

// Load the saved content into the editor
applicableCodeGuidelinesQuill.clipboard.dangerouslyPasteHTML(oldApplicableCodeGuidelinesValue);
applicableCodeGuidelinesQuill.on('text-change', function() {
    $('#applicable-code-guidelines-text').val(applicableCodeGuidelinesQuill.root.innerHTML);

    //save textarea data
    saveTemplatePageTextareaData('#applicable-code-guidelines-text');
})
