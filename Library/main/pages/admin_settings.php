<div id="admin-settings-content" class="content-panel">
    <header class="main-header">
        <h1>Library Settings</h1>
        <p>Manage global settings for the library management system.</p>
    </header>
    
    <section class="settings-card">
        <form id="settings-form">
            <div class="settings-form-grid">
                
                <div class="input-row">
                    <label for="setting-borrow-duration">Borrow Duration (Days)</label>
                    <input type="number" id="setting-borrow-duration" name="borrow_duration_days" class="info-input">
                    <small>Number of days a book can be borrowed.</small>
                </div>
                
                <div class="input-row">
                    <label for="setting-max-books">Max Books per User</label>
                    <input type="number" id="setting-max-books" name="max_books_per_user" class="info-input">
                    <small>Maximum number of books a user can borrow at one time.</small>
                </div>

                <div class="input-row">
                    <label for="setting-fine-rate">Overdue Fine (per day)</label>
                    <input type="text" id="setting-fine-rate" name="overdue_fine_per_day" class="info-input" placeholder="e.g., 1.50">
                    <small>Fine amount (e.g., 1.00) per day a book is overdue.</small>
                </div>

                <div class="input-row">
                    <label for="setting-reservation-expiry">Reservation Expiry (Hours)</label>
                    <input type="number" id="setting-reservation-expiry" name="reservation_expiry_hours" class="info-input">
                    <small>Hours a user has to pick up a reserved book.</small>
                </div>
                
            </div>
            
            <div class="settings-actions">
                <button type="submit" class="settings-btn primary-btn">Save Settings</button>
            </div>
        </form>
    </section>
</div>