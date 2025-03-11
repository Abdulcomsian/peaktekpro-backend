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
                value="{{  $pageData->json_data['report_date'] ?? $created_At }}" required />
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

        <!-- Company Name -->
    

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
                    value="{{  $address->state ?? ''}}" required />
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
            <div id="intro-text-quill" class="bg-white"></div>
            <textarea class="hidden" id="intro-text" name="intro_text" required>{{ $pageData->json_data['intro_text'] ?? '' }}</textarea>
        </div>

    </form>

    <!-- Form for Primary Image -->
    <div class="mb-4">

        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone"
            id="introduction-upload-primary-image">
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Primary Image</span>
                <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
            </div>
        </form>
    </div>

    <div class="mb-4">

        <!-- Form for Certification/Secondary Logo Image -->
        <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone"
            id="introduction-upload-secondary-image">
            <div class="dz-message text-gray-600">
                <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Certification/Secondary
                    Logo</span>
                <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
            </div>
        </form>
    </div>

</div>

@push('scripts')
    <script type="text/javascript">
        // quill
        const introTextQuillOptions = [
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
        var introTextQuill = new Quill('#intro-text-quill', {
            theme: 'snow',
            modules: {
                toolbar: introTextQuillOptions
            }
        });
        // Set the height dynamically via JavaScript
        introTextQuill.root.style.height = '200px';

        // old text value
        let oldIntrolTextValue = "{!! $pageData->json_data['intro_text'] ?? '' !!}";

        // Load the saved content into the editor
        introTextQuill.clipboard.dangerouslyPasteHTML(oldIntrolTextValue);
        introTextQuill.on('text-change', function() {
            $('#intro-text').val(introTextQuill.root.innerHTML);

            //save textarea data
            saveTemplatePageTextareaData('#intro-text');
        });

        // dropzone

        const uploadPrimaryImageDropzone = new Dropzone("#introduction-upload-primary-image", {
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

                let primaryImageData = {
                    name: "{{ $pageData->json_data['primary_image']['file_name'] ?? '' }}",
                    size: "{{ $pageData->json_data['primary_image']['size'] ?? '' }}",
                    url: "{{ $pageData->file_url ?? '' }}",
                    path : "{{ $pageData->json_data['primary_image']['path'] ?? '' }}",
                    type : 'primary_image'
                }

                // Show image on load
                showFileOnLoadInDropzone(this, primaryImageData);

                this.on("sending", function(file, xhr, formData) {
                    formData.append('type', 'primary_image');
                    formData.append('page_id', pageId);
                    formData.append('folder', 'introduction');
                });
                // adding file
                this.on("addedfile", function(file) {
                    if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                        this.removeFile(file);
                        showErrorNotification('Only JPEG, JPG, and PNG images are allowed.');
                    }
                });

                this.on("success", function(file, response) {
                    showSuccessNotification(response.message);
                });

                this.on("removedfile", function(file) {
                    // delete file from dropzone
                    deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                        page_id: pageId,
                        file_key: 'primary_image',
                    });
                });
            }
        });

        const uploadSecondaryImageDropzone = new Dropzone("#introduction-upload-secondary-image", {
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

                let secondaryImageData = {
                    name: "{{ $pageData->json_data['secondary_image']['file_name'] ?? '' }}",
                    size: "{{ $pageData->json_data['secondary_image']['size'] ?? '' }}",
                    url: "{{ $pageData->file_url ?? '' }}",
                    path : "{{ $pageData->json_data['secondary_image']['path'] ?? '' }}",
                    type : 'secondary_image'
                }

                // Show image on load
                showFileOnLoadInDropzone(this, secondaryImageData);

                this.on("sending", function(file, xhr, formData) {
                    formData.append('type', 'secondary_image');
                    formData.append('page_id', pageId);
                    formData.append('folder', 'introduction');
                });
                // adding file
                this.on("addedfile", function(file) {
                    if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                        this.removeFile(file);
                        showErrorNotification('Only JPEG, JPG, and PNG images are allowed.');
                    }
                });

                this.on("success", function(file, response) {
                    showSuccessNotification(response.message);
                });

                this.on("removedfile", function(file) {
                    // delete file from dropzone
                    deleteFileFromDropzone(file, deleteFileFromDropZoneRoute, {
                        page_id: pageId,
                        file_key: 'secondary_image',
                    });

                });
            }
        });
    </script>
@endpush
