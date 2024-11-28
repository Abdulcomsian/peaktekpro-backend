

// Show the appropriate form when the radio button is changed
$("input[name='custom_page_type']").on("change", function() {
    var selectedValue = $("input[name='custom_page_type']:checked").val();
console.log(selectedValue);
     // Toggle visibility of elements based on `data-selected`
     $(".custom-page-container div[data-selected]").each(function() {
        if ($(this).data("selected") === selectedValue) {
            $(this).removeClass("hidden");
        } else {
            $(this).hasClass("hidden") ? '' : $(this).addClass("hidden");
        }
    });

});


// // drop zone

// const customPageSinglePdfDropZone = new Dropzone("#custom-page-single-pdf-dropzone", {
//     url: "/templates/repairibility-assessment",
//     maxFiles: 1,
//     acceptedFiles: ".pdf",
//     addRemoveLinks: true,
//     dictRemoveFile: "Remove",
//     dictDefaultMessage: "Drag & Drop or Click to Upload",
//     init: function() {

//         // When a file is added, check if it's valid based on accepted file types
//         this.on("addedfile", function(file) {
//             if (!file.type.match('application/pdf')) {
//                 // If the file type doesn't match, remove the file from preview
//                 this.removeFile(file);
//                 showErrorNotification('Only PDF files are allowed.')
//             }
//         });
//         this.on("success", function(file, response) {
//             console.log("File uploaded successfully:", response);
//         });
//         this.on("removedfile", function(file) {
//             console.log("File removed:", file);
//         });
//     }
// });

// // Optional: Prevent multiple submissions
// function submitForm() {
//     if (customPageSinglePdfDropZone.getAcceptedFiles().length > 0) {
//         alert("Form submitted successfully!");
//         // Add any further form submission logic if necessary
//     } else {
//         alert("Please upload an image first.");
//     }
// }


// // quill
// const customPageTextQuillOptions = [
//     ['bold', 'italic', 'underline', 'strike'], // toggled buttons
//     ['blockquote', 'code-block'],
//     ['link'],
//     [{
//         'header': 1
//     }, {
//         'header': 2
//     }], // custom button values
//     [{
//         'list': 'ordered'
//     }, {
//         'list': 'bullet'
//     }, {
//         'list': 'check'
//     }],
//     [{
//         'script': 'sub'
//     }, {
//         'script': 'super'
//     }], // superscript/subscript
//     [{
//         'header': [1, 2, 3, 4, 5, 6, false]
//     }],

//     [{
//         'color': []
//     }, {
//         'background': []
//     }], // dropdown with defaults from theme
//     [{
//         'font': []
//     }],
//     [{
//         'align': []
//     }],
//     ['clean'] // remove formatting button
// ];
// var customPageTextQuill = new Quill('#custom-page-text-quill', {
//     theme: 'snow',
//     modules: {
//         toolbar: customPageTextQuillOptions
//     }
// });
// // Set the height dynamically via JavaScript
// customPageTextQuill.root.style.height = '200px';

// // old text value
// let oldCustomPageTextValue = '';

// // Load the saved content into the editor
// customPageTextQuill.clipboard.dangerouslyPasteHTML(oldCustomPageTextValue);
// customPageTextQuill.on('text-change', function() {
//     $('#custom-page-text').val(customPageTextQuill.root.innerHTML);

//     //save textarea data
//     saveTemplatePageTextareaData('#custom-page-text');

// });
