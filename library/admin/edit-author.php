<?php
session_start();
error_reporting(E_ALL);
include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
} else {
    $author_id = intval($_GET['id']); // Get the Author ID (Primary Key) from the URL
    
    // --- 1. Form Submission Handling (POST) ---
    if(isset($_POST['update'])) {
        $new_author_name = $_POST['authorname'];
        // SQL to update author data using the primary key 'id'
        $sql = "UPDATE tblauthors SET 
        AuthorName=:newauthorname, 
        updationDate = NOW()
        WHERE id=:currentauthorid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':newauthorname', $new_author_name, PDO::PARAM_STR);
        $query->bindParam(':currentauthorid', $author_id, PDO::PARAM_INT);

        if($query->execute()) {
            $_SESSION['msg'] = "Author name updated successfully!";
            // Redirect to the manage-authors page after update
            header('location:manage-authors.php');
            exit();
        } else {
            $_SESSION['error'] = "Error updating author record. Please try again.";
        }
    }

    // --- 2. Data Fetching (GET) ---
    $sql_fetch = "SELECT id, AuthorName, creationDate FROM tblauthors WHERE id=:authorid";
    $query_fetch = $dbh->prepare($sql_fetch);
    $query_fetch->bindParam(':authorid', $author_id, PDO::PARAM_INT);
    $query_fetch->execute();
    $result = $query_fetch->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        $_SESSION['error'] = "Author ID not found.";
        header('location:manage-authors.php');
        exit();
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Edit Author</title>
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Edit Author: <?php echo htmlentities($result->AuthorName ?? '');?></h4>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?php if(isset($_SESSION['error']) && !empty($_SESSION['error'])) {?>
                        <div class="alert alert-error">
                            <?php echo htmlentities($_SESSION['error']);?>
                        </div>
                    <?php $_SESSION['error']=""; } ?>
                </div>
            </div>
            
            <div class="form-container-wrapper">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Author Details
                        </div>
                        <div class="panel-body">
                            <form name="updateauthor" method="post">
                                
                                <div class="form-group">
                                    <label>Author Name</label>
                                    <input class="form-control" type="text" name="authorname" value="<?php echo htmlentities($result->AuthorName ?? '');?>" required />
                                </div>
                                
                                <div class="form-group">
                                    <label>Author ID (Primary Key - Non-editable)</label>
                                    <input class="form-control" type="text" value="<?php echo htmlentities($result->id ?? '');?>" readonly />
                                </div>

                                <div class="form-group">
                                    <label>Registration Date</label>
                                    <input class="form-control" type="text" value="<?php echo htmlentities($result->creationDate ?? '');?>" readonly />
                                </div>

                                <button type="submit" name="update" class="update-btn">
                                    <i class="fa fa-refresh"></i> Update Author
                                </button>
                                
                                <a href="manage-authors.php" class="back-link">
                                    <i class="fa fa-arrow-left"></i> Back to Authors
                                </a>
                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>