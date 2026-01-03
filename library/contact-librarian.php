<?php
session_start();
error_reporting(0);
require('includes/config.php');

// Ensure only logged-in students can view this page
if(strlen($_SESSION['login'])==0) { 
    header('location:../index.php');
    exit(); 
} else {
    $sid = $_SESSION['stdid'];
    $currentPage = 'contact-librarian.php'; 
    $msg = $error = "";

    // --- FORM SUBMISSION HANDLING ---
    if(isset($_POST['send'])) {
        $title = $_POST['title'];
        $details = $_POST['details'];
        
        if (empty($title) || empty($details)) {
            $error = "Please fill in both the message title and details.";
        } else {
            // Sanitize and escape inputs
            $title = htmlentities($title);
            $details = htmlentities($details);

            $sql = "INSERT INTO tblcontactmessages (studentID, messageTitle, messageDetails) VALUES (:sid, :title, :details)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':sid', $sid, PDO::PARAM_STR);
            $query->bindParam(':title', $title, PDO::PARAM_STR);
            $query->bindParam(':details', $details, PDO::PARAM_STR);

            if ($query->execute()) {
                $msg = "Your message has been successfully sent to the librarian! We will respond shortly.";
            } else {
                $error = "Something went wrong. Please try again or contact support.";
            }
        }
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Modern LMS | Contact Librarian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container-custom">
            
            <h4 class="header-title-main">Contact the Librarian</h4>
            
            <div class="form-container-box">
                <p class="intro-text">
                    Use the form below to submit a question, report an issue, or request assistance. 
                    We aim to respond to all queries within one business day.
                </p>

                <?php if($msg){?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
                <?php } ?>
                <?php if($error){?>
                    <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                <?php } ?>

                <form method="post" action="contact-librarian.php" class="contact-form">
                    
                    <div class="form-group">
                        <label for="title">Message Subject/Title:</label>
                        <input type="text" id="title" name="title" required placeholder="E.g., Query about Fine, Book Renewal Request, etc." maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="details">Details of Your Message:</label>
                        <textarea id="details" name="details" required rows="6" placeholder="Please provide detailed information about your query..."></textarea>
                    </div>

                    <button type="submit" name="send" class="btn-submit-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                    
                </form>

            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>