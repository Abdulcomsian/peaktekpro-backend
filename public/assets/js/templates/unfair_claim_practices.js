
// Show the appropriate form when the radio button is changed
$("input[name='unfair_claims_type']").on("change", function() {
    var selectedValue = $("input[name='unfair_claims_type']:checked").val();

    console.log(selectedValue)

    if (selectedValue === 'single_pdf') {
        $('#unfair-claims-form-single-pdf').removeClass('hidden');
        $('#unfair-claims-form-shared-pdf').hasClass('hidden') ? '' : $('#unfair-claims-form-shared-pdf').addClass('hidden');
    } else if (selectedValue === 'shared_pdf') {
        $('#unfair-claims-form-shared-pdf').removeClass('hidden');
        $('#unfair-claims-form-single-pdf').hasClass('hidden') ? '' : $('#unfair-claims-form-single-pdf').addClass('hidden');
    }
});


// drop zone
Dropzone.autoDiscover = false;

const unfairClaimsSinglePdfDropZone = new Dropzone("#unfair-claims-form-single-pdf", {
    url: "/templates/repairibility-assessment",
    maxFiles: 1,
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
                showErrorNotification('Only PDF files are allowed.')
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
    if (unfairClaimsSinglePdfDropZone.getAcceptedFiles().length > 0) {
        alert("Form submitted successfully!");
        // Add any further form submission logic if necessary
    } else {
        alert("Please upload an image first.");
    }
}

