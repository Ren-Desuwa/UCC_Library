# Library-Management-Sys
Library System Refactor & Feature Implementation Plan
-----------------------------------------------------

This document outlines the step-by-step plan to refactor the JavaScript frontend and implement new features connecting the PHP backend and the HTML/JS frontend.

==================================================
PART 1: JAVASCRIPT FILE REFACTORING
==================================================

The current `JS/` folder has large files (e.g., `auth.js`, `librarian.js`) with duplicated logic. We will split these into a modular, maintainable structure.

The new `JS/` folder structure will be:

JS/
├── api/
│   ├── apiClient.js     (No change - This is good)
│   ├── adminApi.js
│   ├── authApi.js
│   ├── bookApi.js
│   ├── borrowApi.js
│   └── userApi.js
│
├── modules/
│   ├── admin/
│   │   ├── dashboard.js
│   │   ├── staffManagement.js
│   │   └── accountManagement.js (NEW - For pending requests)
│   │   └── system.js
│   ├── librarian/
│   │   ├── announcements.js
│   │   ├── catalog.js
│   │   ├── dashboard.js
│   │   ├── studentManagement.js (NEW - Will add 'Pending' tab)
│   │   └── transactions.js      (NEW - Will add 'Book Search' & 'Copy Modal')
│   └── student/
│       ├── dashboard.js
│       ├── history.js
│       └── search.js
│
├── shared/
│   ├── modals.js        (NEW - Reusable logic for opening/closing all modals)
│   ├── navigation.js    (NEW - The SPA navigation logic, used by all portals)
│   └── settings.js      (NEW - The "Edit/Save" toggle logic for settings panels)
│
├── admin.js             (REFACTORED - Main file for AdminPortal, imports modules)
├── auth.js              (REFACTORED - Handles only Login.html, Register.html, Forgot*)
├── librarian.js         (REFACTORED - Main file for LibrarianPortal, imports modules)
├── student.js           (REFACTORED - Main file for StudentPortal, imports modules)
└── visitor.js           (NEW - Handles only index.html catalog logic)

This separates responsibilities. For example, `JS/auth.js` will now ONLY handle login/register. The visitor catalog logic it used to have will move to `JS/visitor.js`. `JS/librarian.js` will become a small file that imports its features from the `JS/modules/librarian/` folder.

==================================================
PART 2: NEW FEATURE IMPLEMENTATION PLAN
==================================================

Here are the step-by-step changes for each new feature.

---
### Feature 1: Pending Student Registration
**Goal:** `Register.html` creates a 'Pending' student. A librarian must approve them in a new panel.

#### 1. DB Change (PHP)
1.  **Modify `Account` Table:** Add a new `Status` column. The `IsActive` column will now ONLY be used for "banned".
    * Find `models/AccountDAO.php`.
    * Add `Status` (VARCHAR, default 'Pending') to the `CREATE TABLE` schema (if you were re-creating it).
    * Update `AccountDAO::insert`: Add `Status` to the `INSERT` query.
    * Update `AccountDAO::mapToAccount`: Add `'Status' => $row['Status']` to the mapped array.
    * Update `AccountDAO::update`: Add `Status = :status` to the `UPDATE` query.

#### 2. Backend (PHP)
1.  **Create Public Registration Route:**
    * Open `Api/routes/auth.php`.
    * Add a new route: `case 'register': ...`. This will handle `POST /auth/register`.
    * This route will call a new method: `AuthenticationService::registerPendingStudent($input)`.
2.  **Create New Service Method:**
    * Open `Services/AuthenticationService.php`.
    * Add `public function registerPendingStudent($input)`:
        * It will validate `Name`, `Username` (from Student ID), and `Password`.
        * It will call `$this->db->accountDAO->insert()` with `Role = 'Student'` and `Status = 'Pending'`.
3.  **Modify Librarian User-Fetching Route:**
    * Open `Api/routes/librarian.php`.
    * Modify the `case 'users':` (for `GET /librarian/users`):
        * It should check for a query parameter: `$status = $query['status'] ?? 'Active'`.
        * It will call `UserManagementService::getAllUsers($role = 'Student', $status)`.
