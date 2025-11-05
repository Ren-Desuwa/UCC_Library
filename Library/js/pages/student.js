/**
 * student.js
 * Handles SPA navigation and logic for the Student portal.
 */
document.addEventListener('DOMContentLoaded', () => {

    // --- 1. SPA NAVIGATION LOGIC (FIXED) ---
    const mainNav = document.querySelector(".portal-nav");
    const mobileNav = document.querySelector(".mobile-nav");
    const mainContent = document.querySelector(".main-content");

    if (mainNav && mobileNav && mainContent) {

        const switchPanel = (targetId) => {
            mainContent.querySelectorAll(".content-panel").forEach(panel => {
                panel.classList.remove("active");
            });
            const activePanel = document.getElementById(targetId);
            if (activePanel) {
                activePanel.classList.add("active");
            } else {
                console.warn(`Content panel with ID "${targetId}" not found.`);
            }
        };

        const handleNavClick = (e) => {
            const navItem = e.target.closest(".nav-item");
            if (!navItem) return;

            e.preventDefault();
            const href = navItem.getAttribute("href");
            if (!href || href === "#") return;

            const targetId = navItem.dataset.target;
            if (!targetId) return;

            document.querySelectorAll(`.nav-item[href="${href}"]`).forEach(link => {
                const parentNav = link.closest('.portal-nav, .mobile-nav');
                if (parentNav) {
                    parentNav.querySelectorAll('.nav-item').forEach(item => {
                        item.classList.remove("active");
                    });
                }
                link.classList.add("active");
            });

            switchPanel(targetId);
            window.location.hash = href;
        };

        mainNav.addEventListener("click", handleNavClick);
        mobileNav.addEventListener("click", handleNavClick);

        const currentHash = window.location.hash || "#dashboard";
        let targetPanelId = "dashboard-content";

        const activeLink = mainNav.querySelector(`.nav-item[href="${currentHash}"]`);

        if (activeLink) {
            targetPanelId = activeLink.dataset.target;

            document.querySelectorAll(`.nav-item[href="${currentHash}"]`).forEach(link => {
                const parentNav = link.closest('.portal-nav, .mobile-nav');
                if (parentNav) {
                    parentNav.querySelectorAll('.nav-item').forEach(item => {
                        item.classList.remove("active");
                    });
                }
                link.classList.add("active");
            });
        }

        switchPanel(targetPanelId);

    } else {
        console.error("Navigation or Main Content area not found.");
    }

    // --- 2. MODAL LOGIC (UPDATED) ---
    const announcementsModal = document.getElementById('announcements-modal');
    const bookModal = document.getElementById('book-modal');
    const historyModal = document.getElementById('history-modal');
    const receiptModal = document.getElementById('borrow-receipt-modal');

    // Announcements Modal
    const openAnnouncementsLink = document.getElementById('open-announcements-modal');
    if (openAnnouncementsLink && announcementsModal) {
        openAnnouncementsLink.addEventListener('click', (e) => {
            e.preventDefault();
            announcementsModal.classList.add('active');
        });
    }

    // Book Modal
    const searchPanel = document.getElementById('search-books-content');
    if (searchPanel && bookModal) {
        searchPanel.addEventListener('click', async (e) => {
            const bookButton = e.target.closest('.open-book-modal-btn');
            if (bookButton) {
                e.preventDefault();
                const bookId = bookButton.dataset.bookId;
                if (bookId) {
                    await openStudentBookModal(bookId, bookModal);
                }
            }
        });
        // Listen for "Place Hold" click
        bookModal.addEventListener('click', handleHoldClick);
    }

    // History Modal
    const historyPanel = document.getElementById('history-content');
    if (historyPanel && historyModal) {
        historyPanel.addEventListener('click', async (e) => {
            const historyButton = e.target.closest('.open-history-modal-btn');
            if (historyButton) {
                e.preventDefault();
                const transactionId = historyButton.dataset.transactionId;
                if (transactionId) {
                    await openHistoryModal(transactionId, historyModal);
                }
            }
        });
    }

    // Receipt Modal
    if (receiptModal) {
        const printBtn = document.getElementById('print-receipt-btn');
        if (printBtn) { // Check if print button exists
            printBtn.addEventListener('click', printReceipt);
        }
    }


    // --- 3. SETTINGS PANEL LOGIC (Unchanged) ---
    const editBtn = document.getElementById('edit-account-btn');
    const saveBtn = document.getElementById('save-account-btn');
    const infoInputs = document.querySelectorAll('.account-info-card .info-input');
    const showPasswordCheckbox = document.getElementById('show-password');
    const newPasswordField = document.getElementById('setting-password');
    const confirmPasswordField = document.getElementById('setting-confirm');
    if (editBtn && saveBtn && infoInputs.length > 0) {
        const toggleInputs = (enabled) => {
            infoInputs.forEach(input => {
                input.readOnly = !enabled;
            });
            saveBtn.disabled = !enabled;
            saveBtn.style.display = enabled ? 'inline-block' : 'none';
            editBtn.style.display = enabled ? 'none' : 'inline-block';
        };
        toggleInputs(false);
        editBtn.addEventListener('click', () => {
            toggleInputs(true);
            infoInputs[0].focus();
        });
        saveBtn.addEventListener('click', () => {
            alert('Account information updated!');
            toggleInputs(false);
        });
    }
    if (showPasswordCheckbox && newPasswordField && confirmPasswordField) {
        showPasswordCheckbox.addEventListener('change', () => {
            const type = showPasswordCheckbox.checked ? 'text' : 'password';
            newPasswordField.type = type;
            confirmPasswordField.type = type;
        });
    }

    // --- 4. LOGOUT LOGIC (Unchanged) ---
    const logoutLink = document.getElementById("logout-link");
    const handleLogout = async (e) => {
        e.preventDefault();
        if (!confirm("Are you sure you want to log out?")) return;
        try {
            const response = await fetch("../php/api/auth.php?action=logout", {
                method: "POST"
            });
            const result = await response.json();
            if (result.success) {
                window.location.href = "login.php";
            } else {
                alert("Logout failed: " + result.message);
            }
        } catch (err) {
            alert("An error occurred during logout.");
        }
    };
    if (logoutLink) logoutLink.addEventListener("click", handleLogout);

    // --- 5. NEW CATALOGUE PAGE LOGIC ---
    // Replaced old function with new, simpler search logic
    initNewCataloguePage();
});


