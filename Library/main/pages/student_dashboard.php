<div id="dashboard-content" class="content-panel active">
    <header class="main-header">
        <h1>Hello, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
        <p>Track your books, check overdue items, and stay updated on library news and events.</p>
    </header>

    <section class="stats-grid">
        <div class="stat-card card-blue">
            <div class="stat-content">
                <p class="stat-title">Books Borrowed</p>
                <span class="stat-number">4</span>
            </div>
            <span class="material-icons-round stat-icon">auto_stories</span>
        </div>

        <div class="stat-card card-orange">
            <div class="stat-content">
                <p class="stat-title">Overdue Items <span class="material-icons-round info-icon">info</span></p>
                <span class="stat-number">1</span>
            </div>
            <span class="material-icons-round stat-icon">notifications_active</span>
        </div>

        <div class="stat-card card-green">
            <div class="stat-content">
                <p class="stat-title">Total Books Read</p>
                <span class="stat-number">25</span>
            </div>
            <span class="material-icons-round stat-icon">person</span>
        </div>
    </section>

    <section class="updates-section">
        <h2>Latest Library Updates</h2>
        <div class="updates-list">
            <div class="update-item">
                <div class="tag tag-urgent">[URGENT]</div>
                <div class="update-details">
                    <p class="update-title">Main Floor Collection Area Closed</p>
                    <p class="update-description">Area A-C is temporarily closed for maintenance.</p>
                </div>
            </div>
            <div class="update-item">
                <div class="tag tag-event">[EVENT]</div>
                <div class="update-details">
                    <p class="update-title">End-Of-Semester Book Return Drive</p>
                    <p class="update-description">Drop-off box in the 3rd Floor Hall this week. Avoid Fines!</p>
                </div>
            </div>
            <div class="update-item">
                <div class="tag tag-notice">[NOTICE]</div>
                <div class="update-details">
                    <p class="update-title">New Study Cubicles Available</p>
                    <p class="update-description">New individual study cubicles open on the third floor.</p>
                </div>
            </div>
        </div>
        <a href="#" class="view-all-link" id="open-announcements-modal">View All Announcements (5 total)</a>
    </section>
</div>