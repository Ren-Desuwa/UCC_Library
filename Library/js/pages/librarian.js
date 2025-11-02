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
    // 1. SPA NAVIGATION LOGIC (Original)
    // ===========================================

    // Function to switch content panels
    const switchPanel = (targetId) => {
        // Hide all panels
        mainContent.querySelectorAll(".content-panel").forEach(panel => {
            panel.classList.remove("active");
        });

        // Show the target panel
        const activePanel = document.getElementById(targetId);
        if (activePanel) {
            activePanel.classList.add("active");
            
            // --- MODIFICATION ---
            // If we are switching to the catalog, load its data.
            if (targetId === "librarian-catalog-content") {
                loadCatalog();
            }
            // --- END MODIFICATION ---

        } else {
            console.warn(`Content panel with ID "${targetId}" not found.`);
        }
    };

    // Handle sidebar navigation clicks
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

    // Handle page load based on URL hash
    const currentHash = window.location.hash.substring(1); // e.g., "dashboard"
    let targetPanelId = "librarian-dashboard-content"; // Default
    
    if (currentHash) {
        const activeLink = sidebar.querySelector(`.nav-item[href="#${currentHash}"]`);
        if (activeLink) {
            targetPanelId = activeLink.dataset.target;
            sidebar.querySelectorAll(".nav-item").forEach(item => item.classList.remove("active"));
            activeLink.classList.add("active");
        }
    }
    
    // Show the initial panel
    switchPanel(targetPanelId);
    
    // --- MODIFICATION ---
    // Call catalog load on initial page load if hash is #catalog
    if (targetPanelId === "librarian-catalog-content") {
        loadCatalog();
    }
    // --- END MODIFICATION ---


    // ===========================================
    // 2. LOGOUT LOGIC (Original)
    // ===========================================
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

    // ===========================================
    // 3. CATALOG & MODAL LOGIC (New)
    // ===========================================

    const catalogTableBody = document.getElementById("catalog-table-body");
    const bookModal = document.getElementById("book-form-modal");
    const bookForm = document.getElementById("book-form");
    const openBookModalBtn = document.getElementById("add-new-book-btn");
    const catalogContent = document.getElementById("librarian-catalog-content");

    // Function to load the catalog
    const loadCatalog = async () => {
        if (!catalogTableBody) return;
        
        catalogTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading books...</td></tr>';
        try {
            const response = await fetch("../php/api/librarian.php?action=getBooks");
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            const html = await response.text();
            catalogTableBody.innerHTML = html;
        } catch (error) {
            console.error("Failed to load catalog:", error);
            catalogTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error loading books.</td></tr>';
        }
    };

    // Open "Add Book" modal
    if (openBookModalBtn && bookModal) {
        openBookModalBtn.addEventListener("click", () => {
            bookForm.reset(); // Clear the form
            document.getElementById("book-modal-title").innerText = "Add New Book";
            document.getElementById("book-id").value = ""; // Clear any book ID
            bookModal.classList.add("active");
        });
    }

    // Close any modal
    if (bookModal) {
        bookModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("modal-overlay") || e.target.closest(".modal-close-btn")) {
                bookModal.classList.remove("active");
            }
        });
    }

    // Handle "Add Book" form submission
    if (bookForm) {
        bookForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(bookForm);
            
            // Determine if this is an add or edit
            const bookId = formData.get("book_id");
            const action = bookId ? 'updateBook' : 'addBook'; // (Note: updateBook API not built, but this shows how)
            formData.append('action', action);

            try {
                const response = await fetch("../php/api/librarian.php", {
                    method: "POST",
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    bookModal.classList.remove("active");
                    loadCatalog(); // Refresh the table!
                } else {
                    alert("Error: " + result.message);
                }
            } catch (error) {
                console.error("Failed to submit form:", error);
                alert("A critical error occurred.");
            }
        });
    }

    // TODO: Add event listener for delete/archive buttons
    if (catalogTableBody) {
        catalogTableBody.addEventListener("click", (e) => {
            const deleteBtn = e.target.closest(".delete-action-btn");
            if (deleteBtn) {
                const bookId = deleteBtn.dataset.bookId;
                if (confirm(`Are you sure you want to archive Book ID ${bookId}?`)) {
                    // TODO: Implement archiveBook(bookId) function
                    // async function archiveBook(bookId) {
                    //    const formData = new FormData();
                    //    formData.append('action', 'archiveBook');
                    //    formData.append('book_id', bookId);
                    //    const response = await fetch("../php/api/librarian.php", { method: "POST", body: formData });
                    //    const result = await response.json();
                    //    if (result.success) {
                    //        loadCatalog();
                    //    } else {
                    //        alert(result.message);
                    //    }
                    // }
                    console.log("TODO: Archive book", bookId);
                }
            }
        });
    }

    // ===========================================
    // 4. CIRCULATION LOGIC (New)
    // ===========================================

    // --- Circulation Elements ---
    const borrowForm = document.getElementById("borrow-form");
    const userSearchInput = document.getElementById("borrow-user-search");
    const userNameDiv = document.getElementById("borrow-user-name");
    const bookSearchInput = document.getElementById("borrow-book-search");
    const bookTitleDiv = document.getElementById("borrow-book-title");

    const returnForm = document.getElementById("return-form");
    const returnSearchInput = document.getElementById("return-book-search");
    const returnDetailsDiv = document.getElementById("return-details");

    // --- State for circulation ---
    let currentBorrowUser = null;
    let currentBorrowCopy = null;
    let currentReturnTransaction = null;

    // --- Borrow Form Logic ---
    if (borrowForm) {
        // Find user
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

        // Find book copy
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

        // Finalize checkout
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
                    // Reset form
                    borrowForm.reset();
                    userNameDiv.textContent = "...";
                    bookTitleDiv.textContent = "...";
                    currentBorrowUser = null;
                    currentBorrowCopy = null;
                }
            } catch (e) { alert(e.message); }
        });
    }

    // --- Return Form Logic ---
    if (returnForm) {
        // Find transaction
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

        // Process return
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
                    // Reset form
                    returnForm.reset();
                    returnDetailsDiv.style.display = "none";
                    currentReturnTransaction = null;
                }
            } catch (e) { alert(e.message); }
        });
    }

});