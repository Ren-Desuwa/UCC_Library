<div id="admin-dashboard-content" class="content-panel active">
    <header class="main-header">
        <h1>Admin Dashboard</h1>
        <p>Overview of library activity and administrative tools.</p>
    </header>
    
    <section class="stats-grid">
        <div class="stat-card card-blue">
            <div class="stat-content">
                <span class="stat-title">Total Users</span>
                <span class="stat-number" id="stat-total-users">--</span>
            </div>
            <span class="material-icons-round stat-icon">group</span>
        </div>
        <div class="stat-card card-orange">
            <div class="stat-content">
                <span class="stat-title">Books on Loan</span>
                <span class="stat-number" id="stat-books-loaned">--</span>
            </div>
            <span class="material-icons-round stat-icon">arrow_upward</span>
        </div>
        <div class="stat-card card-green">
            <div class="stat-content">
                <span class="stat-title">Issues Reported</span>
                <span class="stat-number" id="stat-issues">0</span>
            </div>
            <span class="material-icons-round stat-icon">warning</span>
        </div>
    </section>
    
    <section class="announcement-maker-section">
        <h2>Quick Announcement</h2>
        <form class="announcement-maker-form" id="announcement-form">
            <div class="input-row">
                <label for="announcement-title">Title</label>
                <input type="text" id="announcement-title" name="title" required>
            </div>
            <div class="input-row details-row">
                <label for="announcement-message">Message</label>
                <textarea id="announcement-message" name="message" required></textarea>
            </div>
            <div class="input-row type-row">
                <label>Priority</label>
                <div class="tag-selector">
                    <input type="radio" id="priority-high" name="priority" value="High" style="display:none;" checked>
                    <label for="priority-high" class="tag tag-urgent">High</label>
                    
                    <input type="radio" id="priority-normal" name="priority" value="Normal" style="display:none;">
                    <label for="priority-normal" class="tag tag-event">Normal</label>
                    
                    <input type="radio" id="priority-low" name="priority" value="Low" style="display:none;">
                    <label for="priority-low" class="tag tag-notice">Low</label>
                </div>
            </div>
            <div class="submit-row">
                <a href="#announcements" class="manage-announcements-link" id="go-to-announcements">Manage All Announcements</a>
                <button type="submit" class="publish-btn">Publish</button>
            </div>
        </form>
    </section>
</div>