<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modern Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4361ee;
            --bg-light: #f8f9fa;
            --dark-sidebar: #1e1e2d;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: var(--dark-sidebar);
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(255,255,255,0.05);
            font-weight: 600;
            letter-spacing: 1px;
        }

        .nav-link {
            color: #a2a3b7 !important;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            margin: 4px 15px;
            transition: 0.2s;
        }

        .nav-link:hover, .nav-link[aria-expanded="true"] {
            background: rgba(255,255,255,0.08);
            color: #fff !important;
        }

        .nav-link.active-link {
            background: var(--primary-color);
            color: #fff !important;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 20px;
        }

        .submenu {
            background: rgba(0,0,0,0.2);
            list-style: none;
            padding: 5px 0;
        }

        .submenu .nav-link {
            padding-left: 52px;
            font-size: 0.9rem;
        }

        /* Main Content Layout */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
        }

        .top-nav {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .content-area {
            padding: 30px;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        /* Mobile Adjustments */
        @media (max-width: 991.98px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            .main-wrapper {
                margin-left: 0;
            }
            .sidebar.show {
                left: 0;
            }
        }
    </style>
</head>
<body>

<div class="offcanvas-backdrop fade" id="backdrop" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header text-white d-flex align-items-center">
        <i class="material-icons-round me-2 text-primary">dashboard_customize</i>
        <span>ADMIN PRO</span>
    </div>
    
    <div class="mt-4">
        <a href="#" class="nav-link active-link">
            <i class="material-icons-round">home</i> Dashboard
        </a>

        <a class="nav-link" data-bs-toggle="collapse" href="#unitsSub">
            <i class="material-icons-round">apartment</i> Units
            <i class="material-icons-round ms-auto fs-6">expand_more</i>
        </a>
        <ul class="collapse submenu" id="unitsSub">
            <li><a href="#" class="nav-link">Add Unit</a></li>
            <li><a href="#" class="nav-link">View/Edit Units</a></li>
        </ul>

        <a class="nav-link" data-bs-toggle="collapse" href="#usersSub">
            <i class="material-icons-round">people</i> Users
            <i class="material-icons-round ms-auto fs-6">expand_more</i>
        </a>
        <ul class="collapse submenu" id="usersSub">
            <li><a href="{{ route('users.create') }}" class="nav-link">Add User</a></li>
            <li><a href="#" class="nav-link">Assign Seats</a></li>
        </ul>

        <a class="nav-link" data-bs-toggle="collapse" href="#petSub">
            <i class="material-icons-round">description</i> Petitions
            <i class="material-icons-round ms-auto fs-6">expand_more</i>
        </a>
        <ul class="collapse submenu" id="petSub">
            <li><a href="#" class="nav-link">Pending</a></li>
            <li><a href="#" class="nav-link">Verification</a></li>
        </ul>

        <div class="border-top border-secondary my-3 mx-3 opacity-25"></div>

        <a href="#" class="nav-link">
            <i class="material-icons-round">settings</i> Settings
        </a>
    </div>
</div>

<div class="main-wrapper">
    
    <nav class="top-nav d-flex align-items-center justify-content-between">
        <button class="btn btn-light d-lg-none" onclick="toggleSidebar()">
            <i class="material-icons-round">menu</i>
        </button>
        
        <div class="search-bar d-none d-md-block">
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="material-icons-round fs-6">search</i></span>
                <input type="text" class="form-control bg-light border-0" placeholder="Search report...">
            </div>
        </div>

        <div class="dropdown">
            <a href="#" class="text-decoration-none text-dark d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name=John+Doe&background=4361ee&color=fff" class="rounded-circle me-2" width="35">
                <span class="fw-medium d-none d-sm-inline">John Doe</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                <li><a class="dropdown-item py-2" href="#"><i class="material-icons-round align-middle me-2 fs-6">person</i> Profile</a></li>
                <li><a class="dropdown-item py-2 text-danger" href="#"><i class="material-icons-round align-middle me-2 fs-6">logout</i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="content-area">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('backdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
        if(backdrop.style.display === 'block') {
            backdrop.style.display = 'none';
        } else {
            backdrop.style.display = 'block';
        }
    }
</script>

</body>
</html>