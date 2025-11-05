<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/icons/Logo.png" alt="Library MS Logo" class="library-logo"> 
        <div class="logo-text">
            <span class="app-name">Library MS</span>
            <span class="portal-name">Librarian Portal</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#dashboard" class="nav-item active" data-target="librarian-dashboard-content">
            <span class="material-icons-round">dashboard</span>
            Dashboard
        </a>
        <a href="#circulation" class="nav-item" data-target="librarian-circulation-content">
            <span class="material-icons-round">swap_horiz</span>
            Circulation
        </a>
        <a href="#catalog" class="nav-item" data-target="librarian-catalog-content">
            <span class="material-icons-round">menu_book</span>
            Catalog Management
        </a>
        <a href="#users" class="nav-item" data-target="librarian-users-content">
            <span class="material-icons-round">people</span>
            User Management
        </a>
        <a href="#reports" class="nav-item" data-target="librarian-reports-content">
            <span class="material-icons-round">assessment</span>
            Reports
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <span class="material-icons-round user-icon">person_check</span>
            <div class="user-details">
                <p class="user-role"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
                <p class="user-id"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
        </div>
        <a href="#" id="logout-link" class="logout-link">
            <span class="material-icons-round">logout</span>
            Log out
        </a>
    </div>
</aside>