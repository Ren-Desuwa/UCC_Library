<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/icons/Logo.png" alt="Library MS Logo" class="library-logo"> 
        <div class="logo-text">
            <span class="app-name">Library MS</span>
            <span class="portal-name">Student Portal</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="#dashboard" class="nav-item active" data-target="dashboard-content">
            <span class="material-icons-round">bar_chart</span>
            Dashboard
        </a>
        <a href="#search-books" class="nav-item" data-target="search-books-content">
            <span class="material-icons-round">menu_book</span>
            Catalogue
        </a>
        <a href="#history" class="nav-item" data-target="history-content">
            <span class="material-icons-round">history</span>
            History
        </a>
        <a href="#settings" class="nav-item" data-target="settings-content">
            <span class="material-icons-round">settings</span>
            Settings
        </a>
    </nav>
   <div class="sidebar-footer">
        <div class="user-info" id="profile-button"> 
            <span class="material-icons-round user-icon">person</span>
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