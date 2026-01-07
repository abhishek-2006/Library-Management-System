<?php 
// Function to check if a link is active
function isActive($link) {
    // Check global variable $currentPage (set in the calling file) or direct script name
    $currentFile = basename($_SERVER['PHP_SELF']);
    return (isset($GLOBALS['currentPage']) && $GLOBALS['currentPage'] === $link) || ($currentFile === $link) ? 'active-link' : '';
}
?>

<header class="main-header">
    <div class="header-content">
        <a href="dashboard.php" class="brand-logo">
            <i class="fas fa-book-open stat-logo-icon"> LMS</i>
            </a>
        <div style="display: flex; align-items: center;">
            <?php if(isset($_SESSION['login']) && $_SESSION['login']) { ?>
                <span class="user-greeting">
                    Hi, 
                    <span class="user-name-display"><?php echo htmlentities($_SESSION['fname']); ?></span>
                </span>
                <a href="logout.php" class="logout-btn"><i class="fa fa-sign-out"></i> Logout</a>
            <?php } else { ?>
                <a href="index.php" class="logout-btn" style="background: var(--primary-indigo);">Login</a>
            <?php } ?>
        </div>
    </div>
</header>

<nav class="menu-section">
    <div class="menu-nav">
        <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>">Dashboard</a>
        <a href="issued-books.php" class="nav-link <?php echo isActive('issued-books.php'); ?>">Issued Books</a>
        <a href="all-books.php" class="nav-link <?php echo isActive('all-books.php'); ?>">Browse Catalog</a>

        <div class="dropdown">
            <button class="dropdown-btn">My Account <i class="fa fa-chevron-down"></i></button>
            <div class="dropdown-menu">
                <a href="my-profile.php" class="dropdown-item">My Profile</a>
                <a href="update-password.php" class="dropdown-item">Update Password</a>
            </div>
        </div>
    </div>
</nav>