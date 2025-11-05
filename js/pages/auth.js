/**
 * auth.js
 * Handles logic for login.php, register.php, and forgot_password.php
 */

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("login-form");
    const registerForm = document.getElementById("register-form");
    const forgotPasswordForm = document.getElementById("forgot-password-form");
    const resetPasswordForm = document.getElementById("reset-password-form"); // NEW
    const showPasswordCheckbox = document.getElementById("showPassword");

    // Handle Login Form Submission
    if (loginForm) {
        loginForm.addEventListener("submit", handleLogin);
    }

    // Handle Registration Form Submission
    if (registerForm) {
        registerForm.addEventListener("submit", handleRegister);
    }

    // Handle Forgot Password Form Submission
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener("submit", handleForgotPassword);
    }

    // NEW: Handle Reset Password Form Submission
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener("submit", handleResetPassword);
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
        if (input.name.includes("password") || input.name.includes("Password") || input.name.includes("new_password")) {
            input.type = isChecked ? "text" : "password";
        }
    });
}

/**
 * Handles the login form submission.
 * @param {Event} e The form submit event.
 */
async function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const button = form.querySelector('button[type="submit"]');

    if (!formData.get("username") || !formData.get("password")) {
        alert("Please enter both username and password.");
        return;
    }
    setButtonState(button, "Signing in...", true);
    try {
        const response = await fetch("../php/api/auth.php?action=login", {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            window.location.href = "portal.php";
        } else {
            alert(`Login Failed: ${result.message}`);
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

    setButtonState(button, "Registering...", true);
    try {
        const response = await fetch("../php/api/auth.php?action=register", {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            alert("Registration successful! You can now log in.");
            window.location.href = "login.php";
        } else {
            alert(`Registration Failed: ${result.message}`);
        }
    } catch (error) {
        console.error("Registration error:", error);
        alert("An error occurred during registration. Please try again.");
    } finally {
        setButtonState(button, "Register Account", false);
    }
}

/**
 * === UPDATED: Handles the forgot password form submission ===
 * @param {Event} e The form submit event.
 */
async function handleForgotPassword(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const button = form.querySelector('button[type="submit"]');

    const identifier = formData.get("recovery_identifier");
    if (!identifier) {
        alert("Please enter your username or email.");
        return;
    }
    setButtonState(button, "Sending...", true);
    try {
        const response = await fetch("../php/api/auth.php?action=forgotPassword", {
            method: "POST",
            body: formData,
        });
        const result = await response.json();

        // Show the generic message from the server
        alert(result.message);

        if (result.success) {
            // Redirect to the reset page so the user can enter the code
            window.location.href = 'reset_password.php';
        }
    } catch (error) {
        console.error("Forgot Password error:", error);
        alert("An error occurred. Please try again.");
    } finally {
        setButtonState(button, "Send Reset Link", false);
    }
}

/**
 * Handles the reset password form submission.
 * @param {Event} e The form submit event.
 */
async function handleResetPassword(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const button = form.querySelector('button[type="submit"]');

    // Basic validation
    const newPassword = formData.get("new_password");
    const confirmPassword = formData.get("confirm_password");

    if (newPassword !== confirmPassword) {
        alert("New passwords do not match.");
        return;
    }
    if (newPassword.length < 8) {
        alert("Password must be at least 8 characters long.");
        return;
    }

    setButtonState(button, "Resetting...", true);
    try {
        const response = await fetch("../php/api/auth.php?action=resetPassword", {
            method: "POST",
            body: formData,
        });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            window.location.href = 'login.php';
        } else {
            alert(`Error: ${result.message}`);
        }
    } catch (error) {
        console.error("Reset Password error:", error);
        alert("An error occurred. Please try again.");
    } finally {
        setButtonState(button, "Set New Password", false);
    }
}


/**
 * Helper function to update button state during API calls.
 */
function setButtonState(button, text, disabled) {
    if (button) {
        button.textContent = text;
        button.disabled = disabled;
    }
}