/**
 * visitor.js
 * Handles logic for the visitor portal (main/index.php)
 * - Opens/Closes Book Detail Modal
 * - Handles ADVANCED Live Search (Grid/Table switching)
 */

document.addEventListener("DOMContentLoaded", () => {
    const mainContent = document.querySelector(".main-content");
    const bookModal = document.getElementById("book-modal");
    
    // --- Modal Elements ---
    if (!bookModal) {
        console.error("Fatal Error: #book-modal element not found.");
        return;
    }
    const bookModalContent = bookModal.querySelector(".modal-content");
    if (!bookModalContent) {
        console.error("Fatal Error: .modal-content element not found in modal.");
        return;
    }

    // =================================================================
    // MODAL LOGIC
    // =================================================================

    // Event delegation for opening the book modal
    mainContent.addEventListener("click", async (e) => {
        const viewButton = e.target.closest(".open-book-modal-btn");
        
        if (viewButton) {
            e.preventDefault(); // Stop card links from navigating
            const bookId = viewButton.dataset.bookId;
            if (!bookId) return;

            await openBookModal(bookId, bookModal, bookModalContent);
        }
    });

    // Event for closing the modal
    bookModal.addEventListener("click", (e) => {
        if (
            e.target.classList.contains("modal-overlay") ||
            e.target.closest(".modal-close-btn")
        ) {
            closeBookModal(bookModal, bookModalContent);
        }
    });

    // =================================================================
    // CATALOGUE SEARCH LOGIC (Copied from student.js)
    // =================================================================
    initCataloguePage();
});

/**
 * Initializes all logic for the new catalogue page.
 */
function initCataloguePage() {
    // Use the IDs from index.php
    const searchInput = document.getElementById("catalogue-search-input"); 
    const gridView = document.getElementById("catalogue-grid-view");
    const tableView = document.getElementById("catalogue-table-view");
    const tableBody = document.getElementById("catalogue-table-body");
    const filterBtn = document.getElementById("filter-btn");
    const filterDropdown = document.getElementById("filter-dropdown");

    if (!searchInput || !gridView || !tableView || !tableBody || !filterBtn || !filterDropdown) {
        return; // Elements are missing
    }

    // --- Filter Dropdown Logic ---
    filterBtn.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent document click from closing it
        filterDropdown.classList.toggle("active");
    });

    document.addEventListener("click", () => {
        filterDropdown.classList.remove("active"); // Close on any click outside
    });

    filterDropdown.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        const filterType = e.target.dataset.filterType;
        if (filterType) {
            if (searchInput.value.length > 0 && !searchInput.value.endsWith(' ')) {
                searchInput.value += ' ';
            }
            searchInput.value += `${filterType}:`;
            filterDropdown.classList.remove("active");
            searchInput.focus(); 
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
            debouncedSearch(query);
        } else {
            gridView.style.display = "block";
            tableView.style.display = "none";
        }
    };

    searchInput.addEventListener("keyup", performSearch);
    
    /**
     * This "Discord-style" parser function
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


// =================================================================
// MODAL FUNCTIONS (Original from visitor.js)
// =================================================================

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
 * Fetches book details as HTML and opens the modal.
 */
async function openBookModal(bookId, bookModal, bookModalContent) {
    try {
        bookModalContent.innerHTML = '<p style="padding: 30px; text-align: center;">Loading...</p>';
        bookModal.classList.add("active");

        const response = await fetch(`../php/api/catalogue.php?action=getBookDetails&id=${bookId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const html = await response.text();
        bookModalContent.innerHTML = html;

    } catch (error) {
        console.error("Error fetching book details:", error);
        bookModalContent.innerHTML = `
            <div class="modal-header"><h2>Error</h2></div>
            <div class="modal-body">
                <p>Could not load book details. Please try again later.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-close-btn">Close</button>
            </div>`;
    }
}

/**
 * Closes the book modal.
 */
function closeBookModal(bookModal, bookModalContent) {
    bookModal.classList.remove("active");
    
    setTimeout(() => {
        bookModalContent.innerHTML = "";
    }, 300);
}