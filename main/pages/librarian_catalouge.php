<div id="librarian-catalog-content" class="content-panel">
    <header class="main-header">
        <h1>Catalog Management</h1>
        <p>Add, edit, and manage book entries and copies.</p>
    </header>
    
    <section class="search-panel">
        <div class="search-controls">
            <div class="search-input-group">
                <span class="material-icons-round search-icon">search</span>
                <input type="text" id="catalog-search-input" placeholder="Search by Title, Author, or ISBN">
            </div>
            <button class="action-btn" id="add-new-book-btn">
                <span class="material-icons-round" style="font-size: 1.2rem; margin-right: 5px;">add</span>
                Add New Book
            </button>
        </div>

        <div class="results-table-container">
            <table class="results-table" id="cataloging-table">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title & Author</th>
                        <th>Copies</th>
                        <th>Availability</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="catalog-table-body">
                    <tr>
                        <td class="cover-cell"><img src="../assets/covers/1984.jpg" alt="1984"></td>
                        <td>
                            <strong>1984</strong><br>
                            <span style="font-size: 0.9rem; color: #666;">George Orwell</span>
                        </td>
                        <td>5</td>
                        <td>3 Available</td>
                        <td>
                            <button class="action-btn edit-action-btn" data-book-id="1">Edit</button>
                            <button class="action-btn delete-action-btn" data-book-id="1">Delete</button>
                        </td>
                    </tr>
                    </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <p>Page 1 of 10</p>
        </div>
    </section>
</div>