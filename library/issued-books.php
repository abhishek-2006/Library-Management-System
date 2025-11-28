<?php
// --- CRITICAL DEBUG SETTINGS ---
// Set error reporting to catch fatal errors that cause blank pages
error_reporting(0);
ini_set('display_errors', 1);

session_start();

// --- Configuration and Database Connection ---
include('includes/config.php');

// --- Authentication Check ---
if(strlen($_SESSION['login'])==0) { 
    header('location:index.php');
    exit(); 
} else {
    $sid = $_SESSION['stdid'];
    $currentPage = 'issued-books.php';
    $finePerDay = 0.50; // Library Policy: Example rate for fine calculation

    // Query to fetch currently issued books and history 
    $sql = "SELECT 
        tblbooks.BookName, 
        tblbooks.ISBNNumber, 
        tblissuedbookdetails.IssuesDate, 
        tblissuedbookdetails.ReturnDate, 
        tblissuedbookdetails.id as rid, 
        tblissuedbookdetails.fine, 
        tblissuedbookdetails.ReturnStatus, 
        tblbooks.id as bid, 
        tblbooks.bookImage 
        FROM tblissuedbookdetails 
        JOIN tblstudents ON tblstudents.StudentId = tblissuedbookdetails.StudentID 
        JOIN tblbooks ON tblbooks.id = tblissuedbookdetails.BookID 
        WHERE tblstudents.StudentId = :sid
        ORDER BY tblissuedbookdetails.id DESC";

    try {
        $query = $dbh->prepare($sql);
        $query->bindParam(':sid', $sid, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        // Fallback error handler for database issues
        die("Database Query Error: " . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Modern LMS | Issued & History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container-custom">
            
            <h4 class="header-title-main">Issued Books & Borrowing History</h4>
            
            <div class="table-responsive-box">
                <?php if($query->rowCount() > 0) { ?>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Book Image</th>
                            <th>Book Name / ISBN</th>
                            <th>Issued Date</th>
                            <th>Due Date</th>
                            <th>Return Status</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $cnt = 1;
                        foreach($results as $result) { 
                            // --- Logic to determine status, due date, and fine ---
                            $isOverdue = false;
                            $fineDisplay = "-";
                            $statusText = "";
                            $statusClass = "";
                            $dueDate = date('d-m-Y', strtotime($result->IssuesDate . ' + 15 days')); 
                            $today = date('d-m-Y');

                            if ($result->ReturnStatus == 1) { // Book Returned
                                $statusText = "Returned";
                                $statusClass = "status-success";
                                $fineDisplay = ($result->fine > 0) ? number_format($result->fine, 2) : "-";
                                if ($result->fine > 0) {
                                     $statusClass = "status-warning"; // Returned with fine
                                 }
                            } else { // Book Not Returned (Currently Issued)
                                $statusText = "Currently Issued";
                                $statusClass = "status-info";
                                
                                if ($today > $dueDate) {
                                    $isOverdue = true;
                                    $statusText = "Overdue!";
                                    $statusClass = "status-danger";
                                    
                                    // Calculate simple fine for display purposes
                                    $date1 = new DateTime($dueDate);
                                    $date2 = new DateTime($today);
                                    $interval = $date1->diff($date2);
                                    $overdueDays = $interval->days;
                                    $fineDisplay = number_format($overdueDays * $finePerDay, 2) . " (Est.)";
                                }
                                // Use recorded fine if it exists and book is not returned
                                if ($result->fine > 0 && $result->ReturnStatus == 0) {
                                    $fineDisplay = number_format($result->fine, 2);
                                }
                            }
                        ?>
                        <tr class="<?php echo $isOverdue ? 'overdue-row' : ''; ?>">
                            <td data-label="#"><?php echo $cnt;?></td>
                            <td data-label="Image" class="book-image-cell">
                                <?php if($result->bookImage): ?>
                                    <img src="admin/assets/img/<?php echo htmlentities($result->bookImage);?>" alt="Book Cover" class="book-cover-thumb"/>
                                <?php else: ?>
                                    <i class="fas fa-book-open book-cover-icon"></i>
                                <?php endif; ?>
                            </td>
                            <td data-label="Book Name / ISBN">
                                <strong><?php echo htmlentities($result->BookName);?></strong><br/>
                                <span class="isbn-number">ISBN: <?php echo htmlentities($result->ISBNNumber);?></span>
                            </td>
                            <td data-label="Issued Date"><?php echo date('d-M-Y', strtotime($result->IssuesDate));?></td>
                            <td data-label="Due Date" class="<?php echo $isOverdue ? 'overdue-date-text' : ''; ?>">
                                <?php 
                                    echo date('d-M-Y', strtotime($dueDate));
                                ?>
                            </td>
                            <td data-label="Return Status">
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td data-label="Fine" class="fine-amount">
                                <?php echo $fineDisplay; ?>
                            </td>
                        </tr>
                        <?php $cnt++; } ?>
                    </tbody>
                </table>
                
                <?php } else { ?>
                    <div class="empty-state-message">
                        <i class="fas fa-book-open-reader empty-state-icon"></i>
                        <p>No books found in your borrowing history.</p>
                        <a href="dashboard.php" class="btn-primary-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                    </div>
                <?php } ?>
                
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>