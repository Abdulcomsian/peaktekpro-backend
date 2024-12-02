// Show the appropriate form when the radio button is changed
$("input[name='unfair_claims_type']").on("change", function() {
    var selectedValue = $("input[name='unfair_claims_type']:checked").val();

    if (selectedValue === 'single_pdf') {
        $('#unfair-claims-form-single-pdf').removeClass('hidden');
        $('#unfair-claims-form-shared-pdf').hasClass('hidden') ? '' : $('#unfair-claims-form-shared-pdf').addClass('hidden');
    } else if (selectedValue === 'shared_pdf') {
        $('#unfair-claims-form-shared-pdf').removeClass('hidden');
        $('#unfair-claims-form-single-pdf').hasClass('hidden') ? '' : $('#unfair-claims-form-single-pdf').addClass('hidden');
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