4.  **Modify User Service:**
    * Open `Services/UserManagementService.php`.
    * Modify `public function getAllUsers($role = 'Student')` to `public function getAllUsers($role = 'Student', $status = 'Active')`.
    * Update the logic inside to filter by both `Role` AND `Status`.
5.  **Create Librarian Approval Route:**
    * Open `Api/routes/librarian.php`.
    * Add a new endpoint, e.g., `case 'users/approve':`
    * This will handle `PUT /librarian/users/approve`.
    * It will call a new method: `UserManagementService::approveStudentAccount($input['account_id'])`.
6.  **Create New Approval Service:**
    * Open `Services/UserManagementService.php`.
    * Add `public function approveStudentAccount($accountId)`:
        * It fetches the account by ID.
        * It verifies the account is 'Pending'.
        * It sets `Status = 'Active'` and calls `$this->db->accountDAO->update($account)`.

#### 3. Frontend (HTML)
1.  **Librarian Portal:**
    * Open `LibrarianPortal.html`.
    * In `#student-management-content`, add tabs (e.g., `<div class="tabs"><button data-target="active">Active Students</button><button data-target="pending">Pending Approvals</button></div>`).
    * The existing table will be for "Active".
    * Add a new, identical table (e.g., `id="pending-students-table"`) for "Pending" users. This table will need an "Approve" button instead of a "View" button.

#### 4. Frontend (JS)
1.  **Registration Page:**
    * `JS/auth.js`: The `initRegisterPage` form submission will now call a new API function: `authApi.registerStudent(registrationData)`.
    * `JS/api/authApi.js`: This `registerStudent` function (which already exists but hits the wrong endpoint) will be modified to hit `POST /auth/register`.
2.  **Librarian Portal:**
    * `JS/librarian.js`: Will import a new module: `import * as studentManagement from './modules/librarian/studentManagement.js'`.
    * `JS/modules/librarian/studentManagement.js` (New File):
        * This file will contain the logic for the "Student Management" panel.
        * It will add click listeners to the new tabs.
        * "Active" tab click: Calls `userApi.getAllUsers('Student', 'Active')` and renders to the active table.
        * "Pending" tab click: Calls `userApi.getAllUsers('Student', 'Pending')` and renders to the pending table.
        * The "Approve" button in the pending table will call a new API function: `userApi.approveStudent(accountId)`.
    * `JS/api/userApi.js`:
        * Modify `getAllUsers` to `getAllUsers(role, status)` and update the endpoint to use query params.
        * Add `async function approveStudent(accountId)` to hit `PUT /librarian/users/approve`.

---
### Feature 2: "Forgot Password" Admin Panel
**Goal:** `ForgotPassword*.html` creates a 'Pending' request. Admin approves it in a new panel.

#### 1. DB Change (PHP)
1.  **New Table:** Create a new table: `PasswordResetRequest`.
    * Columns: `RequestID` (INT, PK, AI), `AccountID` (INT, FK to `Account`), `Status` (VARCHAR, 'Pending'/'Resolved'), `Timestamp` (DATETIME).

#### 2. Backend (PHP)
1.  **Create Public Request Route:**
    * Open `Api/routes/auth.php`.
    * Add new route: `case 'forgot-password': ...`. This handles `POST /auth/forgot-password`.
    * This calls a new method: `AuthenticationService::requestPasswordReset($input['username'])`.
2.  **Create New Service Method:**
    * Open `Services/AuthenticationService.php`.
    * Add `public function requestPasswordReset($username)`:
        * Finds `Account` by `Username` using `accountDAO->getByUsername()`.
        * If found, inserts a new row into `PasswordResetRequest` with the `AccountID` and `Status = 'Pending'`.
