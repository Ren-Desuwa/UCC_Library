/**
 * catalogue.js
 * * Shared logic for the visitor and student catalogue pages.
 * - Handles advanced search parsing
 * - Handles filter dropdown logic
 * - Handles opening/closing the book detail modal
 * - Provides a 'debounce' utility
 */

// --- UTILITIES ---

/**
 * Debounce function
 * @param {function} func The function to debounce.
 * @param {number} delay The delay in milliseconds.
 */
export function debounce(func, delay) {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}


// --- MODAL LOGIC ---

/**
 * Fetches book details as HTML and opens the modal.
 * @param {string} bookId The ID of the book to fetch.
 * @param {HTMLElement} bookModal The modal overlay element.
 * @param {function} [customizeModalHTML] Optional function to modify the HTML before injection.
 */
export async function openBookModal(bookId, bookModal, customizeModalHTML = null) {
    const bookModalContent = bookModal.querySelector(".modal-content");
    if (!bookModalContent) return;

    try {
        // Show a temporary loading state
        bookModalContent.innerHTML = '<p style="padding: 30px; text-align: center;">Loading...</p>';
        bookModal.classList.add("active");

        // Fetch the modal HTML from the API endpoint
        const response = await fetch(`../php/api/catalogue.php?action=getBookDetails&id=${bookId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        let html = await response.text();

        // Allow the caller to customize the HTML (e.g., student changing buttons)
        if (typeof customizeModalHTML === 'function') {
            html = customizeModalHTML(html);
        }

        // Inject the fetched HTML into the modal
        bookModalContent.innerHTML = html;

    } catch (error) {
        console.error("Error fetching book details:", error);
        // Display a user-friendly error inside the modal
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
 * @param {HTMLElement} bookModal The modal overlay element.
 */
export function closeBookModal(bookModal) {
    const bookModalContent = bookModal.querySelector(".modal-content");
    
    bookModal.classList.remove("active");
    
    // Clear content after closing animation (300ms) to ensure it's fresh next time
    setTimeout(() => {
        if (bookModalContent) {
            bookModalContent.innerHTML = "";
        }
    }, 300);
}


// --- SEARCH LOGIC ---

/**
 * This "Discord-style" parser function
 * @param {string} inputValue The raw text from the search input.
 */
function buildSearchQuery(inputValue) {
    let text = inputValue;
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

/**
 * Initializes all logic for a catalogue page.
 * @param {string} searchInputId 
 * @param {string} gridViewId 
 * @param {string} tableViewId 
 * @param {string} tableBodyId 
 * @param {string} filterBtnId 
 * @param {string} filterDropdownId 
 */
export function initCataloguePage({
    searchInputId,
    gridViewId,
    tableViewId,
    tableBodyId,
    filterBtnId,
    filterDropdownId
}) {
    const searchInput = document.getElementById(searchInputId);
    const gridView = document.getElementById(gridViewId);
    const tableView = document.getElementById(tableViewId);
    const tableBody = document.getElementById(tableBodyId);
    const filterBtn = document.getElementById(filterBtnId);
    const filterDropdown = document.getElementById(filterDropdownId);

    if (!searchInput || !gridView || !tableView || !tableBody || !filterBtn || !filterDropdown) {
        console.warn("Catalogue elements missing, search not initialized.");
        return; 
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
        const query = buildSearchQuery(searchInput.value);
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
}