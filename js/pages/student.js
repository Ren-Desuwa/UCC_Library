/**
 * student.js
 * Handles SPA navigation and logic for the Student portal.
 */
document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. SPA NAVIGATION LOGIC ---
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

    // --- 2. MODAL LOGIC (FIXED) ---
    const announcementsModal = document.getElementById('announcements-modal');
    const bookModal = document.getElementById('book-modal');
    const historyModal = document.getElementById('history-modal');

    // Announcements Modal
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
            const bookButton = e.target.closest('.open-book-modal-btn');
            if (bookButton) {
                e.preventDefault();
                const bookId = bookButton.dataset.bookId;
                if (bookId) {
                    await openStudentBookModal(bookId, bookModal);
                }
            }
        });

        // Listen for clicks to CLOSE the modal (using delegation)
        bookModal.addEventListener('click', (e) => {
            // Close if clicking overlay OR button with the class .modal-close-btn
            if (e.target === bookModal || e.target.closest('.modal-close-btn')) {
                bookModal.classList.remove('active');
            }
        });
    }

    // History Modal (FIXED LOGIC)
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
    // (Settings logic remains the same)
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
    // (Logout logic remains the same)
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
    initCataloguePage();
});


function initCataloguePage() {
    const searchInput = document.getElementById("student-search-input");
    const gridView = document.getElementById("catalogue-grid-view");
    const tableView = document.getElementById("catalogue-table-view");
    const tableBody = document.getElementById("student-search-table-body");
    const filterBtn = document.getElementById("filter-btn"); // <-- RE-ADDED
    const filterDropdown = document.getElementById("filter-dropdown"); // <-- RE-ADDED

    if (!searchInput || !gridView || !tableView || !tableBody || !filterBtn || !filterDropdown) {
        return; // Elements are missing
    }

    // --- RE-ADDED: Filter Dropdown Logic ---
    filterBtn.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent document click from closing it
        filterDropdown.classList.toggle("active");
    });

    document.addEventListener("click", () => {
        filterDropdown.classList.remove("active"); // Close on any click outside
    });

    // UPDATED: This now adds keywords to the search input
    filterDropdown.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        const filterType = e.target.dataset.filterType;
        if (filterType) {
            // Add a space if the input isn't empty
            if (searchInput.value.length > 0 && !searchInput.value.endsWith(' ')) {
                searchInput.value += ' ';
            }
            // Add the keyword
            searchInput.value += `${filterType}:`;
            
            filterDropdown.classList.remove("active");
            searchInput.focus(); // Focus the input so the user can type
        }
    });

    // --- Search Logic ---
    const debouncedSearch = debounce(async (searchQuery) => {
        try {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Searching...</td></tr>';
            
            const formData = new FormData();
            formData.append('action', 'searchBooks');
            formData.append('term', searchQuery.term);
            formData.append('author', searchQuery.author);
            formData.append('genre', searchQuery.genre);
            formData.append('year_from', searchQuery.year_from);
            formData.append('year_to', searchQuery.year_to);
            formData.append('status', searchQuery.status);

            const response = await fetch(`../php/api/catalogue.php`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error("Search request failed");

            const html = await response.text();
            
            if (html.trim() === "") {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No books found matching your criteria.</td></tr>';
            } else {
                tableBody.innerHTML = html;
            }

        } catch (error) {
            console.error("Search error:", error);
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error loading results.</td></tr>';
        }
    }, 300);

    // This function triggers the search
    const performSearch = () => {
        const query = buildSearchQuery();
        
        const hasQuery = Object.values(query).some(val => val.trim() !== "");
        
        if (hasQuery) {
            gridView.style.display = "none";
            tableView.style.display = "block";
            debouncedSearch(query); // Pass the whole query object
        } else {
            gridView.style.display = "block";
            tableView.style.display = "none";
        }
    };

    // Trigger search on keyup in the main input
    searchInput.addEventListener("keyup", performSearch);
    
    /**
     * This "Discord-style" parser function remains the same
     */
    function buildSearchQuery() {
        let text = searchInput.value;
        let query = {
            term: "",
            author: "",
            genre: "",
            year_from: "",
            year_to: "",
            status: ""
        };
        
        const extract = (key) => {
            const regex = new RegExp(`${key}:(?:("([^"]+)")|(\\S+))`, 'i');
            const match = text.match(regex);
            if (match) {
                text = text.replace(regex, ''); 
                return (match[2] || match[3]).trim();
            }
            return '';
        };

        query.author = extract("author");
        query.genre = extract("genre");
        
        const statusMatch = text.match(/(available|is|status):(\S+)/i);
        if (statusMatch) {
            query.status = statusMatch[2].trim().toLowerCase();
            text = text.replace(statusMatch[0], '');
        }

        const fromMatch = text.match(/from:(\d{4})/i);
        const toMatch = text.match(/to:(\d{4})/i);
        if (fromMatch) {
            query.year_from = fromMatch[1];
            text = text.replace(fromMatch[0], '');
        }
        if (toMatch) {
            query.year_to = toMatch[1];
            text = text.replace(toMatch[0], '');
        }
        if (!query.year_from && !query.year_to) {
            const rangeMatch = text.match(/year:(\d{4})\s*[-to\s]+\s*(\d{4})/i);
            if (rangeMatch) {
                query.year_from = rangeMatch[1];
                query.year_to = rangeMatch[2];
                text = text.replace(rangeMatch[0], '');
            }
        }
        if (!query.year_from && !query.year_to) {
            const yearMatch = text.match(/year:(\d{4})/i);
            if (yearMatch) {
                query.year_from = yearMatch[1];
                query.year_to = yearMatch[1]; 
                text = text.replace(yearMatch[0], '');
            }
        }
        
        query.term = text.trim();
        
        return query;
    }
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
 * Fetches book details for the student modal
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
        
        const footer = bookModalContent.querySelector('.book-modal-footer');
        const actions = footer.querySelector('.book-actions');
        
        const signInBtn = actions.querySelector('.sign-in-btn');
        if (signInBtn) {
            signInBtn.textContent = "Place Hold";
            signInBtn.classList.remove("sign-in-btn");
            signInBtn.classList.add("place-hold-btn");
            signInBtn.href = `#hold-${bookId}`; 
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
 * Fetches history details for the student modal
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