<?php
session_start();
error_reporting(E_ALL);
include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    // --- 1. Form Submission Handling (POST) ---
    if(isset($_POST['update'])) {
        $studentid = $_POST['studentid'];
        $fullname = $_POST['fullname'];
        $emailid = $_POST['emailid'];
        $mobilenumber = $_POST['mobilenumber'];
        $sid = intval($_GET['stdid']); 

        // SQL to update student data
        $sql = "UPDATE tblstudents SET 
                    StudentId=:studentid, 
                    FullName=:fullname, 
                    EmailId=:emailid, 
                    MobileNumber=:mobilenumber,
                    UpdationDate = NOW()
                WHERE StudentId=:sid";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
        $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $query->bindParam(':emailid', $emailid, PDO::PARAM_STR);
        $query->bindParam(':mobilenumber', $mobilenumber, PDO::PARAM_STR);
        $query->bindParam(':sid', $sid, PDO::PARAM_INT);
        
        if($query->execute()) {
            $_SESSION['msg'] = "Student record updated successfully!";
            header('location:reg-students.php');
            exit();
        } else {
            $_SESSION['error'] = "Error updating student record. Please try again.";
        }
    }

    // --- 2. Data Fetching (GET) ---
    $sid = intval($_GET['stdid']);
    $sql_fetch = "SELECT StudentId, FullName, EmailId, MobileNumber, RegDate FROM tblstudents WHERE StudentId=:stdid";
    $query_fetch = $dbh->prepare($sql_fetch);
    $query_fetch->bindParam(':stdid', $sid, PDO::PARAM_INT);
    $query_fetch->execute();
    $result = $query_fetch->fetch(PDO::FETCH_OBJ);
    
    if (!$result) {
        $_SESSION['error'] = "Student ID not found.";
        header('location:reg-students.php');
        exit();
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Update Student Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <style>
        
    </style>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">UPDATE STUDENT DETAILS</h4>
            </div>

            <div class="form-container-wrapper">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Student: <?php echo htmlentities($result->FullName); ?> (ID: <?php echo htmlentities($result->StudentId); ?>)
                    </div>
                    <div class="panel-body">
                        <form name="updatestudent" method="post">
                            <div class="form-group">
                                <label>Student ID</label>
                                <input class="form-control" type="text" name="studentid" value="<?php echo htmlentities($result->StudentId);?>" required />
                            </div>

                        <div class="form-group">
                                <label>Full Name</label>
                                <input class="form-control" type="text" name="fullname" value="<?php echo htmlentities($result->FullName);?>" required />
                            </div>
                            
                            <div class="form-group">
                                <label>Email ID</label>
                                <input class="form-control" type="email" name="emailid" value="<?php echo htmlentities($result->EmailId);?>" required />
                            </div>

                            <div class="form-group">
                                <label>Mobile Number</label>
                                <input class="form-control" type="text" name="mobilenumber" value="<?php echo htmlentities($result->MobileNumber);?>" required maxlength="11" />
                            </div>

                            <div class="form-group">
                                <label>Registration Date</label>
                                <input class="form-control" type="text" value="<?php echo date('d-m-Y', strtotime(htmlentities($result->RegDate)));?>" disabled />
                            </div>

                            <button type="submit" name="update" class="update-btn"><i class="fa fa-sync-alt"></i> Update Details</button>
                            <a href="reg-students.php" class="back-link"><i class="fa fa-arrow-left"></i> Back to Students</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php');?>
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
</body>
</html>
<?php } ?>
