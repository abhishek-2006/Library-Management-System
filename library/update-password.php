<?php
session_start();
error_reporting(0);
require('includes/config.php');

if(strlen($_SESSION['login'])==0) { 
    header('location:../index.php');
    exit(); 
} else {
    $currentPage = 'change-password.php';
    $msg = $error = "";
    $sid = $_SESSION['stdid'];
    
    // Function to check if the current password is correct
    function checkCurrentPassword($dbh, $sid, $currentPassword) {
        $sql = "SELECT StudentPassword FROM tblstudents WHERE StudentId=:sid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':sid', $sid, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if ($result && password_verify($currentPassword, $result->StudentPassword)) {
            return true;
        }
        return false;
    }

    // Process form submission
    if(isset($_POST['change'])) {
        
        // --- SECURITY & SANITIZATION ---
        $currentPassword = trim($_POST['currentpassword']);
        $newPassword = trim($_POST['newpassword']);
        $confirmPassword = trim($_POST['confirmpassword']);
        // ---------------------------------

        // Input validation (Client-side validation handles matching, but good to check here)
        if ($newPassword !== $confirmPassword) {
            $error = "New password and Confirm password do not match.";
        } 
        // Check if current password is correct
        else if (!checkCurrentPassword($dbh, $sid, $currentPassword)) {
            $error = "The current password you entered is incorrect.";
        } 
        // Update the password
        else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE tblstudents SET StudentPassword=:hashedPassword WHERE StudentId=:sid";
            $updateQuery = $dbh->prepare($updateSql);
            $updateQuery->bindParam(':hashedPassword', $hashedPassword, PDO::PARAM_STR);
            $updateQuery->bindParam(':sid', $sid, PDO::PARAM_STR);

            if ($updateQuery->execute()) {
                $msg = "Password changed successfully! You can now log in with your new password.";
            } else {
                $error = "Password update failed. Please try again.";
            }
        }
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Modern LMS | Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css"/>
    
    <script>
        // Client-side validation to check if new passwords match
        function validate() {
            var newpwd = document.getElementById('newpassword').value;
            var confpwd = document.getElementById('confirmpassword').value;

            if (newpwd !== confpwd) {
                alert("New Password and Confirm Password fields must match.");
                document.getElementById('confirmpassword').focus();
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container-custom">
            
            <h4 class="header-title-main">Change Account Password</h4>
            
            <div class="form-container-box max-w-lg mx-auto">
                <p class="intro-text">
                    Use strong, unique passwords that are at least 8 characters long.
                </p>

                <?php if($msg){?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
                <?php } ?>
                <?php if($error){?>
                    <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                <?php } ?>
                
                <form method="post" name="chngpwd" action="change-password.php" onsubmit="return validate();" class="contact-form">
                    
                    <div class="form-group">
                        <label for="currentpassword">Current Password <span class="required-star">*</span>:</label>
                        <input type="password" id="currentpassword" name="currentpassword" required class="form-input" placeholder="Enter your current password">
                    </div>

                    <div class="form-group">
                        <label for="newpassword">New Password <span class="required-star">*</span>:</label>
                        <input type="password" id="newpassword" name="newpassword" required minlength="8" class="form-input" placeholder="Enter new password (min 8 characters)">
                    </div>

                    <div class="form-group">
                        <label for="confirmpassword">Confirm New Password <span class="required-star">*</span>:</label>
                        <input type="password" id="confirmpassword" name="confirmpassword" required minlength="8" class="form-input" placeholder="Confirm your new password">
                    </div>

                    <button type="submit" name="change" class="btn-submit-primary mt-4">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>