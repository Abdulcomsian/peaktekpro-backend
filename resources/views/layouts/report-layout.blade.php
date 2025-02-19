<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="{{ csrf_token() }}" name="csrf-token" />
    <title>@yield('title') | {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
    @stack('styles')
</head>

<body>
    <header class=" text-white p-4">
        <nav class=" mx-auto flex justify-between items-center">
            {{-- <!-- Logo and App Name --> --}}
            <div class="flex items-center space-x-4">
                {{-- <img src="{{ asset('assets/logo/logo-white.png') }}" alt="Logo" class="h-14 w-16 object-contain"> --}}
                <!-- Logo -->
                {{-- <a href="{{ route('templates.index') }}" class="text-xl font-bold">{{ config('app.name') }}</a> --}}
            </div>

            <!-- Navigation Links -->
            <div class="flex space-x-4">
                <a href="{{ route('reports.index') }}"
                    class="hover:text-gray-300 text-white btn-gradient p-2 rounded">Reports</a>
                <a href="{{ route('templates.index') }}"
                    class="hover:text-gray-300 text-white btn-gradient p-2 rounded">Templates</a>

                <!-- Add more links as needed -->
            </div>
        </nav>
    </header>

    <main class=" mx-auto">
        @yield('content')
    </main>
    {{-- show toastr --}}
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-4"></div>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        async function showSuccessNotification(message) {
            return new Promise((resolve) => {
                const notification = document.createElement('div');
                notification.classList.add(
                    'bg-green-500', 'text-white', 'p-4', 'rounded', 'shadow-lg', 'flex', 'items-center',
                    'space-x-2', 'animate-slide-in'
                );

                notification.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4 -4" />
                    </svg>
                    <span>${message}</span>
                `;

                document.getElementById('notification-container').appendChild(notification);

                setTimeout(() => {
                    notification.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => notification.remove(), 500);
                }, 3000);

                // Wait for 5 seconds before resolving
                setTimeout(resolve, 3000);
            });
        }


        async function showErrorNotification(message) {
            return new Promise((resolve) => {
                const notification = document.createElement('div');
                notification.classList.add(
                    'bg-red-500', 'text-white', 'p-4', 'rounded', 'shadow-lg', 'flex', 'items-center',
                    'space-x-2', 'animate-slide-in'
                );

                notification.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>${message}</span>
        `;

                document.getElementById('notification-container').appendChild(notification);

                setTimeout(() => {
                    notification.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => notification.remove(), 500);
                }, 3000);

                // Wait for 5 seconds before resolving
                setTimeout(resolve, 3000);
            });
        }

        // debounce function
        function debounce(func, delay) {
            let timer;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timer);
                timer = setTimeout(() => func.apply(context, args), delay);
            };
        }

        // CSRF token setup for AJAX in Laravel
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
