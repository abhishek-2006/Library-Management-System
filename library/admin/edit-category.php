<?php
session_start();
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    $category_id = intval($_GET['id']); // Get the Category ID (Primary Key) from the URL

    // --- 1. Form Submission Handling (POST) ---
    if(isset($_POST['update'])) {
        $new_category_name = $_POST['categoryname'];
        
        // SQL to update category data
        $sql = "UPDATE tblcategory SET 
                    CategoryName=:newcategoryname, 
                    UpdationDate = NOW()
                WHERE id=:currentcategoryid";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':newcategoryname', $new_category_name, PDO::PARAM_STR);
        $query->bindParam(':currentcategoryid', $category_id, PDO::PARAM_INT);
        
        if($query->execute()) {
            $_SESSION['msg'] = "Category name updated successfully!";
            // Redirect to the manage-categories page after update
            header('location:manage-categories.php');
            exit();
        } else {
            $_SESSION['error'] = "Error updating category record. Please try again.";
        }
    }

    // --- 2. Data Fetching (GET) ---
    $sql_fetch = "SELECT id, CategoryName, CreationDate, Status FROM tblcategory WHERE id=:categoryid";
    $query_fetch = $dbh->prepare($sql_fetch);
    $query_fetch->bindParam(':categoryid', $category_id, PDO::PARAM_INT);
    $query_fetch->execute();
    $result = $query_fetch->fetch(PDO::FETCH_OBJ);
    
    if (!$result) {
        $_SESSION['error'] = "Category ID not found.";
        header('location:manage-categories.php');
        exit();
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Edit Category</title>
    
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Edit Category: <?php echo htmlentities($result->CategoryName ?? '');?></h4>
            </div>

            <div class="message-wrapper">
                <?php if(isset($_SESSION['error']) && !empty($_SESSION['error'])) {?>
                    <div class="alert alert-error">
                        <?php echo htmlentities($_SESSION['error']);?>
                    </div>
                <?php $_SESSION['error']=""; } ?>
            </div>
            
            <div class="form-content-wrapper">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Category Details
                    </div>
                    <div class="panel-body">
                        <form name="updatecategory" method="post">

                            <div class="form-group">
                                <label>Category Name</label>
                                <input class="form-control" type="text" name="categoryname" value="<?php echo htmlentities($result->CategoryName ?? '');?>" required />
                            </div>

                            <div class="form-group">
                                <label>Category ID (Primary Key - Non-editable)</label>
                                <input class="form-control" type="text" value="<?php echo htmlentities($result->id ?? '');?>" readonly />
                            </div>

                            <div class="form-group">
                                <label>Creation Date</label>
                                <input class="form-control" type="text" value="<?php echo htmlentities($result->CreationDate ?? '');?>" readonly />
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <input class="form-control" type="text" value="<?php echo ($result->Status == 1 ? 'Active' : 'Inactive') ?? '';?>" readonly />
                            </div>

                            <button type="submit" name="update" class="update-btn">
                                <i class="fa fa-refresh"></i> Update Category
                            </button>

                            <a href="manage-categories.php" class="back-link">
                                <i class="fa fa-arrow-left"></i> Back to Categories
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