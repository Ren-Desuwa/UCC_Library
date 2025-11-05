<div id="admin-accounts-content" class="content-panel">
    <header class="main-header">
        <h1>Account Management</h1>
        <p>Create, edit, and manage all user accounts in the system.</p>
    </header>
    
    <section class="search-panel">
        <div class="search-controls">
            <div class="search-input-group">
                <span class="material-icons-round search-icon">search</span>
                <input type="text" id="admin-account-search" placeholder="Search by Username, Name, or Role">
            </div>
            <button class="action-btn" id="add-librarian-btn">
                <span class="material-icons-round" style="font-size: 1.2rem; margin-right: 5px;">add</span>
                Create Librarian
            </button>
        </div>

        <div class="results-table-container">
            <table class="results-table" id="admin-accounts-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="admin-accounts-table-body">
                    </tbody>
            </table>
        </div>
    </section>
</div>