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

    //save textarea data
    saveTemplatePageTextareaData('#intro-text');
});




const uploadPrimaryImageDropzone = new Dropzone("#introduction-upload-primary-image", {
    url: "/templates/repairibility-assessment",
    maxFiles: 1,
    acceptedFiles: ".jpeg,.jpg,.png",
    dictRemoveFile: "Remove",
    dictDefaultMessage: "Drag & Drop or Click to Upload",
    init: function() {

       // When a file is added, check if it's valid based on accepted file types
       this.on("addedfile", function(file) {
            if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                // If the file type doesn't match, remove the file from preview
                this.removeFile(file);
                showErrorNotification('Only JPEG, JPG, and PNG images are allowed.')
            }
        });
        this.on("success", function(file, response) {
            console.log("File uploaded successfully:", response);
        });
        this.on("removedfile", function(file) {
            console.log("File removed:", file);
        });
    }
});

const uploadSecondaryImageDropzone = new Dropzone("#introduction-upload-secondary-image", {
    url: "/templates/repairibility-assessment",
    maxFiles: 1,
    acceptedFiles: ".jpeg,.jpg,.png",
    dictRemoveFile: "Remove",
    dictDefaultMessage: "Drag & Drop or Click to Upload",
    init: function() {

       // When a file is added, check if it's valid based on accepted file types
       this.on("addedfile", function(file) {
            if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                // If the file type doesn't match, remove the file from preview
                this.removeFile(file);
                showErrorNotification('Only JPEG, JPG, and PNG images are allowed.')
            }
        });
        this.on("success", function(file, response) {
            console.log("File uploaded successfully:", response);
        });
        this.on("removedfile", function(file) {
            console.log("File removed:", file);
        });
    }
});
