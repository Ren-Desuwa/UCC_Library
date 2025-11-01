<div id="search-books-content" class="content-panel">
    <header class="main-header">
        <h1>Search Books</h1>
        <p>Find your next read from our entire collection.</p>
    </header>
    
    <section class="search-panel">
        <div class="search-controls">
            <div class="search-input-group">
                <span class="material-icons-round search-icon">search</span>
                <input type="text" id="student-search-input" placeholder="Search by Title, Author, or Genre">
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
                <tbody id="student-search-table-body">
                    <tr>
                        <td colspan="6" style="text-align: center;">Start typing to search for books...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <p>Page 1 of 1</p> 
        </div>
    </section>
</div>