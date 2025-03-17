<div class="w-full">
    <!-- Section Container -->
    <div id="compatibility-sections-container">
        <!-- Check if sections exist -->
        @if (isset($pageData->json_data['comparision_sections']) && count($pageData->json_data['comparision_sections']) > 0)
            <!-- Loop through sections -->
            @foreach ($pageData->json_data['comparision_sections'] as $index => $section)
                <div class="compatibility-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="{{ $section['id'] }}">
                    <!-- Section Header -->
                    <div class="flex flex-wrap justify-start items-center gap-1 mb-4">
                        @if($index !== 0 || !empty($section['title']))
                            <div>
                                <input type="text"
                                    class="section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                                    placeholder="Section Title" value="{{ $section['title'] }}" />
                            </div>
                        @endif
                        <div>
                            <button class="remove-compatibility-section-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                            <span class="compatiblility-section-drag-handle cursor-pointer">↑↓</span>
                        </div>
                    </div>

                <!-- Compatibility Items Container -->
                <div class="compatibility-items-container flex flex-wrap items-center gap-1">
                    @if (is_array($section['items']) && count($section['items']) > 0)
                        @foreach ($section['items'] as $item)
                            <div class="item flex flex-row gap-2" data-id="{{ $item['id'] }}">
                                <!-- Drag Handle -->
                                <div class="mb-2">
                                    <span class="item-drag-handle cursor-pointer">⇄</span>
                                </div>
                                <div class="flex flex-col flex-wrap">
                                    <!-- Image Upload -->
                                    <div class="mb-2">
                                        <div class="compatibility-dropzone w-full min-h-[200px] border-2 border-dashed border-gray-300 p-4 flex items-center justify-center relative"
                                            id="compatibility-dropzone-{{ $item['id'] }}">
                                            <div class="dz-message text-center text-gray-600">Drop an image here or click to upload</div>
                                        </div>
                                    </div>
                                    <!-- Image Preview -->
                                    <div class="image-preview-container lg:w-[28.9875rem] lg:h-[12.5rem] md:w-[18.9875rem] md:h-[12.5rem] w-[6.9875rem] h-[6.5rem] hidden mb-4"></div>
                                    <!-- Quill Editor -->
                                    <div class="mb-14 lg:w-[28.9875rem] md:w-[28.9875rem] w-full">
                                        <div id="repairability-or-compatibility-text-quill-{{ $item['id'] }}" class="item-editor bg-white"></div>
                                        <textarea class="hidden" id="repairability-or-compatibility-text-{{ $item['id'] }}" name="repairability_or_compatibility_text[]"
                                            required>{{ $item['content'] }}</textarea>
                                    </div>
                                </div>
                                <!-- Remove Button -->
                                <div class="mb-2">
                                    <button
                                        class="remove-compatibility-item-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>


            <!-- Add Item Button -->
            <button class="add-compatibility-item-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">+ Add
                Item</button>
                </div>
            @endforeach
        @else
        <div class="compatibility-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200"
            data-id="{{ \Str::random(8) }}">
            <!-- Section Header -->
            <div class="flex flex-wrap justify-start items-center gap-1 mb-4">
                <!-- <div>
                    <input type="text"
                        class="section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                        placeholder="Section Title" />
                </div> -->
                <div>

                    <button
                        class="remove-compatibility-section-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                    <span class="compatiblility-section-drag-handle cursor-pointer">↑↓</span>
                </div>
            </div>
            {{-- <div class="flex justify-start flex-row flex-wrap items-center my-2">
                    <label for="layout-select" class="font-bold lg:w-2/12 md:w-4/12 w-full">Change Layout:</label>
                    <select class="layout-select border p-2 lg:w-2/12 md:w-4/12 w-full">
                        <option value="one-item">One Item Per Row</option>
                        <option value="two-items">Two Items Per Row</option>
                        <option value="full-width">Full-Width Cover</option>
                    </select>
            </div> --}}

            <!-- Items Container -->
            <div class="compatibility-items-container flex flex-wrap items-center gap-1">
                <!-- Initial Item -->
                <div class="item flex flex-row gap-2" data-id="{{ \Str::random(8) }}">
                    <!-- Drag Handle -->
                    <div class="mb-2">
                        <span class="item-drag-handle cursor-pointer">⇄</span>
                    </div>
                    <div class="flex flex-col flex-wrap">
                        <!-- Image Upload -->
                        <div class="mb-2">
                            <div class="compatibility-dropzone w-full min-h-[200px] border-2 border-dashed border-gray-300 p-4 flex items-center justify-center relative"
                                id="compatibility-dropzone-1">
                                <div class="dz-message text-center text-gray-600">Drop an image here or click to upload
                                </div>
                            </div>
                        </div>
                        <!-- Image Preview (Outside Dropzone) -->
                        <div
                            class="image-preview-container lg:w-[28.9875rem] lg:h-[12.5rem] md:w-[18.9875rem] md:h-[12.5rem] w-[6.9875rem] h-[6.5rem] hidden mb-4">
                        </div>

                        <!-- Quill Editor -->
                        <div class="mb-14 lg:w-[28.9875rem] md:w-[28.9875rem] w-full">
                            <div id="repairability-or-compatibility-text-quill-1" class="item-editor bg-white"></div>
                            <textarea class="hidden" id="repairability-or-compatibility-text-1" name="repairability_or_compatibility_text[]"
                                required>{{ '' }}</textarea>
                        </div>
                    </div>
                    <!-- Remove Button -->
                    <div class="mb-2">
                        <button
                            class="remove-compatibility-item-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                    </div>
                </div>
            </div>


            <!-- Add Item Button -->
            <button class="add-compatibility-item-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">+ Add
                Item</button>
        </div>
        @endif
    </div>

    <!-- Add Section Button -->
    <button id="add-compatibility-section-btn"
        class="mt-6 text-white text-sm bg-blue-600 hover:bg-blue-700 font-medium rounded-md px-4 py-2">
        + Add Section
    </button>
