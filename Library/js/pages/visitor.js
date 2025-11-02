/**
 * visitor.js
 * Handles logic for the visitor portal (main/index.php)
 * - Opens/Closes Book Detail Modal
 * - Handles ADVANCED Live Search (Grid/Table switching)
 */

// Import shared logic
import { initCataloguePage, openBookModal, closeBookModal } from '../shared/catalogue.js';

document.addEventListener("DOMContentLoaded", () => {
    const mainContent = document.querySelector(".main-content");
    const bookModal = document.getElementById("book-modal");
    
    // --- Modal Elements ---
    if (!bookModal) {
        console.error("Fatal Error: #book-modal element not found.");
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

            // Use the shared modal function
            await openBookModal(bookId, bookModal);
        }
    });

    // Event for closing the modal
    bookModal.addEventListener("click", (e) => {
        if (
            e.target.classList.contains("modal-overlay") ||
            e.target.closest(".modal-close-btn")
        ) {
            // Use the shared modal function
            closeBookModal(bookModal);
        }
    });

    // =================================================================
    // CATALOGUE SEARCH LOGIC (Copied from student.js)
    // =================================================================
    
    // Initialize the shared catalogue logic
    initCataloguePage({
        searchInputId: "catalogue-search-input",
        gridViewId: "catalogue-grid-view",
        tableViewId: "catalogue-table-view",
        tableBodyId: "catalogue-table-body",
        filterBtnId: "filter-btn",
        filterDropdownId: "filter-dropdown"
    });
});

// All duplicated functions (debounce, openBookModal, closeBookModal, initCataloguePage, buildSearchQuery)
// have been removed and are now imported from ../shared/catalogue.js