<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') | {{ config('app.name') }}</title>
  @vite('resources/css/app.css')
</head>
<body>
    <header class="bg-gray-900 text-white p-4">
        <nav class="container mx-auto flex justify-between items-center">
            <!-- Logo and App Name -->
            <div class="flex items-center space-x-4">
                <img src="{{ asset('assets/logo/logo-white.png') }}" alt="Logo" class="h-8 w-8 object-contain"> <!-- Logo -->
                <a href="{{ route('templates.index') }}" class="text-xl font-bold">{{ config('app.name') }}</a>
            </div>

            <!-- Navigation Links -->
            <div class="flex space-x-4">
                <a href="{{ route('templates.index') }}" class="text-gray-300 hover:text-white">Templates</a>
                <a href="{{ route('templates.create') }}" class="text-gray-300 hover:text-white">Create Template</a>
                <!-- Add more links as needed -->
            </div>
        </nav>
    </header>

    <main class="container mx-auto">
        @yield('content')
    </main>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

@stack('scripts')
</body>
</html>