</div>

@push('scripts')
<script>
 let compatibilitySectionCount = 1;
    let compatibilityItemCount = 1;

    // Initialize Quill Editor
    function initializeRepariabilityOrCompatibilityPhotosQuill(container, textareaId) {
        var quill = new Quill(container, {
            theme: 'snow',
        });

        // Set the editor's height dynamically
        quill.root.style.height = '200px';

        // Load existing content into the editor
        let content = $(textareaId).val(); // Get the value from the hidden textarea
        if (content) {
            quill.clipboard.dangerouslyPasteHTML(content);
        }

        // Sync Quill content to the hidden textarea on changes
        quill.on('text-change', function() {
            $(textareaId).val(quill.root.innerHTML);
        });
    }

    // Initialize all Quill editors after page load
    document.addEventListener("DOMContentLoaded", function() {
            let comparisionSectionsData = @json($pageData->json_data['comparision_sections'] ?? []);
            if (comparisionSectionsData.length > 0) {
                comparisionSectionsData.forEach(function(section) {
                    if (section.items?.length > 0) {
                        section.items.forEach(function(item) {
                            initializeRepariabilityOrCompatibilityPhotosQuill(
                                '#repairability-or-compatibility-text-quill-' + item.id,
                                '#repairability-or-compatibility-text-' + item.id
                            );
                            initializeCompatibilityDropzone(item.id);
                        })
                    }
                })
            }
        });

    // Function to initialize Dropzone for each new item
    function initializeCompatibilityDropzone(itemId) {
    const dropzoneElement = $(`#compatibility-dropzone-${itemId}`);

    const dropzone = new Dropzone(`#compatibility-dropzone-${itemId}`, {
    url: "{{ route('templates-repairibility-image') }}",
    paramName: 'image', // Ensure this matches the backend input name
    maxFiles: 1,
    acceptedFiles: ".jpeg,.jpg,.png",
    dictDefaultMessage: "Drop an image here or click to upload",
    addRemoveLinks: true,
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    init: function() {
        const repairabilityAssessmentImages = {
        files: JSON.parse(`{!! json_encode($pageData->json_data['comparision_sections'] ?? []) !!}`),
        file_url: "{{ $pageData->file_url ?? '' }}"
    };

    // Find the relevant image for the current item
    const section = repairabilityAssessmentImages.files.find(section =>
        section.items.some(item => item.id === itemId)
    );

    // Preload images from the backend if available
    if (section) {
    const item = section.items.find(item => item.id === itemId);

    if (item && item.image && item.image.path) {
        let imageUrl = `${repairabilityAssessmentImages.file_url}/${item.image.path}`;

        // Generate the image preview HTML
        const imagePreviewHtml = `
            <div class="image-preview lg:w-[18.9875rem] lg:h-[12.5rem] md:w-[18.9875rem] md:h-[12.5rem] w-[6.9875rem] h-[6.5rem] flex justify-center items-center relative">
                <img src="${imageUrl}" alt="Preloaded Image" class="object-cover w-full h-full">
                <button type="button" class="remove-image-btn text-red-500 hover:text-red-700 absolute">Remove</button>
            </div>
        `;

        // Update the Dropzone container
        const dropzoneContainer = $(`#compatibility-dropzone-${itemId}`);
        dropzoneContainer.html(imagePreviewHtml); // Replace Dropzone's default content

        // Add event listener for the remove button
        dropzoneContainer.on("click", ".remove-image-btn", function () {
            deleteFileFromRepairablityDropzone(deleteFileFromRepairablityDropZoneRoute, {
                page_id: pageId,
                'item_id': itemId
            });
            dropzoneContainer.html('<div class="dz-message text-center text-gray-600">Drop an image here or click to upload</div>'); // Restore the default message
        });
    }
}
this.on("addedfile", function(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        const imagePreviewHtml = `
            <div class="image-preview lg:w-[18.9875rem] lg:h-[12.5rem] md:w-[18.9875rem] md:h-[12.5rem] w-[6.9875rem] h-[6.5rem] flex justify-center items-center relative">
                <img src="${e.target.result}" alt="Preloaded Image" class="object-cover w-full h-full">
                <button type="button" class="remove-image-btn text-red-500 hover:text-red-700 absolute">Remove</button>
            </div>
        `;
        // Append image preview inside the Dropzone container
        $(`#compatibility-dropzone-${itemId}`).html(imagePreviewHtml).removeClass("hidden");
    };
    reader.readAsDataURL(file);
});

        this.on("removedfile", function() {
            $(`.item[data-id='${itemId}'] .image-preview-container`).html("").addClass("hidden");
            sendDataToAjax(dropzoneElement.closest('.compatibility-section'));
        });

        this.on("success", function(file, response) {
            console.log('Upload successful:', response);
            sendDataToAjax(dropzoneElement.closest('.compatibility-section'));
        });

        this.on("error", function(file, errorMessage) {
            console.error('Error uploading file:', errorMessage);
        });
    }
});

    return dropzone;
}

