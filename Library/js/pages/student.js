/**
 * student.js
 * Handles SPA navigation and logic for the Student portal.
 */

// Import shared logic
import { initCataloguePage, openBookModal, closeBookModal, debounce } from '../shared/catalogue.js';


document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. SPA NAVIGATION LOGIC ---
    // ... (SPA Navigation logic remains the same) ...
    const sidebar = document.querySelector(".sidebar-nav");
    const mainContent = document.querySelector(".main-content");
    const navbar = document.querySelector(".navbar-links");

    if (sidebar && mainContent) {
        // (SPA Navigation logic remains the same)
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
            const navItem = e.target.closest(".nav-item, .nav-link, .nav-icon-button");
            if (!navItem) return;
            const href = navItem.getAttribute("href");
            if (!href || href === "#") return;
            e.preventDefault(); 
            const sidebarLink = sidebar.querySelector(`.nav-item[href="${href}"]`);
            if (!sidebarLink) return;
            const targetId = sidebarLink.dataset.target;
            if (!targetId) return;
            sidebar.querySelectorAll(".nav-item").forEach(item => {
                item.classList.remove("active");
            });
            if(sidebarLink) sidebarLink.classList.add("active");
            if (navbar) {
                navbar.querySelectorAll(".nav-link").forEach(item => {
                    item.classList.remove("active");
                });
                const navLink = navbar.querySelector(`.nav-link[href="${href}"]`);
                if(navLink) {
                    navLink.classList.add("active");
                }
            }
            const settingsIcon = document.querySelector('.nav-icon-button[href="#settings"]');
            if (settingsIcon) {
                settingsIcon.addEventListener('click', handleNavClick);
            }
            switchPanel(targetId);
            window.location.hash = href;
        };
        sidebar.addEventListener("click", handleNavClick);
        if (navbar) {
            navbar.addEventListener("click", handleNavClick);
        }
        const settingsIcon = document.querySelector('.nav-icon-button[href="#settings"]');
        if (settingsIcon) {
            settingsIcon.addEventListener('click', handleNavClick);
        }
        const currentHash = window.location.hash || "#dashboard";
        let targetPanelId = "dashboard-content";
        const activeLink = sidebar.querySelector(`.nav-item[href="${currentHash}"]`);
        if (activeLink) {
            targetPanelId = activeLink.dataset.target;
            sidebar.querySelectorAll(".nav-item").forEach(item => item.classList.remove("active"));
            activeLink.classList.add("active");
            if (navbar) {
                const activeNavLink = navbar.querySelector(`.nav-link[href="${currentHash}"]`);
                if (activeNavLink) {
                    navbar.querySelectorAll(".nav-link").forEach(item => item.classList.remove("active"));
                    activeNavLink.classList.add("active");
                }
            }
        }
        switchPanel(targetPanelId);
    } else {
        console.error("Sidebar or Main Content area not found.");
    }

    // --- (NEW) PROFILE BUTTON CLICK LOGIC ---
    const profileButton = document.getElementById("profile-button");
    // Find the "Settings" link in the sidebar
    const settingsLink = document.querySelector('.sidebar-nav .nav-item[href="#settings"]');

    if (profileButton && settingsLink) {
        profileButton.addEventListener("click", (e) => {
            e.preventDefault();
            // Programmatically click the hidden "Settings" link
            // This reuses all the existing logic in handleNavClick
            settingsLink.click();
        });
    }

    // --- 2. MODAL LOGIC (FIXED) ---
    const announcementsModal = document.getElementById('announcements-modal');
    const bookModal = document.getElementById('book-modal');
    const historyModal = document.getElementById('history-modal');

    // --- NEW: "See All" Modal Elements ---
    const seeAllModal = document.getElementById("see-all-modal");
    const seeAllModalTitle = document.getElementById("see-all-modal-title");
    const seeAllModalBody = document.getElementById("see-all-modal-body");

    // Announcements Modal
    // ... (existing announcements modal logic) ...
    const openAnnouncementsLink = document.getElementById('open-announcements-modal');
    const closeAnnouncementsBtn = document.getElementById('close-announcements-btn');

    if (openAnnouncementsLink && announcementsModal && closeAnnouncementsBtn) {
        openAnnouncementsLink.addEventListener('click', (e) => {
            e.preventDefault();
            announcementsModal.classList.add('active');
        });
        closeAnnouncementsBtn.addEventListener('click', () => {
            announcementsModal.classList.remove('active');
        });
        // Close on overlay click
        announcementsModal.addEventListener('click', (e) => {
            if (e.target === announcementsModal) {
                announcementsModal.classList.remove('active');
            }
        });
    }

    // Book Modal (FIXED LOGIC)
    const searchPanel = document.getElementById('search-books-content');
    if (searchPanel && bookModal) {
        // Listen for clicks to OPEN the modal
        searchPanel.addEventListener('click', async (e) => {
            // Book Modal
            const bookButton = e.target.closest('.open-book-modal-btn');
            if (bookButton) {
                e.preventDefault();
                const bookId = bookButton.dataset.bookId;
                if (bookId) {
                    await openStudentBookModal(bookId, bookModal);
                }
            }

            // --- NEW: "See All" Modal Trigger ---
            const seeAllButton = e.target.closest(".open-see-all-modal-btn");
            if (seeAllButton) {
                e.preventDefault();
                const genre = seeAllButton.dataset.genre;
                if (!genre || !seeAllModal) return;

                // 1. Open the modal and set title
                seeAllModalTitle.innerText = genre;
                seeAllModalBody.innerHTML = '<p style="padding: 30px; text-align: center;">Loading...</p>';
                seeAllModal.classList.add("active");

                // 2. Fetch the content
                try {
                    const response = await fetch(`../php/api/catalogue.php?action=getGenreShelf&genre=${encodeURIComponent(genre)}`);
                    if (!response.ok) throw new Error("Failed to load shelf.");
                    const html = await response.text();
                    seeAllModalBody.innerHTML = html;
                } catch (error) {
                    seeAllModalBody.innerHTML = `<p style="padding: 30px; text-align: center; color: red;">${error.message}</p>`;
                }
            }
        });

        // Listen for clicks to CLOSE the modal (using delegation)
        bookModal.addEventListener('click', (e) => {
            // Close if clicking overlay OR button with the class .modal-close-btn
            if (e.target === bookModal || e.target.closest('.modal-close-btn')) {
                // Use the shared modal function
                closeBookModal(bookModal);
            }
        });
    }

    // --- NEW: Event for closing the "See All" modal ---
    if (seeAllModal) {
        seeAllModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("modal-overlay") || e.target.closest(".modal-close-btn")) {
                seeAllModal.classList.remove("active");
                // Clear the body after it closes
                setTimeout(() => {
                    seeAllModalBody.innerHTML = '';
                    seeAllModalTitle.innerText = 'All Books';
                }, 300);
            }
        });
    }

    // History Modal (FIXED LOGIC)
    // ... (This logic is unique to student.js, so it stays) ...
    const historyPanel = document.getElementById('history-content');
    if (historyPanel && historyModal) {
        // Listen for clicks to OPEN the modal
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

        // Listen for clicks to CLOSE the modal (using delegation)
        historyModal.addEventListener('click', (e) => {
            // Close if clicking overlay OR button with the class .close-history-modal-btn
            if (e.target === historyModal || e.target.closest('.close-history-modal-btn')) {
                historyModal.classList.remove('active');
            }
        });
    }


    // --- 3. SETTINGS PANEL LOGIC ---
    // ... (Settings logic remains the same) ...
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
    
    // --- 4. LOGOUT LOGIC ---
    // ... (Logout logic remains the same) ...
    const logoutButton = document.getElementById("logout-button");
    const logoutLink = document.getElementById("logout-link");
    const handleLogout = async (e) => {
        e.preventDefault();
        if (!confirm("Are you sure you want to log out?")) return;
        try {
            const response = await fetch("../php/api/auth.php?action=logout", {
                method: "POST"
            });
            const result = await response.json();
            if(result.success) {
                window.location.href = "login.php";
            } else {
                alert("Logout failed: " + result.message);
            }
        } catch (err) {
            alert("An error occurred during logout.");
        }
    };
    if(logoutButton) logoutButton.addEventListener("click", handleLogout);
    if(logoutLink) logoutLink.addEventListener("click", handleLogout);

    // --- 5. NEW CATALOGUE PAGE LOGIC (REPLACED) ---
    // Initialize the shared catalogue logic
    initCataloguePage({
        searchInputId: "student-search-input",
        gridViewId: "catalogue-grid-view",
        tableViewId: "catalogue-table-view",
        tableBodyId: "student-search-table-body",
        filterBtnId: "filter-btn",
        filterDropdownId: "filter-dropdown"
    });
});


