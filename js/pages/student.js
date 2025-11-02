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

    // --- 5. NEW CATALOGUE PAGE LOGIC ---
    initCataloguePage();
});


/**
 * Initializes all logic for the new catalogue page.
 */
function initCataloguePage() {
    const searchInput = document.getElementById("student-search-input");
    const searchBar = document.getElementById("search-bar-advanced");
    const gridView = document.getElementById("catalogue-grid-view");
    const tableView = document.getElementById("catalogue-table-view");
    const tableBody = document.getElementById("student-search-table-body");
    const filterBtn = document.getElementById("filter-btn");
    const filterDropdown = document.getElementById("filter-dropdown");

    if (!searchInput || !gridView || !tableView || !tableBody || !filterBtn || !filterDropdown) {
        return; 
    }

    // --- Filter Dropdown Logic ---
    filterBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        filterDropdown.classList.toggle("active");
    });
    filterDropdown.addEventListener("click", (e) => {
        e.preventDefault();
        const filterType = e.target.dataset.filterType;
        if (filterType) {
            addFilterChip(filterType);
            filterDropdown.classList.remove("active");
        }
    });
    document.addEventListener("click", () => {
        filterDropdown.classList.remove("active");
    });

    // --- Search Bar / View Switching Logic ---
    // --- Search Bar / View Switching Logic (UPDATED) ---
    const debouncedSearch = debounce(async (searchQuery) => {
        try {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Searching...</td></tr>';
            
            // --- UPDATED to use POST and FormData ---
            const formData = new FormData();
            formData.append('action', 'searchBooks');
            formData.append('term', searchQuery.term);
            formData.append('author', searchQuery.author);
            formData.append('genre', searchQuery.genre);

            const response = await fetch(`../php/api/catalogue.php`, {
                method: 'POST',
                body: formData
            });
            // --- END UPDATE ---

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

    searchInput.addEventListener("keyup", (e) => {
        const query = buildSearchQuery();
        
        // Check if any search field has value
        if (query.term.trim() !== "" || query.author.trim() !== "" || query.genre.trim() !== "") {
            gridView.style.display = "none";
            tableView.style.display = "block";
            debouncedSearch(query); // Pass the whole query object
        } else {
            gridView.style.display = "block";
            tableView.style.display = "none";
        }
    });

    // --- Filter Chip Logic ---
    function addFilterChip(type) {
        searchBar.querySelector(`.filter-chip[data-type="${type}"]`)?.remove();
        const chip = document.createElement("span");
        chip.className = "filter-chip";
        chip.dataset.type = type;
        chip.innerHTML = `
            <span class="chip-label">${type.charAt(0).toUpperCase() + type.slice(1)}:</span>
            <span class="chip-close material-icons-round">close</span>
        `;
        searchBar.insertBefore(chip, searchInput);
        searchInput.focus();
        
        // Add click event to remove chip
        chip.querySelector(".chip-close").addEventListener("click", () => {
            chip.remove();
            searchInput.value = ''; // Clear text input when a chip is removed
            searchInput.dispatchEvent(new Event('keyup')); // Trigger a new search
        });

        // Trigger a new search when a chip is added
        searchInput.dispatchEvent(new Event('keyup'));
    }
    
    /**
     * UPDATED: This now builds a query *object*
     */
    function buildSearchQuery() {
        const authorChip = searchBar.querySelector('.filter-chip[data-type="author"]');
        const genreChip = searchBar.querySelector('.filter-chip[data-type="genre"]');
        
        let freeText = searchInput.value;
        let authorQuery = '';
        let genreQuery = '';
        let termQuery = '';

        // If a chip is active, the free text applies *to that chip*
        if (authorChip) {
            authorQuery = freeText;
        } else if (genreChip) {
            genreQuery = freeText;
        } else {
            termQuery = freeText; // No chip, so search by title
        }
        
        return {
            term: termQuery,
            author: authorQuery,
            genre: genreQuery
        };
    }
}

/**
 * Debounce function (copied from visitor.js)
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