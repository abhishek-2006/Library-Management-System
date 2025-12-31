<?php
session_start();
error_reporting(0);
require('includes/config.php');

// Ensure only logged-in students can view this page
if(strlen($_SESSION['login'])==0) { 
    header('location:index.php');
    exit(); 
} else {
    $sid = $_SESSION['stdid'];
    $currentPage = 'request-book.php'; 
    $msg = $error = "";

    // --- FORM SUBMISSION HANDLING ---
    if(isset($_POST['request'])) {
        $bookTitle = $_POST['bookTitle'];
        $bookAuthor = $_POST['bookAuthor'];
        $publisher = $_POST['publisher'];
        $bookISBN = $_POST['bookISBN'];
        $reason = $_POST['reason'];
        
        if (empty($bookTitle)) {
            $error = "The book title is required.";
        } else {
            // Sanitize and escape inputs
            $bookTitle = htmlentities($bookTitle);
            $bookAuthor = htmlentities($bookAuthor);
            $publisher = htmlentities($publisher);
            $bookISBN = htmlentities($bookISBN);
            $reason = htmlentities($reason);

            // 🔑 UPDATED SQL: Include the new 'publisher' column
            $sql = "INSERT INTO tblbookrequests (studentID, bookTitle, bookAuthor, publisher, bookISBN, reason) VALUES (:sid, :title, :author, :publisher, :isbn, :reason)";
            $query = $dbh->prepare($sql);
            
            $query->bindParam(':sid', $sid, PDO::PARAM_STR);
            $query->bindParam(':title', $bookTitle, PDO::PARAM_STR);
            $query->bindParam(':author', $bookAuthor, PDO::PARAM_STR);
            $query->bindParam(':publisher', $publisher, PDO::PARAM_STR);
            $query->bindParam(':isbn', $bookISBN, PDO::PARAM_STR);
            $query->bindParam(':reason', $reason, PDO::PARAM_STR);

            if ($query->execute()) {
                $msg = "Thank you! Your book suggestion has been submitted for review by the library staff.";
            } else {
                $error = "Something went wrong. Please try again or contact the librarian.";
            }
        }
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Modern LMS | Suggest a Book</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container-custom">
            
            <h4 class="header-title-main">Suggest a Book for Acquisition</h4>
            
            <div class="form-container-box">
                <p class="intro-text">
                    Help us expand our collection! Please provide details for the book you would like the library to purchase. 
                    Providing the Publisher and ISBN is highly recommended.
                </p>

                <?php if($msg){?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
                <?php } ?>
                <?php if($error){?>
                    <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                <?php } ?>

                <form method="post" action="request-book.php" class="contact-form">
                    
                    <div class="form-group">
                        <label for="bookTitle">Book Title <span class="required-star">*</span>:</label>
                        <input type="text" id="bookTitle" name="bookTitle" required placeholder="The definite title of the book" maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="bookAuthor">Author Name:</label>
                        <input type="text" id="bookAuthor" name="bookAuthor" placeholder="E.g., Dr. A.P.J. Abdul Kalam" maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="publisher">Publisher / Publication House:</label>
                        <input type="text" id="publisher" name="publisher" placeholder="E.g., Penguin Random House, HarperCollins, Jaico" maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="bookISBN">ISBN (Recommended):</label>
                        <input type="text" id="bookISBN" name="bookISBN" placeholder="ISBN-13 (e.g., 978-0123456789)" maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason for Suggestion:</label>
                        <textarea id="reason" name="reason" rows="4" placeholder="Briefly explain why this book would be valuable to the library and students."></textarea>
                    </div>

                    <button type="submit" name="request" class="btn-submit-primary">
                        <i class="fas fa-box-archive"></i> Submit Book Request
                    </button>
                    
                </form>

            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>