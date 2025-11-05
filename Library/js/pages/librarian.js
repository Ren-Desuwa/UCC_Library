/**
 * admin.js
 * Handles SPA navigation and logic for the Admin portal.
 *
 * --- MERGED ---
 * This file NOW ALSO CONTAINS all logic from librarian.js
 * to provide full functionality for the Admin role.
 */

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar-nav");
    const mainContent = document.querySelector(".main-content");

    if (!sidebar || !mainContent) {
        console.error("Admin Sidebar or Main Content area not found.");
        return;
    }

    // ===========================================
    // --- ADMIN ELEMENT DECLARATIONS ---
    // ===========================================
    const addLibrarianBtn = document.getElementById("add-librarian-btn");
    const addLibrarianModal = document.getElementById("add-librarian-modal");
    const addLibrarianForm = document.getElementById("add-librarian-form");
    const accountsTableBody = document.getElementById("admin-accounts-table-body");
    const settingsForm = document.getElementById("settings-form");
    const logsConsole = document.getElementById("logs-console");
    const refreshLogsBtn = document.getElementById("refresh-logs-btn");
    const announcementForm = document.getElementById("announcement-form");
    const announcementsTableBody = document.getElementById("announcements-table-body");
    
    // ===========================================
    // --- LIBRARIAN ELEMENT DECLARATIONS ---
    // ===========================================
    const catalogTableBody = document.getElementById("catalog-table-body");
    const bookModal = document.getElementById("book-form-modal");
    const bookForm = document.getElementById("book-form");
    const openBookModalBtn = document.getElementById("add-new-book-btn");
    
    // --- Image Preview Elements ---
    const bookCoverUpload = document.getElementById("book-cover-upload");
    const triggerUploadBtn = document.getElementById("trigger-upload-btn");
    const cancelUploadBtn = document.getElementById("cancel-upload-btn");
    const previewGrid = document.querySelector(".image-preview-grid");
    const currentPreviewBox = document.getElementById("book-cover-preview-current");
    const newPreviewBox = document.getElementById("book-cover-preview-new");
    const newPreviewImg = document.getElementById("book-cover-new-preview");

    // --- Archive Page Elements ---
    const archiveTableBody = document.getElementById("archive-table-body");
    const archiveSearchInput = document.getElementById("archive-search-input");
    
    // --- Book Copies Manager Elements ---
    const copiesManagerSection = document.getElementById("book-copies-manager");
    const copiesListBody = document.getElementById("book-copies-list");
    const addCopyForm = document.getElementById("add-copy-form");

    // --- Book Modal Tab Elements ---
    const modalTabsContainer = bookModal ? bookModal.querySelector(".modal-tabs") : null;
    const modalTabPanes = bookModal ? bookModal.querySelectorAll(".modal-tab-pane") : [];
    const copiesTabButton = modalTabsContainer ? modalTabsContainer.querySelector('a[data-pane="book-copies-pane"]') : null;

    // --- User Management Elements ---
    const userSearchInput = document.getElementById("user-search-input"); // This is the search bar
    const userTableBody = document.getElementById("user-table-body"); // This is the table
    
    // --- Student Details Modal Elements ---
    const studentModal = document.getElementById("student-details-modal");
    const studentModalTabs = studentModal ? studentModal.querySelector(".modal-tabs") : null;
    const studentModalPanes = studentModal ? studentModal.querySelectorAll(".modal-tab-pane") : [];
    const studentModalTitle = document.getElementById("student-modal-title");
    
    // --- Circulation Elements ---
    const borrowForm = document.getElementById("borrow-form");
    const borrowUserSearchInput = document.getElementById("borrow-user-search"); 
    const userNameDiv = document.getElementById("borrow-user-name");
    const bookSearchInput = document.getElementById("borrow-book-search");
    const bookTitleDiv = document.getElementById("borrow-book-title");
    const returnForm = document.getElementById("return-form");
    const returnSearchInput = document.getElementById("return-book-search");
    const returnDetailsDiv = document.getElementById("return-details");
    let currentBorrowUser = null;
    let currentBorrowCopy = null;
    let currentReturnTransaction = null;


    // ===========================================
    // --- FUNCTION DEFINITIONS (MERGED) ---
    // ===========================================

    // --- Debounce (Shared) ---
    const debounce = (func, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    };

    // --- Admin Functions ---
    const loadAccounts = async () => { 
        if (!accountsTableBody) return;
        accountsTableBody.innerHTML = '<tr><td colspan="6">Loading accounts...</td></tr>';
        try {
            const response = await fetch("../php/api/admin.php?action=getAllAccounts");
            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            accountsTableBody.innerHTML = ""; // Clear
            result.data.forEach(acc => {
                const statusTag = acc.is_active == 1 
                    ? `<span class="status-tag tag-available">Active</span>`
                    : `<span class="status-tag tag-checkedout">Inactive</span>`;
                
                const actionBtn = acc.is_active == 1
                    ? `<button class="action-btn deactivate-btn" data-id="${acc.account_id}" data-active="0">Deactivate</button>`
                    : `<button class="action-btn activate-btn" data-id="${acc.account_id}" data-active="1">Activate</button>`;

                accountsTableBody.innerHTML += `
                    <tr>
                        <td><strong>${acc.username}</strong></td>
                        <td>${acc.name}</td>
                        <td>${acc.email}</td>
                        <td>${acc.role}</td>
                        <td>${statusTag}</td>
                        <td>${actionBtn}</td>
                    </tr>
                `;
            });
        } catch (error) {
            accountsTableBody.innerHTML = `<tr><td colspan="6" style="color: red;">${error.message}</td></tr>`;
        }
    };
    
    const loadSettings = async () => { 
        if (!settingsForm) return;
        try {
            const response = await fetch("../php/api/admin.php?action=getSettings");
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            
            document.getElementById("setting-borrow-duration").value = result.data.borrow_duration_days;
            document.getElementById("setting-max-books").value = result.data.max_books_per_user;
            document.getElementById("setting-fine-rate").value = result.data.overdue_fine_per_day;
            document.getElementById("setting-reservation-expiry").value = result.data.reservation_expiry_hours;
            
        } catch (error) {
            window.showPopup(`Error loading settings: ${error.message}`);
        }
    };
    
    const loadLogs = async () => { 
        if (!logsConsole) return;
        logsConsole.innerHTML = '<div class="log-line">Loading logs...</div>';
        try {
            const response = await fetch("../php/api/admin.php?action=getLogs");
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            
            logsConsole.innerHTML = ""; // Clear
            if (result.data.length === 0) {
                logsConsole.innerHTML = '<div class="log-line">No logs found.</div>';
                return;
            }

            result.data.forEach(log => {
                const user = log.username || 'System';
                const time = new Date(log.timestamp).toLocaleString();
                const severity = log.severity || 'Info';
                logsConsole.innerHTML += 
                    `<div class="log-line log-line-${severity}">` +
                    `[${time}] [${user}] [${severity.toUpperCase()}]: ${log.action} - ${log.details}` +
                    `</div>`;
            });
        } catch (error) {
            logsConsole.innerHTML = `<div class="log-line log-line-Error">${error.message}</div>`;
        }
    };
    
    const loadAnnouncements = async () => { 
        if (!announcementsTableBody) return;
        announcementsTableBody.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
        try {
            const response = await fetch("../php/api/admin.php?action=getAllAnnouncements");
            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            announcementsTableBody.innerHTML = ""; // Clear
            result.data.forEach(item => {
                const status = item.is_active == 1 ? "Active" : "Inactive";
                announcementsTableBody.innerHTML += `
                    <tr>
                        <td>${item.priority}</td>
                        <td>${item.title}</td>
                        <td>${item.message.substring(0, 50)}...</td>
                        <td>${new Date(item.date_posted).toLocaleDateString()}</td>
                        <td>${status}</td>
                        <td><button class="action-btn edit-action-btn">Edit</button></td>
                    </tr>
                `;
            });
        } catch (error) {
            announcementsTableBody.innerHTML = `<tr><td colspan="6" style="color: red;">${error.message}</td></tr>`;
        }
    };

    // --- Librarian Functions (Copied from librarian.js) ---
    const loadCatalog = async () => {
        if (!catalogTableBody) return;
        catalogTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading books...</td></tr>';
        try {
            const response = await fetch("../php/api/librarian.php?action=getBooks");
            if (!response.ok) throw new Error("Network response was not ok");
            const html = await response.text();
            catalogTableBody.innerHTML = html;
        } catch (error) {
            console.error("Failed to load catalog:", error);
            catalogTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error loading books.</td></tr>';
        }
    };

    const loadArchive = async (searchTerm = "") => {
        if (!archiveTableBody) return;
        archiveTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading archived books...</td></tr>';
        try {
            const query = new URLSearchParams({ action: 'getArchivedBooks', query: searchTerm }).toString();
            const response = await fetch(`../php/api/librarian.php?${query}`);
            if (!response.ok) throw new Error("Network response was not ok");
            const html = await response.text();
            archiveTableBody.innerHTML = html;
        } catch (error) {
            console.error("Failed to load archive:", error);
            archiveTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: red;">Error loading archived books.</td></tr>';
        }
    };
    
    const loadUsers = async (searchTerm = "") => {
        if (!userTableBody) return;
        userTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading users...</td></tr>';
        try {
            const query = new URLSearchParams({ action: 'searchUsers', query: searchTerm }).toString();
            const response = await fetch(`../php/api/librarian.php?${query}`);
            if (!response.ok) throw new Error("Network response was not ok");
            const html = await response.text();
            userTableBody.innerHTML = html;
        } catch (error) {
            console.error("Failed to load users:", error);
            userTableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">${error.message}</td></tr>`;
        }
    };
    
    const resetImagePreview = () => {
        if (bookCoverUpload) bookCoverUpload.value = null;
        if (newPreviewImg) newPreviewImg.src = "#";
        if (previewGrid) previewGrid.classList.remove("show-new");
        if (newPreviewBox) newPreviewBox.style.display = "none";
        if (cancelUploadBtn) cancelUploadBtn.style.display = "none";
        if (currentPreviewBox) currentPreviewBox.style.display = "block";
    };

    const loadBookCopies = async (bookId) => {
        if (!copiesListBody) return;
        copiesListBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading copies...</td></tr>';
        bookModal.dataset.currentBookId = bookId; 
        try {
            const response = await fetch(`../php/api/librarian.php?action=getBookCopies&book_id=${bookId}`);
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            copiesListBody.innerHTML = '';
            if (result.data.length === 0) {
                copiesListBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No copies found. Add one below.</td></tr>';
                return;
            }
            const allStatuses = ['Available', 'Borrowed', 'Overdue', 'Maintenance', 'Archived'];
            const allConditions = ['Good', 'Fair', 'Damaged'];
            result.data.forEach(copy => {
                const row = document.createElement('tr');
                row.dataset.copyId = copy.copy_id;
                let statusOptions = '';
                const isBorrowed = (copy.status === 'Borrowed' || copy.status === 'Overdue');
                allStatuses.forEach(s => {
                    statusOptions += `<option value="${s}" ${s === copy.status ? 'selected' : ''}>${s}</option>`;
                });
                let conditionOptions = '';
                allConditions.forEach(c => {
                    conditionOptions += `<option value="${c}" ${c === copy.condition ? 'selected' : ''}>${c}</option>`;
                });
                row.innerHTML = `
                    <td class="copy-id-cell">#${copy.copy_id}</td>
                    <td><select class="copy-status" ${isBorrowed ? 'disabled' : ''}>${statusOptions}</select></td>
                    <td><select class="copy-condition">${conditionOptions}</select></td>
                    <td><input type="text" class="copy-shelf" value="${copy.shelf_location || ''}" placeholder="e.g., A-01"></td>
                    <td class="action-cell">
                        <button type="button" class="copy-action-btn save-copy-btn" title="Save Changes"><span class="material-icons-round">save</span></button>
                        <button type="button" class="copy-action-btn delete-copy-btn" title="Delete Copy" ${isBorrowed ? 'disabled' : ''}><span class="material-icons-round">delete</span></button>
                    </td>
                `;
                copiesListBody.appendChild(row);
            });
        } catch (error) {
            console.error("Failed to load copies:", error);
            copiesListBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">Error: ${error.message}</td></tr>`;
        }
    };
    
    const switchModalTab = (paneName) => {
        if (!modalTabsContainer || !modalTabPanes.length) return;
        modalTabsContainer.querySelectorAll('.modal-tab-item').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.pane === paneName) tab.classList.add('active');
        });
        modalTabPanes.forEach(pane => {
            pane.classList.remove('active');
            if (pane.id === paneName) pane.classList.add('active');
        });
    };
    
    const switchStudentModalTab = (paneName) => {
        if (!studentModalTabs || !studentModalPanes.length) return;
        studentModalTabs.querySelectorAll('.modal-tab-item').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.pane === paneName) tab.classList.add('active');
        });
        studentModalPanes.forEach(pane => {
            pane.classList.remove('active');
            if (pane.id === paneName) pane.classList.add('active');
        });
    };
    
    const populateStudentProfile = (profile) => {
        if (!profile) return;
        document.getElementById("student-modal-name").textContent = profile.name;
        document.getElementById("student-modal-username").textContent = profile.username;
        document.getElementById("student-modal-email").textContent = profile.email;
        document.getElementById("student-modal-contact").textContent = profile.contact_number || 'N/A';
        document.getElementById("student-modal-joined").textContent = new Date(profile.date_created).toLocaleDateString();
        const statusTag = document.getElementById("student-modal-status-tag");
        const statusDesc = document.getElementById("student-modal-status-desc");
        const toggleBtn = document.getElementById("student-modal-toggle-status-btn");
        toggleBtn.dataset.accountId = profile.account_id;
        if (profile.is_active == 1) {
            statusTag.textContent = "Active";
            statusTag.className = "status-tag tag-available";
            statusDesc.textContent = "This student can borrow books.";
            toggleBtn.textContent = "Deactivate Account";
            toggleBtn.className = "action-btn deactivate-btn";
            toggleBtn.dataset.activeStatus = "0";
        } else {
            statusTag.textContent = "Inactive";
            statusTag.className = "status-tag tag-checkedout";
            statusDesc.textContent = "This account is deactivated.";
            toggleBtn.textContent = "Activate Account";
            toggleBtn.className = "action-btn activate-btn";
            toggleBtn.dataset.activeStatus = "1";
        }
    };

    const populateStudentCurrent = (borrows) => {
        const tableBody = document.getElementById("student-current-table-body");
        tableBody.innerHTML = "";
        if (!borrows || borrows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No active borrows.</td></tr>';
            return;
        }
        borrows.forEach(t => {
            const isOverdue = t.status === 'Overdue';
            tableBody.innerHTML += `
                <tr>
                    <td>${t.title}</td>
                    <td>${new Date(t.date_due).toLocaleDateString()}</td>
                    <td><span class="status-tag ${isOverdue ? 'tag-checkedout' : 'tag-available'}">${t.status}</span></td>
                    <td><button class="action-btn return-book-btn" data-transaction-id="${t.transaction_id}">Mark as Returned</button></td>
                </tr>
            `;
        });
    };

    const populateStudentHistory = (history) => {
        const tableBody = document.getElementById("student-history-table-body");
        tableBody.innerHTML = "";
        if (!history || history.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No borrowing history.</td></tr>';
            return;
        }
        history.forEach(t => {
            const hasFine = t.fine > 0;
            tableBody.innerHTML += `
                <tr>
                    <td>${t.title}</td>
                    <td>${t.date_returned ? new Date(t.date_returned).toLocaleDateString() : 'N/A'}</td>
                    <td>$${parseFloat(t.fine).toFixed(2)}</td>
                    <td>
                        <button class="action-btn issue-fine-btn" data-transaction-id="${t.transaction_id}">Issue Fine</button>
                        ${hasFine ? `<button class="action-btn waive-fine-btn" data-transaction-id="${t.transaction_id}">Waive Fine</button>` : ''}
                    </td>
                </tr>
            `;
        });
    };
    
    const loadStudentDetails = async (accountId) => {
        studentModalTitle.textContent = "Loading...";
        switchStudentModalTab('student-profile-pane');
        document.getElementById("student-current-table-body").innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading...</td></tr>';
        document.getElementById("student-history-table-body").innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading...</td></tr>';
        document.getElementById("student-issue-fine-form").reset();
        studentModal.dataset.currentAccountId = accountId;
        try {
            const response = await fetch(`../php/api/librarian.php?action=getStudentDetails&account_id=${accountId}`);
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            studentModalTitle.textContent = result.data.profile.name;
            populateStudentProfile(result.data.profile);
            populateStudentCurrent(result.data.currentBorrows);
            populateStudentHistory(result.data.history);
        } catch (error) {
            window.showPopup(`Error loading student details: ${error.message}`);
            studentModal.classList.remove("active");
        }
    };

    // ===========================================
    // 1. SPA NAVIGATION LOGIC (MERGED)
    // ===========================================
    
    const switchPanel = (targetId) => {
        mainContent.querySelectorAll(".content-panel").forEach(panel => {
            panel.classList.remove("active");
        });
        const activePanel = document.getElementById(targetId);
        if (activePanel) {
            activePanel.classList.add("active");
            
            // --- MERGED: Load data for BOTH Admin and Librarian panels ---
            // Admin Panels
            if (targetId === "admin-dashboard-content") { /* Dashboard might have dynamic stats */ }
            if (targetId === "admin-accounts-content") loadAccounts();
            if (targetId === "admin-settings-content") loadSettings();
            if (targetId === "admin-logs-content") loadLogs();
            if (targetId === "admin-announcements-content") loadAnnouncements();
            
            // Librarian Panels
            if (targetId === "librarian-catalog-content") loadCatalog();
            if (targetId === "librarian-archive-content") loadArchive();
            if (targetId === "librarian-users-content") loadUsers();
            if (targetId === "librarian-circulation-content") { /* No initial load needed */ }
            if (targetId === "librarian-dashboard-content") { /* No initial load needed */ }

        } else {
            console.warn(`Content panel with ID "${targetId}" not found.`);
        }
    };

    sidebar.addEventListener("click", (e) => {
        const navItem = e.target.closest(".nav-item");
        if (!navItem) return;
        
        e.preventDefault(); 
        const targetId = navItem.dataset.target;
        if (!targetId) return;

        sidebar.querySelectorAll(".nav-item").forEach(item => {
            item.classList.remove("active");
        });
        navItem.classList.add("active");
        
        switchPanel(targetId);
        window.location.hash = navItem.getAttribute("href");
    });

    // Initial page load
    const currentHash = window.location.hash.substring(1);
    let targetPanelId = "admin-dashboard-content"; // Default to admin dashboard
    if (currentHash) {
        const activeLink = sidebar.querySelector(`.nav-item[href="#${currentHash}"]`);
        if (activeLink) {
            targetPanelId = activeLink.dataset.target;
            sidebar.querySelectorAll(".nav-item").forEach(item => item.classList.remove("active"));
            activeLink.classList.add("active");
        }
    }
    switchPanel(targetPanelId); // This will now load data for the initial panel

    // ===========================================
    // 2. LOGOUT LOGIC (SHARED)
    // ===========================================
    const logoutLink = document.getElementById("logout-link");
    const handleLogout = async (e) => {
        e.preventDefault();
        if (!confirm("Are you sure you want to log out?")) return;
        try {
            const response = await fetch("../php/api/auth.php?action=logout", { method: "POST" });
            const result = await response.json();
            if(result.success) {
                window.location.href = "login.php";
            } else {
                window.showPopup("Logout failed: " + result.message);
            }
        } catch (err) {
            window.showPopup("An error occurred during logout.");
        }
    };
    if(logoutLink) logoutLink.addEventListener("click", handleLogout);

    // ===========================================
    // 3. ADMIN-SPECIFIC PAGE LISTENERS
    // ===========================================

    // --- ACCOUNTS PAGE ---
    if (addLibrarianBtn && addLibrarianModal) {
        addLibrarianBtn.addEventListener("click", () => {
            addLibrarianForm.reset();
            addLibrarianModal.classList.add("active");
        });
        addLibrarianModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("modal-overlay") || e.target.closest(".modal-close-btn")) {
                addLibrarianModal.classList.remove("active");
            }
        });
    }

    if (addLibrarianForm) {
        addLibrarianForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(addLibrarianForm);
            formData.append('action', 'createLibrarian');
            
            try {
                const response = await fetch("../php/api/admin.php", { method: "POST", body: formData });
                const result = await response.json();
                if (result.success) {
                    window.showPopup(result.message || "Librarian created successfully!"); // Added fallback message
                    addLibrarianModal.classList.remove("active");
                    loadAccounts(); // Refresh the table
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                window.showPopup(`Error: ${error.message}`);
            }
        });
    }

    if (accountsTableBody) {
        accountsTableBody.addEventListener("click", async (e) => {
            const btn = e.target.closest(".activate-btn, .deactivate-btn");
            if (!btn) return;
            
            const accountId = btn.dataset.id;
            const isActive = btn.dataset.active;
            const actionText = isActive == 1 ? "activate" : "deactivate";

            if (confirm(`Are you sure you want to ${actionText} this account?`)) {
                const formData = new FormData();
                formData.append('action', 'toggleAccountStatus');
                formData.append('account_id', accountId);
                formData.append('is_active', isActive);

                try {
                    // Note: This API endpoint is in admin.php, which is correct
                    const response = await fetch("../php/api/admin.php", { method: "POST", body: formData });
                    const result = await response.json();
                    if (result.success) {
                        window.showPopup(result.message);
                        loadAccounts(); // Refresh
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    window.showPopup(`Error: ${error.message}`);
                }
            }
        });
    }

    // --- SETTINGS PAGE ---
    if (settingsForm) {
        settingsForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(settingsForm);
            formData.append('action', 'updateSettings');
            
            try {
                const response = await fetch("../php/api/admin.php", { method: "POST", body: formData });
                const result = await response.json();
                if (result.success) {
                    window.showPopup(result.message);
                    loadSettings(); // Refresh
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                window.showPopup(`Error: ${error.message}`);
            }
        });
    }

    // --- LOGS CONSOLE PAGE ---
    if (refreshLogsBtn) {
        refreshLogsBtn.addEventListener("click", loadLogs);
    }
    
    // --- DASHBOARD ANNOUNCEMENT ---
    if (announcementForm) {
        announcementForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(announcementForm);
            formData.append('action', 'createAnnouncement');
            
            try {
                const response = await fetch("../php/api/admin.php", { method: "POST", body: formData });
                const result = await response.json();
                if (result.success) {
                    window.showPopup(result.message);
                    announcementForm.reset();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                window.showPopup(`Error: ${error.message}`);
            }
        });
    }

    // ===========================================
    // 4. LIBRARIAN-SPECIFIC PAGE LISTENERS (MERGED)
    // ===========================================

    // --- BOOK MODAL TABS ---
    if (modalTabsContainer) {
        modalTabsContainer.addEventListener('click', (e) => {
            e.preventDefault();
            const tabButton = e.target.closest('.modal-tab-item');
            if (tabButton && !tabButton.classList.contains('disabled')) {
                const paneName = tabButton.dataset.pane;
                switchModalTab(paneName);
                const bookId = bookModal.dataset.currentBookId;
                if (paneName === 'book-copies-pane' && bookId) {
                    if (!copiesListBody.hasChildNodes() || copiesListBody.innerText.includes("Loading")) {
                        loadBookCopies(bookId);
                    }
                }
            }
        });
    }

    // --- "ADD NEW BOOK" BUTTON ---
    if (openBookModalBtn && bookModal) {
        openBookModalBtn.addEventListener("click", () => {
            bookForm.reset(); 
            document.getElementById("book-modal-title").innerText = "Add New Book";
            document.getElementById("book-id").value = ""; 
            resetImagePreview();
            switchModalTab('book-details-pane');
            if (copiesTabButton) copiesTabButton.classList.add('disabled');
            if (copiesListBody) copiesListBody.innerHTML = '';
            bookModal.dataset.currentBookId = '';
            bookModal.classList.add("active");
        });
    }

    // --- BOOK MODAL (CLOSE & SUBMIT) ---
    if (bookModal) {
        bookModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("modal-overlay") || e.target.closest(".modal-close-btn")) {
                bookModal.classList.remove("active");
                resetImagePreview();
            }
        });
    }
    if (bookForm) {
        bookForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(bookForm);
            const bookId = formData.get("book_id");
            const action = bookId ? 'updateBook' : 'addBook';
            formData.append('action', action);
            try {
                const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                const result = await response.json();
                if (result.success) {
                    window.showPopup(result.message);
                    bookModal.classList.remove("active");
                    loadCatalog(); 
                } else {
                    window.showPopup("Error: " + result.message);
                }
            } catch (error) {
                console.error("Failed to submit form:", error);
                window.showPopup("A critical error occurred.");
            }
        });
    }
    
    // --- CATALOG TABLE (EDIT/ARCHIVE) ---
    if (catalogTableBody) {
        catalogTableBody.addEventListener("click", async (e) => {
            const archiveBtn = e.target.closest(".archive-action-btn");
            const editBtn = e.target.closest(".edit-action-btn");
            if (archiveBtn) {
                e.preventDefault();
                const bookId = archiveBtn.dataset.bookId;
                if (!bookId) return;
                if (confirm(`Are you sure you want to archive Book ID ${bookId}?`)) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'archiveBook');
                        formData.append('book_id', bookId);
                        const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                        const result = await response.json();
                        if (result.success) {
                            window.showPopup(result.message);
                            loadCatalog();
                        } else { window.showPopup("Error: " + result.message); }
                    } catch (error) { window.showPopup("An error occurred: " + error.message); }
                }
            }
            if (editBtn) {
                e.preventDefault();
                const bookId = editBtn.dataset.bookId;
                if (!bookId) return;
                try {
                    const response = await fetch(`../php/api/librarian.php?action=getBookForEdit&book_id=${bookId}`);
                    const result = await response.json();
                    if (!result.success) throw new Error(result.message);
                    const book = result.data;
                    bookForm.reset(); 
                    resetImagePreview(); 
                    document.getElementById("book-modal-title").innerText = "Edit Book";
                    document.getElementById("book-id").value = book.book_id;
                    document.getElementById("book-title").value = book.title;
                    document.getElementById("book-authors").value = book.authors;
                    document.getElementById("book-genres").value = book.genres;
                    document.getElementById("book-isbn").value = book.isbn;
                    document.getElementById("book-publisher").value = book.publisher;
                    document.getElementById("book-year").value = book.year_published;
                    document.getElementById("book-desc").value = book.description;
                    const preview = document.getElementById("book-cover-preview");
                    preview.src = book.cover_url ? `../assets/covers/${book.cover_url}` : "../assets/covers/CoverBookTemp.png";
                    switchModalTab('book-details-pane');
                    if (copiesTabButton) copiesTabButton.classList.remove('disabled');
                    if (copiesListBody) copiesListBody.innerHTML = ''; 
                    bookModal.dataset.currentBookId = book.book_id;
                    bookModal.classList.add("active");
                } catch (error) { window.showPopup("Failed to load book for editing: " + error.message); }
            }
        });
    }

    // --- "ADD NEW COPY" FORM ---
    if (addCopyForm) {
        addCopyForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const bookId = bookModal.dataset.currentBookId;
            if (!bookId) { window.showPopup("Error: No book ID found. Cannot add copy."); return; }
            const conditionInput = document.getElementById("add-copy-condition");
            const shelfInput = document.getElementById("add-copy-shelf");
            const formData = new FormData();
            formData.append('action', 'addBookCopy');
            formData.append('book_id', bookId);
            formData.append('condition', conditionInput.value);
            formData.append('shelf_location', shelfInput.value);
            try {
                const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                const result = await response.json();
                if (result.success) {
                    window.showPopup(result.message);
                    shelfInput.value = ''; 
                    await loadBookCopies(bookId); 
                } else { throw new Error(result.message); }
            } catch (error) { window.showPopup(`Error adding copy: ${error.message}`); }
        });
    }
    
    // --- "SAVE/DELETE COPY" BUTTONS ---
    if (copiesListBody) {
        copiesListBody.addEventListener("click", async (e) => {
            const saveBtn = e.target.closest(".save-copy-btn");
            const deleteBtn = e.target.closest(".delete-copy-btn");
            const bookId = bookModal.dataset.currentBookId;
            if (saveBtn) {
                const row = saveBtn.closest("tr");
                const copyId = row.dataset.copyId;
                const formData = new FormData();
                formData.append('action', 'updateBookCopy');
                formData.append('copy_id', copyId);
                formData.append('status', row.querySelector('.copy-status').value);
                formData.append('condition', row.querySelector('.copy-condition').value);
                formData.append('shelf_location', row.querySelector('.copy-shelf').value);
                try {
                    const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                    const result = await response.json();
                    if (result.success) {
                        window.showPopup(result.message);
                        await loadBookCopies(bookId); 
                    } else { throw new Error(result.message); }
                } catch (error) {
                    window.showPopup(`Error updating copy: ${error.message}`);
                    await loadBookCopies(bookId); 
                }
            }
            if (deleteBtn) {
                const row = deleteBtn.closest("tr");
                const copyId = row.dataset.copyId;
                if (!confirm(`Are you sure you want to PERMANENTLY DELETE Copy ID #${copyId}?`)) return;
                const formData = new FormData();
                formData.append('action', 'deleteBookCopy');
                formData.append('copy_id', copyId);
                try {
                    const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                    const result = await response.json();
                    if (result.success) {
                        window.showPopup(result.message);
                        await loadBookCopies(bookId); 
                    } else { throw new Error(result.message); }
                } catch (error) { window.showPopup(`Error deleting copy: ${error.message}`); }
            }
        });
    }

    // --- ARCHIVE PAGE (RESTORE) ---
    if (archiveTableBody) {
        archiveTableBody.addEventListener("click", async (e) => {
            const restoreBtn = e.target.closest(".restore-action-btn");
            if (restoreBtn) {
                e.preventDefault();
                const bookId = restoreBtn.dataset.bookId;
                if (!bookId) return;
                if (confirm(`Are you sure you want to restore Book ID ${bookId}?`)) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'unarchiveBook');
                        formData.append('book_id', bookId);
                        const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                        const result = await response.json();
                        if (result.success) {
                            window.showPopup(result.message);
                            loadArchive(archiveSearchInput ? archiveSearchInput.value : ""); 
                        } else { window.showPopup("Error: " + result.message); }
                    } catch (error) { window.showPopup("An error occurred: " + error.message); }
                }
            }
        });
    }

    // --- ARCHIVE PAGE (SEARCH) ---
    if (archiveSearchInput) {
        archiveSearchInput.addEventListener("keyup", debounce(() => {
            loadArchive(archiveSearchInput.value);
        }, 300));
    }

    // --- USER MANAGEMENT (SEARCH) ---
    if (userSearchInput) {
        userSearchInput.addEventListener("keyup", debounce(() => {
            loadUsers(userSearchInput.value);
        }, 300));
    }

    // --- USER MANAGEMENT (VIEW DETAILS) ---
    if (userTableBody) {
        userTableBody.addEventListener("click", (e) => {
            const detailsBtn = e.target.closest(".view-details-btn");
            if (detailsBtn) {
                const accountId = detailsBtn.dataset.accountId;
                if (studentModal) {
                    studentModal.classList.add("active");
                    loadStudentDetails(accountId);
                } else {
                    window.showPopup(`Error: Student modal not found. Cannot view details for #${accountId}`);
                }
            }
        });
    }

    // --- STUDENT DETAILS MODAL (ALL LISTENERS) ---
    if (studentModal) {
        studentModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("modal-overlay") || e.target.closest(".modal-close-btn")) {
                studentModal.classList.remove("active");
            }
        });
        studentModalTabs.addEventListener("click", (e) => {
            e.preventDefault();
            const tabButton = e.target.closest(".modal-tab-item");
            if (tabButton) {
                switchStudentModalTab(tabButton.dataset.pane);
            }
        });
        studentModal.addEventListener("click", async (e) => {
            const accountId = studentModal.dataset.currentAccountId;
            if (e.target.id === "student-modal-toggle-status-btn") {
                const btn = e.target;
                const newStatus = btn.dataset.activeStatus;
                const actionText = newStatus == 1 ? "activate" : "deactivate";
                if (confirm(`Are you sure you want to ${actionText} this account?`)) {
                    const formData = new FormData();
                    formData.append('action', 'toggleStudentStatus');
                    formData.append('account_id', accountId);
                    formData.append('is_active', newStatus);
                    try {
                        const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                        const result = await response.json();
                        if (!result.success) throw new Error(result.message);
                        window.showPopup(result.message);
                        await loadStudentDetails(accountId); 
                        await loadUsers(userSearchInput ? userSearchInput.value : "");
                    } catch (error) { window.showPopup(`Error: ${error.message}`); }
                }
            }
            if (e.target.classList.contains("return-book-btn")) {
                const transactionId = e.target.dataset.transactionId;
                if (confirm(`Manually return Transaction #${transactionId}?`)) {
                    const formData = new FormData();
                    formData.append('action', 'manuallyReturnBook');
                    formData.append('transaction_id', transactionId);
                    try {
                        const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                        const result = await response.json();
                        if (!result.success) throw new Error(result.message);
                        window.showPopup(result.message);
                        await loadStudentDetails(accountId);
                    } catch (error) { window.showPopup(`Error: ${error.message}`); }
                }
            }
            if (e.target.classList.contains("waive-fine-btn")) {
                const transactionId = e.target.dataset.transactionId;
                if (confirm(`Waive entire fine for Transaction #${transactionId}?`)) {
                    const formData = new FormData();
                    formData.append('action', 'waiveFine');
                    formData.append('transaction_id', transactionId);
                    try {
                        const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                        const result = await response.json();
                        if (!result.success) throw new Error(result.message);
                        window.showPopup(result.message);
                        await loadStudentDetails(accountId);
                    } catch (error) { window.showPopup(`Error: ${error.message}`); }
                }
            }
            if (e.target.classList.contains("issue-fine-btn")) {
                const transactionId = e.target.dataset.transactionId;
                switchStudentModalTab('student-actions-pane');
                document.getElementById('fine-transaction-id').value = transactionId;
                document.getElementById('fine-amount').focus();
            }
        });
        
        const issueFineForm = document.getElementById("student-issue-fine-form");
        if (issueFineForm) {
            issueFineForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const accountId = studentModal.dataset.currentAccountId;
                const formData = new FormData(issueFineForm);
                formData.append('action', 'issueFine');
                try {
                    const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                    const result = await response.json();
                    if (!result.success) throw new Error(result.message);
                    window.showPopup(result.message);
                    issueFineForm.reset();
                    await loadStudentDetails(accountId);
                } catch (error) { window.showPopup(`Error: ${error.message}`); }
            });
        }
    }

    // --- BOOK FORM IMAGE PREVIEW ---
    if (triggerUploadBtn) {
        triggerUploadBtn.addEventListener("click", () => bookCoverUpload.click());
    }
    if (bookCoverUpload) {
        bookCoverUpload.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    newPreviewImg.src = event.target.result;
                    previewGrid.classList.add("show-new");
                    newPreviewBox.style.display = "block";
                    cancelUploadBtn.style.display = "inline-block";
                };
                reader.readAsDataURL(file);
            }
        });
    }
    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener("click", resetImagePreview);
    }

    // --- CIRCULATION PANEL ---
    if (borrowForm) {
        borrowUserSearchInput.addEventListener("change", async () => {
            try {
                const response = await fetch(`../php/api/librarian.php?action=findUser&query=${borrowUserSearchInput.value}`);
                const result = await response.json();
                if (result.success) {
                    userNameDiv.textContent = result.name;
                    currentBorrowUser = result.account_id;
                } else {
                    userNameDiv.textContent = result.message;
                    currentBorrowUser = null;
                }
            } catch (e) { userNameDiv.textContent = e.message; currentBorrowUser = null;}
        });
        bookSearchInput.addEventListener("change", async () => {
            try {
                const response = await fetch(`../php/api/librarian.php?action=findCopy&copy_id=${bookSearchInput.value}`);
                const result = await response.json();
                if (result.success) {
                    bookTitleDiv.textContent = result.title;
                    currentBorrowCopy = result.copy_id;
                } else {
                    bookTitleDiv.textContent = result.message;
                    currentBorrowCopy = null;
                }
            } catch (e) { bookTitleDiv.textContent = e.message; currentBorrowCopy = null;}
        });
        borrowForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            if (!currentBorrowUser || !currentBorrowCopy) {
                window.showPopup("Please select a valid user AND a valid book copy.");
                return;
            }
            const formData = new FormData();
            formData.append('action', 'borrowBook');
            formData.append('account_id', currentBorrowUser);
            formData.append('copy_id', currentBorrowCopy);
            try {
                const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                const result = await response.json();
                window.showPopup(result.message);
                if (result.success) {
                    borrowForm.reset();
                    userNameDiv.textContent = "...";
                    bookTitleDiv.textContent = "...";
                    currentBorrowUser = null;
                    currentBorrowCopy = null;
                }
            } catch (e) { window.showPopup(e.message); }
        });
    }
    if (returnForm) {
        returnSearchInput.addEventListener("change", async () => {
            try {
                const response = await fetch(`../php/api/librarian.php?action=findReturn&copy_id=${returnSearchInput.value}`);
                const result = await response.json();
                if (result.success) {
                    const t = result.transaction;
                    document.getElementById("return-book-title").textContent = t.title;
                    document.getElementById("return-user-name").textContent = t.user_name;
                    document.getElementById("return-due-date").textContent = new Date(t.date_due).toLocaleDateString();
                    document.getElementById("return-status").textContent = t.status;
                    document.getElementById("return-book-cover").src = `../assets/covers/${t.cover_url}`;
                    currentReturnTransaction = t.transaction_id;
                    returnDetailsDiv.style.display = "block";
                } else {
                    window.showPopup(result.message);
                    returnDetailsDiv.style.display = "none";
                    currentReturnTransaction = null;
                }
            } catch (e) { window.showPopup(e.message); currentReturnTransaction = null; returnDetailsDiv.style.display = "none";}
        });
        returnForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            if (!currentReturnTransaction) {
                window.showPopup("Please find a valid transaction first.");
                return;
            }
            const formData = new FormData();
            formData.append('action', 'returnBook');
            formData.append('transaction_id', currentReturnTransaction);
            try {
                const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                const result = await response.json();
                window.showPopup(result.message);
                if (result.success) {
                    returnForm.reset();
                    returnDetailsDiv.style.display = "none";
                    currentReturnTransaction = null;
                }
            } catch (e) { window.showPopup(e.message); }
        });
    }

});