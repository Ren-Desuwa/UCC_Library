/**
 * librarian.js
 * Handles SPA navigation and logic for the Librarian portal.
 */

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar-nav");
    const mainContent = document.querySelector(".main-content");

    if (!sidebar || !mainContent) {
        console.error("Sidebar or Main Content area not found.");
        return;
    }

    // ===========================================
    // 3. CATALOG & MODAL LOGIC (Declarations moved up)
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
    const archiveSearchInput = document.getElementById("archive-search-input"); // <-- ADDED
    
    // --- Book Copies Manager Elements ---
    const copiesManagerSection = document.getElementById("book-copies-manager");
    const copiesListBody = document.getElementById("book-copies-list");
    const addCopyForm = document.getElementById("add-copy-form");

    // --- NEW: Modal Tab Elements ---
    const modalTabsContainer = bookModal ? bookModal.querySelector(".modal-tabs") : null;
    const modalTabPanes = bookModal ? bookModal.querySelectorAll(".modal-tab-pane") : [];
    const copiesTabButton = modalTabsContainer ? modalTabsContainer.querySelector('a[data-pane="book-copies-pane"]') : null;


    // ===========================================
    // 4. CIRCULATION LOGIC (Declarations moved up)
    // ===========================================
    // ... (circulation declarations remain unchanged) ...
    const borrowForm = document.getElementById("borrow-form");
    const userSearchInput = document.getElementById("borrow-user-search");
    const userTableBody = document.getElementById("user-table-body");
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
    // FUNCTION DEFINITIONS (MOVED UP)
    // ===========================================
    /**
     * --- NEW: Debounce function to limit API calls ---
     */
    const debounce = (func, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    };

    /**
     * Function to load the main catalog
     */
    const loadCatalog = async () => {
        // ... (function remains unchanged) ...
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

    /**
     * --- MODIFIED: Function to load the archive ---
     */
    const loadArchive = async (searchTerm = "") => { // <-- ADDED searchTerm
        if (!archiveTableBody) return;

        archiveTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading archived books...</td></tr>';
        try {
            // --- NEW: Add query to fetch ---
            const query = new URLSearchParams({
                action: 'getArchivedBooks',
                query: searchTerm
            }).toString();

            const response = await fetch(`../php/api/librarian.php?${query}`);
            // --- END NEW ---

            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            const html = await response.text();
            archiveTableBody.innerHTML = html;
        } catch (error) {
            console.error("Failed to load archive:", error);
            archiveTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: red;">Error loading archived books.</td></tr>';
        }
    };

    // --- NEW: Function to load users ---
    const loadUsers = async (searchTerm = "") => {
        if (!userTableBody) return;
        userTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading users...</td></tr>';
        
        try {
            const query = new URLSearchParams({
                action: 'searchUsers',
                query: searchTerm
            }).toString();
            
            const response = await fetch(`../php/api/librarian.php?${query}`);
            if (!response.ok) throw new Error("Network response was not ok");
            
            const html = await response.text();
            userTableBody.innerHTML = html;
        } catch (error) {
            console.error("Failed to load users:", error);
            userTableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">${error.message}</td></tr>`;
        }
    };
    /**
     * Function to reset the image preview
     */
    const resetImagePreview = () => {
        // ... (function remains unchanged) ...
        if (bookCoverUpload) bookCoverUpload.value = null;
        if (newPreviewImg) newPreviewImg.src = "#";
        if (previewGrid) previewGrid.classList.remove("show-new");
        if (newPreviewBox) newPreviewBox.style.display = "none";
        if (cancelUploadBtn) cancelUploadBtn.style.display = "none";
        if (currentPreviewBox) currentPreviewBox.style.display = "block";
    };

    /**
     * Function to load book copies into the modal
     */
    const loadBookCopies = async (bookId) => {
        // ... (function remains unchanged, still populates copiesListBody) ...
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
    
    /**
     * --- NEW: Function to switch modal tabs ---
     */
    const switchModalTab = (paneName) => {
        if (!modalTabsContainer || !modalTabPanes.length) return;

        // Activate the correct tab button
        modalTabsContainer.querySelectorAll('.modal-tab-item').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.pane === paneName) {
                tab.classList.add('active');
            }
        });

        // Activate the correct tab pane
        modalTabPanes.forEach(pane => {
            pane.classList.remove('active');
            if (pane.id === paneName) {
                pane.classList.add('active');
            }
        });
    };


    // ===========================================
    // 1. SPA NAVIGATION LOGIC
    // ===========================================
    
    // --- MODIFIED: Function to switch content panels ---
    const switchPanel = (targetId) => {
        mainContent.querySelectorAll(".content-panel").forEach(panel => {
            panel.classList.remove("active");
        });
        const activePanel = document.getElementById(targetId);
        if (activePanel) {
            activePanel.classList.add("active");
            
            // Load content on panel switch
            if (targetId === "librarian-catalog-content") loadCatalog();
            if (targetId === "librarian-archive-content") loadArchive();
            if (targetId === "librarian-users-content") loadUsers(); // <-- ADDED

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
    const currentHash = window.location.hash.substring(1);
    let targetPanelId = "librarian-dashboard-content";
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
    // 2. LOGOUT LOGIC
    // ===========================================
    // ... (Logout logic remains unchanged) ...
    const logoutButton = document.getElementById("logout-button");
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
                alert("Logout failed: " + result.message);
            }
        } catch (err) {
            alert("An error occurred during logout.");
        }
    };
    if(logoutButton) logoutButton.addEventListener("click", handleLogout);
    if(logoutLink) logoutLink.addEventListener("click", handleLogout);

    // ===========================================
    // 3. CATALOG & MODAL LOGIC (Event Listeners)
    // ===========================================

    // --- NEW: Handle Modal Tab Clicks ---
    if (modalTabsContainer) {
        modalTabsContainer.addEventListener('click', (e) => {
            e.preventDefault();
            const tabButton = e.target.closest('.modal-tab-item');
            if (tabButton && !tabButton.classList.contains('disabled')) {
                const paneName = tabButton.dataset.pane;
                switchModalTab(paneName);

                // If switching to copies, load them
                const bookId = bookModal.dataset.currentBookId;
                if (paneName === 'book-copies-pane' && bookId) {
                    // Check if already loaded
                    if (!copiesListBody.hasChildNodes() || copiesListBody.innerText.includes("Loading")) {
                        loadBookCopies(bookId);
                    }
                }
            }
        });
    }

    // Open "Add Book" modal
    if (openBookModalBtn && bookModal) {
        openBookModalBtn.addEventListener("click", () => {
            bookForm.reset(); 
            document.getElementById("book-modal-title").innerText = "Add New Book";
            document.getElementById("book-id").value = ""; 
            resetImagePreview();
            
            // --- MODIFIED: Reset and disable copies tab ---
            switchModalTab('book-details-pane'); // Set to first tab
            if (copiesTabButton) copiesTabButton.classList.add('disabled');
            if (copiesListBody) copiesListBody.innerHTML = '';
            bookModal.dataset.currentBookId = '';
            
            bookModal.classList.add("active");
        });
    }

    // Close any modal
    if (bookModal) {
        bookModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("modal-overlay") || e.target.closest(".modal-close-btn")) {
                bookModal.classList.remove("active");
                resetImagePreview();
            }
        });
    }

    // Handle "Add/Edit Book" form submission (the main details)
    if (bookForm) {
        bookForm.addEventListener("submit", async (e) => {
            // ... (this function remains unchanged) ...
            e.preventDefault();
            const formData = new FormData(bookForm);
            const bookId = formData.get("book_id");
            const action = bookId ? 'updateBook' : 'addBook';
            formData.append('action', action);
            try {
                const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    bookModal.classList.remove("active");
                    loadCatalog(); 
                } else {
                    alert("Error: " + result.message);
                }
            } catch (error) {
                console.error("Failed to submit form:", error);
                alert("A critical error occurred.");
            }
        });
    }
    
    // Handle clicks on the main Catalog table (Edit, Archive)
    if (catalogTableBody) {
        catalogTableBody.addEventListener("click", async (e) => {
            const archiveBtn = e.target.closest(".archive-action-btn");
            const editBtn = e.target.closest(".edit-action-btn");

            // --- ARCHIVE LOGIC ---
            if (archiveBtn) {
                // ... (archive logic remains unchanged) ...
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
                            alert(result.message);
                            loadCatalog();
                        } else {
                            alert("Error: " + result.message);
                        }
                    } catch (error) {
                        alert("An error occurred: " + error.message);
                    }
                }
            }

            // --- EDIT LOGIC (MODIFIED) ---
            if (editBtn) {
                e.preventDefault();
                const bookId = editBtn.dataset.bookId;
                if (!bookId) return;

                try {
                    // 1. Fetch the book's current data
                    const response = await fetch(`../php/api/librarian.php?action=getBookForEdit&book_id=${bookId}`);
                    const result = await response.json();
                    if (!result.success) throw new Error(result.message);
                    const book = result.data;

                    // 2. Populate the modal form
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

                    // --- MODIFIED: Enable copies tab and set book ID ---
                    switchModalTab('book-details-pane'); // Reset to details tab
                    if (copiesTabButton) copiesTabButton.classList.remove('disabled');
                    if (copiesListBody) copiesListBody.innerHTML = ''; // Clear old copies
                    bookModal.dataset.currentBookId = book.book_id; // Set book ID
                    // Note: We DON'T load copies here. We wait for the user to click the tab.
                    
                    // 3. Open the modal
                    bookModal.classList.add("active");

                } catch (error) {
                    alert("Failed to load book for editing: " + error.message);
                }
            }
        });
    }

    // --- Handle "Add New Copy" form submission ---
    if (addCopyForm) {
        addCopyForm.addEventListener("submit", async (e) => {
            // ... (this function remains unchanged) ...
            e.preventDefault();
            const bookId = bookModal.dataset.currentBookId;
            if (!bookId) {
                alert("Error: No book ID found. Cannot add copy.");
                return;
            }
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
                    alert(result.message);
                    shelfInput.value = ''; 
                    await loadBookCopies(bookId); 
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert(`Error adding copy: ${error.message}`);
            }
        });
    }
    
    // --- Handle "Save Copy" and "Delete Copy" clicks ---
    if (copiesListBody) {
        copiesListBody.addEventListener("click", async (e) => {
            // ... (this function remains unchanged) ...
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
                        alert(result.message);
                        await loadBookCopies(bookId); 
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    alert(`Error updating copy: ${error.message}`);
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
                        alert(result.message);
                        await loadBookCopies(bookId); 
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    alert(`Error deleting copy: ${error.message}`);
                }
            }
        });
    }
