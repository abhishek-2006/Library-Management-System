<?php 
// Function to check if a link is active (Requires $currentPage to be set in PHP)
function isActive($link) {
    $currentFile = basename($_SERVER['PHP_SELF']);
    return (isset($GLOBALS['currentPage']) && $GLOBALS['currentPage'] === $link) || ($currentFile === $link) ? 'active-link' : '';
}

// --- FETCH APP NAME FROM DB ---
try {
    // Using the confirmed table name: tbllibrarysettings
    $sql_app = "SELECT SettingValue FROM tbllibrarysettings WHERE SettingName = 'AppName'";
    $query_app = $dbh->prepare($sql_app);
    $query_app->execute();
    $result_app = $query_app->fetch(PDO::FETCH_ASSOC);
    if ($result_app) {
        $appName = htmlentities($result_app['SettingValue']);
    }
} catch (PDOException $e) {
    // Fail silently, use default name
}

// Assuming $_SESSION['alogin'] holds the admin identifier (e.g., email)
$adminName = isset($_SESSION['alogin']) ? explode('@', $_SESSION['alogin'])[0] : 'Admin';
?>

<header class="main-header">
    <div class="header-content">
        <a href="dashboard.php" class="brand-logo">
            <i class="fas fa-tools stat-logo-icon"></i> 
            <span class="brand-name"> Modern LMS
                <span class="brand-accent">ADMIN</span>
            </span>
        </a>
        
        <div class="right-controls">
            <?php if(strlen($_SESSION['alogin']) != 0) { ?>
                <span class="user-greeting">
                    <i class="fas fa-user-shield"></i> 
                    Welcome, <span class="user-name-display"><?php echo htmlentities(ucfirst($adminName)); ?></span>
                <a href="logout.php" class="logout-btn"><i class="fa fa-sign-out-alt"></i> Logout</a>
                </span>
            <?php } else { ?>
                <a href="index.php" class="login-btn"><i class="fa fa-sign-in-alt"></i> Login</a>
            <?php } ?>
        </div>
    </div>
</header>

<nav class="menu-section">
    <div class="menu-nav">
        <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>"><i class="fa fa-tachometer-alt"></i> Dashboard</a>
        
        <div class="dropdown">
            <button class="dropdown-btn"><i class="fas fa-tags"></i> Management <i class="fa fa-chevron-down"></i></button>
            <div class="dropdown-menu">

                <!-- CATEGORIES -->
                <a href="add-category.php" class="dropdown-item <?php echo isActive('add-category.php'); ?>">Add Category</a>
                <a href="manage-categories.php" class="dropdown-item <?php echo isActive('manage-categories.php'); ?>">Manage Categories</a>
                <div class="dropdown-divider"></div> 

                <!-- AUTHORS -->
                <a href="add-author.php" class="dropdown-item <?php echo isActive('add-author.php'); ?>">Add Author</a>
                <a href="manage-authors.php" class="dropdown-item <?php echo isActive('manage-authors.php'); ?>">Manage Authors</a>
                <div class="dropdown-divider"></div> 

                <!-- PUBLISHERS (NEW) -->
                <a href="add-publisher.php" class="dropdown-item <?php echo isActive('add-publisher.php'); ?>">Add Publisher</a>
                <a href="manage-publishers.php" class="dropdown-item <?php echo isActive('manage-publishers.php'); ?>">Manage Publishers</a>
            </div>
        </div>

        <div class="dropdown">
            <button class="dropdown-btn"><i class="fa fa-book"></i> Books <i class="fa fa-chevron-down"></i></button>
            <div class="dropdown-menu">
                <a href="add-book.php" class="dropdown-item <?php echo isActive('add-book.php'); ?>">Add Book</a>
                <a href="manage-books.php" class="dropdown-item <?php echo isActive('manage-books.php'); ?>">Manage Books</a>
            </div>
        </div>
        
        <div class="dropdown">
            <button class="dropdown-btn"><i class="fa fa-exchange-alt"></i> Lending <i class="fa fa-chevron-down"></i></button>
            <div class="dropdown-menu">
                <a href="issue-book.php" class="dropdown-item <?php echo isActive('issue-book.php'); ?>">Issue New Book</a>
                <a href="manage-issued-books.php" class="dropdown-item <?php echo isActive('manage-issued-books.php'); ?>">Manage Issued/Returned</a>
                <a href="manage-book-requests.php" class="dropdown-item <?php echo isActive('manage-book-requests.php'); ?>">Manage Book Requests</a>
                <a href="manage-borrow-requests.php" class="dropdown-item <?php echo isActive('manage-borrow-requests.php'); ?>">Manage Borrow Requests</a>
            </div>
        </div>
        
        <a href="reg-students.php" class="nav-link <?php echo isActive('reg-students.php'); ?>"><i class="fa fa-users"></i> Reg Students</a>
        
        <div class="dropdown">
            <button class="dropdown-btn"><i class="fa fa-cogs"></i> System <i class="fa fa-chevron-down"></i></button>
            <div class="dropdown-menu">
                <a href="change-password.php" class="dropdown-item <?php echo isActive('change-password.php'); ?>">Change Password</a>
                <a href="manage-settings.php" class="dropdown-item <?php echo isActive('manage-settings.php'); ?>">Library Settings</a>
                <a href="contact-messages.php" class="dropdown-item <?php echo isActive('contact-messages.php'); ?>">Contact Messages</a>
            </div>
        </div>
    </div>
</nav>