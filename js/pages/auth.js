/**
 * auth.js
 * Handles logic for login.php and register.php
 */

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("login-form");
    const registerForm = document.getElementById("register-form");
    const showPasswordCheckbox = document.getElementById("showPassword");

    // Handle Login Form Submission
    if (loginForm) {
        loginForm.addEventListener("submit", handleLogin);
    }

    // Handle Registration Form Submission
    if (registerForm) {
        registerForm.addEventListener("submit", handleRegister);
    }

    // Handle "Show Password" Checkbox
    if (showPasswordCheckbox) {
        showPasswordCheckbox.addEventListener("change", togglePasswordVisibility);
    }
});

/**
 * Toggles password visibility for all password fields on the page.
 */
function togglePasswordVisibility() {
    const showPasswordCheckbox = document.getElementById("showPassword");
    const isChecked = showPasswordCheckbox.checked;

    // Find all password inputs on the page
    const passwordInputs = document.querySelectorAll(
        'input[type="password"], input[type="text"]'
    );

    passwordInputs.forEach(input => {
        // Only toggle inputs that are meant to be passwords
        if (input.name.includes("password") || input.name.includes("Password")) {
            input.type = isChecked ? "text" : "password";
        }
    });
}

/**
 * Handles the login form submission.
 * @param {Event} e The form submit event.
 */
async function handleLogin(e) {
    e.preventDefault(); // Prevent default form submission
    const form = e.target;
    const formData = new FormData(form);
    const button = form.querySelector('button[type="submit"]');

    // Basic validation
    if (!formData.get("username") || !formData.get("password")) {
        alert("Please enter both username and password.");
        return;
    }

    setButtonState(button, "Signing in...", true);

    try {
        // This is the API endpoint that would use AuthService.php
        const response = await fetch("../php/api/auth.php?action=login", {
            method: "POST",
            body: formData,
        });

        const result = await response.json();
        
        if (result.success) {
            // Success! Redirect to the main portal.
            window.location.href = "portal.php";
            document.getElementById("login-form").reset();
        } else {
            // Show error message from the server
            alert(`Login Failed: invalid username or password.`);
            //alert(`Login Failed: ${result.message}`);

        }
    } catch (error) {
        console.error("Login error:", error);
        alert("An error occurred while trying to log in. Please try again.");
    } finally {
        setButtonState(button, "Sign in", false);
    }
}

/**
 * Handles the registration form submission.
 * @param {Event} e The form submit event.
 */
async function handleRegister(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const button = form.querySelector('button[type="submit"]');

    // Client-side validation
    const password = formData.get("password");
    const confirmPassword = formData.get("confirmPassword");

    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
    }

    if (password.length < 8) {
        alert("Password must be at least 8 characters long.");
        return;
    }
    
    // You would add more validation here (e.g., email format, required fields)

    setButtonState(button, "Registering...", true);

    try {
        // This is the API endpoint that would use AuthService.php
        const response = await fetch("../php/api/auth.php?action=register", {
            method: "POST",
            body: formData,
        });

        const result = await response.json();

        if (result.success) {
            // Success! Redirect to the login page.
            alert("Registration successful! You can now log in.");
            window.location.href = "login.php";

        } else {
            // Show error message from the server
            alert(`Registration Failed: ${result.message}`);
        }
    } catch (error) {
        console.error("Registration error:", error);
        alert("An error occurred during registration. Please try again. test");
        
        alert(error.message);
    } finally {
        setButtonState(button, "Register Account", false);
    }
}

/**
 * Helper function to update button state during API calls.
 * @param {HTMLButtonElement} button The button element.
 * @param {string} text The text to display.
 * @param {boolean} disabled Whether to disable the button.
 */
function setButtonState(button, text, disabled) {
    if (button) {
        button.textContent = text;
        button.disabled = disabled;
    }
}