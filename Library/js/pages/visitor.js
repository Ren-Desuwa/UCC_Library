/**
 * visitor.js
 * Handles ALL logic for the visitor portal (index.php AND about.php)
 * - Theme Toggle (Global)
 * - Mobile Nav (Global)
 * - Page-specific logic for index.php (Search, Sort, Modals)
 */

document.addEventListener("DOMContentLoaded", () => {

    // --- Global Elements (for all pages) ---
    const themeToggleBtn = document.getElementById("theme-toggle-btn");
    const mobileNavToggle = document.querySelector(".mobile-nav-toggle");
    const mobileNav = document.querySelector(".mobile-nav");

    // --- Index.php Page-Specific Elements ---
    const gridViewBtn = document.getElementById("grid-view-btn");
    const listViewBtn = document.getElementById("list-view-btn");
    const gridViewContainer = document.getElementById("catalogue-grid-view");
    const listViewContainer = document.getElementById("catalogue-list-view");
    const searchInput = document.getElementById("catalogue-search-input");
    const sortDropdown = document.getElementById("sort-dropdown");
    const tableBody = document.getElementById("catalogue-table-body");
    const gridBody = document.getElementById("catalogue-grid-body");

    // =================================================================
    // GLOBAL: THEME TOGGLE
    // =================================================================
    const applySavedTheme = () => {
        const savedTheme = localStorage.getItem("theme");
        if (savedTheme === "light") {
            document.body.classList.remove("theme-dark");
        } else {
            document.body.classList.add("theme-dark"); // Default to dark
        }
    };

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener("click", () => {
            if (document.body.classList.contains("theme-dark")) {
                document.body.classList.remove("theme-dark");
                localStorage.setItem("theme", "light");
            } else {
                document.body.classList.add("theme-dark");
                localStorage.setItem("theme", "dark");
            }
        });
    }
    applySavedTheme(); // Apply theme on page load

    // =================================================================
    // GLOBAL: MOBILE NAV TOGGLE
    // =================================================================
    if (mobileNavToggle && mobileNav) {
        mobileNavToggle.addEventListener("click", () => {
            mobileNav.classList.toggle("active");
        });
    }

    // =================================================================
    // PAGE-SPECIFIC: VIEW TOGGLE (Only on index.php)
    // =================================================================
    if (gridViewBtn && listViewBtn && gridViewContainer && listViewContainer) {
        gridViewBtn.addEventListener("click", () => {
            gridViewContainer.classList.add("active");
            listViewContainer.classList.remove("active");
            gridViewBtn.classList.add("active");
            listViewBtn.classList.remove("active");
        });

        listViewBtn.addEventListener("click", () => {
            listViewContainer.classList.add("active");
            gridViewContainer.classList.remove("active");
            listViewBtn.classList.add("active");
            gridViewBtn.classList.remove("active");
        });
    }

    // =================================================================
    // GLOBAL: MODAL LOGIC (for Book, Privacy, Terms)
    // =================================================================

    // --- Open Book Modal (Specific) ---
    document.addEventListener("click", async (e) => {
        const modalTrigger = e.target.closest(".open-book-modal-btn");
        if (modalTrigger) {
            const bookModal = document.getElementById("book-modal");
            const bookModalContent = bookModal.querySelector(".modal-content");
            if (bookModal && bookModalContent) {
                const bookId = modalTrigger.dataset.bookId;
                if (!bookId) return;
                await openBookModal(bookId, bookModal, bookModalContent);
            }
        }
    });

    // --- Open Text Modals (Generic) ---
    document.addEventListener("click", (e) => {
        const modalTrigger = e.target.closest(".modal-trigger-link");
        if (modalTrigger) {
            e.preventDefault(); // Stop link from jumping
            const modalId = modalTrigger.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        }
    });

    // --- Close ANY Modal (Generic) ---
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            // Check if click is on the overlay itself OR on ANY close button
            if (e.target.classList.contains('modal-overlay') ||
                e.target.closest('.modal-close-btn-icon') || // New text-modal close icon
                e.target.closest('.modal-close-btn') // Old book-modal close button
            ) {
                // Special handling for book-modal (to clear content)
                if (modal.id === 'book-modal') {
                    const bookModalContent = modal.querySelector('.modal-content');
                    if (bookModalContent) {
                        closeBookModal(modal, bookModalContent);
                    }
                } else {
                    // Generic close for all other modals
                    modal.classList.remove('active');
                }
            }
        });
    });

    // =================================================================
    // PAGE-SPECIFIC: SEARCH & SORT LOGIC (Only on index.php)
    // =================================================================
    if (searchInput && sortDropdown && tableBody && gridBody) {

        const debounce = (func, delay) => {
            let timeoutId;
            return (...args) => {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    func.apply(this, args);
                }, delay);
            };
        };

        const debouncedSearch = debounce(async (searchTerm, sortBy = 'title_asc') => {
            try {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Searching...</td></tr>';
                gridBody.innerHTML = '<p class="no-books-message">Searching...</p>';

                const baseUrl = `../php/api/catalogue.php?action=searchBooks&term=${encodeURIComponent(searchTerm)}&sort=${encodeURIComponent(sortBy)}`;

                const tableResponse = fetch(baseUrl);
                const gridResponse = fetch(`${baseUrl}&view=grid`);

                const [tableResult, gridResult] = await Promise.all([tableResponse, gridResponse]);

                if (!tableResult.ok || !gridResult.ok) {
                    throw new Error("One or more search requests failed");
                }

                const htmlTable = await tableResult.text();
                const htmlGrid = await gridResult.text();

                if (htmlTable.trim() === "") {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No books found.</td></tr>';
                } else {
                    tableBody.innerHTML = htmlTable;
                }

                if (htmlGrid.trim() === "") {
                    gridBody.innerHTML = '<p class="no-books-message">No books found.</p>';
                } else {
                    gridBody.innerHTML = htmlGrid;
                }

            } catch (error) {
                console.error("Search error:", error);
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error loading results.</td></tr>';
                gridBody.innerHTML = '<p class="no-books-message" style="color: red;">Error loading results.</p>';
            }
        }, 300);

        searchInput.addEventListener("keyup", (e) => {
            const searchTerm = e.target.value;
            debouncedSearch(searchTerm, sortDropdown.value);
        });

        sortDropdown.addEventListener("change", () => {
            debouncedSearch(searchInput.value, sortDropdown.value);
        });
    }
});


// =================================================================
// MODAL HELPER FUNCTIONS (Global)
// =================================================================
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
                <button type_button" class="modal-close-btn">Close</button>
            </div>`;
    }
}

function closeBookModal(bookModal, bookModalContent) {
    bookModal.classList.remove("active");
    setTimeout(() => {
        bookModalContent.innerHTML = "";
    }, 300);
}