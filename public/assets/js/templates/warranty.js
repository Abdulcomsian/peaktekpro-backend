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
let oldWarrantyTextValue = '';

// Load the saved content into the editor
warrantyTextQuill.clipboard.dangerouslyPasteHTML(oldWarrantyTextValue);
warrantyTextQuill.on('text-change', function() {
    $('#warranty-text').val(warrantyTextQuill.root.innerHTML);

    //save textarea data
    saveTemplatePageTextareaData('#warranty-text');

});
