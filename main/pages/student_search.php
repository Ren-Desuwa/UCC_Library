<?php
    // We need the DB connection and the CatalogueService
    require_once __DIR__ . '/../../php/db_connect.php';
    require_once __DIR__ . '/../../php/services/CatalogueService.php';
    
    $catalogueService = new CatalogueService($conn);
    
    // Get all books for the initial page load
    $books = $catalogueService->searchBooks("", "", "", 50, 0); 
?>

<div id="search-books-content" class="content-panel">
    <header class="main-header">
        <h1>Library Catalogue</h1>
        <p>Browse our collection or search for something specific.</p>
    </header>

    <section class="catalogue-section">
        <header class="catalogue-header">
            <div class="search-input-group hero-search">
                <span class="material-icons-round search-icon">search</span>
                <input type="text" id="student-search-input" placeholder="Search by Title, Author, or Genre...">
            </div>
            
            <div class="catalogue-controls">
                <select id="student-sort-dropdown" class="sort-dropdown">
                    <option value="title_asc">Sort by Title (A-Z)</option>
                    <option value="author_asc">Sort by Author (A-Z)</option>
                </select>
                <div class="view-switcher">
                    <button id="student-grid-view-btn" class="icon-btn active" aria-label="Grid View">
                        <span class="material-icons-round">grid_view</span>
                    </button>
                    <button id="student-list-view-btn" class="icon-btn" aria-label="List View">
                        <span class="material-icons-round">view_list</span>
                    </button>
                </div>
            </div>
        </header>

        <div id="student-catalogue-grid-view" class="catalogue-view active">
            <div id="student-grid-body" class="catalogue-grid-container">
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

        <div id="student-catalogue-list-view" class="catalogue-view">
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
                    <tbody id="student-list-body">
                        <?php if (empty($books)): ?>
                            <tr><td colspan="6" style="text-align: center;">No books found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <?php
                                    $isAvailable = $book['available_copies'] > 0;
                                    $statusTag = $isAvailable ? 'tag-available' : 'tag-checkedout';
                                    $statusText = $isAvailable ? 'Available' : 'Checked Out';
                                ?>
                                <tr>
                                    <td class="cover-cell"><img src="../assets/covers/<?php echo htmlspecialchars($book['cover_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover"></td>
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
</div>