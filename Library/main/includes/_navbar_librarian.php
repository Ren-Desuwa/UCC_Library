<link rel="stylesheet" href="../css/shared/navbar.css">
<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-logo">
            <img src="../assets/icons/Logo.png" alt="Library Logo">
            <span class="navbar-title">imLibrary</span>
        </div>
        
        <div class="navbar-links">
            <a href="#circulation" class="nav-link">Circulation</a>
            <a href="#catalog" class="nav-link">Catalog</a>
        </div>
        
        <div class="navbar-profile">
            <span class="nav-username">
                Librarian: <?php echo htmlspecialchars($_SESSION['name']); ?>
            </span>
            <a href="#settings" class="nav-icon-button">
                <span class="material-icons-round">settings</span>
            </a>
            <a href="#" id="logout-button" class="nav-icon-button">
                <span class="material-icons-round">logout</span>
            </a>
        </div>
    </div>
</nav>