3.  **Create Admin Management Panel Routes:**
    * Open `Api/routes/admin.php`.
    * Add new route `case 'password-requests':` to handle `GET /admin/password-requests?status=pending`. This will call `AdminAccountService::getPendingResets()`.
    * Add new route `case 'password-requests/resolve':` to handle `POST /admin/password-requests/resolve`. This will call `AdminAccountService::resolvePasswordRequest($input['request_id'])`.
    * The *existing* route `case 'accounts': ... subRoute 'reset-password':` (which handles `POST /admin/accounts/reset-password`) is still needed.
4.  **Create New Admin Service Methods:**
    * Open `Services/Admin/AdminAccountService.php`.
    * Add `public function getPendingResets()`: Fetches all requests from `PasswordResetRequest` with `Status = 'Pending'`.
    * Add `public function resolvePasswordRequest($requestId)`: Sets the `PasswordResetRequest` row's `Status` to `'Resolved'`.

#### 3. Frontend (HTML)
1.  **Admin Portal:**
    * Open `AdminPortal.html`.
    * Add a new nav item: `<a href="#account-management" ... data-target="account-management-content">... Account Management</a>`.
    * Add a new content panel: `<div id="account-management-content" class="content-panel">...</div>`.
    * Inside this panel, add a table (`id="pending-resets-table"`) to list pending requests. It needs a "Resolve" button.

#### 4. Frontend (JS)
1.  **Forgot Password Pages:**
    * `JS/auth.js`: The forms on `ForgotPassword*.html` will be wired up to call a new API function: `authApi.requestPasswordReset(username)`.
    * `JS/api/authApi.js`: Add `async function requestPasswordReset(username)` to hit `POST /auth/forgot-password`.
2.  **Admin Portal:**
    * `JS/admin.js`: Will import and run logic from `JS/modules/admin/accountManagement.js`.
    * `JS/modules/admin/accountManagement.js` (New File):
        * This file will load when the `#account-management-content` panel is shown.
        * It will call `adminApi.getPendingResets()` and render the table.
        * The "Resolve" button will open a modal, ask for a *new* password, and then call **two** APIs:
            1.  `adminApi.resetUserPassword(accountId, newPassword)` (the existing API to change the pass).
            2.  `adminApi.resolvePasswordRequest(requestId)` (the new API to close the request).
    * `JS/api/adminApi.js`: Add `getPendingResets()` and `resolvePasswordRequest()`.

---
### Feature 3: Unified "Add Account" Modals
**Goal:** Admin has a master "Add Account" modal. Librarian has a simple "Add Student" modal.

#### 1. Backend (PHP)
1.  **Refactor Admin Account Creation:**
    * Open `Api/routes/admin.php`.
    * Change `case 'librarians':` (for `POST`) to `case 'accounts':` (for `POST`). This route is now `POST /admin/accounts`.
    * This route will now call `AdminAccountService::createAccount($input)`.
2.  **Refactor Admin Service:**
    * Open `Services/Admin/AdminAccountService.php`.
    * Rename `createLibrarianAccount` to `createAccount($input)`.
    * This method will read `$input['Role']`, `$input['Name']`, `$input['Username']`, `$input['Password']`.
    * It will set `Status = ($input['Role'] === 'Student') ? 'Pending' : 'Active'`.
    * It will then call `AccountDAO::insert`.
3.  **Librarian Account Creation (No Change):**
    * The `POST /librarian/users` route (handled by `UserManagementService::createStudentAccount`) is already correct. It will create a pending student, which is what we want.

#### 2. Frontend (HTML)
1.  **Admin Portal:**
    * Open `AdminPortal.html`.
    * Rename the modal `id="librarian-modal"` to `id="account-form-modal"`.
    * Change the modal title to "Create New Account".
    * Rework the form: Remove all fields (License No, Employee ID, etc.).
    * Add new fields:
        * `<label for="account-role">Role</label><select id="account-role"><option value="Student">Student</option><option value="Librarian">Librarian</option><option value="Admin">Admin</option></select>`
        * `<label for="account-name">Name</label><input id="account-name">`
        * `<label for="account-username">Student/Staff ID (Username)</label><input id="account-username">`
        * `<label for="account-password">Password</label><input id="account-password" type="password">`
        * `<label for="account-email">Email</label><input id="account-email" type="email">`
