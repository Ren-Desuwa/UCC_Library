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
    
    // --- NEW: "See All" Modal Elements ---
    const seeAllModal = document.getElementById("see-all-modal");
    const seeAllModalTitle = document.getElementById("see-all-modal-title");
    const seeAllModalBody = document.getElementById("see-all-modal-body");

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

        // --- NEW: "See All" Modal Trigger ---
        const seeAllButton = e.target.closest(".open-see-all-modal-btn");
        if (seeAllButton) {
            e.preventDefault();
            const genre = seeAllButton.dataset.genre;
            if (!genre || !seeAllModal) return;

            // 1. Open the modal and set title
            seeAllModalTitle.innerText = genre;
            seeAllModalBody.innerHTML = '<p style="padding: 30px; text-align: center;">Loading...</p>';
            seeAllModal.classList.add("active");

            // 2. Fetch the content
            try {
                const response = await fetch(`../php/api/catalogue.php?action=getGenreShelf&genre=${encodeURIComponent(genre)}`);
                if (!response.ok) throw new Error("Failed to load shelf.");
                const html = await response.text();
                seeAllModalBody.innerHTML = html;
            } catch (error) {
                seeAllModalBody.innerHTML = `<p style="padding: 30px; text-align: center; color: red;">${error.message}</p>`;
            }
        }
    });

    // Event for closing the book modal
    bookModal.addEventListener("click", (e) => {
        if (
            e.target.classList.contains("modal-overlay") ||
            e.target.closest(".modal-close-btn")
        ) {
            // Use the shared modal function
            closeBookModal(bookModal);
        }
    });

    // --- NEW: Event for closing the "See All" modal ---
    if (seeAllModal) {
        seeAllModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("modal-overlay") || e.target.closest(".modal-close-btn")) {
                seeAllModal.classList.remove("active");
                // Clear the body after it closes
                setTimeout(() => {
                    seeAllModalBody.innerHTML = '';
                    seeAllModalTitle.innerText = 'All Books';
                }, 300);
            }
        });
    }

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