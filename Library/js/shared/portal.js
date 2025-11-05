/**
 * portal.js
 * Handles shared logic for the internal portal:
 * - New User Dropdown Menu
 * - Mobile Menu population and toggle
 */

document.addEventListener("DOMContentLoaded", () => {

    // === 1. User Dropdown Menu ===
    const userMenuToggle = document.getElementById("user-menu-toggle");
    const userMenuDropdown = document.getElementById("user-menu-dropdown");

    if (userMenuToggle && userMenuDropdown) {
        userMenuToggle.addEventListener("click", (e) => {
            e.stopPropagation(); // Stop click from bubbling up to document
            userMenuDropdown.classList.toggle("active");
            userMenuToggle.classList.toggle("active");
        });

        // Close dropdown if clicking anywhere else
        document.addEventListener("click", (e) => {
            if (!userMenuDropdown.contains(e.target) && !userMenuToggle.contains(e.target)) {
                userMenuDropdown.classList.remove("active");
                userMenuToggle.classList.remove("active");
            }
        });

        // === THIS IS THE FIX ===
        // Add a click listener for the theme toggle LINK in the dropdown
        const themeLink = userMenuDropdown.querySelector("#theme-toggle-link");
        if (themeLink) {
            themeLink.addEventListener("click", (e) => {
                e.preventDefault();
                // Find the real, hidden button and click it
                const themeBtn = document.getElementById("theme-toggle-btn");
                if (themeBtn) {
                    themeBtn.click(); // This triggers the logic in visitor.js
                }
            });
        }
        // === END OF FIX ===
    }

    // === 2. Mobile Menu ===
    const mobileNavToggle = document.querySelector(".mobile-nav-toggle");
    const mobileNav = document.querySelector(".mobile-nav");

    if (mobileNavToggle && mobileNav) {

        // 1. Clone links and user info into mobile menu
        const desktopNav = document.querySelector(".portal-nav");
        // Find links in the new dropdown menu
        const desktopDropdown = document.getElementById("user-menu-dropdown");

        if (desktopNav) {
            // Clone all navigation items
            const navItems = desktopNav.querySelectorAll(".nav-item");
            navItems.forEach(item => {
                mobileNav.appendChild(item.cloneNode(true));
            });
        }

        if (desktopDropdown) {
            // Clone user info, theme toggle, and logout link from the dropdown
            const userInfo = desktopDropdown.querySelectorAll(".user-info-dropdown");
            const dividers = desktopDropdown.querySelectorAll(".dropdown-divider");
            const themeLink = desktopDropdown.querySelector("#theme-toggle-link");
            const logoutLink = desktopDropdown.querySelector("#logout-link");

            // Add a divider
            mobileNav.appendChild(dividers[0].cloneNode(true));

            userInfo.forEach(info => {
                mobileNav.appendChild(info.cloneNode(true));
            });

            // Add a divider
            mobileNav.appendChild(dividers[1].cloneNode(true));

            if (themeLink) mobileNav.appendChild(themeLink.cloneNode(true));
            if (logoutLink) mobileNav.appendChild(logoutLink.cloneNode(true));
        }

        // 2. Add click event for the toggle button
        mobileNavToggle.addEventListener("click", () => {
            mobileNav.classList.toggle("active");
        });

        // 3. Add click event to all links inside mobile menu to close it
        mobileNav.addEventListener("click", (e) => {
            // Close menu if a nav link is clicked
            if (e.target.closest("a.nav-item")) {
                mobileNav.classList.remove("active");
            }

            // Handle theme toggle click
            if (e.target.closest("#theme-toggle-link")) {
                e.preventDefault();
                // We just need to trigger the *real* theme toggle
                // The visitor.js file will handle the actual theme change
                document.getElementById("theme-toggle-btn").click();
            }

            // Handle logout click
            if (e.target.closest("#logout-link")) {
                e.preventDefault();
                // The student.js/librarian.js file will handle the logout logic
                // We just need to trigger its click
                document.getElementById("logout-link").click();
                mobileNav.classList.remove("active");
            }
        });
    }
});