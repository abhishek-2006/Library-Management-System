<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
} else {
    // Set currentPage for header active link highlighting
    $currentPage = 'dashboard.php'; 

    // --- 1. COUNT LISTED BOOKS ---
    $sql_books ="SELECT id from tblbooks";
    $query_books = $dbh -> prepare($sql_books);
    $query_books->execute();
    $listdbooks = $query_books->rowCount();

    // --- 2. COUNT TOTAL ISSUED BOOKS (ALL TIME) ---
    $sql_issued ="SELECT id from tblissuedbookdetails";
    $query_issued = $dbh -> prepare($sql_issued);
    $query_issued->execute();
    $issuedbooks = $query_issued->rowCount();

    // --- 3. COUNT RETURNED BOOKS ---
    $status = 1; // 1 for Returned
    $sql_returned ="SELECT id from tblissuedbookdetails where ReturnStatus=:status";
    $query_returned = $dbh -> prepare($sql_returned);
    $query_returned->bindParam(':status', $status, PDO::PARAM_STR);
    $query_returned->execute();
    $returnedbooks = $query_returned->rowCount();

    // --- 4. COUNT REGISTERED STUDENTS ---
    $sql_students ="SELECT id from tblstudents";
    $query_students = $dbh -> prepare($sql_students);
    $query_students->execute();
    $regstds = $query_students->rowCount();

    // --- 5. COUNT LISTED AUTHORS ---
    $sql_authors ="SELECT id from tblauthors";
    $query_authors = $dbh -> prepare($sql_authors);
    $query_authors->execute();
    $listdathrs = $query_authors->rowCount();

    // --- 6. COUNT LISTED CATEGORIES ---
    $sql_categories ="SELECT id from tblcategory";
    $query_categories = $dbh -> prepare($sql_categories);
    $query_categories->execute();
    $listdcats = $query_categories->rowCount();

    // NEW: 7. COUNT PENDING BORROW REQUESTS (from tblrequests) ---
    $sql_borrow_count = "SELECT COUNT(id) AS pending_borrows FROM tblrequests WHERE Status='Pending'";
    $query_borrow_count = $dbh->prepare($sql_borrow_count);
    $query_borrow_count->execute();
    $pending_borrow_requests = $query_borrow_count->fetch(PDO::FETCH_ASSOC)['pending_borrows'];
    
    // 8. COUNT PENDING NEW BOOK REQUESTS (from tblbookrequests) ---
    $sql_book_req_count = "SELECT COUNT(id) AS pending_book_requests FROM tblbookrequests WHERE status=0";
    $query_book_req_count = $dbh->prepare($sql_book_req_count);
    $query_book_req_count->execute();
    $pending_new_book_requests = $query_book_req_count->fetch(PDO::FETCH_ASSOC)['pending_book_requests'];
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Admin Dash Board</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <style>
        .header-title-main {
            font-size: 2em;
            color: #333;
            margin-bottom: 5px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .header-title-sub {
            font-size: 1.5em;
            color: #555;
            margin-top: 20px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>

    <div class="container-custom">
        <h1 class="header-title-main">ADMINISTRATION DASHBOARD</h1>
        <h2 class="header-title-sub">Core Operations Statistics</h2>
        <div class="dashboard-grid">
            <a href="manage-books.php" class="stat-card card-success">
                <i class="fa fa-book-open stat-icon"></i> 
                <span class="stat-value"><?php echo htmlentities($listdbooks);?></span>
                <span class="stat-label">Books Listed</span>
            </a>

            <a href="manage-issued-books.php" class="stat-card card-info">
                <i class="fa fa-exchange-alt stat-icon"></i> 
                <span class="stat-value"><?php echo htmlentities($issuedbooks);?> </span>
                <span class="stat-label">Total Times Issued</span>
            </a>

            <a href="manage-issued-books.php" class="stat-card card-success">
                <i class="fa fa-undo-alt stat-icon"></i> 
                <span class="stat-value"><?php echo htmlentities($returnedbooks);?></span>
                <span class="stat-label">Times Books Returned</span>
            </a>

            <a href="reg-students.php" class="stat-card card-warning">
                <i class="fa fa-user-graduate stat-icon"></i> 
                <span class="stat-value"><?php echo htmlentities($regstds);?></span>
                <span class="stat-label">Registered Students</span>
            </a>
        </div>

        <h2 class="header-title-sub">Requests & Catalog Management</h2>
        
        <div class="dashboard-grid">
            <a href="manage-borrow-requests.php" class="feature-card feature-orange">
                <i class="fa fa-inbox feature-icon"></i> 
                <div class="feature-value"><?php echo htmlentities($pending_borrow_requests);?></div>
                <div class="feature-label">Pending Borrow Requests</div>
            </a>
            
            <a href="manage-book-requests.php" class="feature-card feature-indigo">
                <i class="fa fa-hourglass-half feature-icon"></i>
                <div class="feature-value"><?php echo htmlentities($pending_new_book_requests);?></div>
                <div class="feature-label">Pending New Book Requests</div>
            </a>

            <a href="manage-authors.php" class="feature-card feature-green">
                <i class="fa fa-pen-fancy feature-icon"></i> 
                <div class="feature-value"><?php echo htmlentities($listdathrs);?></div>
                <div class="feature-label">Authors Listed</div>
            </a>
            <a href="manage-categories.php" class="feature-card feature-red">
                <i class="fa fa-layer-group feature-icon"></i> 
                <div class="feature-value"><?php echo htmlentities($listdcats);?> </div>
                <div class="feature-label">Listed Categories</div>
            </a>
            
            <a href="manage-settings.php" class="feature-card feature-blue">
                <i class="fa fa-cogs feature-icon"></i>
                <div class="feature-value">...</div>
                <div class="feature-label">System Settings</div>
            </a>
        </div>
    </div>
    <?php include('includes/footer.php');?>
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
</body>
</html>
<?php } ?>
