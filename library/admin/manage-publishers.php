<?php
error_reporting(E_ALL); 
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit;
} else { 
    
    // --- Delete Logic ---
    if(isset($_GET['del'])) {
        $id_to_delete = intval($_GET['del']); 
        $sql = "DELETE FROM tblpublishers WHERE id=:id"; 
        $query = $dbh->prepare($sql);
        $query -> bindParam(':id', $id_to_delete, PDO::PARAM_INT);
        $query -> execute();
        
        $_SESSION['delmsg']="Publisher deleted successfully"; 
        header('location:manage-publishers.php');
        exit;
    }

    // --- Database Query for Publishers Listing ---
    $sql = "SELECT id, PublisherName, CreationDate, UpdationDate FROM tblpublishers";
    $query = $dbh -> prepare($sql);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Manage Publishers</title>
    
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Manage Publishers</h4>
            </div>

            <div class="message-wrapper">
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
            
            <div class="table-container-wrapper">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Publishers Listing
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="custom-table" id="publishersTable"> 
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Publisher Name</th>
                                        <th>Creation Date</th>
                                        <th>Updation Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $cnt=1;
                                    if($query->rowCount() > 0) {
                                        foreach($results as $result) {?>
                                            <tr class="odd gradeX">
                                                <td class="center"><?php echo htmlentities($cnt);?></td>
                                                <td class="center"><?php echo htmlentities($result->PublisherName ?? '');?></td>
                                                <td class="center"><?php echo htmlentities($result->CreationDate ?? '');?></td>
                                                <td class="center"><?php echo htmlentities($result->UpdationDate ?? '');?></td>
                                                <td class="center">
                                                    <a href="edit-publisher.php?id=<?php echo htmlentities($result->id);?>">
                                                        <button class="custom-btn btn-primary-theme"><i class="fa fa-edit "></i> Edit</button> 
                                                    </a>
                                                    <a href="manage-publishers.php?del=<?php echo htmlentities($result->id);?>" onclick="return confirm('Are you sure you want to delete this Publisher?');" >  
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
    
    <?php include('includes/footer.php');?>
    
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            $('#publishersTable').dataTable(); 
        });
    </script>
</body>
</html>
<?php } ?>