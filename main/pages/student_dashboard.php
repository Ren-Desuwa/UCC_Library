<?php // We need the session to get the user's name ?>
<div id="dashboard-content" class="content-panel">
    <header class="main-header">
        <h1>Hello, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
        <p>Track your books, check overdue items, and stay updated...</p>
    </header>
    <section class="stats-grid">
        </section>
    <section class="updates-section">
        </section>
</div>