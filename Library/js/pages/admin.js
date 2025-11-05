/**
 * admin.js
 * Handles SPA navigation and logic for the Admin portal.
 */

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar-nav");
    const mainContent = document.querySelector(".main-content");

    if (!sidebar || !mainContent) {
        console.error("Admin Sidebar or Main Content area not found.");
        return;
    }

    // ===========================================
    // 1. SPA NAVIGATION LOGIC
    // ===========================================
    
    const switchPanel = (targetId) => {
        mainContent.querySelectorAll(".content-panel").forEach(panel => {
            panel.classList.remove("active");
        });
        const activePanel = document.getElementById(targetId);
        if (activePanel) {
            activePanel.classList.add("active");
            
            // --- NEW: Load data when panel is activated ---
            if (targetId === "admin-accounts-content") loadAccounts();
            if (targetId === "admin-settings-content") loadSettings();
            if (targetId === "admin-logs-content") loadLogs();
            if (targetId === "admin-announcements-content") loadAnnouncements();
            
            // Trigger librarian functions if switching to a librarian panel
            if (targetId === "librarian-catalog-content") {
                // We assume librarian.js has a global loadCatalog or is self-init
                // For this example, we'll just log it.
                console.log("Switched to librarian catalog.");
                if (typeof loadCatalog === 'function') loadCatalog();
            }
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
    switchPanel(targetPanelId);

    // ===========================================
    // 2. LOGOUT LOGIC (Copied from other portal JS)
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
    // 3. ADMIN-SPECIFIC PAGE LOGIC
    // ===========================================

    // --- ACCOUNTS PAGE ---
    const addLibrarianBtn = document.getElementById("add-librarian-btn");
    const addLibrarianModal = document.getElementById("add-librarian-modal");
    const addLibrarianForm = document.getElementById("add-librarian-form");
    const accountsTableBody = document.getElementById("admin-accounts-table-body");

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
                    window.showPopup(result.message);
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
    const settingsForm = document.getElementById("settings-form");
    
    const loadSettings = async () => {
        if (!settingsForm) return;
        try {
            const response = await fetch("../php/api/admin.php?action=getSettings");
            const result = await response.json();
            if (!result.success) throw new Error(result.message);
            
            // Populate form
            document.getElementById("setting-borrow-duration").value = result.data.borrow_duration_days;
            document.getElementById("setting-max-books").value = result.data.max_books_per_user;
            document.getElementById("setting-fine-rate").value = result.data.overdue_fine_per_day;
            document.getElementById("setting-reservation-expiry").value = result.data.reservation_expiry_hours;
            
        } catch (error) {
            window.showPopup(`Error loading settings: ${error.message}`);
        }
    };

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
    const logsConsole = document.getElementById("logs-console");
    const refreshLogsBtn = document.getElementById("refresh-logs-btn");

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

    if (refreshLogsBtn) {
        refreshLogsBtn.addEventListener("click", loadLogs);
    }
    
    // --- DASHBOARD ANNOUNCEMENT ---
    const announcementForm = document.getElementById("announcement-form");
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

    // --- ANNOUNCEMENTS PAGE ---
    const announcementsTableBody = document.getElementById("announcements-table-body");
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

});