2.  **Librarian Portal:**
    * Open `LibrarianPortal.html`.
    * Add an "Add New Student" button to the `#student-management-content` panel.
    * Add a *new, simple* modal: `<div id="add-student-modal" ...>`.
    * This modal's form will *only* have fields for `Name`, `Student ID (Username)`, and `Password`.

#### 3. Frontend (JS)
1.  **Admin Portal:**
    * `JS/modules/admin/staffManagement.js` (New File):
        * The "Add New..." button will open `#account-form-modal`.
        * The submit button will gather all fields (Role, Name, Username, etc.) and call a new API function: `adminApi.createAccount(accountData)`.
    * `JS/api/adminApi.js`:
        * Rename `createLibrarianAccount` to `createAccount` and change its endpoint to `POST /admin/accounts`.
2.  **Librarian Portal:**
    * `JS/modules/librarian/studentManagement.js`:
        * The new "Add New Student" button will open `#add-student-modal`.
        * The submit button will call the *existing* `authApi.registerStudent(studentData)` (which hits the public `POST /auth/register` route, creating a pending student, which is perfect).

---
### Feature 4: Login with Username / Student ID
**Goal:** Allow login with either.

This is just a clarification, as "Student ID" / "Staff ID" is just a friendly name for the `Username` field.
* **PHP:** No change needed. `AuthenticationService::login` already uses the `username` field.
* **HTML:** `Login.html` label "Username or Student ID" is already correct. No change needed.
* **JS:** `JS/auth.js` already takes the value from this field and sends it as `username`. No change needed.

---
### Feature 5 & 6: Book Copy Selection (Checkout & View)
**Goal:** Librarian must select a specific *available* copy to check out. A new "View Copies" feature is also needed.

#### 1. Backend (PHP)
1.  **Modify DAO:**
    * Open `models/BookCopyDAO.php`.
    * Modify `getByBookID($bookID)` to `getByBookID($bookID, $status = null)`.
    * Inside, change the query: `SELECT * FROM BookCopy WHERE BookID = :bookId`.
    * Add: `if ($status) { $query .= " AND Status = :status"; }`.
    * If `$status` is set, bind it: `$stmt->bindParam(':status', $status)`.
2.  **Create New Route:**
    * Open `Api/routes/librarian.php`.
    * Add a new route: `case 'books': ... if (!empty($segments[1]) && !empty($segments[2]) && $segments[2] === 'copies') { ... }`.
    * This handles `GET /librarian/books/{id}/copies`.
    * It will check for a query param: `$status = $query['status'] ?? null`.
    * It will call a new method: `BookManagementService::getBookCopies($segments[1], $status)`.
3.  **Create New Service Method:**
    * Open `Services/BookManagementService.php`.
    * Add `public function getBookCopies($bookId, $status = null)`:
        * This just calls and returns `$this->db->bookCopyDAO->getByBookID($bookId, $status)`.
4.  **Modify Checkout Service:**
    * Open `Services/TransactionManagementService.php`.
    * Modify `checkoutBook($userId, $copyId, $daysToLoan = 14)` to `checkoutBook($userId, $copyIds, $daysToLoan = 14)`.
    * Change `$copyIds` to be an *array*.
    * Wrap the entire logic (from `get user account` to `logAction`) in a `foreach ($copyIds as $copyId) { ... }`.
    * This will loop through each `copyId` and create a separate transaction for it.

#### 2. Frontend (HTML)
1.  **Librarian Portal ("Borrow" Panel):**
    * Open `LibrarianPortal.html`.
    * In the "Borrow (Check-out)" card, **add a book search bar** (e.g., `<input type="text" id="borrow-book-search" placeholder="Search Book by Title...">`).
    * Add a search results list: `<div id="borrow-search-results"></div>`.
    * Add a **new, hidden modal**: `<div id="book-copy-modal" ...>`. This modal will contain a table to list available copies.
