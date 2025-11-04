<?php
// This file holds all modals for the Admin portal.
?>

<div id="add-librarian-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create Librarian Account</h2>
        </div>
        
        <form id="add-librarian-form">
            <div class="modal-body">
                <p>Fill in the details for the new librarian account. A temporary password will be set.</p>
                <br>
                <div class="input-row">
                    <label for="lib-name">Full Name</label>
                    <input type="text" id="lib-name" name="name" required class="info-input">
                </div>
                <div class="form-grid-double">
                    <div class="input-row">
                        <label for="lib-username">Username</label>
                        <input type="text" id="lib-username" name="username" required class="info-input">
                    </div>
                    <div class="input-row">
                        <label for="lib-physical-id">Physical ID (Staff ID)</label>
                        <input type="text" id="lib-physical-id" name="physical_id" required class="info-input">
                    </div>
                </div>
                <div class="input-row">
                    <label for="lib-email">Email</label>
                    <input type="email" id="lib-email" name="email" required class="info-input">
                </div>
                <div class="input-row">
                    <label for="lib-contact">Contact Number (Optional)</label>
                    <input type="tel" id="lib-contact" name="contactNumber" class="info-input">
                </div>
                <div class="input-row">
                    <label for="lib-password">Temporary Password</label>
                    <input type="password" id="lib-password" name="password" required class="info-input">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-close-btn" data-target="#add-librarian-modal">Cancel</button>
                <button type="submit" class="action-btn">Create Account</button>
            </div>
        </form>
    </div>
</div>

<div id="manage-announcements-modal" class="modal-overlay">
    <div class="modal-content manage-announcements-content">
        <div class="modal-header">
            <h2>Manage All Announcements</h2>
        </div>
        <div class="modal-body">
            <p>This modal would contain a table of all announcements for editing or deleting.</p>
            <div id="all-announcements-list">
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-close-btn" data-target="#manage-announcements-modal">Close</button>
        </div>
    </div>
</div>