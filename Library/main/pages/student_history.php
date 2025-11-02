<div id="history-content" class="content-panel">
    <header class="main-header">
        <h1>History</h1>
        <p>View your past and current borrowing records.</p>
    </header>
    
    <section class="history-panel search-panel">
        <div class="search-controls">
            <div class="search-input-group">
                <span class="material-icons-round search-icon">search</span>
                <input type="text" id="history-search-input" placeholder="Filter by Title or Status">
            </div>
            <select class="sort-dropdown" id="history-sort">
                <option>Sort by Date (Newest)</option>
                <option>Sort by Title (A-Z)</option>
            </select>
        </div>

        <div class="results-table-container">
            <table class="results-table history-table">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Borrowed Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="history-table-body">
                    <tr>
                        <td class="cover-cell"><img src="../assets/covers/The Glass Shore.jpg" alt="The Glass Shore" class="book-cover"></td>
                        <td>The Glass Shore</td>
                        <td>Oct 5, 2025</td>
                        <td>Oct 8, 2025</td>
                        <td><span class="status-tag tag-returned">Returned</span></td>
                        <td><button class="action-btn open-history-modal-btn" data-transaction-id="3">View</button></td>
                    </tr>
                    </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <p>Page 1 of 5</p>
        </div>
    </section>
</div>