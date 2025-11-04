<?php
// This file holds all modals for the Librarian portal.
?>

<div id="book-form-modal" class="modal-overlay">
    <div class="modal-content book-form-content">
        
        <div class="modal-tabs">
            <a href="#book-details-pane" class="modal-tab-item active" data-pane="book-details-pane">1. Book Details</a>
            <a href="#book-copies-pane" class="modal-tab-item" data-pane="book-copies-pane">2. Manage Copies</a>
        </div>
        <div id="book-details-pane" class="modal-tab-pane active">
            <form id="book-form" class="book-form-body">
                <input type="hidden" id="book-id" name="book_id">
                
                <div class="book-form-grid">
                    
                    <div class="book-form-image-area">
                        <label class="info-label">Book Cover</label>
                        <div class="image-preview-grid">
                            <div class="image-preview-box" id="book-cover-preview-current">
                                <span class="preview-label">Current</span>
                                <img src="../assets/covers/CoverBookTemp.png" alt="Current Cover" id="book-cover-preview">
                            </div>
                            <div class="image-preview-box" id="book-cover-preview-new" style="display: none;">
                                <span class="preview-label">New</span>
                                <img src="#" alt="New Cover Preview" id="book-cover-new-preview">
                            </div>
                        </div>
                        <input type="file" id="book-cover-upload" name="cover_image" accept="image/*" style="display: none;">
                        <div class="image-upload-actions">
                            <button type="button" class="action-btn" id="trigger-upload-btn">Change Image</button>
                            <button type="button" class="modal-close-btn" id="cancel-upload-btn" style="display: none;">Cancel</button>
                        </div>
                    </div>
                    
                    <div class="book-form-details-area">
                        <div class="input-row">
                            <label for="book-title">Title</label>
                            <input type="text" id="book-title" name="title" required class="info-input" placeholder="Enter book title">
                        </div>
                        <div class="input-row">
                            <label for="book-authors">Author(s)</label>
                            <input type="text" id="book-authors" name="authors" class="info-input" placeholder="e.g., George Orwell, J.R.R. Tolkien">
                        </div>
                        <div class="input-row">
                            <label for="book-genres">Genre(s)</label>
                            <input type="text" id="book-genres" name="genres" class="info-input" placeholder="e.g., Dystopian, Fantasy">
                        </div>
                        <div class="input-row">
                            <label for="book-isbn">ISBN</label>
                            <input type="text" id="book-isbn" name="isbn" required class="info-input" placeholder="e.g., 978-1-2345-6789-0">
                        </div>
                        <div class="input-row">
                            <label for="book-publisher">Publisher</label>
                            <input type="text" id="book-publisher" name="publisher" class="info-input" placeholder="Enter publisher name">
                        </div>
                        <div class="input-row">
                            <label for="book-year">Year Published</label>
                            <input type="number" id="book-year" name="year" class="info-input" placeholder="e.g., 1949" min="1000" max="2100">
                        </div>
                        <div class="input-row details-row">
                            <label for="book-desc">Description / Plot Summary</label>
                            <textarea id="book-desc" name="description" class="info-input" placeholder="Brief description of the book..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="book-copies-pane" class="modal-tab-pane">
            <div class="book-copies-manager" id="book-copies-manager">
                <h3 class="copies-manager-title">Manage Copies</h3>
                
                <div class="copies-table-container">
                    <table class="copies-table" id="book-copies-table">
                        <thead>
                            <tr>
                                <th>Copy ID</th>
                                <th>Status</th>
                                <th>Condition</th>
                                <th>Shelf Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="book-copies-list">
                            </tbody>
                    </table>
                </div>

                <form class="add-copy-form" id="add-copy-form">
                    <h4 class="add-copy-title">Add New Copy</h4>
                    <div class="add-copy-inputs">
                        <select id="add-copy-condition" required>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                        <input type="text" id="add-copy-shelf" placeholder="Shelf Location (e.g., A-01)" required>
                        <button type="submit" class="action-btn add-copy-btn">
                            <span class="material-icons-round">add</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-close-btn" data-target="#book-form-modal">Cancel</button>
            <button type="submit" class="action-btn" form="book-form">Save Details</button>
        </div>
    </div>
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