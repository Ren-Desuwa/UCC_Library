<?php
    // We need the DB connection and the CatalogueService
    require_once __DIR__ . '/../php/db_connect.php';
    require_once __DIR__ . '/../php/services/CatalogueService.php';
    
    // Use your CatalogueService to get books for the catalogue
    $catalogueService = new CatalogueService($conn);
    // We use searchBooks with an empty query to get all books
    $books = $catalogueService->searchBooks("", 20, 0); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library MS - Visitor Portal</title>
    
    <link rel="stylesheet" href="../css/shared/base.css">
    <link rel="stylesheet" href="../css/shared/sidebar.css">
    <link rel="stylesheet" href="../css/shared/main.css">
    <link rel="stylesheet" href="../css/shared/tables.css">
    <link rel="stylesheet" href="../css/shared/modals.css">
    <link rel="stylesheet" href="../css/shared/forms.css">
    <link rel="stylesheet" href="../css/shared/responsive.css">

    <link rel="stylesheet" href="../css/pages/visitor.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">

        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/icons/Logo.png" alt="Library MS Logo" class="library-logo"> 
                <div class="logo-text">
                    <span class="app-name">Library MS</span>
                    <span class="portal-name">Visitor Portal</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="#search-books" class="nav-item active" data-target="search-books-content">
                    <span class="material-icons-round">search</span>
                    Search Books
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <span class="material-icons-round user-icon">account_circle</span>
                    <div class="user-details">
                        <p class="user-role">Guest</p>
                        <p class="user-id">Not Signed In</p>
                    </div>
                </div>
                <a href="login.php" class="login-link">
                    <span class="material-icons-round">login</span>
                    Sign In / Log In
                </a>
            </div>
        </aside>

        <main class="main-content">
            
            <div id="search-books-content" class="content-panel active">
                <header class="main-header">
                    <h1>Library Catalogue</h1>
                    <p>Search our collection of books, journals, and other resources.</p>
                </header>

                <section class="search-panel">
                    <div class="search-controls">
                        <div class="search-input-group">
                            <span class="material-icons-round search-icon">search</span>
                            <input type="text" id="catalogue-search-input" placeholder="Search by Title, Author, or Genre">
                        </div>
                        <select class="sort-dropdown">
                            <option>Sort by Title (A-Z)</option>
                            <option>Sort by Author</option>
                            <option>Sort by Date</option>
                        </select>
                    </div>

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
                                        <tr>
                                            <td class="cover-cell">
                                                <img src="../assets/covers/<?php echo htmlspecialchars($book['cover_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover">
                                            </td>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            
                                            <td><?php echo htmlspecialchars($book['author_names'] ?? 'N/A'); ?></td> 
                                            <td><?php echo htmlspecialchars($book['genre_names'] ?? 'N/A'); ?></td> 
                                            
                                            <td><span class="status-tag tag-available">Available</span></td>
                                            
                                            <td><button class="action-btn open-book-modal-btn" data-book-id="<?php echo $book['book_id']; ?>">View</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pagination">
                        <p>Page 1 of 1</p> </div>
                </section>
            </div>
        </main>
    </div>

    <div id="book-modal" class="modal-overlay">
        <div class="modal-content book-modal-content">
            </div>
    </div>
    
    <script type="module" src="../js/pages/visitor.js"></script>
</body>
</html>