
// quill
const authorizationFooterTextQuillOptions = [
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
var authorizationFooterTextQuill = new Quill('#authorization-footer-text-quill', {
    theme: 'snow',
    modules: {
        toolbar: authorizationFooterTextQuillOptions
    }
});
// Set the height dynamically via JavaScript
authorizationFooterTextQuill.root.style.height = '200px';

// old text value
let oldAuthorizationlFooterTextValue = '';

// Load the saved content into the editor
authorizationFooterTextQuill.clipboard.dangerouslyPasteHTML(oldAuthorizationlFooterTextValue);
authorizationFooterTextQuill.on('text-change', function() {
    $('#authorization-footer-text').val(authorizationFooterTextQuill.root.innerHTML);

    //save textarea data
    saveTemplatePageTextareaData('#authorization-footer-text');
});


let authorizationSectionCounter = 1;

// Update Grand Total
function updateAuthorizationGrandTotal() {
    let grandTotal = 0;
    $(".authorization-section-total").each(function () {
        grandTotal += parseFloat($(this).text().replace("$", "")) || 0;
    });
    $("#authorization-grand-total").text(`$${grandTotal.toFixed(2)}`);
}

// Function to Create a New Row
function createRow() {
    return `
    <div class="row flex flex-wrap items-center space-x-4" data-id="row_${authorizationSectionCounter}">
        <span class="row-drag-handle cursor-pointer">↑↓</span>
        <input type="text" class="item-description flex-grow border border-gray-300 rounded-md px-2 py-1"
            placeholder="Item Description">
        <input type="number" class="item-qty w-20 border border-gray-300 rounded-md px-2 py-1"
            placeholder="Qty" min="0" step="0.01">
        <input type="number" class="item-price w-20 border border-gray-300 rounded-md px-2 py-1"
            placeholder="Unit Price" min="0" step="0.01">
        <div class="line-total-container w-24 text-right flex-1">
            <span class="line-total block">$0.00</span>
        </div>
        <button class="remove-authorization-row-btn text-red-500 hover:text-red-700 font-medium text-sm">
            X
        </button>
    </div>`;
}

// Add New Section with Default Row
$("#add-authorization-section-btn").click(function () {
    const newSection = `
    <div class="authorization-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="section_${authorizationSectionCounter}">
        <div class="flex justify-start items-center mb-4 gap-1">
            <div>
                <input type="text"
                class="authorization-section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                placeholder="Section Title" />
            </div>
            <div>
                <button class="remove-authorization-section-btn text-red-500 hover:text-red-700 font-medium text-sm">
                X
                </button>
                <span class="authorization-section-drag-handle cursor-pointer">↑↓</span>
            </div>
        </div>
        <div class="rows-container space-y-4">
            ${createRow()}
        </div>
        <button class="add-authorization-row-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">
            + Add Row
        </button>
        <div class="flex justify-between items-center mt-4">
            <span class="text-lg font-medium text-gray-700">Section Total:</span>
            <span class="authorization-section-total text-lg font-semibold text-gray-800">$0.00</span>
        </div>
    </div>`;
    $("#authorization-sections-container").append(newSection);

    // Reinitialize the sortable functionality after adding a new section
    makeSectionRowContainerSortable()

});

// Add Row in Section
$(document).on("click", ".add-authorization-row-btn", function () {
    const newRow = createRow();
    $(this).siblings(".rows-container").append(newRow);
});

// Update Row details, Section Total and Grand Total
$(document).on("input", ".item-qty, .item-price, .item-description", function () {

    const row = $(this).closest(".row");
    const rowId = row.data("id");
    const description = row.find(".item-description").val();
    const qty = parseFloat(row.find(".item-qty").val()) || 0;
    const price = parseFloat(row.find(".item-price").val()) || 0;

    const lineTotal = qty * price;
    row.find(".line-total").text(`$${lineTotal.toFixed(2)}`);

    // Update Section Total
    const section = $(this).closest(".authorization-section");
    let sectionTotal = 0;
    section.find(".line-total").each(function () {
        sectionTotal += parseFloat($(this).text().replace("$", "")) || 0;
    });
    section.find(".authorization-section-total").text(`$${sectionTotal.toFixed(2)}`);

    updateAuthorizationGrandTotal();

    // Send AJAX request to update the row
    // $.ajax({
    //     url: "/update-row",
    //     method: "POST",
    //     data: {
    //         id: rowId,
    //         description: description,
    //         qty: qty,
    //         price: price,
    //     },
    //     success: function (response) {
    //         console.log("Row updated:", response);
    //     },
    //     error: function (xhr) {
    //         console.error("Failed to update row:", xhr.responseText);
    //     },
    // });
});

// Update Section Title
$(document).on("input", ".authorization-section-title", function () {
    const sectionId = $(this).closest(".authorization-section").data("id");
    const title = $(this).val();

    // $.ajax({
    //     url: "/update-section",
    //     method: "POST",
    //     data: { id: sectionId, title: title },
    //     success: function (response) {
    //         console.log("Section updated:", response);
    //     },
    //     error: function (xhr) {
    //         console.error("Failed to update section:", xhr.responseText);
    //     },
    // });
});

// Remove Section
$(document).on("click", ".remove-authorization-section-btn", function () {
    const section = $(this).closest(".authorization-section");
    const sectionId = section.data("id");

    // Remove the section
    section.remove();

    // Update the Grand Total
    updateAuthorizationGrandTotal();

    // $.ajax({
    //     url: "/delete-section",
    //     method: "DELETE",
    //     data: { id: sectionId },
    //     success: function (response) {
    //         section.remove(); // Remove section from DOM
    //         console.log("Section removed:", response);
    //     },
    //     error: function (xhr) {
    //         console.error("Failed to remove section:", xhr.responseText);
    //     },
    // });
});

// Remove Row
$(document).on("click", ".remove-authorization-row-btn", function () {
    const row = $(this).closest(".row");
    const rowId = row.data("id");
    const section = $(this).closest(".authorization-section");

    // Remove the row
    row.remove();

    // Update the Section Total
    let sectionTotal = 0;
    section.find(".line-total").each(function () {
        sectionTotal += parseFloat($(this).text().replace("$", "")) || 0;
    });
    section.find(".authorization-section-total").text(`$${sectionTotal.toFixed(2)}`);

    // Update the Grand Total
    updateAuthorizationGrandTotal();

    // $.ajax({
    //     url: "/delete-row",
    //     method: "DELETE",
    //     data: { id: rowId },
    //     success: function (response) {
    //         row.remove(); // Remove row from DOM
    //         console.log("Row removed:", response);
    //     },
    //     error: function (xhr) {
    //         console.error("Failed to remove row:", xhr.responseText);
    //     },
    // });
});

// Make sections sortable (drag to reorder sections)
$("#authorization-sections-container").sortable({
    items: ".authorization-section",  // Only sections can be dragged
    handle: ".authorization-section-drag-handle",  // Drag handle element
    opacity: 0.5,
    start: function(event, ui) {
        ui.item.css("background-color", "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
    },
    stop: function(event, ui) {
        ui.item.css("background-color", "white"); // Reset opacity
    },
    update: function(event, ui) {
        console.log('Sections reordered');
        // Trigger any updates or reordering here if needed
    },
    cancel: ".remove-authorization-section-btn, input, button" // Prevent drag interference
});

// Function to make rows sortable (drag to reorder rows)
function makeSectionRowContainerSortable() {
    $(".rows-container").sortable({
        items: ".row",  // Only rows can be dragged
        handle: ".row-drag-handle",  // Drag handle element
        opacity: 0.5,
        start: function(event, ui) {
            ui.item.css("background-color", "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
        },
        stop: function(event, ui) {
            ui.item.css("background-color", "white"); // Reset opacity
        },
        update: function(event, ui) {
            console.log('Rows reordered');
            // Trigger any updates or reordering here if needed
        }
    });
}

// Initially apply sortable to the rowscontainer
makeSectionRowContainerSortable();


