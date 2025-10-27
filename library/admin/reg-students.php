<?php
session_start();
error_reporting(E_ALL); 
include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit;
} else { 
    
    // --- Delete Logic ---
    if(isset($_GET['del'])) {
        $id=$_GET['del'];
        // SQL to delete the student record from the tblstudents table using PDO prepared statement
        $sql = "DELETE FROM tblstudents WHERE StudentId=:studentid"; 
        $query = $dbh->prepare($sql);
        $query -> bindParam(':studentid',$studentid, PDO::PARAM_STR);
        $query -> execute();
        
        // Set success message
        $_SESSION['delmsg']="Student record deleted successfully"; 
        
        // Redirect back to the same page
        header('location:reg-students.php');
        exit;
    }

    // --- Database Query for Registered Students ---
    // Selecting all necessary student fields. Assumes columns are id, FullName, StudentId, etc.
    $sql = "SELECT 
                StudentId,                  /* Student Roll Number / Unique ID */
                FullName,                   /* Student Name */
                EmailId, 
                MobileNumber, 
                RegDate, 
                Status 
            FROM tblstudents";
    $query = $dbh -> prepare($sql);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Manage Registered Students</title>
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <style type="text/css"> 
        /* Status Styling (Active/Blocked) */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .active-status { 
            background-color: #d1fae5; /* Light Green */
            color: #065f46; /* Dark Green text */
        } 
        .blocked-status { 
            background-color: #fee2e2; /* Light Red */
            color: #991b1b; /* Dark Red text */
        } 
        /* Message Styling */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 8px;
}
.alert-success {
    background-color: #d1fae5; /* Light Green */
    border-color: #a7f3d0;
    color: #065f46; /* Dark Green text */
}
.alert-danger {
    background-color: #fee2e2; /* Light Red */
    border-color: #fecaca;
    color: #991b1b; /* Dark Red text */
}
    </style>
</head>
<body>
    <?php include('includes/header.php');?>
<div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="header-line">Manage Registered Students</h4>
                </div>
            </div>

            <div class="row">
                <?php 
                if(isset($_SESSION['error']) && $_SESSION['error']!="") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-danger" >
                            <strong>Error :</strong> 
                            <?php echo htmlentities($_SESSION['error']); $_SESSION['error']=""; ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if(isset($_SESSION['msg']) && $_SESSION['msg']!="") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-success" >
                            <strong>Success :</strong> 
                            <?php echo htmlentities($_SESSION['msg']); $_SESSION['msg']=""; ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if(isset($_SESSION['updatemsg']) && $_SESSION['updatemsg']!="") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-success" >
                            <strong>Success :</strong> 
                            <?php echo htmlentities($_SESSION['updatemsg']); $_SESSION['updatemsg']=""; ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if(isset($_SESSION['delmsg']) && $_SESSION['delmsg']!="") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-success" >
                            <strong>Success :</strong> 
                            <?php echo htmlentities($_SESSION['delmsg']); $_SESSION['delmsg']=""; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel-default">
                        <div class="panel-heading">
                            Registered Students Listing
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="custom-table" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Email Id</th>
                                            <th>Mobile Number</th>
                                            <th>Reg Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $cnt=1;
                                        if($query->rowCount() > 0) {
                                            foreach($results as $result) { ?>
                                                <tr class="odd gradeX">
                                                    <td class="center"><?php echo htmlentities($cnt);?></td>
                                                    <!-- Uses StudentId column from query -->
                                                    <td class="center"><?php echo htmlentities($result->StudentId);?></td>
                                                    <!-- Uses FullName column from query -->
                                                    <td class="center"><?php echo htmlentities($result->FullName);?></td>
                                                    <td class="center"><?php echo htmlentities($result->EmailId);?></td>
                                                    <td class="center"><?php echo htmlentities($result->MobileNumber);?></td>
                                                    <td class="center"><?php echo htmlentities($result->RegDate);?></td>
                                                    <td class="center">
                                                        <?php if($result->Status == 1) { ?>
                                                            <span class="status-badge active-status"><?php echo htmlentities("Active"); ?></span>
                                                            <?php } else { ?>
                                                                <span class="status-badge blocked-status"><?php echo htmlentities("Blocked"); ?></span>
                                                                <?php } ?>
                                                    </td>
                                                    <td>
                                                        <!-- Edit Button - linking to an assumed edit-student.php page -->
                                                        <a href="edit-student.php?stdid=<?php echo htmlentities($result->StudentId);?>"><button class="custom-btn btn-primary-theme"><i class="fa fa-edit "></i> Edit</button> 
                                                        
                                                        <!-- Delete Button - passes the primary key 'studentid' as 'del' parameter -->
                                                        <a href="reg-students.php?del=<?php echo htmlentities($result->StudentId);?>" onclick="return confirm('Are you sure you want to delete this student record?');" >  
                                                        <button class="custom-btn btn-danger-theme"><i class="fa fa-trash"></i> Delete</button>
                                                        </a>
                                                    </td>
                                                </tr>
                                        <?php $cnt++; } 
                                        } ?> 
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
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script> 
</body>
</html>
<?php } ?>
