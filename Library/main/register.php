<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Register</title>
    <!-- Updated to use the modular auth CSS -->
    <link rel="stylesheet" href="../css/pages/auth.css">
    <link rel="stylesheet" href="../css/shared/modals.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>
    <?php include 'includes/_modals_shared.php'; ?>
    

    <div class="register-container">
        <div class="register-card">
            <div class="left-panel">
                <div class="content-wrapper">
                    <h1 class="main-title">Welcome to Library Management System</h1>
                    <p class="description">Access library resources, check book availability, and manage your borrowed items through our comprehensive student library management system.</p>
                    
                    </div>
            </div>
            
            <div class="right-panel">
                <!-- UPDATED LOGO CONTAINER -->
                <div class="logo-container">
                    <!-- This is now a simple link with button behavior -->
                    <a href="index.php" class="logo-link" aria-label="Go to Visitor Home">
                        <img src="../assets/icons/LibraryMS_Logo.png" alt="Library Logo">
                    </a>
                </div>
                <!-- END UPDATED LOGO CONTAINER -->
                
                <div class="form-wrapper">
                    <h2 class="form-title">Student Registration</h2>
                    <p class="form-subtitle">Enter your details to create an account</p>
                    
                    <!-- Updated form with ID and name attributes for JS -->
                    <form class="registration-form" id="register-form">
                        
                        <div class="form-section-title">Personal Details</div>
                        <!-- NOTE: Added 'name' attributes to all inputs -->
                        <div class="form-group-triple">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="firstName">
                            </div>
                            <div class="form-group">
                                <label for="middleName">Middle Name</label>
                                <input type="text" id="middleName" name="middleName">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name (Suffix)</label>
                                <input type="text" id="lastName" name="lastName">
                            </div>
                        </div>

                        <div class="form-group">
                            <!-- NOTE: id/name is 'username' to match auth logic -->
                            <label for="username">Student ID (Username)</label>
                            <input type="text" id="username" name="username" placeholder="e.g., 20240131-C">
                        </div>
                        
                        <div class="form-section-title">Contact Details</div>

                        <sub>*Need one of either Email or Contact Number</sub>
                        <br>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>
                        
                        <div class="form-group">
                            <label for="contactNumber">Contact Number</label>
                            <input type="tel" id="contactNumber" name="contactNumber">
                        </div>

                        <div class="form-section-title">Account Security</div>
                        <div class="form-group-double">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password">
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword">
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="showPassword">
                                <label for="showPassword">Show Passwords</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="register-button">Register Account</button>
                        
                        <!-- Updated link to login.php -->
                        <a href="login.php" class="signin-link">Already have an account? Sign In</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/shared/ui.js"></script>
    <script type="module" src="../js/pages/auth.js"></script>
</body>
</html>

