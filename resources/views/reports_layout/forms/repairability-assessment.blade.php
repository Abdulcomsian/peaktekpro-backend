<div class="w-full p-6 bg-white shadow rounded-lg">
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone"
        id="repairabilityAssessmentDropzone">
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Image</span>
            <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
        </div>
    </form>

    <form action="/upload" method="POST">
        <!-- Descriptive Text Field -->
        <div class="my-6">
            <label for="roof-repair-limitations-text" class="block text-gray-700 text-sm font-medium mb-2">Descriptive
                Text for Roof Repair Limitations</label>
            <div id="roof-repair-limitations-quill" class="bg-white"></div>
            <textarea class="hidden" id="roof-repair-limitations-text" name="roof_repair_limitations_text" required>{{ $pageData->json_data['roof_repair_limitations_text'] ?? '' }}</textarea>
        </div>
    </form>
</div>

@push('scripts')
    <script type="text/javascript">
        // drop zone
        Dropzone.autoDiscover = false;

        const repairabilityAssessmentDropzone = new Dropzone("#repairabilityAssessmentDropzone", {
            url: saveMultipleFilesFromDropZoneRoute,
            uploadMultiple: true,
            parallelUploads: 100,
            maxFiles: 100,
            acceptedFiles: ".jpeg,.jpg,.png",
            addRemoveLinks: true,
            dictRemoveFile: "Remove",
            dictDefaultMessage: "Drag & Drop or Click to Upload",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            init: function() {

                let repairabilityAssessmentImages = {
                    files : JSON.parse(`{!! json_encode($pageData->json_data['repariability_assessment_images'] ?? []) !!}`),
                    file_url : "{{ $pageData->file_url ?? '' }}"
                }
                // Show images on load
                showMultipleFilesOnLoadInDropzone(this, repairabilityAssessmentImages, 'repariability_assessment_images');

                // When a file is added, check if it's valid based on accepted file types
                this.on("addedfile", function(file) {
                    if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                        // If the file type doesn't match, remove the file from preview
                        this.removeFile(file);
                        showErrorNotification('Only JPEG, JPG, and PNG images are allowed.')
                    }
                });
                this.on("sending", function(file, xhr, formData) {
                    formData.append('type', 'repariability_assessment_images');
                    formData.append('page_id', pageId);
                    formData.append('folder', 'repairability_assessment');
                });

                this.on("successmultiple", function(files, response) {
                    if (response.status && response.file_details.length === files.length) {
                        // Iterate through each uploaded file and its corresponding server response
                        files.forEach((file, index) => {
                            const fileData = response.file_details[index];  // Match file with its response data

                            // Add custom keys from the server response to the file object
                            file.file_id = fileData.file_id;

                        });
                        showSuccessNotification(response.message);
                    } else {
                        showErrorNotification("Mismatch between uploaded files and server response.");
                    }
                });

                this.on("removedfile", function(file) {

                    // delete file from dropzone
                    deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                        page_id: pageId,
                        file_key: 'repariability_assessment_images',
                        file_id: file.file_id,
                    });

                });
            }
        });


        // quill

        const roofRepairLimitationsQuillOptions = [
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
        var roofRepairLimitationsQuill = new Quill('#roof-repair-limitations-quill', {
            theme: 'snow',
            modules: {
                toolbar: roofRepairLimitationsQuillOptions
            }
        });
        // Set the height dynamically via JavaScript
        roofRepairLimitationsQuill.root.style.height = '200px';

        // old intro text value
        let oldRoofRepairLimitationText = "{!! $pageData->json_data['roof_repair_limitations_text'] ?? '' !!}";

        // Load the saved content into the editor
        roofRepairLimitationsQuill.clipboard.dangerouslyPasteHTML(oldRoofRepairLimitationText);
        roofRepairLimitationsQuill.on('text-change', function() {
            $('#roof-repair-limitations-text').val(roofRepairLimitationsQuill.root.innerHTML);

            //save textarea data
            saveReportPageTextareaData('#roof-repair-limitations-text');
        });
    </script>
@endpush
