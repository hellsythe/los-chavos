<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="Description" content="@yield('description')">
    <title> @yield('title', config('app.name'))</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    @vite(['resources/back/css/app.css', 'resources/back/js/app.js'])
</head>

<body class="h-screen" data-theme="{{ Cache::get('theme', config('base.theme')) }}">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen">
        @include('base::back.layouts.partial.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('base::back.layouts.partial.header')

            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                <div class="container mx-auto px-6 py-8 relative">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('scanComplete', function(e) {
            window.location.href = '/admin/order/' + e.detail;
         })
    </script>
</body>

</html>