// Save form data
function sendDataToAjax(element) {
    const sectionContainer = $(element).closest('.compatibility-section');
    const sectionId = sectionContainer.data('id');
    const sectionTitle = sectionContainer.find('.section-title').val();
    const sectionOrder = sectionContainer.index();

    const repairabilityCompatibilitySection = {
        id: sectionId,
        title: sectionTitle,
        sectionOrder: sectionOrder
    };

    const itemData = [];
    sectionContainer.find('.item').each(function(index) {
        const itemId = $(this).data('id');
        const imageUrl = $(this).find('.image-preview img').attr('src') || null;
        const editorContent = $(this).find('.item-editor').html();

        itemData.push({
            id: itemId,
            order: index,
            image: imageUrl,
            content: editorContent
        });
    });

    const requestData = {
        page_id: pageId,
        repairabilityCompatibilitySection: repairabilityCompatibilitySection,
        items: itemData
    };

    $.ajax({
        url: "{{ route('templates.repariablity-combatibility.update') }}",
        method: "POST",
        data: requestData,
        success: function(response) {
            showSuccessNotification(response.message);
        },
        error: function(error) {
            showErrorNotification(response.message);
        }
    });
}

let typingTimer; // Variable to store the timeout

$('#compatibility-sections-container').on('input', '.section-title, .item-editor', function () {
    clearTimeout(typingTimer); // Clear the previous timeout
    const $this = $(this);

    typingTimer = setTimeout(function () {
        sendDataToAjax($this); // Call the AJAX function after the delay
    }, 500); // Delay of 500 milliseconds
});


    // Add new section
 // Add new section
