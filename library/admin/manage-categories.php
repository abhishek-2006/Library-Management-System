<?php
session_start();
error_reporting(E_ALL); 
include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit;
} else { 
    
    // --- Delete Logic ---
    // The primary key 'id' is passed via the 'del' URL parameter
    if(isset($_GET['del'])) {
        $id_to_delete = intval($_GET['del']); 
        
        // SQL to delete the category record
        $sql = "DELETE FROM tblcategory WHERE id=:id"; 
        $query = $dbh->prepare($sql);
        $query -> bindParam(':id', $id_to_delete, PDO::PARAM_INT);
        $query -> execute();
        
        $_SESSION['delmsg']="Category deleted successfully"; 
        header('location:manage-categories.php');
        exit;
    }

    // --- Database Query for Categories Listing ---
    $sql = "SELECT id, CategoryName, CreationDate, UpdationDate FROM tblcategory";
    $query = $dbh -> prepare($sql);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Online Library Management System | Manage Categories</title>
    
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet" /> 
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Manage Categories</h4>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?php 
                    if(isset($_SESSION['delmsg']) && !empty($_SESSION['delmsg'])) {
                    ?>
                        <div class="alert alert-success">
                            <?php echo htmlentities($_SESSION['delmsg']);?>
                        </div>
                    <?php 
                        $_SESSION['delmsg'] = ""; 
                    } 
                    ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Categories Listing
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="custom-table" id="categoriesTable"> 
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Category Name</th>
                                            <th>Creation Date</th>
                                            <th>Updation Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php 
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{               
?>                                      
                                        <tr class="odd gradeX">
                                            <td class="center"><?php echo htmlentities($cnt);?></td>
                                            <td class="center"><?php echo htmlentities($result->CategoryName ?? '');?></td>
                                            <td class="center"><?php echo htmlentities($result->CreationDate ?? '');?></td>
                                            <td class="center"><?php echo htmlentities($result->UpdationDate ?? '');?></td>
                                            <td class="center">
                                                <a href="edit-category.php?id=<?php echo htmlentities($result->id);?>"><button class="custom-btn btn-primary-theme"><i class="fa fa-edit "></i> Edit</button> 
                                                
                                                <a href="manage-categories.php?del=<?php echo htmlentities($result->id);?>" onclick="return confirm('Are you sure you want to delete this Category?');" >  
                                                <button class="custom-btn btn-danger-theme"><i class="fa fa-trash"></i> Delete</button>
                                                </a>
                                            </td>
                                        </tr>
<?php $cnt=$cnt+1;}} ?>                                      
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
    
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTables using the new ID
            $('#categoriesTable').dataTable(); 
        });
    </script>
</body>
</html>
<?php } ?>