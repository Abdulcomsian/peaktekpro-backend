// let rowCount = 1; // Initialize row count for unique IDs

// // Add New Row Button Click
// $('#add-photo-row').click(function() {
//     rowCount++; // Increment the row count for each new row

//     // Add new row dynamically
//     $('#dynamic-photos-container').append(`
//         <div class="photo-row flex space-x-6 mb-6" id="photo-row-${rowCount}">
//             <div class="flex-1">
//                 <label for="photo${rowCount}" class="block text-gray-700 text-sm font-medium mb-2">Upload Photo ${rowCount}</label>
//                 <input type="file" id="photo${rowCount}" name="photos[]" accept="image/*" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
//             </div>

//             <div class="flex-1">
//                 <label for="caption${rowCount}" class="block text-gray-700 text-sm font-medium mb-2">Caption for Photo ${rowCount}</label>
//                 <input type="text" id="caption${rowCount}" name="captions[]" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter caption" required>
//             </div>

//             <button type="button" class="remove-row text-red-500 hover:text-red-600 mt-6">Remove</button>
//         </div>
//     `);
// });

// // Remove Row Click (on dynamically added rows)
// $(document).on('click', '.remove-row', function() {
//     $(this).closest('.photo-row').remove();
// });




let compatibilitySectionCount = 1;
let compatibilityItemCount = 1;

// Initialize Quill Editor
function initializeRepariabilityOrCompatibilityPhotosQuill(container, textareaId) {
    var repariabilityOrCompatibilityPhotosQuill = new Quill(container, {
        theme: 'snow',
    });

    // Set the height of the editor dynamically
    repariabilityOrCompatibilityPhotosQuill.root.style.height = '200px';

    // Load any existing content into the editor
    let repariabilityOrCompatibilityPhotosTextValue = $(textareaId).val(); // Get the existing value from the textarea
    repariabilityOrCompatibilityPhotosQuill.clipboard.dangerouslyPasteHTML(repariabilityOrCompatibilityPhotosTextValue);

    // Update the textarea whenever Quill content changes
    repariabilityOrCompatibilityPhotosQuill.on('text-change', function () {
        $(textareaId).val(repariabilityOrCompatibilityPhotosQuill.root.innerHTML);
    });
}

// Function to initialize Dropzone for each new item
function initializeCompatibilityDropzone(itemId) {
    var myDropzone = new Dropzone(`#compatibility-dropzone-${itemId}`, {
        url: '/templates/repairibility-assessment',
        paramName: 'file',
        maxFiles: 1,
        acceptedFiles: ".jpeg,.jpg,.png",
        dictDefaultMessage: "Drop an image here or click to upload",
        addRemoveLinks: true, // Show remove link
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Manually add CSRF token from the meta tag
        },
        init: function() {

            // When a file is added, check if it's valid based on accepted file types
            this.on("addedfile", function(file) {
                // Immediately hide the Dropzone when an image is selected
                $(`.item[data-id='item_${itemId}'] .compatibility-dropzone`).addClass('hidden');

                // You can process the image before uploading, for example, preview it
                var reader = new FileReader();
                reader.onload = function(e) {
                    // Create image preview HTML
                    var imagePreviewHtml = `
                        <div class="image-preview lg:w-[18.9875rem] lg:h-[12.5rem] md:w-[18.9875rem] md:h-[12.5rem] w-[6.9875rem] h-[6.5rem] flex justify-center items-center relative">
                            <img src="${e.target.result}" alt="Uploaded Image" class="object-cover">
                            <button type="button" class="remove-image-btn text-red-500 hover:text-red-700 absolute">Remove</button>
                        </div>
                    `;
                    // Add preview to the image container
                    $(`.item[data-id='item_${itemId}'] .image-preview-container`).html(imagePreviewHtml).removeClass("hidden");

                };
                reader.readAsDataURL(file);
            });
            this.on("success", function(file, response) {

                console.log('sucess')
                // // Hide the entire Dropzone container and show the custom image preview
                // $(`.item[data-id='item_${itemId}'] .compatibility-dropzone`).addClass('hidden');
                // // Handle successful upload
                // var imageUrl = response.url; // Assuming the response contains the image URL
                // var imagePreviewHtml = `
                // <div class="image-preview w-full flex justify-center items-center relative">
                //     <img src="${imageUrl}" alt="Uploaded Image" class="w-full h-auto object-cover">
                //     <button type="button" class="remove-image-btn text-red-500 hover:text-red-700 absolute top-2 right-2">Remove</button>
                // </div>
                // `;
                // $(`.item[data-id='item_${itemId}'] .image-preview-container`).html(imagePreviewHtml).removeClass("hidden");
                // $(`.item[data-id='item_${itemId}'] .remove-compatibility-item-btn`).removeClass("hidden");
            });

            // When a file is removed, reset the container
            this.on("removedfile", function(file) {
                $(`.item[data-id='item_${itemId}'] .image-preview-container`).html("").addClass("hidden"); // Hide preview container
                $(`.item[data-id='item_${itemId}'] .compatibility-dropzone`).removeClass('hidden'); // Show Dropzone again
            });
        }
    });

    // Remove image functionality (custom button)
    $(document).on("click", `.item[data-id='item_${itemId}'] .remove-image-btn`, function() {
        myDropzone.removeAllFiles(true); // Remove all files from Dropzone
        $(`.item[data-id='item_${itemId}'] .image-preview-container`).html("").addClass("hidden"); // Hide preview container
        $(`.item[data-id='item_${itemId}'] .compatibility-dropzone`).removeClass('hidden'); // Show Dropzone again
    });
}


