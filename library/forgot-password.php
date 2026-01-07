<?php
error_reporting(0);
require('includes/config.php'); 

$error_message = "";
$success_message = "";

if(isset($_POST['change'])) {
    
    // Check for captcha verification
    if ($_POST["vercode"] != $_SESSION["vercode"] OR $_SESSION["vercode"]=='') {
        $error_message = 'Incorrect verification code.';
    } else {
        $email=$_POST['email'];
        $mobile=$_POST['mobile'];
        $newpassword=md5($_POST['newpassword']);

        // Check if Email and Mobile match a student record
        $sql ="SELECT EmailId FROM tblstudents WHERE EmailId=:email and MobileNumber=:mobile";
        $query= $dbh -> prepare($sql);
        $query-> bindParam(':email', $email, PDO::PARAM_STR);
        $query-> bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $query-> execute();

        if($query -> rowCount() > 0) {
            // If match found, update the password
            $con="update tblstudents set Password=:newpassword where EmailId=:email and MobileNumber=:mobile";
            $chngpwd1 = $dbh->prepare($con);
            $chngpwd1-> bindParam(':email', $email, PDO::PARAM_STR);
            $chngpwd1-> bindParam(':mobile', $mobile, PDO::PARAM_STR);
            $chngpwd1-> bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
            $chngpwd1->execute();
            
            $success_message = 'Your Password successfully changed. Redirecting to login...';
            echo "<script>setTimeout(function() { document.location='login.php'; }, 2000);</script>";
        } else {
            $error_message = 'Email ID or Mobile number is invalid.'; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Online Library Management System | Password Recovery</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-indigo': '#4338CA', 
                        'accent-orange': '#F59E0B', 
                        'info-blue': '#3B82F6', 
                        'danger-red': '#EF4444', 
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        .form-input { transition: all 0.3s ease; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .form-input:focus { border-color: #4338CA; box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.25); }
    </style>
    <script type="text/javascript">
    function valid() {
        if(document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value) {
            alert("New Password and Confirm Password Field do not match!");
            document.chngpwd.confirmpassword.focus();
            return false;
        }
        return true;
    }
    </script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col antialiased">
    <div class="flex-grow py-16 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="max-w-md w-full">
            
            <div class="text-center mb-10">
                <h4 class="text-3xl sm:text-4xl font-extrabold text-gray-800 border-b-4 border-accent-orange pb-1 inline-block">PASSWORD RECOVERY</h4>
            </div>

            <?php if($error_message): ?>
            <div class="bg-danger-red/10 border border-danger-red text-danger-red p-3 rounded-lg mb-6 font-medium text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlentities($error_message); ?>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="bg-green-600/10 border border-green-600 text-green-600 p-3 rounded-lg mb-6 font-medium text-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo htmlentities($success_message); ?>
            </div>
            <?php endif; ?>

            <div class="bg-white p-8 shadow-2xl rounded-xl border-t-8 border-primary-indigo">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-700">Change Your Password</h2>
                    <p class="text-gray-500 text-sm mt-1">Verify your registered details to set a new password.</p>
                </div>
                
                <form role="form" name="chngpwd" method="post" onsubmit="return valid();" class="space-y-5">
                    
                    <div class="form-group">
                        <label for="email" class="label-text">Registered Email ID</label>
                        <input id="email" class="form-input" type="email" name="email" required autocomplete="off" />
                    </div>

                    <div class="form-group">
                        <label for="mobile" class="label-text">Registered Mobile No</label>
                        <input id="mobile" class="form-input" type="text" name="mobile" required autocomplete="off" maxlength="10" />
                    </div>

                    <div class="form-group">
                        <label for="newpassword" class="label-text">New Password</label>
                        <input id="newpassword" class="form-input" type="password" name="newpassword" required autocomplete="off" />
                    </div>

                    <div class="form-group">
                        <label for="confirmpassword" class="label-text">Confirm New Password</label>
                        <input id="confirmpassword" class="form-input" type="password" name="confirmpassword" required autocomplete="off" />
                    </div>

                    <div class="flex items-end space-x-4 pt-2">
                        <div class="flex-1">
                            <label for="vercode" class="label-text">Verification Code :</label>
                            <input id="vercode" type="text" name="vercode" maxlength="5" autocomplete="off" required class="form-input w-full px-3 py-2 text-xl tracking-wider uppercase text-center" />
                        </div>
                        <div class="h-10 border border-gray-300 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
                            <img src="captcha.php" alt="Verification Code" class="h-full w-auto block" style="object-fit: cover; filter: drop-shadow(0 0 1px #00000030);">
                        </div>
                    </div> 

                    <div class="pt-4">
                      <button type="submit" name="change" class="w-full py-3 px-4 bg-primary-indigo text-white font-bold text-lg rounded-lg shadow-xl hover:bg-indigo-800 transition duration-300 transform hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-indigo-300">
                        <i class="fas fa-redo-alt mr-2"></i> Change Password
                      </button>
                    </div>

                    <div class="text-center pt-2">
                        <a href="login.php" class="text-sm font-medium text-danger-red hover:text-red-700 transition duration-150">
                            ← Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include('includes/footer.php');?>
</body>
</html>