/**
 * visitor2.js
 * Handles logic for the "Netflix-style" visitor portal (index2.php)
 * - Opens/Closes Book Detail Modal
 */

document.addEventListener("DOMContentLoaded", () => {
    const mainContent = document.querySelector(".main-content-netflix");
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
        // Find the book card that was clicked
        const bookCard = e.target.closest(".book-card-netflix");
        
        if (bookCard) {
            e.preventDefault(); // It's a link, so prevent it from adding '#' to URL
            const bookId = bookCard.dataset.bookId;
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
});


// =================================================================
// MODAL FUNCTIONS (Copied from visitor.js)
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