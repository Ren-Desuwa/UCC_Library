<div id="librarian-circulation-content" class="content-panel">
    <header class="main-header">
        <h1>Circulation</h1>
        <p>Process book check-outs and returns.</p>
    </header>
    
    <div class="borrow-return-grid">
        <div class="card-panel">
            <h2 class="card-title-new">Check-Out Book</h2>
            <form id="borrow-form">
                <div class="search-input-group-alt">
                    <input type="text" id="borrow-user-search" placeholder="Search Student ID or Username...">
                    <span class="material-icons-round">search</span>
                </div>
                
                <div class="borrow-info-group">
                    <label class="info-label-alt">Student Name</label>
                    <div class="info-value-alt" id="borrow-user-name">...</div>
                </div>
                
                <div class="search-input-group-alt">
                    <input type="text" id="borrow-book-search" placeholder="Scan or Type Book Copy ID...">
                    <span class="material-icons-round">qr_code_scanner</span>
                </div>

                <div class="borrow-info-group">
                    <label class="info-label-alt">Book Title</label>
                    <div class="info-value-alt" id="borrow-book-title">...</div>
                </div>
                
                <button type="submit" class="finalize-checkout-btn">Finalize Check-Out</button>
            </form>
        </div>
        
        <div class="card-panel">
            <h2 class="card-title-new">Return Book</h2>
            <form id="return-form">
                <div class="search-input-group-alt">
                    <input type="text" id="return-book-search" placeholder="Scan or Type Book Copy ID...">
                    <span class="material-icons-round">qr_code_scanner</span>
                </div>
                
                <div id="return-details" style="display: none;">
                    <h3 class="return-book-title" id="return-book-title">...</h3>
                    <div class="return-details-grid">
                        <div>
                            <p class="return-detail-label">Borrower: <span class="return-detail-value" id="return-user-name">...</span></p>
                            <p class="return-detail-label">Due Date: <span class="return-detail-value" id="return-due-date">...</span></p>
                            <p class="return-detail-label">Status: <span class="return-detail-value" id="return-status">...</span></p>
                        </div>
                        <img src="../assets/covers/CoverBookTemp.png" alt="Cover" class="return-book-cover-small" id="return-book-cover">
                    </div>

                    <div class="return-condition-group">
                        <label class="info-label-alt">Condition</label>
                        <select class="return-condition-select" id="return-condition">
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="process-return-btn">Process Return</button>
            </form>
        </div>
    </div>
</div>