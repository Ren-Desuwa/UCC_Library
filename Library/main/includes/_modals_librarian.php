<?php
// This file holds all modals for the Librarian portal.
?>

<div id="book-form-modal" class="modal-overlay">
    </div>


<div id="student-details-modal" class="modal-overlay">
    <div class="modal-content student-modal-content"> <div class="modal-header">
            <h2 id="student-modal-title">Student Details</h2>
        </div>
        
        <div class="modal-tabs">
            <a href="#student-profile-pane" class="modal-tab-item active" data-pane="student-profile-pane">Profile</a>
            <a href="#student-current-pane" class="modal-tab-item" data-pane="student-current-pane">Current Borrows</a>
            <a href="#student-history-pane" class="modal-tab-item" data-pane="student-history-pane">Borrowing History</a>
            <a href="#student-actions-pane" class="modal-tab-item" data-pane="student-actions-pane">Actions</a>
        </div>
        
        <div id="student-profile-pane" class="modal-tab-pane active">
            <div class="student-profile-grid">
                <div class="student-profile-info">
                    <h3 class="student-info-title">Account Details</h3>
                    <dl class="student-info-list">
                        <dt>Full Name:</dt>
                        <dd id="student-modal-name">...</dd>
                        
                        <dt>Username:</dt>
                        <dd id="student-modal-username">...</dd>
                        
                        <dt>Email:</dt>
                        <dd id="student-modal-email">...</dd>
                        
                        <dt>Contact:</dt>
                        <dd id="student-modal-contact">...</dd>
                        
                        <dt>Member Since:</dt>
                        <dd id="student-modal-joined">...</dd>
                    </dl>
                </div>
                
                <div class="student-profile-status">
                    <h3 class="student-info-title">Account Status</h3>
                    <div id="student-modal-status-tag" class="status-tag tag-available">...</div>
                    <p id="student-modal-status-desc">This student can borrow books.</p>
                    
                    <button class="action-btn deactivate-btn" id="student-modal-toggle-status-btn" data-account-id="" data-active-status="">
                        Deactivate Account
                    </button>
                </div>
            </div>
        </div>

        <div id="student-current-pane" class="modal-tab-pane">
            <div class="student-modal-table-container">
                <table class="student-history-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="student-current-table-body">
                        </tbody>
                </table>
            </div>
        </div>

        <div id="student-history-pane" class="modal-tab-pane">
            <div class="student-modal-table-container">
                <table class="student-history-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Date Returned</th>
                            <th>Fine</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="student-history-table-body">
                        </tbody>
                </table>
            </div>
        </div>

        <div id="student-actions-pane" class="modal-tab-pane">
            <div class="student-actions-grid">
                
                <div class="student-action-card">
                    <h4>Issue Manual Fine</h4>
                    <p>Add a fine to a specific transaction (e.g., for damage).</p>
                    <form id="student-issue-fine-form">
                        <div class="form-grid-double">
                            <div class="input-row">
                                <label for="fine-transaction-id">Transaction ID</label>
                                <input type="number" id="fine-transaction-id" name="transaction_id" class="info-input" placeholder="e.g., 123">
                            </div>
                            <div class="input-row">
                                <label for="fine-amount">Fine Amount ($)</label>
                                <input type="text" id="fine-amount" name="amount" class="info-input" placeholder="e.g., 5.00">
                            </div>
                        </div>
                        <button type="submit" class="action-btn deactivate-btn">Issue Fine</button>
                    </form>
                </div>

                <div class="student-action-card">
                    <h4>Send Notification (Future Feature)</h4>
                    <p>Send a manual notification to this user's account.</p>
                    <form id="student-notify-form">
                        <div class="input-row">
                            <label for="notify-message">Message</label>
                            <textarea id="notify-message" name="message" class="info-input" placeholder="Your message..." disabled></textarea>
                        </div>
                        <button type="submit" class="action-btn" disabled>Send Message</button>
                    </form>
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="modal-close-btn" data-target="#student-details-modal">Close</button>
        </div>
    </div>
</div>