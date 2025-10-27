<?php
session_start();
error_reporting(0);
include('includes/config.php'); 

// 1. Check if the user is logged in
if(strlen($_SESSION['login'])==0) { 
    header('location:../../index.php');
    exit(); 
} else {
    $sid = $_SESSION['stdid'];
    
    // Set the current page name for the active link indicator
    $currentPage = 'dashboard.php'; 

    // --- DATABASE QUERIES ---

    // 1. Total Books Issued
    $sql1 ="SELECT COUNT(id) AS total_issued FROM tblissuedbookdetails WHERE StudentID=:sid";
    $query1 = $dbh->prepare($sql1);
    $query1->bindParam(':sid', $sid, PDO::PARAM_STR);
    $query1->execute();
    $result1 = $query1->fetch(PDO::FETCH_ASSOC);
    $issuedbooks = $result1['total_issued'];

    // 2. Books Pending Return
    $rsts = 0; 
    $sql2 ="SELECT COUNT(id) AS total_pending FROM tblissuedbookdetails WHERE StudentID=:sid AND RetrunStatus=:rsts";
    $query2 = $dbh->prepare($sql2);
    $query2->bindParam(':sid', $sid, PDO::PARAM_STR);
    $query2->bindParam(':rsts', $rsts, PDO::PARAM_INT);
    $query2->execute();
    $result2 = $query2->fetch(PDO::FETCH_ASSOC);
    $pendingbooks = $result2['total_pending'];

    // 3. Total Penalty / Fines
    $rstsReturned = 1; 
    $sql3 ="SELECT SUM(fine) AS total_fine FROM tblissuedbookdetails WHERE StudentID=:sid AND RetrunStatus=:rstsReturned";
    $query3 = $dbh->prepare($sql3);
    $query3->bindParam(':sid', $sid, PDO::PARAM_STR);
    $query3->bindParam(':rstsReturned', $rstsReturned, PDO::PARAM_INT);
    $query3->execute();
    $result3 = $query3->fetch(PDO::FETCH_ASSOC);
    $totalfine = $result3['total_fine'] ? $result3['total_fine'] : 0;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Modern LMS | Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css"/>    
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container-custom">
            
            <h4 class="header-title">Welcome Back, <?php echo htmlentities($_SESSION['fname']); ?>!</h4>
            
            <div class="dashboard-grid grid-4-cols">
                <a href="issued-books.php" class="stat-card card-info">
                    <i class="fas fa-book-open-reader stat-icon"></i>
                    <div class="stat-value"><?php echo htmlentities($issuedbooks);?></div>
                    <div class="stat-label">Total Books Issued</div>
                </a>
                <a href="issued-books.php?view=pending" class="stat-card card-warning">
                    <i class="fas fa-clock-rotate-left stat-icon"></i>
                    <div class="stat-value"><?php echo htmlentities($pendingbooks);?></div>
                    <div class="stat-label">Books Pending Return</div>
                </a>
                <a href="my-profile.php?view=fine" class="stat-card card-danger">
                    <i class="fas fa-indian-rupee-sign stat-icon"></i>
                    <div class="stat-value">₹<?php echo htmlentities($totalfine);?></div>
                    <div class="stat-label">Total Penalty Due</div>
                </a>
                <a href="my-profile.php" class="stat-card card-success">
                    <i class="fas fa-user-gear stat-icon"></i>
                    <div class="stat-value">View</div>
                    <div class="stat-label">My Profile Details</div>
                </a>
            </div>
            
            <section class="quick-access-section"> 
                <h4 class="header-title-sub">Quick Access & Study Resources</h4>
                <div class="quick-access-grid">
                    
                    <a href="library-hours.php" class="feature-card feature-green">
                        <i class="fas fa-clock feature-icon"></i>
                        <div class="feature-title">Library Timings</div>
                        <div class="feature-description">Check opening and closing hours.</div>
                        <span class="feature-link">View Info &raquo;</span>
                    </a>
                    
                    <a href="contact-librarian.php" class="feature-card feature-orange">
                        <i class="fas fa-envelope-open-text feature-icon"></i>
                        <div class="feature-title">Need Help?</div>
                        <div class="feature-description">Get in touch with a librarian directly.</div>
                        <span class="feature-link" >Contact Us &raquo;</span>
                    </a>
                    
                    <a href="request-book.php" class="feature-card feature-indigo" style="border-left: 5px solid #4F46E5; background: white; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-decoration: none; color: inherit;">
                        <i class="fas fa-box-archive feature-icon"></i>
                        <div class="feature-title">Suggest a Book</div>
                        <div class="feature-description">Fill out a form to request an item for the collection.</div>
                        <span class="feature-link" >Make Request &raquo;</span>
                    </a>
                    
                </div>
            </section>

        </div>
    </div>
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>