<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OneCharge Admin Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS (For Maps) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

    <!-- Sidebar Component -->
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="main-content">
        
        <!-- Navbar Component -->
        @include('partials.navbar')

        <!-- Dynamic Content -->
        @yield('content')

    </main>

    <!-- Modals Component -->
    @include('partials.modals')

    <!-- Scripts Component -->
    @include('partials.scripts')

</body>
</html>