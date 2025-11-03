<?php
// This file holds all modals for the Librarian portal.
?>

<div id="book-form-modal" class="modal-overlay">
    <div class="modal-content book-form-content">
        
        <div class="modal-header">
            <h2 id="book-modal-title">Book Details</h2> 
        </div>
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
            <div class.="book-copies-manager" id="book-copies-manager">
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

                <form class.="add-copy-form" id="add-copy-form">
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