$(document).on('click', '#add-compatibility-section-btn', function() {
    const sectionsContainer = $('#compatibility-sections-container');
    const hasExistingSections = sectionsContainer.children('.compatibility-section').length > 0;
    const newSectionId = generateBase64Key(8);

    // Conditionally include the title input based on existing sections
    const titleInput = hasExistingSections ? `
        <div>
            <input type="text"
                class="section-title w-full text-lg font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-300 rounded-md px-2 py-1"
                placeholder="Section Title" />
        </div>
    ` : '';

    const newCompatibilitySection = `
        <div class="compatibility-section bg-white shadow-md rounded-lg mb-6 p-4 border border-gray-200" data-id="${newSectionId}">
            <div class="flex flex-wrap justify-start items-center gap-1 mb-4">
                ${titleInput}
                <div>
                    <button class="remove-compatibility-section-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                    <span class="compatiblility-section-drag-handle cursor-pointer">↑↓</span>
                </div>
            </div>
            <div class="compatibility-items-container flex flex-wrap items-center gap-1"></div>
            <button class="add-compatibility-item-btn text-blue-600 hover:text-blue-700 font-medium text-sm mt-4">+ Add Item</button>
        </div>
    `;

        sectionsContainer.append(newCompatibilitySection);
            makeCompatibilitySectionItemsContainerSortable();
        });

        // Add new item
        $(document).on('click', '.add-compatibility-item-btn', function() {
        const uniqueKey = generateBase64Key(8); // Generate the key once and reuse it
        const newCompatibilityItem = `
            <div class="item flex flex-row gap-2" data-id="${uniqueKey}">
                <div class="mb-2">
                    <span class="item-drag-handle cursor-pointer">⇄</span>
                </div>
                <div class="flex flex-col flex-wrap">
                    <div class="mb-2">
                        <div class="compatibility-dropzone w-full min-h-[200px] border-2 border-dashed border-gray-300 p-4 flex items-center justify-center relative"
                            id="compatibility-dropzone-${uniqueKey}">
                            <div class="dz-message text-center text-gray-600">Drop an image here or click to upload</div>
                        </div>
                    </div>
                    <div class="image-preview-container lg:w-[28.9875rem] lg:h-[12.5rem] md:w-[18.9875rem] md:h-[12.5rem] w-[6.9875rem] h-[6.5rem] hidden mb-2"></div>
                    <div class="mb-14 lg:w-[28.9875rem] md:w-[28.9875rem] w-full">
                        <div id="repairability-or-compatibility-text-quill-${uniqueKey}" class="item-editor bg-white"></div>
                        <textarea class="hidden" id="repairability-or-compatibility-text-${uniqueKey}" name="repairability_or_compatibility_text[]"
                            required></textarea>
                    </div>
                </div>
                <div class="mb-2">
                    <button class="remove-compatibility-item-btn text-red-500 hover:text-red-700 font-medium text-sm">X</button>
                </div>
            </div>
        `;
        $(this).siblings('.compatibility-items-container').append(newCompatibilityItem);
    initializeCompatibilityDropzone(uniqueKey);

        initializeRepariabilityOrCompatibilityPhotosQuill(
            `#repairability-or-compatibility-text-quill-${uniqueKey}`,
            `#repairability-or-compatibility-text-${uniqueKey}`
        );
    });

    // Remove section
    $(document).on('click', '.remove-compatibility-section-btn', function() {
        // $(this).closest('.compatibility-section').remove();
        const section = $(this).closest(".compatibility-section");
        const sectionId = section.data("id");

        $.ajax({
            url: "{{ route('template.repariablity.remove-section') }}",
            method: "DELETE",
            data: {
                page_id: pageId,
                section_id: sectionId
            },
            success: function(response) {
                section.remove();
                if (response.status) {
                        showSuccessNotification(response.message);
                    } else {
                        showErrorNotification(response.message);
                    }
            },
            error: function(xhr) {
                showErrorNotification(xhr.responseJSON.message);
            },
        });
    });

    // Remove item
    $(document).on('click', '.remove-compatibility-item-btn', function() {
        // $(this).closest('.item').remove();
        const Item = $(this).closest(".item");
        const itemId = Item.data("id");

        $.ajax({
            url: "{{ route('template.repariablity.remove-section') }}",
            method: "DELETE",
            data: {
                page_id: pageId,
                item_id: itemId
            },
            success: function(response) {
                Item.remove();
                if (response.status) {
                        showSuccessNotification(response.message);
                    } else {
                        showErrorNotification(response.message);
                    }
            },
            error: function(xhr) {
                showErrorNotification(xhr.responseJSON.message);
            },
        });
    });

    // Initialize Quill for the default item
    @if (empty($pageData->json_data['comparision_sections'] ?? null))
    initializeRepariabilityOrCompatibilityPhotosQuill(
        '#repairability-or-compatibility-text-quill-1',
        '#repairability-or-compatibility-text-1'
    );
    @endif

    @if (empty($pageData->json_data['comparision_sections'] ?? null))
        document.addEventListener('DOMContentLoaded', function() {
            initializeCompatibilityDropzone(1);
        });
