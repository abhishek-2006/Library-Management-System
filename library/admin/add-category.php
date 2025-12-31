<?php
session_start();
error_reporting(0);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    // --- 1. Form Submission Handling (POST) ---
    if(isset($_POST['add'])) {
        $category_name = $_POST['categoryname'];
        $status = 1;

        // SQL to insert new category
        $sql = "INSERT INTO tblcategory(CategoryName, Status) VALUES(:categoryname, :status)";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':categoryname', $category_name, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        
        if($query->execute()) {
            $_SESSION['msg'] = "Category added successfully!";
            header('location:add-category.php'); 
            exit();
        } else {
            $_SESSION['error'] = "Error adding category. Please try again.";
        }
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Add Category</title> 
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Add Category</h4>
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
                        Enter Category Details
                    </div>
                    <div class="panel-body">
                        <form name="addcategory" method="post">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input class="form-control" type="text" name="categoryname" required />
                            </div>
                            <button type="submit" name="add" class="update-btn">
                                <i class="fa fa-plus"></i> Add Category
                            </button>
                            
                            <a href="manage-categories.php" class="back-link">
                                <i class="fa fa-arrow-left"></i> View Categories
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