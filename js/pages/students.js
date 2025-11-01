/**
 * student.js
 * Handles SPA navigation and logic for the Student portal.
 */

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar-nav");
    const mainContent = document.querySelector(".main-content");
    const navbar = document.querySelector(".navbar-links"); // Added for navbar

    if (!sidebar || !mainContent || !navbar) {
        console.error("Sidebar, Navbar, or Main Content area not found.");
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

    // --- Navigation Handler (for Sidebar and Navbar) ---
    const handleNavClick = (e, navContainer) => {
        const navItem = e.target.closest(".nav-item, .nav-link");
        if (!navItem) return;

        e.preventDefault(); 
        
        const href = navItem.getAttribute("href");
        if (!href || href === "#") return; // Ignore empty links

        // Find the corresponding link in the *sidebar* to get the data-target
        const sidebarLink = sidebar.querySelector(`.nav-item[href="${href}"]`);
        if (!sidebarLink) return;

        const targetId = sidebarLink.dataset.target;
        if (!targetId) return;

        // Update active link in sidebar
        sidebar.querySelectorAll(".nav-item").forEach(item => {
            item.classList.remove(item.getAttribute("href") === href ? "active" : "active");
        });
        if(sidebarLink) sidebarLink.classList.add("active");
        
        // Update active link in navbar
        navbar.querySelectorAll(".nav-link").forEach(item => {
             item.classList.remove(item.getAttribute("href") === href ? "active" : "active");
        });
         if(navbar.querySelector(`.nav-link[href="${href}"]`)) {
            navbar.querySelector(`.nav-link[href="${href}"]`).classList.add("active");
         }

        // Switch the panel
        switchPanel(targetId);

        // Update URL hash
        window.location.hash = href;
    };

    // Handle sidebar navigation clicks
    sidebar.addEventListener("click", (e) => handleNavClick(e, sidebar));
    
    // Handle navbar navigation clicks
    navbar.addEventListener("click", (e) => handleNavClick(e, navbar));


    // --- Handle page load based on URL hash ---
    const currentHash = window.location.hash || "#dashboard"; // Default to dashboard
    let targetPanelId = "dashboard-content"; // Default

    const activeLink = sidebar.querySelector(`.nav-item[href="${currentHash}"]`);
    if (activeLink) {
        targetPanelId = activeLink.dataset.target;
        
        // Set correct links as active
        sidebar.querySelectorAll(".nav-item").forEach(item => item.classList.remove("active"));
        activeLink.classList.add("active");
        
        const activeNavLink = navbar.querySelector(`.nav-link[href="${currentHash}"]`);
        if (activeNavLink) {
            navbar.querySelectorAll(".nav-link").forEach(item => item.classList.remove("active"));
            activeNavLink.classList.add("active");
        }
    }
    
    // Show the initial panel
    switchPanel(targetPanelId);

    // --- Logout Logic ---
    const logoutButton = document.getElementById("logout-button"); // From navbar
    const logoutLink = document.getElementById("logout-link"); // From sidebar
    
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