2.  **Librarian Portal ("Cataloging" Panel):**
    * Open `LibrarianPortal.html`.
    * In the `<thead>` of `#cataloging-table`, add a new `<th>` (e.g., "Copies").
    * In the `<tbody>` `<tr>` (template), modify the "Action" `<td>` to add a new button: `<button class="action-btn view-copies-btn" data-book-id="...">Copies</button>`.
    * Add another **new, hidden modal**: `<div id="view-all-copies-modal" ...>`. This modal will have a table to list *all* copies and their statuses.

#### 3. Frontend (JS)
1.  **New API Functions:**
    * `JS/api/bookApi.js`:
        * Add `async function getBookCopies(bookId, status = null)`:
            * Builds the URL: `librarian/books/${bookId}/copies`.
            * If `status` is provided, adds `?status=${status}`.
            * Returns the `apiClient` call.
2.  **Librarian Portal (Transactions):**
    * `JS/modules/librarian/transactions.js` (New File):
        * This file will hold all logic for the "Borrow & Return" panel.
        * Add a `keypress` listener to `#borrow-book-search`. On 'Enter', it will call `bookApi.searchBooks()`.
        * Search results will be rendered in `#borrow-search-results` as clickable buttons.
        * When a book result is clicked (this gives you `bookId`):
            1.  Call `bookApi.getBookCopies(bookId, 'Available')`.
            2.  Populate the `#book-copy-modal` with the available copies (e.g., "Copy #123 - Shelf F3 - Good").
            3.  Show the `#book-copy-modal`.
        * When a copy is clicked in the modal:
            1.  Add its `copyId` to a temporary array (e.g., `copiesToCheckout = []`).
            2.  Add the book title to the UI list.
            3.  Close the modal.
        * The "Finalize Check-out" button:
            1.  Will now be enabled only if `copiesToCheckout.length > 0`.
            2.  It will call `borrowApi.checkoutBook(studentID, copiesToCheckout)`.
3.  **Librarian Portal (Cataloging):**
    * `JS/modules/librarian/catalog.js` (New File):
        * Will contain the `loadCatalogingData` and `setupCatalogingLogic` functions.
        * In the table event listener, add a new handler for `.view-copies-btn`.
        * This handler will call `bookApi.getBookCopies(bookId)` (with *no* status filter).
        * It will populate and show the `#view-all-copies-modal` with *all* copies and their statuses.

---
### Feature 7: Other Code Fixes
**Goal:** Implement small fixes identified during the initial review.

1.  **Book Return with Condition:**
    * `PHP`: `Services/TransactionManagementService.php` -> `returnBook($input)`: This function already receives the full `$input`. It's ready.
    * `JS`: `JS/api/borrowApi.js` -> `returnBook(transactionId, details = {})`: Modify this to `returnBook(transactionId, condition, notes)`.
    * The `details` object will be `{ transaction_id: transactionId, condition: condition, notes: notes }`.
    * `JS/modules/librarian/transactions.js`: The "Process Return" button will now gather `condition` and `notes` from the form and pass them to `borrowApi.returnBook()`.
2.  **Student Password Change:**
    * `HTML`: `StudentPortal.html` -> `#settings-content`: In the "Change Password" section, add a new `info-group` for "Current Password" (`<input type="password" id="setting-current-password">`).
    * `JS`: `JS/student.js` -> `setupAccountSettingsLogic()`:
        * The "Confirm Change" button listener will now also get the value from `#setting-current-password`.
        * It will call `userApi.studentChangePassword(currentPassword, newPassword)`.
3.  **Book Edit Form (Copies):**
    * `JS`: `JS/modules/librarian/catalog.js`:
        * In the `resetBookForm(book = null)` helper function:
        * Add logic: `const isEditMode = !!book;`.
        * `document.querySelector('.copies-control').style.display = isEditMode ? 'none' : 'flex';` (This will hide the "Copies" counter when editing a book).