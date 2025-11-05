/**
 * librarian.js
 * Handles SPA navigation and logic for the Librarian portal.
 */

document.addEventListener("DOMContentLoaded", () => {
    // --- 1. SPA NAVIGATION LOGIC (FIXED) ---
    // Select the new nav bars
    const mainNav = document.querySelector(".portal-nav");
    const mobileNav = document.querySelector(".mobile-nav");
    const mainContent = document.querySelector(".main-content");

    if (!mainNav || !mobileNav || !mainContent) {
        console.error("Navigation or Main Content area not found.");
        return;
    }

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
        } else {
            console.warn(`Content panel with ID "${targetId}" not found.`);
        }
    };

    // Function to handle clicks on any nav link
    const handleNavClick = (e) => {
        const navItem = e.target.closest(".nav-item");
        if (!navItem) return;

        e.preventDefault();
        const href = navItem.getAttribute("href");
        if (!href || href === "#") return;

        const targetId = navItem.dataset.target;
        if (!targetId) return;

        // Update active class on BOTH navs
        document.querySelectorAll(`.nav-item[href="${href}"]`).forEach(link => {
            // Remove 'active' from all siblings
            const parentNav = link.closest('.portal-nav, .mobile-nav');
            parentNav.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove("active");
            });
            // Add 'active' to the clicked link
            link.classList.add("active");
        });

        switchPanel(targetId);
        window.location.hash = href;
    };

    // Attach listeners to both nav bars
    mainNav.addEventListener("click", handleNavClick);
    mobileNav.addEventListener("click", handleNavClick);

    // Handle page load based on URL hash
    const currentHash = window.location.hash.substring(1); // e.g., "dashboard"
    let targetPanelId = "librarian-dashboard-content"; // Default
    let hashSelector = "#dashboard";

    if (currentHash) {
        hashSelector = "#" + currentHash;
    }

    // Find the link in the main nav to get the data-target
    const activeLink = mainNav.querySelector(`.nav-item[href="${hashSelector}"]`);

    if (activeLink) {
        targetPanelId = activeLink.dataset.target;

        // Set active class on both navs
        document.querySelectorAll(`.nav-item[href="${hashSelector}"]`).forEach(link => {
            const parentNav = link.closest('.portal-nav, .mobile-nav');
            parentNav.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove("active");
            });
            link.classList.add("active");
        });
    }

    // Show the initial panel
    switchPanel(targetPanelId);

    // --- 2. LOGOUT LOGIC (Unchanged) ---
    const logoutButton = document.getElementById("logout-button"); // Legacy?
    const logoutLink = document.getElementById("logout-link"); // Main link

    const handleLogout = async (e) => {
        e.preventDefault();
        if (!confirm("Are you sure you want to log out?")) return;

        try {
            const response = await fetch("../php/api/auth.php?action=logout", {
                method: "POST"
            });
            const result = await response.json();

            if (result.success) {
                window.location.href = "login.php";
            } else {
                alert("Logout failed: " + result.message);
            }
        } catch (err) {
            alert("An error occurred during logout.");
        }
    };

    if (logoutButton) logoutButton.addEventListener("click", handleLogout);
    if (logoutLink) logoutLink.addEventListener("click", handleLogout);
});