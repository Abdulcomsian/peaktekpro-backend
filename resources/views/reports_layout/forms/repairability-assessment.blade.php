
<div class="w-full p-6 bg-white shadow rounded-lg">
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone relative"
        id="repairabilityAssessmentDropzone">
        <!-- Loader inside the dropzone form -->
        <div id="loader" class="upload-box-loader1" style="display: none;">
            <div class="spinner"></div>
            <span class="mt-2 text-sm">Uploading...</span>
        </div>
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Image</span>
            <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
        </div>
    </form>

    <form action="/upload" method="POST">
        <!-- Descriptive Text Field -->
        <div class="my-6">
            <label for="roof-repair-limitations-text" class="block text-gray-700 text-sm font-medium mb-2">
                Descriptive Text for Roof Repair Limitations
            </label>
            <div id="roof-repair-limitations-quill" class="bg-white" style="position: static"></div>
            <textarea class="hidden" id="roof-repair-limitations-text" name="roof_repair_limitations_text" required>
                {{ $pageData->json_data['roof_repair_limitations_text'] ?? '' }}
            </textarea>
        </div>
    </form>
</div>

<style>
    .upload-box-loader1 {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 15px;
    border-radius: 10px;
    z-index: 10;
    display: flex;
    align-items: center;
    flex-direction: column;
    text-align: center;
}

/* Spinner Animation */
.spinner {
    width: 20px;
    height: 20px;
    border: 5px solid rgba(255, 255, 255, 0.3);
    border-top: 5px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

</style>
@push('scripts')
    <script type="text/javascript">
        // drop zone
        Dropzone.autoDiscover = false;

        const repairabilityAssessmentDropzone = new Dropzone("#repairabilityAssessmentDropzone", {
            url: saveFileFromDropZoneRoute,
            // uploadMultiple: false, // Change to false to prevent multiple file uploads
            // parallelUploads: 1,
            maxFiles: 1, // Allows only one file at a time
            acceptedFiles: ".jpeg,.jpg,.png",
            // addRemoveLinks: true,
            dictRemoveFile: "Remove",
            addRemoveLinks: true,

            // dictDefaultMessage: "Drag & Drop or Click to Upload",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            init: function() {
                // let repairabilityAssessmentImages = {
                //     files: JSON.parse(`{!! json_encode($pageData->json_data['repariability_assessment_images'] ?? []) !!}`),
                //     file_url: "{{ $pageData->file_url ?? '' }}"
                // };

                let repairabilityAssessmentImages = {
                    name: "{{ $pageData->json_data['repariability_assessment_images']['file_name'] ?? '' }}",
                    size: "{{ $pageData->json_data['repariability_assessment_images']['size'] ?? '' }}",
                    url: "{{ $pageData->file_url ?? '' }}",
                    path: "{{ $pageData->json_data['repariability_assessment_images']['path'] ?? '' }}",
                    type: 'repariability_assessment_images'
                }

                // Show existing image on load
                // showMultipleFilesOnLoadInDropzone(this, repairabilityAssessmentImages, 'repariability_assessment_images');

                showFileOnLoadInDropzone(this, repairabilityAssessmentImages);

                this.on("addedfile", function(file) {
                    if (this.files.length > 1) {
                        this.removeFile(this.files[
                        0]); // Remove the previous file if a new one is added
                    }

                if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                    this.removeFile(file);
                    showErrorNotification('Only JPEG, JPG, and PNG images are allowed.');
                }
            });

            this.on("sending", function(file, xhr, formData) {
                formData.append('type', 'repariability_assessment_images');
                formData.append('page_id', pageId);
                formData.append('folder', 'repairability_assessment');

                document.getElementById('loader').style.display = 'flex';

            });

              // Hide loader on upload complete
              this.on("complete", function(file) {
                document.getElementById('loader').style.display = 'none';
            });

            this.on("success", function(file, response) {
                    showSuccessNotification(response.message);
            });

            this.on("removedfile", function(file) {
                // delete file from dropzone
                deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                    page_id: pageId,
                    file_key: 'repariability_assessment_images',
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
