<?php 
require('includes/config.php');
error_reporting(0);

if(isset($_POST['signup'])) {
    
    //code for captcha verification
    if ($_POST["vercode"] != $_SESSION["vercode"] OR $_SESSION["vercode"]=='') {
        echo "<script>alert('Incorrect verification code');</script>" ;
    } else {
        //Code for student ID (using file-based counter)
        $count_my_page = ("studentid.txt");
        $hits = file($count_my_page);
        $hits[0] ++;
        $fp = fopen($count_my_page , "w");
        fputs($fp , "$hits[0]");
        fclose($fp); 
        $StudentId= $hits[0];
        $fname=$_POST['fullname'];
        $mobileno=$_POST['mobileno'];
        $email=$_POST['email']; 
        $password=md5($_POST['password']); 
        $status=1;
        
        // Database insertion using PDO
        $sql="INSERT INTO tblstudents(StudentId,FullName,MobileNumber,EmailId,Password,Status) VALUES(:StudentId,:fname,:mobileno,:email,:password,:status)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':StudentId',$StudentId,PDO::PARAM_STR);
        $query->bindParam(':fname',$fname,PDO::PARAM_STR);
        $query->bindParam(':mobileno',$mobileno,PDO::PARAM_STR);
        $query->bindParam(':email',$email,PDO::PARAM_STR);
        $query->bindParam(':password',$password,PDO::PARAM_STR);
        $query->bindParam(':status',$status,PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        
        if($lastInsertId) {
            // Success: Display alert and redirect to the login page (login.php)
            echo '<script>';
            echo 'alert("Your Registration was successful and your student ID is: '.$StudentId.'");';
            echo 'document.location = "login.php";'; // Redirect command
            echo '</script>';
        } else {
            // Failure: Display error message
            echo "<script>alert('Something went wrong. Please try again');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Library Management System | Student Signup</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-indigo': '#4338CA',
                        'accent-orange': '#F59E0B',
                        'danger-red': '#EF4444',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link href="assets/css/style.css" rel="stylesheet"/>
    <style>
        .form-input {
            transition: all 0.3s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .form-input:focus {
            border-color: #4338CA; /* Primary Indigo */
            box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.25);
        }
    </style>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script type="text/javascript">
    
    function valid() {
        if(document.signup.password.value!= document.signup.confirmpassword.value) {
            alert("Password and Confirm Password Field do not match !!");
            document.signup.confirmpassword.focus();
            return false;
        }
        return true;
        }
        
    function checkAvailability() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "check_availability.php",
            data:'emailid='+$("#emailid").val(),
            type: "POST",
            success:function(data){
                $("#user-availability-status").html(data);
                $("#loaderIcon").hide();
            },
            error:function (){}
        });
        }
    </script> 
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col antialiased">
    
    <div class="flex-grow py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl mx-auto">
            
            <div class="text-center mb-8">
                <h4 class="text-3xl sm:text-4xl font-extrabold text-gray-800 border-b-4 border-accent-orange pb-1 inline-block">Student Registration</h4>
            </div>

            <div class="bg-white p-6 sm:p-8 md:p-10 shadow-2xl rounded-xl border-t-8 border-danger-red">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-700">CREATE YOUR ACCOUNT</h2>
                    <p class="text-gray-500 text-sm mt-1">Fill out the form to get your unique Student ID.</p>
                </div>
                
                <form name="signup" method="post" onsubmit="return valid();" class="space-y-5">
                    
                    <div>
                        <label for="fullname" class="block text-sm font-semibold text-gray-700 mb-1">Enter Full Name</label>
                        <input id="fullname" class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" type="text" name="fullname" autocomplete="off" required />
                    </div>
                    
                    <div>
                        <label for="mobileno" class="block text-sm font-semibold text-gray-700 mb-1">Mobile Number :</label>
                        <input id="mobileno" class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" type="text" name="mobileno" maxlength="10" autocomplete="off" required />
                    </div>
                    
                    <div>
                        <label for="emailid" class="block text-sm font-semibold text-gray-700 mb-1">Enter Email</label>
                        <input id="emailid" class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" type="email" name="email" onblur="checkAvailability()" autocomplete="off" required />
                        <span id="user-availability-status" class="mt-1 text-xs font-medium" style="min-height: 0.75rem; display: block;"></span> 
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Enter Password</label>
                        <input id="password" class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" type="password" name="password" autocomplete="off" required />
                    </div>
                    
                    <div>
                        <label for="confirmpassword" class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password </label>
                        <input id="confirmpassword" class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" type="password" name="confirmpassword" autocomplete="off" required />
                    </div>
                    
                    <div class="flex items-end space-x-4">
                        <div class="flex-1">
                            <label for="vercode" class="block text-sm font-semibold text-gray-700 mb-1">Verification Code :</label>
                            <input id="vercode" type="text" name="vercode" maxlength="5" autocomplete="off" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none text-xl tracking-wider uppercase text-center" />
                        </div>
                        <div class="h-10 border border-gray-300 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
                            <img src="captcha.php" alt="Verification Code" class="h-full w-auto block" style="object-fit: cover; filter: drop-shadow(0 0 1px #00000030);">
                        </div>
                    </div>
                    
                    <button type="submit" name="signup" class="w-full py-3 px-4 bg-danger-red text-white font-bold text-lg rounded-lg shadow-xl hover:bg-red-600 transition duration-300 transform hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-red-300" id="submit">
                        Register Now
                    </button>

                    <div class="text-center pt-3">
                        <a href="login.php" class="text-sm font-medium text-primary-indigo hover:text-indigo-800 transition duration-150">Already have an account? Login here.</a>
                    </div>
                </form>
                
            </div>
            
        </div>
    </div>
    <?php include('includes/footer.php'); ?>
    </body>
</html>