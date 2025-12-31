<?php
session_start();
error_reporting(0);
require('includes/config.php'); 

// Ensure any existing sessions (student or admin) are explicitly cleared before attempting a new login.
if(isset($_SESSION['login']) || isset($_SESSION['alogin'])){
    unset($_SESSION['login']);
    unset($_SESSION['alogin']);
    unset($_SESSION['stdid']);
    unset($_SESSION['fname']); 
}

if(isset($_POST['login'])) {
    
    // 1. Captcha Verification
    if ($_POST["vercode"] != $_SESSION["vercode"] OR $_SESSION["vercode"]=='') {
        echo "<script>alert('Incorrect verification code');</script>" ;
    } else {
        // *** USING PLAINTEXT PASSWORD INPUT FOR SIMPLICITY ***
        $login_input = $_POST['emailid']; 
        $password = $_POST['password']; 

        // 2. CHECK FOR ADMIN CREDENTIALS (tbladmin)
        $sqlAdmin = "SELECT AdminUserName FROM tbladmin WHERE AdminUserName=:login_input AND Password=:password";
        $queryAdmin = $dbh->prepare($sqlAdmin);
        $queryAdmin->bindParam(':login_input', $login_input, PDO::PARAM_STR);
        $queryAdmin->bindParam(':password', $password, PDO::PARAM_STR);
        $queryAdmin->execute();

        if($queryAdmin->rowCount() > 0) {
            // Admin Login Success
            $_SESSION['alogin'] = $login_input; 
            echo "<script type='text/javascript'> document.location ='admin/dashboard.php'; </script>";
        } else {
            // 3. CHECK FOR STUDENT CREDENTIALS (tblstudents)
            // *** DUAL CHECK: EITHER EmailId OR StudentId ***
            $sqlStudent = "SELECT id, StudentId, Status, FullName FROM tblstudents WHERE (EmailId=:login_input OR StudentId=:login_input) AND Password=:password";
            
            $queryStudent = $dbh->prepare($sqlStudent);
            $queryStudent->bindParam(':login_input', $login_input, PDO::PARAM_STR);
            $queryStudent->bindParam(':password', $password, PDO::PARAM_STR);
            $queryStudent->execute();
            $studentResults = $queryStudent->fetchAll(PDO::FETCH_OBJ);

            if($queryStudent->rowCount() > 0) {
                // Student Login Success
                foreach ($studentResults as $result) {
                    $_SESSION['fname'] = $result->FullName;
                    $_SESSION['stdid'] = $result->StudentId;

                    if($result->Status == 1) {  
                        $_SESSION['db_id'] = $result->id;
                        $_SESSION['login'] = $result->FullName; // Use FullName as the 'login' display name
                        echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
                    } else {
                        // Account Blocked
                        echo "<script>alert('Your Account Has been blocked. Please contact admin');</script>";
                    }
                }
            } else {
                // 4. FAILED LOGIN (No match in either table)
                echo "<script>alert('Invalid Details (Username/Email/Student ID and Password).');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <title>Library Management System | Login</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
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
    <link href="assets/css/style.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col antialiased">
    
    <div class="flex-grow py-16 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="max-w-md w-full">
            
            <div class="text-center mb-10">
                <h4 class="text-3xl sm:text-4xl font-extrabold text-gray-800 border-b-4 border-accent-orange pb-1 inline-block">LIBRARY ACCESS</h4>
            </div>

            
            <div class="bg-white p-8 shadow-2xl rounded-xl border-t-8 border-info-blue">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-700">Login to Dashboard</h2>
                    <p class="text-gray-500 text-sm mt-1">Students and Admins use this form.</p>
                </div>
                
                <form role="form" method="post" class="space-y-5">

                    <div class="form-group">
                        <label for="emailid" class="block text-sm font-semibold text-gray-700 mb-1">Email ID / Student ID / Username</label>
                        <input id="emailid" class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" type="text" name="emailid" required autocomplete="off" />
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                        <input id="password" class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" type="password" name="password" required autocomplete="off"  />
                        <p class="mt-2 text-sm text-primary-indigo hover:text-indigo-800 transition duration-150">
                            <a href="forgot-password.php" class="font-medium">Forgot Password</a>
                        </p>
                    </div>

                    <div class="flex items-end space-x-4 pt-2">
                        <div class="flex-1">
                            <label for="vercode" class="block text-sm font-semibold text-gray-700 mb-1">Verification Code :</label>
                            <input id="vercode" type="text" name="vercode" maxlength="5" autocomplete="off" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none text-xl tracking-wider uppercase text-center" />
                        </div>
                        <div class="h-10 border border-gray-300 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
                            <img src="captcha.php" alt="Verification Code" class="h-full w-auto block" style="object-fit: cover; filter: drop-shadow(0 0 1px #00000030);">
                        </div>
                    </div> 

                    <div class="flex justify-center pt-4">
                        <button type="submit" name="login" class="w-full py-3 px-8 bg-info-blue text-white font-bold text-lg rounded-lg shadow-xl hover:bg-blue-600 transition duration-300 transform hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-blue-300">
                            <i class="fas fa-sign-in-alt"></i> LOG IN
                        </button>
                    </div>
                    
                    <div class="text-center pt-2">
                        <a href="signup.php" class="text-sm font-medium text-danger-red hover:text-red-700 transition duration-150 border-b border-transparent hover:border-danger-red">
                            Not Register Yet?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include('includes/footer.php');?>
</body>
</html>