/**
 * === NEW: Initializes new simple catalogue page ===
 */
function initNewCataloguePage() {
    // Get all the new elements
    const searchInput = document.getElementById("student-search-input");
    const sortDropdown = document.getElementById("student-sort-dropdown");
    const gridViewBtn = document.getElementById("student-grid-view-btn");
    const listViewBtn = document.getElementById("student-list-view-btn");
    const gridViewContainer = document.getElementById("student-catalogue-grid-view");
    const listViewContainer = document.getElementById("student-catalogue-list-view");
    const gridBody = document.getElementById("student-grid-body");
    const listBody = document.getElementById("student-list-body");

    // Exit if we're not on the catalogue page
    if (!searchInput || !sortDropdown || !gridViewBtn || !listViewBtn) {
        return;
    }

    // --- View Toggle Logic ---
    gridViewBtn.addEventListener("click", () => {
        gridViewContainer.classList.add("active");
        listViewContainer.classList.remove("active");
        gridViewBtn.classList.add("active");
        listViewBtn.classList.remove("active");
    });
    listViewBtn.addEventListener("click", () => {
        listViewContainer.classList.add("active");
        gridViewContainer.classList.remove("active");
        listViewBtn.classList.add("active");
        gridViewBtn.classList.remove("active");
    });

    // --- Search & Sort Logic ---
    const debouncedSearch = debounce(async (searchTerm, sortBy) => {
        try {
            listBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Searching...</td></tr>';
            gridBody.innerHTML = '<p class="no-books-message">Searching...</p>';

            // --- FIX: Use GET request to match catalogue.php API ---
            const baseUrl = `../php/api/catalogue.php?action=searchBooks&term=${encodeURIComponent(searchTerm)}&sort=${encodeURIComponent(sortBy)}`;

            // 1. Fetch Grid HTML
            const gridResponse = await fetch(`${baseUrl}&view=grid`);
            const htmlGrid = await gridResponse.text();
            if (htmlGrid.trim() === "") {
                gridBody.innerHTML = '<p class="no-books-message">No books found.</p>';
            } else {
                gridBody.innerHTML = htmlGrid;
            }

            // 2. Fetch List HTML
            const listResponse = await fetch(`${baseUrl}&view=list`);
            const htmlList = await listResponse.text();
            if (htmlList.trim() === "") {
                listBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No books found.</td></tr>';
            } else {
                listBody.innerHTML = htmlList;
            }

        } catch (error) {
            console.error("Search error:", error);
            listBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error loading results.</td></tr>';
            gridBody.innerHTML = '<p class="no-books-message" style="color: red;">Error loading results.</p>';
        }
    }, 300);

    // Listeners
    searchInput.addEventListener("keyup", () => {
        debouncedSearch(searchInput.value, sortDropdown.value);
    });
    sortDropdown.addEventListener("change", () => {
        debouncedSearch(searchInput.value, sortDropdown.value);
    });
}

