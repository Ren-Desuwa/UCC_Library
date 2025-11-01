<div id="librarian-users-content" class="content-panel">
    <header class="main-header">
        <h1>User Management</h1>
        <p>Search, view, and manage student and staff accounts.</p>
    </header>
    
    <section class="search-panel">
        <div class="search-controls">
            <div class="search-input-group">
                <span class="material-icons-round search-icon">search</span>
                <input type="text" id="user-search-input" placeholder="Search by Student ID, Username, or Email">
            </div>
        </div>

        <div class="results-table-container">
            <table class="results-table" id="user-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    <tr>
                        <td>alex</td>
                        <td>Alex Reyes</td>
                        <td>alex@imlibrary.com</td>
                        <td><span class="status-tag tag-available">Active</span></td>
                        <td>
                            <button class="action-btn" data-account-id="2">View Details</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>