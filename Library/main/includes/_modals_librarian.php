<?php
// This file holds all modals for the Librarian portal.
?>

<div id="book-form-modal" class="modal-overlay">
    <div class="modal-content book-form-content">
        <div class="modal-header">
            <h2 id="book-modal-title">Add New Book</h2>
        </div>
        
        <form id="book-form" class="book-form-body">
            <input type="hidden" id="book-id" name="book_id">
            
            <div class="book-form-grid">
                
                <div class="book-form-image-area">
                    <label class="info-label">Book Cover</label>
                    <img src="../assets/covers/CoverBookTemp.png" alt="Book Cover Preview" id="book-cover-preview" style="width: 100%; max-width: 250px; height: auto; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 10px;">
                    <input type="file" id="book-cover-upload" name="cover_image" accept="image/*">
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
        
        <div class="modal-footer">
            <button type="button" class="modal-close-btn" data-target="#book-form-modal">Cancel</button>
            <button type="submit" class="action-btn" form="book-form">Save Book</button>
        </div>
    </div>
</div>