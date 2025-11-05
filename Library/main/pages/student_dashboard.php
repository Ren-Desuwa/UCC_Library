<?php
// --- 1. GET THE LIVE DATA ---

// We need the DB connection
require_once __DIR__ . '/../../php/db_connect.php'; // Corrected path

// Get the logged-in user's ID
$accountId = $_SESSION['account_id'];

// --- Query for Stat Cards ---
$stats = [
    'books_borrowed' => 0,
    'overdue_items' => 0,
    'total_books_read' => 0
];
$sqlStats = "SELECT
                COUNT(CASE WHEN status IN ('Active', 'Overdue') THEN 1 END) AS books_borrowed,
                COUNT(CASE WHEN status = 'Overdue' THEN 1 END) AS overdue_items,
                COUNT(CASE WHEN status = 'Completed' THEN 1 END) AS total_books_read
             FROM transactions
             WHERE account_id = ?";
$stmtStats = $conn->prepare($sqlStats);
$stmtStats->bind_param("i", $accountId);
$stmtStats->execute();
$resultStats = $stmtStats->get_result();
if ($resultStats) {
    $stats = $resultStats->fetch_assoc();
}

// --- Query for Announcements ---
$announcements = [];
$sqlAnnounce = "SELECT * FROM announcements WHERE is_active = 1 ORDER BY date_posted DESC LIMIT 3";
$resultAnnounce = $conn->query($sqlAnnounce);
if ($resultAnnounce) {
    $announcements = $resultAnnounce->fetch_all(MYSQLI_ASSOC);
}

// --- Query for Total Announcement Count ---
$totalAnnouncements = 0;
$sqlAnnounceCount = "SELECT COUNT(*) AS total FROM announcements WHERE is_active = 1";
$resultAnnounceCount = $conn->query($sqlAnnounceCount);
if ($resultAnnounceCount) {
    $totalAnnouncements = $resultAnnounceCount->fetch_assoc()['total'];
}

// === REMOVED: Query for New Arrivals is now gone ===

?>
<div id="dashboard-content" class="content-panel active">
    <header class="main-header">
        <h1>Hello, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
        <p>Track your books, check overdue items, and stay updated on library news and events.</p>
    </header>

    <section class="stats-grid">
        <div class="stat-card dashboard-stat-card card-blue">
            <div class="stat-content">
                <p class="stat-title">Books Borrowed</p>
                <span class="stat-number"><?php echo $stats['books_borrowed']; ?></span>
            </div>
            <span class="material-icons-round stat-icon">auto_stories</span>
        </div>

        <div class="stat-card dashboard-stat-card card-orange">
            <div class="stat-content">
                <p class="stat-title">Overdue Items <span class="material-icons-round info-icon">info</span></p>
                <span class="stat-number"><?php echo $stats['overdue_items']; ?></span>
            </div>
            <span class="material-icons-round stat-icon">notifications_active</span>
        </div>

        <div class="stat-card dashboard-stat-card card-green">
            <div class="stat-content">
                <p class="stat-title">Total Books Read</p>
                <span class="stat-number"><?php echo $stats['total_books_read']; ?></span>
            </div>
            <span class="material-icons-round stat-icon">person</span>
        </div>
    </section>

    <section class="updates-section">
        <h2>Latest Library Updates</h2>
        <div class="updates-list">
            <?php if (empty($announcements)): ?>
                <p class="no-updates">No library updates at this time.</p>
            <?php else: ?>
                <?php foreach ($announcements as $announcement): ?>
                    <?php
                        $tagClass = 'tag-notice';
                        $tagText = '[NOTICE]';
                        if ($announcement['priority'] == 'High') {
                            $tagClass = 'tag-urgent';
                            $tagText = '[URGENT]';
                        }
                    ?>
                    <div class="update-item">
                        <div class="tag <?php echo $tagClass; ?>"><?php echo $tagText; ?></div>
                        <div class="update-details">
                            <p class="update-title"><?php echo htmlspecialchars($announcement['title']); ?></p>
                            <p class="update-description"><?php echo htmlspecialchars($announcement['message']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <a href="#" class="view-all-link" id="open-announcements-modal">
                View All Announcements (<?php echo $totalAnnouncements; ?> total)
            </a>
        </div>
    </section>

    </div>