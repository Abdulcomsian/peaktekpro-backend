<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Report Title -->
        <div class="mb-4">
            <label for="report-title" class="block text-gray-700 text-sm font-medium mb-2">Report Title</label>
            <input type="text" id="report-title" name="report_title" placeholder="Enter report title"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{ $pageData->json_data['report_title'] ?? $template->title }}" required />
        </div>

        <!-- Date -->
        <div class="mb-4">
            <label for="report-date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
            <input type="date" id="report-date" name="report_date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{ $pageData->json_data['report_date'] ?? $created_At }}" required />
        </div>

        <div class="flex flex-wrap lg:gap-4 md:gap-4">
            <!-- First Name -->
            <div class="mb-4 grow">
                <label for="first-name" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                <input type="text" id="first-name" name="first_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $firstName ?? '' }}" required />
            </div>
            <!-- Last Name -->
            <div class="mb-4 grow">
                <label for="last-name" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                <input type="text" id="last-name" name="last_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $lastName ?? '' }}" required />
            </div>
        </div>

        <!-- Address -->
        <div class="mb-4">
            <label for="company-address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
            <input type="text" id="company-address" name="company_address"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{ $address->formatedAddress ?? '' }}" required />
        </div>

        <div class="flex flex-wrap lg:gap-4 md:gap-4">
            <!-- City -->
            <div class="mb-4 grow">
                <label for="company-city" class="block text-gray-700 text-sm font-medium mb-2">City</label>
                <input type="text" id="company-city" name="company_city"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $address->city ?? ''}}" required />
            </div>

            <!-- State/Province -->
            <div class="mb-4 grow">
                <label for="company-province"
                    class="block text-gray-700 text-sm font-medium mb-2">State/Province</label>
                <input type="text" id="company-province" name="company_province"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $address->state ?? ''}}" required />
            </div>

            <!-- Zip Code / Postal Code -->
            <div class="mb-4 grow">
                <label for="company-postal-code" class="block text-gray-700 text-sm font-medium mb-2">Zip code/Postal
                    code</label>
                <input type="text" id="company-postal-code" name="company_postal_code"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $address->postalCode ?? '' }}" required />
            </div>
        </div>

        <!-- Introductory Text -->
        <div class="mb-4">
            <label for="intro-text" class="block text-gray-700 text-sm font-medium mb-2">Introductory Text</label>
            <div id="intro-text-quill" class="bg-white no-scroll"></div>
            <textarea class="hidden" id="intro-text" name="intro_text" required>{{ $pageData->json_data['intro_text'] ?? '' }}</textarea>
        </div>
    </form>

    <!-- Primary Image Upload -->
    <div class="mb-4">
        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone"
            id="introduction-upload-primary-image">
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Primary Image</span>
                <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
            </div>
        </form>
    </div>

    <!-- Secondary Image Upload -->
    <div class="mb-4">
        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone"
            id="introduction-upload-secondary-image">
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Certification/Secondary Logo</span>
                <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
            </div>
        </form>
    </div>
</div>
<style>
    /* Custom Quill Editor Styles */
    #intro-text-quill {
        height: 300px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
    }

    .ql-container.ql-snow {
        border: none;
        height: calc(100% - 42px);
        /* Account for toolbar height */
    }

    .ql-editor {
        min-height: 150px !important;
        padding: 12px 16px !important;
        font-size: 14px;
        line-height: 1.5;
        overflow: hidden;
    }

    .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid #e5e7eb;
        padding: 8px;
    }
</style>
@push('scripts')
<script type="text/javascript">
    // Quill Editor Initialization
    const introTextQuillOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        ['link'],
        [{
            'header': 1
        }, {
            'header': 2
        }],
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
        }],
        [{
            'header': [1, 2, 3, 4, 5, 6, false]
        }],
        [{
            'color': []
        }, {
            'background': []
        }],
        [{
            'font': []
        }],
        [{
            'align': []
        }],
        ['clean']
    ];

    const introTextQuill = new Quill('#intro-text-quill', {
        theme: 'snow',
        modules: {
            toolbar: introTextQuillOptions,
            clipboard: {
                matchVisual: false // Prevent extra empty lines
            }
        }
    });

    // Load content safely and handle empty space
    const initialContent = @json($pageData->json_data['intro_text'] ?? '');
    if (initialContent.trim() === '') {
        introTextQuill.root.innerHTML = '<p><br></p>'; // Minimal empty state
    } else {
        introTextQuill.clipboard.dangerouslyPasteHTML(initialContent);
    }

    // Handle content changes
    introTextQuill.on('text-change', function() {
        const content = introTextQuill.root.innerHTML;
        document.getElementById('intro-text').value = content === '<p><br></p>' ? '' : content;
        saveTemplatePageTextareaData('#intro-text');
    });
    // Dropzone Configuration
    function initDropzone(selector, type, fileData) {
        return new Dropzone(selector, {
            url: saveFileFromDropZoneRoute,
            maxFiles: 1,
            acceptedFiles: ".jpeg,.jpg,.png",
            dictRemoveFile: "Remove",
            dictDefaultMessage: "Drag & Drop or Click to Upload",
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            init: function() {
                if (fileData.name && fileData.url) {
                    const mockFile = {
                        name: fileData.name,
                        size: fileData.size,
                        dataURL: fileData.url,
                        accepted: true
                    };
                    this.emit("addedfile", mockFile);
                    this.emit("thumbnail", mockFile, fileData.url);
                    this.emit("complete", mockFile);
                    this.files.push(mockFile);
                }

                this.on("sending", (file, xhr, formData) => {
                    formData.append('type', type);
                    formData.append('page_id', pageId);
                    formData.append('folder', 'introduction');
                });

                this.on("addedfile", file => {
                    if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                        this.removeFile(file);
                        showErrorNotification('Only JPEG, JPG, and PNG images are allowed.');
                    }
                });

                this.on("success", (file, response) => {
                    showSuccessNotification(response.message);
                    if (response.path) file.previewElement.dataset.path = response.path;
                });

                this.on("removedfile", file => {
                    deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                        page_id: pageId,
                        file_key: type,
                        file_path: file.previewElement?.dataset?.path
                    });
                });

                this.on("error", (file, message) => {
                    showErrorNotification(message);
                    this.removeFile(file);
                });
            }
        });
    }

    // Initialize Dropzones
    const primaryImageData = {
        name: @json($pageData-> json_data['primary_image']['file_name'] ?? ''),
        size: @json($pageData -> json_data['primary_image']['size'] ?? ''),
        url: @json(isset($pageData -> json_data['primary_image']['path']) ? asset('storage/'.$pageData -> json_data['primary_image']['path']) : ''),
        path: @json($pageData -> json_data['primary_image']['path'] ?? '')
    };

    const secondaryImageData = {
        name: @json($pageData -> json_data['secondary_image']['file_name'] ?? ''),
        size: @json($pageData -> json_data['secondary_image']['size'] ?? ''),
        url: @json(isset($pageData -> json_data['secondary_image']['path']) ? asset('storage/'.$pageData -> json_data['secondary_image']['path']) : ''),
        path: @json($pageData -> json_data['secondary_image']['path'] ?? '')
    };

    const uploadPrimaryImageDropzone = initDropzone("#introduction-upload-primary-image", 'primary_image', primaryImageData);
    const uploadSecondaryImageDropzone = initDropzone("#introduction-upload-secondary-image", 'secondary_image', secondaryImageData);
</script>
@endpush