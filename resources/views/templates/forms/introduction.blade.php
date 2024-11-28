<div class="w-full mx-auto p-6 bg-white shadow rounded-lg">
    <form action="/submit-report" method="POST">
        <!-- Report Title -->
        <div class="mb-4">
            <label for="report-title" class="block text-gray-700 text-sm font-medium mb-2">Report Title</label>
            <input type="text" id="report-title" name="report_title" placeholder="Enter report title"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data" required />
        </div>

        <!-- Date -->
        <div class="mb-4">
            <label for="report-date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
            <input type="date" id="report-date" name="report_date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                required />
        </div>
        <div class="flex flex-wrap lg:gap-4 md:gap-4">

            <!-- First Name -->
            <div class="mb-4 grow">
                <label for="first-name" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                <input type="text" id="first-name" name="first_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    required />
            </div>
            <!-- Last Name -->
            <div class="mb-4 grow">
                <label for="last-name" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                <input type="text" id="last-name" name="last_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    required />
            </div>

        </div>

        <!-- Company Name -->
        <div class="mb-4">
            <label for="company-name" class="block text-gray-700 text-sm font-medium mb-2">Company Name</label>
            <input type="text" id="company-name" name="company_name"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                required />
        </div>

        <!-- Address -->
        <div class="mb-4">
            <label for="company-address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
            <input type="text" id="company-address" name="company_address"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                required />
        </div>

        <div class="flex flex-wrap lg:gap-4 md:gap-4">

            <!-- City -->
            <div class="mb-4 grow">
                <label for="company-city" class="block text-gray-700 text-sm font-medium mb-2">City</label>
                <input type="text" id="company-city" name="company_city"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    required />
            </div>

            <!-- State/Province -->
            <div class="mb-4 grow">
                <label for="company-province"
                    class="block text-gray-700 text-sm font-medium mb-2">State/Province</label>
                <input type="text" id="company-province" name="company_province"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    required />
            </div>

            <!-- Zip Code / Postal Code -->
            <div class="mb-4 grow">
                <label for="company-postal-code" class="block text-gray-700 text-sm font-medium mb-2">Zip code/Postal
                    code</label>
                <input type="text" id="company-postal-code" name="company_postal_code"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200 focus:border-blue-500 inp-data"
                    required />
            </div>
        </div>


        <!-- Introductory Text -->
        <div class="mb-4">
            <label for="intro-text" class="block text-gray-700 text-sm font-medium mb-2">Introductory Text</label>
            <div id="intro-text-quill" class="bg-white"></div>
            <textarea class="hidden" id="intro-text" name="intro_text" required>{{ '' }}</textarea>
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
