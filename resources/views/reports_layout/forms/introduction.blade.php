<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Report Title -->
        <div class="mb-4">
            <label for="report-title" class="block text-gray-700 text-sm font-medium mb-2">Report Title</label>
            <input type="text" id="report-title" name="report_title" placeholder="Enter report title"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{  $pageData->json_data['report_title'] ?? $report->title }}" required  />
        </div>

        <!-- Date -->
        <div class="mb-4">
            <label for="report-date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
            <input type="date" id="report-date" name="report_date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{ $pageData->json_data['report_date'] ?? $created_At ?? '' }}"  required />
        </div>
        <div class="flex flex-wrap lg:gap-4 md:gap-4">

            <!-- First Name -->
            <div class="mb-4 grow">
                <label for="first-name" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                <input type="text" id="first-name" name="first_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $firstName ?? '' }}" readonly />
            </div>
            <!-- Last Name -->
            <div class="mb-4 grow">
                <label for="last-name" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                <input type="text" id="last-name" name="last_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $lastName ?? '' }}" readonly />
            </div>

        </div>

        <!-- Company Name -->
        <!-- <div class="mb-4">
            <label for="company-name" class="block text-gray-700 text-sm font-medium mb-2">Company Name</label>
            <input type="text" id="company-name" name="company_name"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{ $pageData->json_data['company_name'] ?? '' }}" required />
        </div> -->

        <!-- Address -->
        <div class="mb-4">
            <label for="company-address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
            <input type="text" id="company-address" name="company_address"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data-address"
                placeholder="Enter your address" value="{{ $address->formatedAddress ?? '' }}"
                autocomplete="off" readonly />
            <!-- Container for suggestions -->
            <div id="suggestions"></div>
        </div>

        <div class="flex flex-wrap gap-4">
            <!-- City -->
            <div class="mb-4 grow">
                <label for="company-city" class="block text-gray-700 text-sm font-medium mb-2">City</label>
                <input type="text" id="company-city" name="company_city"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data-address"
                    placeholder="City" value="{{ $address->city ?? '' }}" readonly/>
            </div>

            <!-- State/Province -->
            <div class="mb-4 grow">
                <label for="company-province"
                    class="block text-gray-700 text-sm font-medium mb-2">State/Province</label>
                <input type="text" id="company-province" name="company_province"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data-address"
                    placeholder="State/Province" value="{{ $address->state ?? '' }}" readonly />
            </div>

            <!-- Zip Code / Postal Code -->
            <div class="mb-4 grow">
                <label for="company-postal-code" class="block text-gray-700 text-sm font-medium mb-2">Zip code/Postal
                    code</label>
                <input type="text" id="company-postal-code" name="company_postal_code"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data-address"
                    placeholder="Postal Code" value="{{ $address->postalCode ?? '' }}" readonly />
            </div>
        </div>

        <!-- Introductory Text -->
        <div class="mb-4">
            <label for="intro-text" class="block text-gray-700 text-sm font-medium mb-2">Introductory Text</label>
            <div id="intro-text-quill" class="bg-white" style="position: static"></div>
            <textarea class="hidden" id="intro-text" name="intro_text" required>{{ $pageData->json_data['intro_text'] ?? '' }}</textarea>
        </div>

    </form>


    <!-- Primary Image Dropzone -->
<div class="w-full bg-white shadow rounded-lg relative">
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone"
        id="introduction-upload-primary-image">
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Primary Image</span>
            <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
        </div>
    </form>

    <!-- Primary Image Loader -->
    <div id="primary-image-loader" class="upload-box-loader hidden">
        <div class="spinner"></div>
        <p>Uploading...</p>
    </div>
