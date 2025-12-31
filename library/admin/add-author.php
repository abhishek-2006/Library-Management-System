<?php
session_start();
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    // --- 1. Form Submission Handling (POST) ---
    if(isset($_POST['add'])) {
        $author_name = $_POST['authorname'];

        // SQL to check if the author name already exists
        $check_sql = "SELECT id FROM tblauthors WHERE AuthorName=:authorname";
        $check_query = $dbh->prepare($check_sql);
        $check_query->bindParam(':authorname', $author_name, PDO::PARAM_STR);
        $check_query->execute();

        if($check_query->rowCount() > 0) {
            $_SESSION['error'] = "Error: Author name already exists.";
        } else {
            // SQL to insert new author
            $insert_sql = "INSERT INTO tblauthors(AuthorName) VALUES(:authorname)";
            
            $insert_query = $dbh->prepare($insert_sql);
            $insert_query->bindParam(':authorname', $author_name, PDO::PARAM_STR);
            
            if($insert_query->execute()) {
                $_SESSION['msg'] = "Author added successfully!";
                // Redirect to the same page to clear the form and display message
                header('location:add-author.php'); 
                exit();
            } else {
                $_SESSION['error'] = "Error adding author. Please try again.";
            }
        }
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Add Author</title>
    
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            
            <div class="page-header-wrapper">
                <h4 class="header-line">Add Author</h4>
            </div>

            <div class="message-wrapper">
                <?php if(isset($_SESSION['msg']) && !empty($_SESSION['msg'])) {?>
                    <div class="alert alert-success">
                        <?php echo htmlentities($_SESSION['msg']);?>
                    </div>
                <?php $_SESSION['msg']=""; } ?>

                <?php if(isset($_SESSION['error']) && !empty($_SESSION['error'])) {?>
                    <div class="alert alert-error">
                        <?php echo htmlentities($_SESSION['error']);?>
                    </div>
                <?php $_SESSION['error']=""; } ?>
            </div>
            
            <div class="form-container-wrapper">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Enter Author Details
                    </div>
                    <div class="panel-body">
                        <form name="addauthor" method="post">
                            
                            <div class="form-group">
                                <label>Author Name</label>
                                <input class="form-control" type="text" name="authorname" required />
                            </div>
                            
                            <button type="submit" name="add" class="update-btn">
                                <i class="fa fa-plus"></i> Add Author
                            </button>
                            
                            <a href="manage-authors.php" class="back-link">
                                <i class="fa fa-arrow-left"></i> View Authors
                            </a>
                        </form>
                    </div>
                </div>
            </div>

        </div> </div> <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>