/**
 * Debounce function
 */
function debounce(func, delay) {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

/**
 * --- UPDATED: Fetches book details and adds a "Place Hold Now" button ---
 */
async function openStudentBookModal(bookId, bookModal) {
    const bookModalContent = bookModal.querySelector(".modal-content");

    try {
        bookModalContent.innerHTML = '<p style="padding: 30px; text-align: center;">Loading...</p>';
        bookModal.classList.add("active");

        const response = await fetch(`../php/api/catalogue.php?action=getBookDetails&id=${bookId}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const html = await response.text();
        bookModalContent.innerHTML = html;

        // --- BUTTON FIX ---
        const footer = bookModalContent.querySelector('.modal-footer');
        const signInLink = footer.querySelector('a.primary-btn');

        if (signInLink) {
            const placeHoldButton = document.createElement('button');
            placeHoldButton.id = 'place-hold-btn'; // Use this ID to listen for clicks
            placeHoldButton.className = 'action-btn primary-btn';
            placeHoldButton.textContent = 'Place Hold Now'; // Set new text
            placeHoldButton.dataset.bookId = bookId;

            signInLink.replaceWith(placeHoldButton);
        }

    } catch (error) {
        console.error("Error fetching book details:", error);
        bookModalContent.innerHTML = `
            <div class="modal-header"><h2>Error</h2></div>
            <div class="modal-body"><p>Could not load book details. Please try again later.</p></div>
            <div class="modal-footer"><button type="button" class="modal-close-btn">Close</button></div>`;
    }
}

/**
 * --- UPDATED: Handles the "Place Hold Now" button click ---
 */
async function handleHoldClick(e) {
    const holdButton = e.target.closest('#place-hold-btn');
    if (!holdButton) return;

    const bookId = holdButton.dataset.bookId;
    if (!bookId) return;

    if (!confirm("Are you sure you want to place a hold for this book?\nA librarian will review your request.")) return;

    holdButton.textContent = "Placing Hold...";
    holdButton.disabled = true;

    try {
        const formData = new FormData();
        formData.append('book_id', bookId);

        // Call the new "requestHold" action
        const response = await fetch('../php/api/student.php?action=requestHold', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Close the book modal
            document.getElementById('book-modal').classList.remove('active');
            // Show a simple success message
            alert("Hold placed successfully! A librarian will process your request.");
            // You might want to refresh the dashboard or catalogue here
        } else {
            alert(`Error: ${result.message}`);
        }

    } catch (error) {
        console.error("Hold error:", error);
        alert("An unexpected error occurred. Please try again.");
    } finally {
        holdButton.textContent = "Place Hold Now";
        holdButton.disabled = false;
    }
}

/**
 * --- REMOVED Receipt Modal Functions ---
 */
function printReceipt() {
    // This function is still here for the old modal, but won't be called by "Place Hold"
    const content = document.getElementById('receipt-content-to-print').innerHTML;
    const printWindow = window.open('', '', 'height=500,width=500');
    printWindow.document.write('<html><head><title>Transaction Receipt</title>');
    printWindow.document.write('<style>body{font-family: Arial, sans-serif; line-height: 1.6;} p{margin-bottom: 10px;} strong{display: inline-block; min-width: 120px;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}


/**
 * Fetches history details for the student modal
 */
async function openHistoryModal(transactionId, historyModal) {
    const historyModalContent = historyModal.querySelector(".modal-content");

    try {
        historyModalContent.innerHTML = '<p style="padding: 30px; text-align: center;">Loading...</p>';
        historyModal.classList.add("active");

        const response = await fetch(`../php/api/student.php?action=getHistoryDetails&id=${transactionId}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const html = await response.text();
        historyModalContent.innerHTML = html;

    } catch (error) {
        console.error("Error fetching history details:", error);
        historyModalContent.innerHTML = `
            <div class="modal-header"><h2>Error</h2></div>
            <div class="modal-body"><p>Could not load history details. Please try again later.</p></div>
            <div class="modal-footer"><button type="button" class="modal-close-btn close-history-modal-btn">Close</button></div>`;
    }
}