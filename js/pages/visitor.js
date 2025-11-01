/**
 * visitor.js
 * Handles logic for the visitor portal (main/index.php)
 * - Opens/Closes Book Detail Modal
 * - Handles Live Search
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

    // --- Search Elements ---
    const searchInput = document.getElementById("catalogue-search-input");
    const tableBody = document.getElementById("catalogue-table-body");

    if (!searchInput || !tableBody) {
        console.error("Search elements not found. Make sure #catalogue-search-input and #catalogue-table-body exist.");
        return;
    }

    // =================================================================
    // MODAL LOGIC
    // =================================================================

    // Event delegation for opening the book modal
    mainContent.addEventListener("click", async (e) => {
        // Find the button that was clicked, even if the icon inside it was clicked
        const viewButton = e.target.closest(".open-book-modal-btn");
        
        if (viewButton) {
            const bookId = viewButton.dataset.bookId;
            if (!bookId) return;

            await openBookModal(bookId, bookModal, bookModalContent);
        }
    });

    // Event for closing the modal
    bookModal.addEventListener("click", (e) => {
        // Close if clicking the dark overlay background or a close button
        if (
            e.target.classList.contains("modal-overlay") ||
            e.target.classList.contains("modal-close-btn")
        ) {
            closeBookModal(bookModal, bookModalContent);
        }
    });

    // =================================================================
    // SEARCH LOGIC (NEW)
    // =================================================================

    // Debounce function to limit how often the search API is called
    const debounce = (func, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    };

    // Create a debounced version of the search handler
    const debouncedSearch = debounce(async (searchTerm) => {
        try {
            // Show loading state
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Searching...</td></tr>';
            
            // Call the API
            const response = await fetch(`../php/api/catalogue.php?action=searchBooks&term=${encodeURIComponent(searchTerm)}`);
            if (!response.ok) {
                throw new Error("Search request failed");
            }

            const html = await response.text();
            
            // Update table
            if (html.trim() === "") {
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No books found matching your search.</td></tr>';
            } else {
                tableBody.innerHTML = html;
            }

        } catch (error) {
            console.error("Search error:", error);
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error loading results.</td></tr>';
        }
    }, 300); // Wait 300ms after user stops typing

    // Listen for typing in the search bar
    searchInput.addEventListener("keyup", (e) => {
        const searchTerm = e.target.value;
        debouncedSearch(searchTerm);
    });
});


// =================================================================
// MODAL FUNCTIONS
// =================================================================

/**
 * Fetches book details as HTML and opens the modal.
 * @param {string} bookId The ID of the book to fetch.
 * @param {HTMLElement} bookModal The modal overlay element.
 * @param {HTMLElement} bookModalContent The modal content area.
 */
async function openBookModal(bookId, bookModal, bookModalContent) {
    try {
        // Show a temporary loading state
        bookModalContent.innerHTML = '<p style="padding: 30px; text-align: center;">Loading...</p>';
        bookModal.classList.add("active");

        // Fetch the modal HTML from the API endpoint
        const response = await fetch(`../php/api/catalogue.php?action=getBookDetails&id=${bookId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const html = await response.text();

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
 * @param {HTMLElement} bookModalContent The modal content area.
 */
function closeBookModal(bookModal, bookModalContent) {
    bookModal.classList.remove("active");
    
    // Clear content after closing animation (300ms) to ensure it's fresh next time
    setTimeout(() => {
        bookModalContent.innerHTML = "";
    }, 300);
}