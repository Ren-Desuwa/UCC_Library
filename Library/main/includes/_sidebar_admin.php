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
        
        <span class="nav-section-header">Admin Tools</span>
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
        
        <span class="nav-section-header">Librarian Tools</span>
        <a href="#circulation" class="nav-item" data-target="librarian-circulation-content">
            <span class="material-icons-round">swap_horiz</span>
            Circulation
        </a>
        <a href="#catalog" class="nav-item" data-target="librarian-catalog-content">
            <span class="material-icons-round">menu_book</span>
            Catalog Management
        </a>
        <a href="#archive" class="nav-item" data-target="librarian-archive-content">
            <span class="material-icons-round">archive</span>
            Archive
        </a>
        <a href="#users" class="nav-item" data-target="librarian-users-content">
            <span class="material-icons-round">people</span>
            User Search
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

<style>
/* Add this style to your admin.css or sidebar.css */
.nav-section-header {
    padding: 15px 20px 5px 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
    opacity: 0.6;
    text-transform: uppercase;
    display: block;
}
</style>