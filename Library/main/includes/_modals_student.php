<div id="announcements-modal" class="modal-overlay">
    <div class="modal-content text-modal-content"> <div class="modal-header">
            <h2>View All Announcements</h2>
            <button class="modal-close-btn-icon"><span class="material-icons-round">close</span></button>
        </div>
        
        <div class="modal-body">
            <div class="updates-list modal-updates-list">
                <div class="update-item"><div class="tag tag-urgent">[URGENT]</div><div class="update-details"><p class="update-title">Main Floor Collection Area Closed</p><p class="update-description">Area A-C is temporarily closed for maintenance.</p></div></div>
                <div class="update-item"><div class="tag tag-event">[EVENT]</div><div class="update-details"><p class="update-title">End-Of-Semester Book Return Drive</p><p class="update-description">Drop-off box in the 3rd Floor Hall this week. Avoid Fines!</p></div></div>
                <div class="update-item"><div class="tag tag-notice">[NOTICE]</div><div class="update-details"><p class="update-title">New Study Cubicles Available</p><p class="update-description">New individual study cubicles open on the third floor.</p></div></div>
                <div class="update-item"><div class="tag tag-urgent">[URGENT]</div><div class="update-details"><p class="update-title">Water Leak Repair</p><p class="update-description">Lower level closed until further notice.</p></div></div>
            </div>
        </div>

        <div class="modal-footer" style="justify-content: flex-end;">
            <button id="close-announcements-btn" class="modal-close-btn">Close</button>
        </div>
    </div>
</div>

<div id="book-modal" class="modal-overlay">
    <div class="modal-content book-modal-content">
        </div>
</div>

<div id="history-modal" class="modal-overlay">
    <div class="modal-content history-modal-content">
        </div>
</div>

<div id="borrow-receipt-modal" class="modal-overlay">
    <div class="modal-content text-modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Borrowing Successful</h2>
            <button class="modal-close-btn-icon"><span class="material-icons-round">close</span></button>
        </div>
        <div class="modal-body" id="receipt-content-to-print">
            <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-color);">Transaction Receipt</h3>
            <div class="receipt-details">
                <p><strong>Book Title:</strong> <span id="receipt-book-title"></span></p>
                <p><strong>Borrowed By:</strong> <span id="receipt-user-name"></span></p>
                <p><strong>Transaction ID:</strong> <span id="receipt-trans-id"></span></p>
                <p><strong>Date Borrowed:</strong> <span id="receipt-date-borrowed"></span></p>
                <p><strong>Date Due:</strong> <span id="receipt-date-due" style="font-weight: 700; color: var(--primary);"></span></p>
            </div>
            <p style="text-align: center; margin-top: 20px; color: var(--secondary-text);">
                Thank you! Please return the book on or before the due date.
            </p>
        </div>
        <div class="modal-footer" style="justify-content: space-between;">
            <button id="print-receipt-btn" class="action-btn">
                <span class="material-icons-round">print</span>
                Print
            </button>
            <button class="modal-close-btn">Close</button>
        </div>
    </div>
</div>