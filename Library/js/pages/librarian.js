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
    const archiveSearchInput = document.getElementById("archive-search-input");
    
    // --- Book Copies Manager Elements ---
    const copiesManagerSection = document.getElementById("book-copies-manager");
    const copiesListBody = document.getElementById("book-copies-list");
    const addCopyForm = document.getElementById("add-copy-form");

    // --- Modal Tab Elements ---
    const modalTabsContainer = bookModal ? bookModal.querySelector(".modal-tabs") : null;
    const modalTabPanes = bookModal ? bookModal.querySelectorAll(".modal-tab-pane") : [];
    const copiesTabButton = modalTabsContainer ? modalTabsContainer.querySelector('a[data-pane="book-copies-pane"]') : null;

    // --- MODIFIED: User Management Elements ---
    const userSearchInput = document.getElementById("user-search-input"); // This is the search bar
    const userTableBody = document.getElementById("user-table-body"); // This is the table
    
    // --- NEW: Student Details Modal Elements ---
    const studentModal = document.getElementById("student-details-modal");
    const studentModalTabs = studentModal ? studentModal.querySelector(".modal-tabs") : null;
    const studentModalPanes = studentModal ? studentModal.querySelectorAll(".modal-tab-pane") : [];
    const studentModalTitle = document.getElementById("student-modal-title");


    // ===========================================
    // 4. CIRCULATION LOGIC (Declarations moved up)
    // ===========================================
    const borrowForm = document.getElementById("borrow-form");
    // NOTE: userSearchInput (borrow-user-search) is different from the main user search
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
    // FUNCTION DEFINITIONS (MOVED UP)
    // ===========================================
    
    const debounce = (func, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    };

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

    // --- MODIFIED: Function to load users ---
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

    /**
     * --- NEW: Function to switch STUDENT modal tabs ---
     */
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

    /**
     * --- NEW: Functions to populate student modal ---
     */
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
                    <td>
                        <span class="status-tag ${isOverdue ? 'tag-checkedout' : 'tag-available'}">
                            ${t.status}
                        </span>
                    </td>
                    <td>
                        <button class="action-btn return-book-btn" data-transaction-id="${t.transaction_id}">
                            Mark as Returned
                        </button>
                    </td>
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
                        <button class="action-btn issue-fine-btn" data-transaction-id="${t.transaction_id}">
                            Issue Fine
                        </button>
                        ${hasFine ? `<button class="action-btn waive-fine-btn" data-transaction-id="${t.transaction_id}">Waive Fine</button>` : ''}
                    </td>
                </tr>
            `;
        });
    };

    /**
     * --- NEW: Main function to load all student details ---
     */
    const loadStudentDetails = async (accountId) => {
        // 1. Reset modal
        studentModalTitle.textContent = "Loading...";
        switchStudentModalTab('student-profile-pane');
        document.getElementById("student-current-table-body").innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading...</td></tr>';
        document.getElementById("student-history-table-body").innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading...</td></tr>';
        document.getElementById("student-issue-fine-form").reset();
        
        // 2. Set account ID on forms
        studentModal.dataset.currentAccountId = accountId;

        // 3. Fetch data
        try {
            const response = await fetch(`../php/api/librarian.php?action=getStudentDetails&account_id=${accountId}`);
            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            // 4. Populate panes
            studentModalTitle.textContent = result.data.profile.name;
            populateStudentProfile(result.data.profile);
            populateStudentCurrent(result.data.currentBorrows);
            populateStudentHistory(result.data.history);

        } catch (error) {
            alert(`Error loading student details: ${error.message}`);
            studentModal.classList.remove("active");
        }
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
            if (targetId === "librarian-users-content") loadUsers(); // <-- This line is in your file

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
                } catch (error) {
                    alert("Failed to load book for editing: " + error.message);
                }
            }
        });
    }

    if (addCopyForm) {
        addCopyForm.addEventListener("submit", async (e) => {
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
                            alert(result.message);
                            loadArchive(archiveSearchInput ? archiveSearchInput.value : ""); 
                        } else {
                            alert("Error: " + result.message);
                        }
                    } catch (error) {
                        alert("An error occurred: " + error.message);
                    }
                }
            }
        });
    }

    if (archiveSearchInput) {
        archiveSearchInput.addEventListener("keyup", debounce(() => {
            loadArchive(archiveSearchInput.value);
        }, 300));
    }

    // --- MODIFIED: User Search ---
    if (userSearchInput) {
        userSearchInput.addEventListener("keyup", debounce(() => {
            loadUsers(userSearchInput.value);
        }, 300));
    }

    // --- MODIFIED: User Table Clicks ---
    if (userTableBody) {
        userTableBody.addEventListener("click", (e) => {
            const detailsBtn = e.target.closest(".view-details-btn");
            if (detailsBtn) {
                const accountId = detailsBtn.dataset.accountId;
                // --- NEW LOGIC ---
                if (studentModal) {
                    studentModal.classList.add("active");
                    loadStudentDetails(accountId);
                } else {
                    alert(`Error: Student modal not found. Cannot view details for #${accountId}`);
                }
                // --- END NEW ---
            }
        });
    }

    // --- NEW: Student Details Modal Listeners ---
    if (studentModal) {
        // Close modal
        studentModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("modal-overlay") || e.target.closest(".modal-close-btn")) {
                studentModal.classList.remove("active");
            }
        });

        // Tab switching
        studentModalTabs.addEventListener("click", (e) => {
            e.preventDefault();
            const tabButton = e.target.closest(".modal-tab-item");
            if (tabButton) {
                switchStudentModalTab(tabButton.dataset.pane);
            }
        });
        
        // --- Event Delegation for all modal actions ---
        studentModal.addEventListener("click", async (e) => {
            const accountId = studentModal.dataset.currentAccountId;

            // Toggle Status Button
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
                        alert(result.message);
                        await loadStudentDetails(accountId); // Reload profile pane
                        await loadUsers(userSearchInput.value); // Reload main user list
                    } catch (error) { alert(`Error: ${error.message}`); }
                }
            }

            // Mark as Returned Button
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
                        alert(result.message);
                        await loadStudentDetails(accountId); // Reload both panes
                    } catch (error) { alert(`Error: ${error.message}`); }
                }
            }

            // Waive Fine Button
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
                        alert(result.message);
                        await loadStudentDetails(accountId); // Reload history pane
                    } catch (error) { alert(`Error: ${error.message}`); }
                }
            }
            
            // Issue Fine (from History)
            if (e.target.classList.contains("issue-fine-btn")) {
                const transactionId = e.target.dataset.transactionId;
                switchStudentModalTab('student-actions-pane');
                document.getElementById('fine-transaction-id').value = transactionId;
                document.getElementById('fine-amount').focus();
            }
        });
        
        // Issue Fine (from Form)
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
                    alert(result.message);
                    issueFineForm.reset();
                    // Reload history pane
                    await loadStudentDetails(accountId);
                } catch (error) { alert(`Error: ${error.message}`); }
            });
        }
    }


    // ===========================================
    // 4. BOOK FORM IMAGE PREVIEW LOGIC (Event Listeners)
    // ===========================================
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
    if (borrowForm) {
        // Use borrowUserSearchInput here
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