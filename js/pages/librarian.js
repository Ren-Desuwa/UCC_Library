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

    // Handle sidebar navigation clicks
    sidebar.addEventListener("click", (e) => {
        const navItem = e.target.closest(".nav-item");
        if (!navItem) return;

        e.preventDefault(); 

        // Get the target content panel ID from the 'data-target' attribute
        const targetId = navItem.dataset.target;
        if (!targetId) return;

        // Update active link in sidebar
        sidebar.querySelectorAll(".nav-item").forEach(item => {
            item.classList.remove("active");
        });
        navItem.classList.add("active");

        // Switch the panel
        switchPanel(targetId);

        // Update URL hash
        window.location.hash = navItem.getAttribute("href");
    });

    // Handle page load based on URL hash
    const currentHash = window.location.hash.substring(1); // e.g., "dashboard"
    let targetPanelId = "librarian-dashboard-content"; // Default
    
    if (currentHash) {
        const activeLink = sidebar.querySelector(`.nav-item[href="#${currentHash}"]`);
        if (activeLink) {
            targetPanelId = activeLink.dataset.target;
            // Set correct link as active
            sidebar.querySelectorAll(".nav-item").forEach(item => item.classList.remove("active"));
            activeLink.classList.add("active");
        }
    }
    
    // Show the initial panel
    switchPanel(targetPanelId);

    // TODO: Add logout logic
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
});