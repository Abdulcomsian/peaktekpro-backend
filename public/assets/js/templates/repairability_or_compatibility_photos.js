
// const roofRepairLimitationsOptions = [
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
// var roofRepairLimitationsQuill = new Quill('#roof-repair-limitations-quill', {
//     theme: 'snow',
//     modules: {
//         toolbar: roofRepairLimitationsOptions
//     }
// });
// // Set the height dynamically via JavaScript
// roofRepairLimitationsQuill.root.style.height = '200px';

// // old intro text value
// let oldRoofRepairLimitationText = '';

// // Load the saved content into the editor
// roofRepairLimitationsQuill.clipboard.dangerouslyPasteHTML(oldRoofRepairLimitationText);
// roofRepairLimitationsQuill.on('text-change', function() {
//     $('#roof-repair-limitations-text').val(roofRepairLimitationsQuill.root.innerHTML);
// });


let rowCount = 1; // Initialize row count for unique IDs

// Add New Row Button Click
$('#add-photo-row').click(function() {
    rowCount++; // Increment the row count for each new row

    // Add new row dynamically
    $('#dynamic-photos-container').append(`
        <div class="photo-row flex space-x-6 mb-6" id="photo-row-${rowCount}">
            <div class="flex-1">
                <label for="photo${rowCount}" class="block text-gray-700 text-sm font-medium mb-2">Upload Photo ${rowCount}</label>
                <input type="file" id="photo${rowCount}" name="photos[]" accept="image/*" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex-1">
                <label for="caption${rowCount}" class="block text-gray-700 text-sm font-medium mb-2">Caption for Photo ${rowCount}</label>
                <input type="text" id="caption${rowCount}" name="captions[]" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter caption" required>
            </div>

            <button type="button" class="remove-row text-red-500 hover:text-red-600 mt-6">Remove</button>
        </div>
    `);
});

// Remove Row Click (on dynamically added rows)
$(document).on('click', '.remove-row', function() {
    $(this).closest('.photo-row').remove();
});
