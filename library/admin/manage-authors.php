<?php
session_start();
error_reporting(E_ALL); 
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit;
} else { 
    // --- Delete Logic ---
    if(isset($_GET['del'])) {
        $id_to_delete = intval($_GET['del']); 
        // SQL to delete the author record from the tblauthors table using 'id'
        $sql = "DELETE FROM tblauthors WHERE id=:id"; 
        $query = $dbh->prepare($sql);
        $query -> bindParam(':id', $id_to_delete, PDO::PARAM_INT);
        $query -> execute();
        // Set message and redirect
        $_SESSION['delmsg']="Author deleted successfully"; 
        header('location:manage-authors.php');
        exit;
    }
    
    // --- Database Query for Authors Listing ---
    $sql = "SELECT id, AuthorName, creationDate, updationDate FROM tblauthors";
    $query = $dbh -> prepare($sql);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Manage Authors</title>
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
<style>
    /* 3. Table Theme (DataTable) */
    #dataTables {
        border-collapse: collapse;
        width: 100%;
        /* Ensure the DataTable container is centered if it doesn't span full width */
        margin-left: auto;
        margin-right: auto;
    }
    #dataTables th, #dataTables td {
        padding: 14px 12px;
        text-align: left;
        /* Subtle row separation */
        border-bottom: 1px solid #e9edf1;
        font-size: 14px;
        vertical-align: middle;
    }
    
    /* Table Header Theme */
    #dataTables thead th {
        background-color: #f8f9fa; /* Very light background for header */
        color: #495057; /* Darker text for header */
        font-weight: 700;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }
    
    /* Hover Row Theme */
    #dataTables tbody tr:hover {
        background-color: #eaf0f7; /* Soft blue highlight on hover */
        cursor: pointer;
    }
    
    /* Alternating Row Stripe Theme (Zebra Striping) */
    #dataTables tbody tr:nth-child(even) {
        background-color: #fcfcfc; /* Very subtle stripe */
    }

    /* 4. Action Button Styling (Reusing previous themes) */
    .custom-btn {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        margin: 2px 0;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        border-radius: 6px;
        transition: background-color 0.2s;
    }
    .custom-btn i { margin-right: 5px; }

    /* Primary (Edit) Button */
    .btn-primary-theme { 
        background-color: #6366f1; /* Primary Purple */
        color: #ffffff; 
    }
    .btn-primary-theme:hover { background-color: #4f46e5; }

    /* Danger (Delete) Button */
    .btn-danger-theme { 
        background-color: #ef4444; /* Red */
        color: #ffffff; 
    }
    .btn-danger-theme:hover { background-color: #dc2626; }

</style>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Manage Authors</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?php if(isset($_SESSION['delmsg']) && $_SESSION['delmsg']!="") {?>
                        <div class="alert alert-success" >
                            <?php echo htmlentities($_SESSION['delmsg']);?>
                        </div>
                    <?php $_SESSION['delmsg']=""; } ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Authors Listing
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="custom-table" id="dataTables">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Author Name</th>
                                            <th>Creation Date</th>
                                            <th>Updation Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php 
$cnt=1;
if($query->rowCount() > 0) {
foreach($results as $result) {               
?>                                      
                                        <tr class="odd gradeX">
                                            <td class="center"><?php echo htmlentities($cnt);?></td>
                                            <td class="center"><?php echo htmlentities($result->AuthorName ?? '');?></td>
                                            <td class="center"><?php echo htmlentities($result->creationDate ?? '');?></td>
                                            <td class="center"><?php echo htmlentities($result->updationDate ?? '');?></td>
                                            <td class="center">
                                                <a href="edit-author.php?id=<?php echo htmlentities($result->id);?>"><button class="custom-btn btn-primary-theme"><i class="fa fa-edit "></i> Edit</button> 
                                                
                                                <a href="manage-authors.php?del=<?php echo htmlentities($result->id);?>" onclick="return confirm('Are you sure you want to delete this author?');" >  
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
</body>
</html>
<?php } ?>