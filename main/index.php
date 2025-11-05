<?php
    // We need the DB connection and the CatalogueService
    require_once __DIR__ . '/../php/db_connect.php';
    require_once __DIR__ . '/../php/services/CatalogueService.php';
    
    $catalogueService = new CatalogueService($conn);
    // This call now returns 'available_copies' for each book
    $books = $catalogueService->searchBooks("", "", "", 20, 0); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library MS - Visitor Portal</title>
    
    <link rel="stylesheet" href="../css/shared/base.css">
    <link rel="stylesheet" href="../css/shared/main.css">
    <link rel="stylesheet" href="../css/shared/tables.css">
    <link rel="stylesheet" href="../css/shared/modals.css">
    <link rel="stylesheet" href="../css/shared/forms.css">
    <link rel="stylesheet" href="../css/shared/responsive.css">
    <link rel="stylesheet" href="../css/pages/visitor.css">
    
    <link rel="stylesheet" href="../css/pages/visitor-redesign.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body class="theme-dark"> <header class="site-header">
        <div class="header-content">
            <div class="header-logo">
                <img src="../assets/icons/Logo.png" alt="Library MS Logo" class="library-logo">
                <span class="app-name">Library MS</span>
            </div>
            <nav class="header-nav">
                <a href="index.php" class="nav-link active">Search Catalogue</a>
                <a href="about.php" class="nav-link">About Us</a>
            </nav>
            <div class="header-actions">
                <button id="theme-toggle-btn" class="icon-btn" aria-label="Toggle light/dark theme">
                    <span class="material-icons-round">light_mode</span>
                </button>
                <a href="login.php" class="action-btn primary-btn">
                    Sign In
                    <span class="material-icons-round">login</span>
                </a>
            </div>
            <button class="mobile-nav-toggle icon-btn" aria-label="Toggle navigation">
                <span class="material-icons-round">menu</span>
            </button>
        </div>
    </header>

    <nav class="mobile-nav">
        <a href="index.php" class="nav-link active">Search Catalogue</a>
        <a href="about.php" class="nav-link">About Us</a>
        <a href="login.php" class="nav-link">Sign In / Log In</a>
    </nav>

    <main class="main-content">
        
        <section class="hero-section" style="background-image: url('../assets/images/library-hero-bg.jpg');">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1>Welcome to the Library Portal</h1>
                <p>Find books, journals, and other resources available in our physical collection.</p>
                <div class="search-input-group hero-search">
                    <span class="material-icons-round search-icon">search</span>
                    <input type="text" id="catalogue-search-input" placeholder="Search by Title, Author, or Genre...">
                </div>
            </div>
        </section>

        <section id="catalogue-section" class="catalogue-section">
            <header class="catalogue-header">
                <h2>Full Catalogue</h2>
                <div class="catalogue-controls">
                    <select id="sort-dropdown" class="sort-dropdown">
                        <option value="title_asc">Sort by Title (A-Z)</option>
                        <option value="author_asc">Sort by Author (A-Z)</option>
                    </select>
                    <div class="view-switcher">
                        <button id="grid-view-btn" class="icon-btn active" aria-label="Grid View">
                            <span class="material-icons-round">grid_view</span>
                        </button>
                        <button id="list-view-btn" class="icon-btn" aria-label="List View">
                            <span class="material-icons-round">view_list</span>
                        </button>
                    </div>
                </div>
            </header>

            <div id="catalogue-grid-view" class="catalogue-view active">
                <div id="catalogue-grid-body" class="catalogue-grid-container">
                    <?php if (empty($books)): ?>
                        <p class="no-books-message">No books found in the catalogue.</p>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <?php
                                $isAvailable = $book['available_copies'] > 0;
                                $statusTag = $isAvailable ? 'tag-available' : 'tag-checkedout';
                                $statusText = $isAvailable ? 'Available' : 'Checked Out';
                            ?>
                            <div class="book-card open-book-modal-btn" data-book-id="<?php echo $book['book_id']; ?>">
                                <div class="card-cover-wrapper">
                                    <img src="../assets/covers/<?php echo htmlspecialchars($book['cover_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover-card">
                                </div>
                                <div class="card-details">
                                    <h3 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                                    <p class="card-author"><?php echo htmlspecialchars($book['author_names'] ?? 'N/A'); ?></p>
                                    <span class="status-tag <?php echo $statusTag; ?>"><?php echo $statusText; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div id="catalogue-list-view" class="catalogue-view">
                <div class="results-table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Author(s)</th>
                                <th>Genre(s)</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="catalogue-table-body">
                            <?php if (empty($books)): ?>
                                <tr><td colspan="6" style="text-align: center;">No books found in the catalogue.</td></tr>
                            <?php else: ?>
                                <?php foreach ($books as $book): ?>
                                    <?php
                                        $isAvailable = $book['available_copies'] > 0;
                                        $statusTag = $isAvailable ? 'tag-available' : 'tag-checkedout';
                                        $statusText = $isAvailable ? 'Available' : 'Checked Out';
                                    ?>
                                    <tr>
                                        <td class="cover-cell">
                                            <img src="../assets/covers/<?php echo htmlspecialchars($book['cover_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover">
                                        </td>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author_names'] ?? 'N/A'); ?></td> 
                                        <td><?php echo htmlspecialchars($book['genre_names'] ?? 'N/A'); ?></td> 
                                        <td><span class="status-tag <?php echo $statusTag; ?>"><?php echo $statusText; ?></span></td>
                                        <td><button class="action-btn open-book-modal-btn" data-book-id="<?php echo $book['book_id']; ?>">View</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="pagination">
                <p>Page 1 of 1</p> 
            </div>
        </section>

    </main>

    <footer class="site-footer-main">
        <p>Library Management System - For Educational Purposes</p>
        <div class="footer-links">
            <a href="#" class="modal-trigger-link" data-modal-target="privacy-modal">Privacy Policy</a>
            <a href="#" class="modal-trigger-link" data-modal-target="terms-modal">Terms of Use</a>
        </div>
    </footer>

    <div id="book-modal" class="modal-overlay">
        <div class="modal-content book-modal-content">
            </div>
    </div>
    
    <div id="privacy-modal" class="modal-overlay">
        <div class="modal-content text-modal-content">
            <div class="modal-header">
                <h2>Privacy Policy</h2>
                <button class="modal-close-btn-icon"><span class="material-icons-round">close</span></button>
            </div>
            <div class="modal-body">
                <h3>Our Commitment to Your Privacy</h3>
                <p>This system is for educational purposes. All personal data, including Student ID, name, and borrowing history, is stored securely within our local database. This information is used solely for the function of the library management system, such as tracking checkouts and managing reservations.</p>
                
                <h3>Data Usage</h3>
                <p>Your data will not be shared with any third parties. It is protected and will only be used to:</p>
                <ul>
                    <li>Manage your library account and borrowed items.</li>
                    <li>Notify you of due dates or available reservations.</li>
                    <li>Ensure accountability for library resources.</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="terms-modal" class="modal-overlay">
        <div class="modal-content text-modal-content">
            <div class="modal-header">
                <h2>Terms of Use</h2>
                <button class="modal-close-btn-icon"><span class="material-icons-round">close</span></button>
            </div>
            <div class="modal-body">
                <h3>Library Rules & Regulations</h3>
                <p>By using this system and borrowing books, you agree to the following terms:</p>
                <ul>
                    <li>All borrowed items must be returned on or before the specified due date.</li>
                    <li>Failure to return a book on time will result in a temporary suspension of borrowing privileges and/or a fine, as per university policy.</li>
                    <li>You are responsible for the condition of the books you borrow. Any damage (e.g., writing, torn pages, water damage) must be reported and may incur a replacement fee.</li>
                    <li>Do not share your Student ID or account with others. You are solely responsible for all items checked out to your account.</li>
                    <li>Violations of these terms may lead to disciplinary action.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script type="module" src="../js/pages/visitor.js"></script> 
</body>
</html>