</div>
<br>
<!-- Secondary Image Dropzone -->
<div class="w-full bg-white shadow rounded-lg relative">
    <form action="/upload" method="POST" enctype="multipart/form-data" class="dropzone"
        id="introduction-upload-secondary-image">
        <div class="dz-message text-gray-600">
            <span class="block text-lg font-semibold">Drag & Drop or Click to Upload Secondary Image</span>
            <small class="text-gray-500">Only jpeg, jpg and png files are allowed</small>
        </div>
    </form>

    <!-- Secondary Image Loader -->
    <div id="secondary-image-loader" class="upload-box-loader hidden">
        <div class="spinner"></div>
        <p>Uploading...</p>
    </div>
</div>


</div>



<style>
    /* Spinner Animation */
/* Loader inside dropzone */
.upload-box-loader {
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

/* Hide loader initially */
.hidden {
    display: none;
}


</style>

@push('scripts')
    <!-- Google Maps API (Include Places Library) -->
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBxqWY-xr9Pm9ZYMAYL08XOWu3X6Tz-Brw&libraries=places"> -->
    </script>

    <script>
        function initAutocomplete() {
            // Target the address input field
            const addressInput = document.getElementById('company-address');

            // Initialize Google Places Autocomplete
            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                types: ['geocode'], // Restrict to address suggestions
                componentRestrictions: {
                    country: "us"
                } // Restrict to a specific country (optional)
            });

            // Extract address details when a suggestion is selected
            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) {
                    console.error("No details available for input: " + addressInput.value);
                    return;
                }

                // Populate the address field with the formatted address
                addressInput.value = place.formatted_address;

                // Extract components (city, state, zip code)
                let city = '',
                    province = '',
                    postalCode = '';
                place.address_components.forEach(component => {
                    if (component.types.includes('locality')) {
                        city = component.long_name;
                        console.log("city", city);

                    }
                    if (component.types.includes('administrative_area_level_1')) {
                        province = component.short_name;
                        console.log("province", province);

                    }
                    if (component.types.includes('postal_code')) {
                        postalCode = component.long_name;
                        console.log("postalcode", postalCode);
                    }

                });

                $('#company-city').val(city);
                $('#company-province').val(province);
                $('#company-postal-code').val(postalCode);

                saveAddressData();
            });
        }

        // Initialize the autocomplete when the page loads
        window.onload = initAutocomplete;
   
    </script>

    





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
            saveReportPageTextareaData('#intro-text');
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
                    path: "{{ $pageData->json_data['primary_image']['path'] ?? '' }}",
                    type: 'primary_image'
                }

                // Show image on load
                showFileOnLoadInDropzone(this, primaryImageData);

                this.on("sending", function(file, xhr, formData) {
                    document.getElementById("primary-image-loader").classList.remove("hidden"); // Show loader

                    formData.append('type', 'primary_image');
                    formData.append('page_id', pageId);
                    formData.append('folder', 'introduction');
                });
              
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

                this.on("success", function(file, response) {
                    document.getElementById("primary-image-loader").classList.add("hidden"); // Hide loader

                    showSuccessNotification(response.message);
                });
                this.on("error", function (file, errorMessage) {
                    document.getElementById("primary-image-loader").classList.add("hidden"); // Hide loader
                    showErrorNotification(errorMessage);
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
                    path: "{{ $pageData->json_data['secondary_image']['path'] ?? '' }}",
                    type: 'secondary_image'
                }

                // Show image on load
                showFileOnLoadInDropzone(this, secondaryImageData);

                this.on("sending", function(file, xhr, formData) {
                    document.getElementById("secondary-image-loader").classList.remove("hidden"); // Show loader

                    formData.append('type', 'secondary_image');
                    formData.append('page_id', pageId);
                    formData.append('folder', 'introduction');
                });
              
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

                this.on("success", function(file, response) {
                    document.getElementById("secondary-image-loader").classList.add("hidden"); // Hide loader

                    showSuccessNotification(response.message);
                });

                this.on("error", function (file, errorMessage) {
                    document.getElementById("secondary-image-loader").classList.add("hidden"); // Hide loader
                    showErrorNotification(errorMessage);
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