// All of the duplicated catalogue logic is gone.
// We just need to keep the student-specific modal functions.

/**
 * Fetches book details for the student modal
 */
async function openStudentBookModal(bookId, bookModal) {
    // This function customizes the HTML *after* it's fetched.
    const customizeHTML = (html) => {
        // Create a temporary div to parse the HTML string
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;

        // Find the "Sign In" button and change it to "Place Hold"
        const signInBtn = tempDiv.querySelector('.sign-in-btn');
        if (signInBtn) {
            signInBtn.textContent = "Place Hold";
            signInBtn.classList.remove("sign-in-btn");
            signInBtn.classList.add("place-hold-btn");
            signInBtn.href = `#hold-${bookId}`; // Make it a hold link
        }
        return tempDiv.innerHTML;
    };

    // Call the shared openBookModal function, passing our customization function
    await openBookModal(bookId, bookModal, customizeHTML);
}

/**
 * Fetches history details for the student modal
 * (This is unique to student.js and remains unchanged)
 */
async function openHistoryModal(transactionId, historyModal) {
    const historyModalContent = historyModal.querySelector(".modal-content");
    
    try {
        historyModalContent.innerHTML = '<p style="padding: 30px; text-align: center;">Loading...</p>';
        historyModal.classList.add("active");

        // Call the new student API endpoint
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