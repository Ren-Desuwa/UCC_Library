<div id="announcements-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>View All Announcements</h2>
        </div>
        
        <div class="modal-body">
            <div class="updates-list modal-updates-list">
                <div class="update-item"><div class="tag tag-urgent">[URGENT]</div><div class="update-details"><p class="update-title">Main Floor Collection Area Closed</p><p class="update-description">Area A-C is temporarily closed for maintenance.</p></div></div>
                <div class="update-item"><div class="tag tag-event">[EVENT]</div><div class="update-details"><p class="update-title">End-Of-Semester Book Return Drive</p><p class="update-description">Drop-off box in the 3rd Floor Hall this week. Avoid Fines!</p></div></div>
                <div class="update-item"><div class="tag tag-notice">[NOTICE]</div><div class="update-details"><p class="update-title">New Study Cubicles Available</p><p class="update-description">New individual study cubicles open on the third floor.</p></div></div>
                <div class="update-item"><div class="tag tag-urgent">[URGENT]</div><div class="update-details"><p class="update-title">Water Leak Repair</p><p class="update-description">Lower level closed until further notice.</p></div></div>
            </div>
        </div>

        <div class="modal-footer">
            <button id="close-announcements-btn" class="modal-close-btn">Close</button>
        </div>
    </div>
</div>

<div id="book-modal" class="modal-overlay">
    <div class="modal-content book-modal-content">
        <div class="book-details-grid">
            <div class="book-cover-area">
                <img src="../assets/covers/CoverBookTemp.png" alt="Book Cover" class="book-detail-cover">
            </div>
            <div class="book-info-area">
                <h2 class="book-detail-title">Loading...</h2>
                <dl class="book-meta-list">
                    <dt>Author</dt><dd>...</dd>
                    <dt>Shelf Location</dt><dd>...</dd>
                    <dt>ISBN</dt><dd>...</dd>
                    <dt>Publisher</dt><dd>...</dd>
                    <dt>Description</dt><dd>...</dd>
                </dl>
            </div>
        </div>
        <div class="modal-footer book-modal-footer">
            <div class="book-status-info">
            </div>
            <div class="book-actions">
                <button class="action-btn place-hold-btn">Place Hold</button>
                <button id="close-book-modal-btn" class="modal-close-btn close-book-modal-btn">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="history-modal" class="modal-overlay">
    <div class="modal-content history-modal-content">
        <div class="history-details-grid">
            <div class="book-cover-area">
                <img src="../assets/covers/CoverBookTemp.png" alt="Book Cover" class="book-detail-cover">
            </div>
            <div class="book-info-area">
                <h2 class="book-detail-title">Loading...</h2>
                <dl class="book-meta-list">
                    <dt>Author</dt><dd>...</dd>
                    <dt>Shelf Location</dt><dd>...</dd>
                    <dt>ISBN</dt><dd>...</dd>
                </dl>
            </div>
            <div class="receipt-area">
                <h3 class="receipt-title">Transaction Receipt</h3>
                <dl class="receipt-list">
                    <dt>Borrowed Date:</dt><dd>...</dd>
                    <dt>Expected Due:</dt><dd>...</dd>
                    <dt>Actual Return:</dt><dd>...</dd>
                    <dt>Status:</dt><dd class="receipt-status-text">...</dd>
                </dl>
            </div>
            <div class="notes-area">
                <h3 class="notes-title">Librarian Notes</h3>
                <div class="notes-box">
                    <p>...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer book-modal-footer history-modal-footer">
            <div class="book-actions">
                <button id="close-history-modal-btn" class="modal-close-btn close-history-modal-btn">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="see-all-modal" class="modal-overlay">
    <div class="modal-content see-all-modal-content">
        <div class="modal-header">
            <h2 id="see-all-modal-title">All Books</h2>
        </div>
        
        <div class="modal-body" id="see-all-modal-body">
            <p style="padding: 30px; text-align: center;">Loading...</p>
        </div>

        <div class="modal-footer">
            <button class="modal-close-btn" data-target="#see-all-modal">Close</button>
        </div>
    </div>
</div>