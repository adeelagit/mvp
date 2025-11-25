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
    {{-- <link rel="stylesheet" href="{{ asset('css/admin.css') }}"> --}}
    <style>
        :root {
    --primary-color: #0d6efd;
    --primary-light: #e7f1ff;
    --secondary-bg: #f5f7fa;
    --sidebar-width: 260px;
    --text-color: #344767;
    --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --border-color: #e9ecef;
}

body {
    font-family: 'Inter', sans-serif;
    background-color: var(--secondary-bg);
    color: var(--text-color);
    overflow-x: hidden;
}

/* Sidebar Styles */
.sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: white;
    border-right: 1px solid var(--border-color);
    z-index: 1000;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.brand-logo {
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid var(--border-color);
}

.brand-logo i {
    font-size: 24px;
    color: var(--primary-color);
}

.brand-logo span {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
}

.nav-links {
    padding: 20px 16px;
    flex-grow: 1;
    overflow-y: auto;
}

.nav-category {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #8898aa;
    margin: 20px 0 10px 16px;
}

.nav-item {
    margin-bottom: 4px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #67748e;
    border-radius: 8px;
    transition: all 0.2s;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
}

.nav-link:hover {
    background-color: #f8f9fa;
    color: #344767;
}

.nav-link.active {
    background-color: var(--primary-color);
    color: white;
    box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
}

.nav-link.active i {
    color: white;
}

.nav-link i {
    width: 20px;
    text-align: center;
    font-size: 1rem;
}

/* Main Content */
.main-content {
    margin-left: var(--sidebar-width);
    padding: 24px;
    transition: all 0.3s ease;
    min-height: 100vh;
}

/* Header */
.top-header {
    background: white;
    padding: 16px 24px;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

/* Cards */
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--card-shadow);
    border: none;
    height: 100%;
    transition: transform 0.2s;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-3px);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

/* Tables & Content Containers */
.content-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: var(--card-shadow);
    margin-bottom: 24px;
}

.table thead th {
    background-color: #f8f9fa;
    color: #8898aa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    border-bottom: 1px solid var(--border-color);
    padding: 12px 16px;
}

.table td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.9rem;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-lg {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    object-fit: contain;
    background: #f8f9fa;
    padding: 4px;
}

/* Badges */
.badge-dot {
    padding-left: 0;
    padding-right: 0;
    background: transparent;
    color: #67748e;
    font-weight: 500;
    font-size: 0.85rem;
}
.badge-dot i {
    display: inline-block;
    vertical-align: middle;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    margin-right: 6px;
}

/* Forms */
.form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #344767;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

/* Plate Card */
.plate-card {
    background: #2d3436;
    color: white;
    border: 2px solid #000;
    border-radius: 8px;
    padding: 8px 16px;
    font-family: 'Courier New', monospace;
    font-weight: bold;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Mobile Responsive */
@media (max-width: 991px) {
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.show {
        transform: translateX(0);
    }
    .main-content {
        margin-left: 0;
    }
}

/* Utilities */
.bg-gradient-primary { background: linear-gradient(310deg, #2152ff, #21d4fd); color: white; }
.bg-gradient-success { background: linear-gradient(310deg, #17ad37, #98ec2d); color: white; }
.bg-gradient-warning { background: linear-gradient(310deg, #f53939, #fbcf33); color: white; }
.bg-gradient-info { background: linear-gradient(310deg, #1171ef, #11cdef); color: white; }

.cursor-pointer { cursor: pointer; }

/* Map */
#map { height: 250px; width: 100%; border-radius: 8px; z-index: 1; }

.avatar-xxl {
    width: 85px !important;
    height: 85px !important;
}

    </style>
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