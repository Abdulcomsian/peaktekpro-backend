
const roofRepairLimitationsOptions = [
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
var roofRepairLimitationsQuill = new Quill('#roof-repair-limitations-quill', {
    theme: 'snow',
    modules: {
        toolbar: roofRepairLimitationsOptions
    }
});
// Set the height dynamically via JavaScript
roofRepairLimitationsQuill.root.style.height = '200px';

// old intro text value
let oldRoofRepairLimitationText = '';

// Load the saved content into the editor
roofRepairLimitationsQuill.clipboard.dangerouslyPasteHTML(oldRoofRepairLimitationText);
roofRepairLimitationsQuill.on('text-change', function() {
    $('#roof-repair-limitations-text').val(roofRepairLimitationsQuill.root.innerHTML);
});
