<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Report Title -->
        <div class="mb-4">
            <label for="report-title" class="block text-gray-700 text-sm font-medium mb-2">Report Title</label>
            <input type="text" id="report-title" name="report_title" placeholder="Enter report title"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{ $pageData->json_data['report_title'] ?? '' }}" required />
        </div>

        <!-- Date -->
        <div class="mb-4">
            <label for="report-date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
            <input type="date" id="report-date" name="report_date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{ $pageData->json_data['report_date'] ?? '' }}" onclick=openCalendar() required />
        </div>
        <div class="flex flex-wrap lg:gap-4 md:gap-4">

            <!-- First Name -->
            <div class="mb-4 grow">
                <label for="first-name" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                <input type="text" id="first-name" name="first_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $pageData->json_data['first_name'] ?? '' }}" required />
            </div>
            <!-- Last Name -->
            <div class="mb-4 grow">
                <label for="last-name" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                <input type="text" id="last-name" name="last_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    value="{{ $pageData->json_data['last_name'] ?? '' }}" required />
            </div>

        </div>

        <!-- Company Name -->
        <div class="mb-4">
            <label for="company-name" class="block text-gray-700 text-sm font-medium mb-2">Company Name</label>
            <input type="text" id="company-name" name="company_name"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                value="{{ $pageData->json_data['company_name'] ?? '' }}" required />
        </div>

        <!-- Address -->
        <div class="mb-4">
            <label for="company-address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
            <input type="text" id="company-address" name="company_address"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                placeholder="Enter your address" autocomplete="off" required />
            <!-- Container for suggestions -->
            <div id="suggestions"></div>
        </div>

        <div class="flex flex-wrap gap-4">
            <!-- City -->
            <div class="mb-4 grow">
                <label for="company-city" class="block text-gray-700 text-sm font-medium mb-2">City</label>
                <input type="text" id="company-city" name="company_city"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    placeholder="City" required />
            </div>

            <!-- State/Province -->
            <div class="mb-4 grow">
                <label for="company-province"
                    class="block text-gray-700 text-sm font-medium mb-2">State/Province</label>
                <input type="text" id="company-province" name="company_province"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    placeholder="State/Province" required />
            </div>

            <!-- Zip Code / Postal Code -->
            <div class="mb-4 grow">
                <label for="company-postal-code" class="block text-gray-700 text-sm font-medium mb-2">Zip code/Postal
                    code</label>
                <input type="text" id="company-postal-code" name="company_postal_code"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    placeholder="Postal Code" required />
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
    <!-- <script>
        // Debounce function to reduce the rate of API calls
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // References to the input and suggestions container
        const addressInput = document.getElementById('company-address');
        console.log("address", addressInput);
        const suggestionsContainer = document.getElementById('suggestions');
        console.log("suggestion container", suggestionsContainer);
        const apiKey = 'AIzaSyBxqWY-xr9Pm9ZYMAYL08XOWu3X6Tz-Brw'; // Replace with your actual API key
        console.log("api key", apiKey);
        // Fetch geocoding results for a given query
        async function fetchGeocode(query) {
            const url =
                `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(query)}&key=${apiKey}`;
            try {
                console.log('Fetching geocode for:', query);
                const response = await fetch(url);
                const data = await response.json();
                console.log('API response:', data);
                if (data.status !== "OK") {
                    console.warn("Geocoding API returned:", data.status);
                    return [];
                }
                return data.results;
            } catch (error) {
                console.error('Error fetching geocode data:', error);
                return [];
            }
        }

        // Display the fetched suggestions
        function displaySuggestions(results) {
            suggestionsContainer.innerHTML = '';
            if (results.length === 0) {
                suggestionsContainer.style.display = 'none';
                return;
            }
            results.forEach(result => {
                const item = document.createElement('div');
                item.classList.add('suggestion-item');
                item.textContent = result.formatted_address;
                item.addEventListener('click', function() {
                    selectSuggestion(result);
                });
                suggestionsContainer.appendChild(item);
            });
            suggestionsContainer.style.display = 'block';
        }

        // When a suggestion is selected, update the input fields
        function selectSuggestion(result) {
            // Update address field with the formatted address
            addressInput.value = result.formatted_address;
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.style.display = 'none';

            // Extract components for city, state/province, and postal code
            let city = '',
                province = '',
                postalCode = '';
            result.address_components.forEach(component => {
                if (component.types.includes('locality')) {
                    city = component.long_name;
                }
                if (component.types.includes('administrative_area_level_1')) {
                    province = component.short_name;
                }
                if (component.types.includes('postal_code')) {
                    postalCode = component.long_name;
                }
            });

            // Populate the additional fields
            document.getElementById('company-city').value = city;
            document.getElementById('company-province').value = province;
            document.getElementById('company-postal-code').value = postalCode;
        }

        // Debounced input event handler to trigger geocoding
        const handleInput = debounce(async function() {
            const query = addressInput.value;
            // Increase the threshold for triggering a lookup
            if (query.length < 1) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
                return;
            }
            const results = await fetchGeocode(query);
            displaySuggestions(results);
        }, 500);

        // Listen to input events on the address field
        addressInput.addEventListener('input', handleInput);

        // Hide suggestions when clicking outside of the input or suggestions container
        document.addEventListener('click', function(e) {
            if (!addressInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
            }
        });
    </script> -->
    <script>
        // Replace with your actual API key
        const apiKey = 'AIzaSyBxqWY-xr9Pm9ZYMAYL08XOWu3X6Tz-Brw';
        // console.log("api key",apiKey);
        // Function to reverse geocode using latitude and longitude
        function reverseGeocode(lat, lng) {
            const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`;
            // console.log(url);
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status === "OK" && data.results.length) {
                        console.log("inside if condition");
                        const result = data.results[0];
                        // Update the address field with the full formatted address
                        document.getElementById('company-address').value = result.formatted_address;

                        // Extract additional address components (city, state/province, postal code)
                        let city = '',
                            province = '',
                            postalCode = '';
                        result.address_components.forEach(component => {
                            if (component.types.includes('locality')) {
                                city = component.long_name;
                            }
                            if (component.types.includes('administrative_area_level_1')) {
                                province = component.short_name;
                            }
                            if (component.types.includes('postal_code')) {
                                postalCode = component.long_name;
                            }
                        });
                        document.getElementById('company-city').value = city;
                        document.getElementById('company-province').value = province;
                        document.getElementById('company-postal-code').value = postalCode;
                    } else {
                        console.error("Reverse Geocoding failed:", data.status);
                    }
                })
                .catch(error => {
                    console.error("Error fetching geocode data:", error);
                });
        }

        // Use the browser's geolocation API to get the current position
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    // Call the reverse geocode function with obtained coordinates
                    const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`;
                    console.log(url);

                    reverseGeocode(lat, lng);
                },
                (error) => {
                    console.error("Error obtaining geolocation:", error);
                }
            );
        } else {
            console.error("Geolocation is not supported by this browser.");
        }


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
                    path: "{{ $pageData->json_data['secondary_image']['path'] ?? '' }}",
                    type: 'secondary_image'
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