// --- Archive Page Event Listener (Restore) ---
    if (archiveTableBody) {
        archiveTableBody.addEventListener("click", async (e) => {
            const restoreBtn = e.target.closest(".restore-action-btn");

            if (restoreBtn) {
                e.preventDefault();
                const bookId = restoreBtn.dataset.bookId;
                if (!bookId) return;

                if (confirm(`Are you sure you want to restore Book ID ${bookId}? It will appear in the main catalogue again.`)) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'unarchiveBook');
                        formData.append('book_id', bookId);

                        const response = await fetch("../php/api/librarian.php", {
                            method: "POST",
                            body: formData
                        });
                        const result = await response.json();

                        if (result.success) {
                            alert(result.message);
                            // --- MODIFIED: Reload with current search term ---
                            loadArchive(archiveSearchInput ? archiveSearchInput.value : ""); 
                        } else {
                            alert("Error: " . result.message);
                        }
                    } catch (error) {
                        alert("An error occurred: " + error.message);
                    }
                }
            }
        });
    }

    // --- NEW: Archive Search ---
    if (archiveSearchInput) {
        // Use a 300ms debounce to prevent spamming the server
        archiveSearchInput.addEventListener("keyup", debounce(() => {
            loadArchive(archiveSearchInput.value);
        }, 300));
    }

    // --- NEW: User Search ---
    if (userSearchInput) {
        userSearchInput.addEventListener("keyup", debounce(() => {
            loadUsers(userSearchInput.value);
        }, 300));
    }

    // --- NEW: User Table Clicks (for future modal) ---
    if (userTableBody) {
        userTableBody.addEventListener("click", (e) => {
            const detailsBtn = e.target.closest(".view-details-btn");
            if (detailsBtn) {
                const accountId = detailsBtn.dataset.accountId;
                alert(`(Future feature): View details for Account ID #${accountId}`);
                // We will build the modal for this in the next step!
            }
        });
    }

    // ===========================================
    // 4. BOOK FORM IMAGE PREVIEW LOGIC (Event Listeners)
    // ===========================================
    // ... (image preview logic remains unchanged) ...
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

    // ===========================================
    // 5. CIRCULATION LOGIC (Event Listeners)
    // ===========================================
    // ... (circulation logic remains unchanged) ...
    if (borrowForm) {
        userSearchInput.addEventListener("change", async () => {
            try {
                const response = await fetch(`../php/api/librarian.php?action=findUser&query=${userSearchInput.value}`);
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
                alert("Please select a valid user AND a valid book copy.");
                return;
            }
            const formData = new FormData();
            formData.append('action', 'borrowBook');
            formData.append('account_id', currentBorrowUser);
            formData.append('copy_id', currentBorrowCopy);
            try {
                const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                const result = await response.json();
                alert(result.message);
                if (result.success) {
                    borrowForm.reset();
                    userNameDiv.textContent = "...";
                    bookTitleDiv.textContent = "...";
                    currentBorrowUser = null;
                    currentBorrowCopy = null;
                }
            } catch (e) { alert(e.message); }
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
                    alert(result.message);
                    returnDetailsDiv.style.display = "none";
                    currentReturnTransaction = null;
                }
            } catch (e) { alert(e.message); currentReturnTransaction = null; returnDetailsDiv.style.display = "none";}
        });
        returnForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            if (!currentReturnTransaction) {
                alert("Please find a valid transaction first.");
                return;
            }
            const formData = new FormData();
            formData.append('action', 'returnBook');
            formData.append('transaction_id', currentReturnTransaction);
            try {
                const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                const result = await response.json();
                alert(result.message);
                if (result.success) {
                    returnForm.reset();
                    returnDetailsDiv.style.display = "none";
                    currentReturnTransaction = null;
                }
            } catch (e) { alert(e.message); }
        });
    }

});