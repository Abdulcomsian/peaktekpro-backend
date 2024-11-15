
// Show the appropriate form when the radio button is changed
$("input[name='product_compatibility_type']").on("change", function() {
    var selectedValue = $("input[name='product_compatibility_type']:checked").val();

    console.log(selectedValue)

    if (selectedValue === 'pdf') {
        $('#product-compatibility-form-upload-pdf').removeClass('hidden');
        $('#product-compatibility-form-text-page').hasClass('hidden') ? '' : $('#product-compatibility-form-text-page').addClass('hidden');
    } else if (selectedValue === 'text') {
        $('#product-compatibility-form-text-page').removeClass('hidden');
        $('#product-compatibility-form-upload-pdf').hasClass('hidden') ? '' : $('#product-compatibility-form-upload-pdf').addClass('hidden');
    }
});


// drop zone
Dropzone.autoDiscover = false;

const productCompatibilityDropzone = new Dropzone("#product-compatibility-form-upload-pdf", {
    url: "/templates/repairibility-assessment",
    uploadMultiple: true,
    parallelUploads: 100,
    maxFiles: 100,
    acceptedFiles: ".pdf",
    addRemoveLinks: true,
    dictRemoveFile: "Remove",
    dictDefaultMessage: "Drag & Drop or Click to Upload",
    init: function() {

        // When a file is added, check if it's valid based on accepted file types
        this.on("addedfile", function(file) {
            if (!file.type.match('application/pdf')) {
                // If the file type doesn't match, remove the file from preview
                this.removeFile(file);
                showErrorNotification('Only PDFs are allowed.')
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

// Optional: Prevent multiple submissions
function submitForm() {
    if (productCompatibilityDropzone.getAcceptedFiles().length > 0) {
        alert("Form submitted successfully!");
        // Add any further form submission logic if necessary
    } else {
        alert("Please upload an image first.");
    }
}



// quill

const productCompatibilityQuill = [
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
var productCompatibilityText = new Quill('#product-compatibility-quill', {
    theme: 'snow',
    modules: {
        toolbar: productCompatibilityQuill
    }
});
// Set the height dynamically via JavaScript
productCompatibilityText.root.style.height = '200px';

// old intro text value
let oldProductCompatibilityText = '';

// Load the saved content into the editor
productCompatibilityText.clipboard.dangerouslyPasteHTML(oldProductCompatibilityText);
productCompatibilityText.on('text-change', function() {
    $('#product-compatibility-text').val(productCompatibilityText.root.innerHTML);
});

