<?php
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    if(isset($_POST['add'])) {
        $publisher_name = $_POST['publishername'];
        
        $check_sql = "SELECT id FROM tblpublishers WHERE PublisherName=:publishername";
        $check_query = $dbh->prepare($check_sql);
        $check_query->bindParam(':publishername', $publisher_name, PDO::PARAM_STR);
        $check_query->execute();

        if($check_query->rowCount() > 0) {
            $_SESSION['error'] = "Error: Publisher name already exists.";
        } else {
            // Assuming the table name is tblpublishers
            $insert_sql = "INSERT INTO tblpublishers(PublisherName) VALUES(:publishername)";
            
            $insert_query = $dbh->prepare($insert_sql);
            $insert_query->bindParam(':publishername', $publisher_name, PDO::PARAM_STR);
            
            if($insert_query->execute()) {
                $_SESSION['msg'] = "Publisher added successfully!";
                header('location:add-publisher.php'); 
                exit();
            } else {
                $_SESSION['error'] = "Error adding publisher. Please try again.";
            }
        }
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Add Publisher</title>
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Add Publisher</h4>
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
                        Enter Publisher Details
                    </div>
                    <div class="panel-body">
                        <form name="addpublisher" method="post">
                            
                            <div class="form-group">
                                <label>Publisher Name</label>
                                <input class="form-control" type="text" name="publishername" required />
                            </div>
                            
                            <button type="submit" name="add" class="update-btn">
                                <i class="fa fa-plus"></i> Add Publisher
                            </button>
                            
                            <a href="manage-publishers.php" class="back-link">
                                <i class="fa fa-arrow-left"></i> View Publishers
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