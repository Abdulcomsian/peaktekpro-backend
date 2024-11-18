// quill
const introTextQuillOptions = [
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
var introTextQuill = new Quill('#intro-text-quill', {
    theme: 'snow',
    modules: {
        toolbar: introTextQuillOptions
    }
});
// Set the height dynamically via JavaScript
introTextQuill.root.style.height = '200px';

// old text value
let oldIntrolTextValue = '';

// Load the saved content into the editor
introTextQuill.clipboard.dangerouslyPasteHTML(oldIntrolTextValue);
introTextQuill.on('text-change', function() {
    $('#intro-text').val(introTextQuill.root.innerHTML);
});
