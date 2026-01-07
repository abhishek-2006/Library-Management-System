<?php
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    // Security Check: Redirect if not logged in
    header('location:../../index.php');
    exit();
}
else {
    $admin_username = $_SESSION['alogin'];

    // --- Function to check current password ---
    function checkPassword($currentPass, $username, $dbh) {
        // Hash the input current password using MD5 for comparison
        $hashedCurrentPass = md5($currentPass);
        
        $sql = "SELECT Password FROM tbladmin WHERE AdminUserName=:username";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if($query->rowCount() > 0) {
            // Compare the MD5 hash from the database with the hashed input
            return $result->Password === $hashedCurrentPass;
        }
        return false;
    }

    // --- Form Submission Handling ---
    if(isset($_POST['change'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // 1. Validate if new passwords match (client-side script also helps here)
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "New Password and Confirm Password do not match.";
        } 
        // 2. Verify current password
        else if (!checkPassword($currentPassword, $admin_username, $dbh)) {
            $_SESSION['error'] = "Error: Your Current Password is incorrect.";
        } 
        // 3. Update the password
        else {
            // Hash the new password using MD5 before updating
            $hashedNewPassword = md5($newPassword);
            
            $update_sql = "UPDATE tbladmin SET Password=:new_password, updationDate=CURRENT_TIMESTAMP WHERE AdminUserName=:username";
            $update_query = $dbh->prepare($update_sql);
            
            $update_query->bindParam(':new_password', $hashedNewPassword, PDO::PARAM_STR);
            $update_query->bindParam(':username', $admin_username, PDO::PARAM_STR);
            
            if($update_query->execute()) {
                $_SESSION['msg'] = "Password changed successfully!";
                // Invalidate the session slightly to force a re-login soon, for better security
                // Optional: session_destroy(); header('location:index.php');
            } else {
                $_SESSION['error'] = "Error changing password. Please try again.";
            }
        }
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Admin Change Password</title>
    
    <!-- Assuming your project uses a standard CSS setup -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <script type="text/javascript">
        // Client-side validation to ensure new passwords match
        function validate() {
            var newPass = document.getElementById('new_password').value;
            var confPass = document.getElementById('confirm_password').value;
            
            if (newPass !== confPass) {
                alert("New Password and Confirm Password do not match!");
                document.getElementById('new_password').focus();
                return false;
            }
            return true;
        }
    </script>

</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Change Admin Password</h4>
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
            
            <div class="form-container-wrapper medium-form"> 
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Update Password for <?php echo htmlentities($admin_username); ?>
                    </div>
                    <div class="panel-body">
                        <form name="chngpwd" method="post" onSubmit="return validate();">
                            
                            <!-- Current Password -->
                            <div class="form-group">
                                <label for="current_password">Enter Current Password</label>
                                <input class="form-control" type="password" id="current_password" name="current_password" required autocomplete="off" />
                            </div>
                            
                            <!-- New Password -->
                            <div class="form-group">
                                <label for="new_password">Enter New Password</label>
                                <input class="form-control" type="password" id="new_password" name="new_password" required autocomplete="off" />
                            </div>
                            
                            <!-- Confirm New Password -->
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input class="form-control" type="password" id="confirm_password" name="confirm_password" required autocomplete="off" />
                            </div>
                            
                            <button type="submit" name="change" class="update-btn">
                                <i class="fa fa-sync"></i> Change Password
                            </button>
                            
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
