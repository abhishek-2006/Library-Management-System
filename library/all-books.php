<?php
session_start();
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['login'])==0) { 
    header('location:index.php');
    exit(); 
} else {
    $currentPage = 'all-books.php';

    // Query to fetch all books and their current availability (Stock)
    $sql = "SELECT 
        tblbooks.id as bookid, 
        BookName, 
        CategoryName, 
        AuthorName, 
        ISBNNumber, 
        BookPrice, 
        tblbooks.bookImage,
        (tblbooks.bookCopies - COALESCE(SUM(CASE WHEN tblissuedbookdetails.ReturnStatus IS NULL OR tblissuedbookdetails.ReturnStatus = 0 THEN 1 ELSE 0 END), 0)) AS currentStock
        FROM tblbooks
        JOIN tblcategory ON tblcategory.id = tblbooks.CatId
        JOIN tblauthors ON tblauthors.id = tblbooks.AuthorId
        LEFT JOIN tblissuedbookdetails ON tblissuedbookdetails.BookID = tblbooks.id
        GROUP BY tblbooks.id
        ORDER BY BookName";
    
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Modern LMS | Book Catalog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container-custom">
            
            <h4 class="header-title-main">Library Book Catalog</h4>
            
            <div class="book-grid">
                <?php if($query->rowCount() > 0) {
                    foreach($results as $result) { 
                        $stockStatus = $result->currentStock > 0;
                        $stockClass = $stockStatus ? 'stock-in' : 'stock-out';
                        $stockText = $stockStatus ? 'Available' : 'Out of Stock';
                ?>
                <div class="book-card">
                    <div class="book-cover-wrapper">
                        <?php if($result->bookImage): ?>
                            <img src="admin/assets/img/<?php echo htmlentities($result->bookImage);?>" alt="<?php echo htmlentities($result->BookName);?> Cover" class="book-cover-lg"/>
                        <?php else: ?>
                            <i class="fas fa-book-open book-cover-placeholder"></i>
                        <?php endif; ?>
                    </div>
                    <div class="book-details">
                        <h5 class="book-title-grid"><?php echo htmlentities($result->BookName);?></h5>
                        <p class="book-author">by <?php echo htmlentities($result->AuthorName);?></p>
                        <div class="book-info-badges">
                            <span class="category-badge"><?php echo htmlentities($result->CategoryName);?></span>
                            <span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stockText; ?> (<?php echo htmlentities($result->currentStock);?>)</span>
                        </div>
                        <p class="isbn-text">ISBN: <?php echo htmlentities($result->ISBNNumber);?></p>
                        <a href="book-details.php?bookid=<?php echo htmlentities($result->bookid);?>" class="btn-detail-link">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php }
                } else { ?>
                    <div class="empty-state-message full-width-message">
                        <i class="fas fa-book-atlas empty-state-icon"></i>
                        <p>The library catalog is currently empty.</p>
                        <a href="request-book.php" class="btn-primary-link"><i class="fas fa-box-archive"></i> Suggest a Book</a>
                    </div>
                <?php } ?>
            </div>
            
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>