@endif

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
            const compatibilitySectionsOrder = $("#compatibility-sections-container .compatibility-section")
                .map(function() {
                    return $(this).data("id");
                }).get();
            $.ajax({
                url: "{{ route('templates.page.repariablity-combatibility-ordering.update') }}",
                method: 'POST',
                data: {
                    page_id: pageId,
                    sections_order: compatibilitySectionsOrder,
                },
                success: function(response) {
                    if (response.status) {
                        showSuccessNotification(response.message);
                    } else {
                        showErrorNotification(response.message);
                    }
                },
                error: function(xhr) {
                    showErrorNotification("Failed to reorder sections:", xhr.responseText);
                }
            });

        },
    });

    // Function to make items in sections sortable (drag to reorder items)
    function makeCompatibilitySectionItemsContainerSortable() {
        $(".compatibility-items-container").sortable({
            items: ".item", // Only items can be dragged
            handle: ".item-drag-handle", // Drag handle element
            opacity: 0.5,
            start: function(event, ui) {
                ui.item.css("background-color", "rgba(96, 165, 250, 0.5)"); // Set opacity of dragging item
            },
            stop: function(event, ui) {
                ui.item.css("background-color", "white"); // Reset opacity
            },
            update: function(event, ui) {
                console.log('item reordered');

                const compatibilityItemsOrder = $(this).find(".item").map(function(index) {
                    return {
                        id: $(this).data("id"),
                        order: index, // Update the order dynamically
                    };
                }).get();

                $.ajax({
                    url: "{{ route('templates.page.repariablity-combatibility-items-ordering.update') }}",
                    method: 'POST',
                    data: {
                        page_id: pageId,
                        items: compatibilityItemsOrder,
                    },
                    success: function(response) {
                        if (response.status) {
                            showSuccessNotification(response.message);
                        } else {
                            showErrorNotification(response.message);
                        }
                    },
                    error: function(xhr) {
                        showErrorNotification("Failed to reorder items:", xhr.responseText);
                    }
                });
            }

        });
    }

    // Apply sortable to each section's items after initial load
    $(".compatibility-section").each(function() {
        makeCompatibilitySectionItemsContainerSortable();
    });
</script>
@endpush

