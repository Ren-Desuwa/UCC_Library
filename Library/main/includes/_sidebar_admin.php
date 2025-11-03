<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/icons/Logo.png" alt="Library MS Logo" class="library-logo"> 
        <div class="logo-text">
            <span class="app-name">Library MS</span>
            <span class="portal-name">Admin Portal</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#dashboard" class="nav-item active" data-target="admin-dashboard-content">
            <span class="material-icons-round">analytics</span>
            Dashboard
        </a>
        <a href="#accounts" class="nav-item" data-target="admin-accounts-content">
            <span class="material-icons-round">manage_accounts</span>
            Account Management
        </a>
        <a href="#announcements" class="nav-item" data-target="admin-announcements-content">
            <span class="material-icons-round">campaign</span>
            Announcements
        </a>
        <a href="#settings" class="nav-item" data-target="admin-settings-content">
            <span class="material-icons-round">settings</span>
            Library Settings
        </a>
        <a href="#logs" class="nav-item" data-target="admin-logs-content">
            <span class="material-icons-round">plagiarism</span>
            System Logs
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <span class="material-icons-round user-icon">admin_panel_settings</span>
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