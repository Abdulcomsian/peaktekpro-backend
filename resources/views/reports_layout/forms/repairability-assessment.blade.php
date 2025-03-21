<div class="w-full p-6 bg-white shadow rounded-lg">
    <!-- Image Upload Form -->
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone mb-6" 
        id="repairabilityAssessmentDropzone">
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Image</span>
            <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
        </div>
    </form>

    <!-- Descriptive Text Form -->
     <!-- Descriptive Text Form -->
     <form action="/upload" method="POST">
        <div class="my-6">
            <label for="roof-repair-limitations-text" class="block text-gray-700 text-sm font-medium mb-2">
                Descriptive Text for Roof Repair Limitations
            </label>
            <div id="roof-repair-limitations-quill" class="bg-white quill-editor-container"></div>
            <textarea class="hidden" id="roof-repair-limitations-text" name="roof_repair_limitations_text" required>
                {{ $pageData->json_data['roof_repair_limitations_text'] ?? '' }}
            </textarea>
        </div>
    </form>
</div>
<div id="upload-loader" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
</div>
<style>
    /* Custom Quill Editor Styles */
    .quill-editor-container {
        height: 200px;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .ql-container.ql-snow {
        border: none;
        height: calc(100% - 42px); /* Account for toolbar height */
    }

    .ql-editor {
        min-height: 150px !important;
        padding: 12px 16px !important;
        font-size: 14px;
        line-height: 1.5;
        overflow-y: auto;
    }

    .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid #e5e7eb;
        padding: 8px;
    }

    .ql-editor p {
        margin: 0 0 8px 0;
    }
</style>

@push('scripts')
<script type="text/javascript">
    // Initialize Dropzone
    Dropzone.autoDiscover = false;

    const assessmentDropzone = new Dropzone("#repairabilityAssessmentDropzone", {
        url: saveFileFromDropZoneRoute,
        maxFiles: 1,
        acceptedFiles: ".jpeg,.jpg,.png",
        dictRemoveFile: "Remove",
        addRemoveLinks: true,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        init: function() {
            // Existing image data
            const existingFile = {
                name: @json($pageData->json_data['repairability_assessment_images']['file_name'] ?? ''),
                size: @json($pageData->json_data['repairability_assessment_images']['size'] ?? ''),
                url: @json(isset($pageData->json_data['repairability_assessment_images']['path']) 
                    ? asset('storage/'.$pageData->json_data['repairability_assessment_images']['path']) 
                    : ''),
                path: @json($pageData->json_data['repairability_assessment_images']['path'] ?? '')
            };

            // Initialize with existing file
            if (existingFile.name && existingFile.url) {
                const mockFile = { 
                    name: existingFile.name, 
                    size: existingFile.size,
                    accepted: true
                };
                
                this.emit("addedfile", mockFile);
                this.emit("thumbnail", mockFile, existingFile.url);
                this.emit("complete", mockFile);
                this.files.push(mockFile);
            }

            this.on("addedfile", file => {
                if (this.files.length > 1) {
                    this.removeFile(this.files[0]);
                }

                if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                    this.removeFile(file);
                    showErrorNotification('Only JPEG, JPG, and PNG images are allowed.');
                }
            });

            this.on("sending", (file, xhr, formData) => {
                $("#upload-loader").removeClass("hidden"); // Show loader

                formData.append('type', 'repairability_assessment_images');
                formData.append('page_id', pageId);
                formData.append('folder', 'repairability_assessment');
            });

            this.on("success", (file, response) => {
                $("#upload-loader").addClass("hidden"); // Hide loader

                showSuccessNotification(response.message);
                if(response.path) file.previewElement.dataset.path = response.path;
            });

            this.on("removedfile", file => {
                deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                    page_id: pageId,
                    file_key: 'repairability_assessment_images',
                    file_path: file.previewElement?.dataset?.path
                });
            });

            this.on("error", (file, message) => {
                $("#upload-loader").addClass("hidden"); // Hide loader

                showErrorNotification(message);
                this.removeFile(file);
            });
        }
    });

    // Initialize Quill Editor
    const quillOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        ['link'],
        [{ header: 1 }, { header: 2 }],
        [{ list: 'ordered' }, { list: 'bullet' }, { list: 'check' }],
        [{ script: 'sub' }, { script: 'super' }],
        [{ header: [1, 2, 3, 4, 5, 6, false] }],
        [{ color: [] }, { background: [] }],
        [{ font: [] }],
        [{ align: [] }],
        ['clean']
    ];

    const roofRepairQuill = new Quill('#roof-repair-limitations-quill', {
        theme: 'snow',
        modules: { toolbar: quillOptions }
    });

    // Set initial content
    roofRepairQuill.root.style.height = '200px';
    roofRepairQuill.clipboard.dangerouslyPasteHTML(
        @json($pageData->json_data['roof_repair_limitations_text'] ?? '')
    );

    // Sync with textarea
    roofRepairQuill.on('text-change', () => {
        $('#roof-repair-limitations-text').val(roofRepairQuill.root.innerHTML);
        saveReportPageTextareaData('#roof-repair-limitations-text');
    });
</script>
@endpush