// Add new section
$(document).on('click', '#add-compatibility-section-btn', function () {
    compatibilitySectionCount++;
    const newCompatibilitySection = `
         <div class="compatibility-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="section_1">
            <div class="flex flex-wrap justify-start items-center gap-1 mb-4">
                <div>
                    <input type="text"
                        class="section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                        placeholder="Section Title" />
                </div>
                <div>
                    <button class="remove-compatibility-section-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                    <span class="compatiblility-section-drag-handle cursor-pointer">↑↓</span>
                </div>
            </div>
            <div class="compatibility-items-container space-y-4"></div>
            <button class="add-compatibility-item-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">+ Add Item</button>
        </div>
            `;
    $('#compatibility-sections-container').append(newCompatibilitySection);
    makeCompatibilitySectionItemsContainerSortable();
});

// Add new item
$(document).on('click', '.add-compatibility-item-btn', function () {
    compatibilityItemCount++;
    const newCompatibilityItem = `
            <div class="item flex flex-wrap items-center space-x-4" data-id="item_${compatibilityItemCount}">
                <div class="mb-2">
                    <span class="item-drag-handle cursor-pointer">↑↓</span>
                </div>
                <div class="mb-2">
                    <div class="compatibility-dropzone w-full min-h-[200px] border-2 border-dashed border-gray-300 p-4 flex items-center justify-center relative"
                        id="compatibility-dropzone-${compatibilityItemCount}">
                        <div class="dz-message text-center text-gray-600">Drop an image here or click to upload</div>
                    </div>
                </div>
                <div class="image-preview-container w-[18.9875rem] h-[12.5rem] hidden mb-2"></div>
                <div class="mb-2">
                    <div id="repairability-or-compatibility-text-quill-${compatibilityItemCount}" class="item-editor bg-white"></div>
                    <textarea class="hidden" id="repairability-or-compatibility-text-${compatibilityItemCount}" name="repairability_or_compatibility_text[]"
                        required></textarea>
                </div>
                <div class="mb-2">
                    <button class="remove-compatibility-item-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                </div>
            </div>
    `;
    $(this).siblings('.compatibility-items-container').append(newCompatibilityItem);
    initializeRepariabilityOrCompatibilityPhotosQuill(`#repairability-or-compatibility-text-quill-${compatibilityItemCount}`, `#repairability-or-compatibility-text-${compatibilityItemCount}`);
    initializeCompatibilityDropzone(compatibilityItemCount)
});

// Remove section
$(document).on('click', '.remove-compatibility-section-btn', function () {
    $(this).closest('.compatibility-section').remove();
});

// Remove item
$(document).on('click', '.remove-compatibility-item-btn', function () {
    $(this).closest('.item').remove();
});

// Initialize Quill for the default item
initializeRepariabilityOrCompatibilityPhotosQuill(
    '#repairability-or-compatibility-text-quill-1',
    '#repairability-or-compatibility-text-1'
);

// initialize Dropzone for the default item
initializeCompatibilityDropzone(1);

// Drag and Drop Initialization on sections
$('#compatibility-sections-container').sortable({
    items: '.compatibility-section',
    handle: '.compatiblility-section-drag-handle',
    opacity: 0.5,
    start: function(event, ui) {
        ui.item.css("background-color", "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
    },
    stop: function(event, ui) {
        ui.item.css("background-color", "white"); // Reset opacity
    },
    update: function(event, ui) {
        console.log('section reordered');
        // Trigger any updates or reordering here if needed
    }
});

// Function to make rows sortable (drag to reorder rows)
function makeCompatibilitySectionItemsContainerSortable() {
    $(".compatibility-items-container").sortable({
        items: ".item ",  // Only rows can be dragged
        handle: ".item-drag-handle",  // Drag handle element
        opacity: 0.5,
        start: function(event, ui) {
            ui.item.css("background-color", "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
        },
        stop: function(event, ui) {
            ui.item.css("background-color", "white"); // Reset opacity
        },
        update: function(event, ui) {
            console.log('item reordered');
            // Trigger any updates or reordering here if needed
        }
    });
}
// Initially apply sortable to the items of section
makeCompatibilitySectionItemsContainerSortable()

