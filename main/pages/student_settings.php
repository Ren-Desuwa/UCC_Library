<div id="settings-content" class="content-panel">
    <header class="main-header">
        <h1>Settings</h1>
        <p>Manage your account security and update your profile information.</p>
    </header>

    <section class="settings-card account-info-card">
        <h2 class="card-title">Account Information</h2>
        
        <div class="info-group-grid">
            
            <div class="info-group">
                <label for="setting-name" class="info-label">Name</label>
                <input type="text" id="setting-name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" readonly class="info-input">
            </div>

            <div class="info-group">
                <label for="setting-contact" class="info-label">Contact No.</label>
                <input type="text" id="setting-contact" value="09184576921" readonly class="info-input">
            </div>

            <div class="info-group">
                <label for="setting-email" class="info-label">Email</label>
                <input type="email" id="setting-email" value="student@school.edu" readonly class="info-input">
            </div>
            
        </div>

        <div class="info-actions">
            <button id="edit-account-btn" class="settings-btn secondary-btn edit-btn" style="display: inline-block;">Edit</button>
            <button id="save-account-btn" class="settings-btn primary-btn save-btn" disabled style="display: none;">Save</button>
        </div>
    </section>
    
    <section class="settings-card password-card">
        <h2 class="card-title">Change Password</h2>
        
        <div class="info-group-grid">
            <div class="info-group">
                <label for="setting-password" class="info-label">New Password</label>
                <input type="password" id="setting-password" placeholder="Enter new password" class="info-input">
            </div>

            <div class="info-group">
                <label for="setting-confirm" class="info-label">Confirm Password</label>
                <input type="password" id="setting-confirm" placeholder="Re-enter new password" class="info-input">
            </div>
        </div>
        
        <div class="show-password-check">
            <input type="checkbox" id="show-password">
            <label for="show-password">Show Password</label>
        </div>

        <div class="password-actions">
            <button id="confirm-password-btn" class="settings-btn primary-btn confirm-btn">Confirm Change</button>
        </div>
    </section>
</div>