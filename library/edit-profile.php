<?php
session_start();
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['login'])==0){
    header("location:../index.php");
    exit();
}

$sid = $_SESSION['stdid'];
$msg = $error = "";

// =======================
// FETCH USER DETAILS
// =======================
$sql = "SELECT StudentId, FullName, EmailId, MobileNumber FROM tblstudents WHERE StudentId=:sid";
$query = $dbh->prepare($sql);
$query->bindParam(':sid', $sid, PDO::PARAM_STR);
$query->execute();
$user = $query->fetch(PDO::FETCH_OBJ);

if(!$user){
    die("Error: Unable to load profile data.");
}

// =======================
// UPDATE PROFILE
// =======================
if(isset($_POST['updateProfile'])){
    $fname  = $_POST['fullname'];
    $mobile = $_POST['mobileno'];
    $email  = $_POST['emailid'];

    $checkSql = "SELECT StudentId FROM tblstudents WHERE EmailId=:email AND StudentId!=:sid";
    $checkQ = $dbh->prepare($checkSql);
    $checkQ->bindParam(':email',$email,PDO::PARAM_STR);
    $checkQ->bindParam(':sid',$sid,PDO::PARAM_STR);
    $checkQ->execute();
    
    if($checkQ->rowCount() > 0){
        $error = "Email already used by another account.";
    } else {
        $updateSql = "UPDATE tblstudents 
        SET FullName=:fname, MobileNumber=:mobile, EmailId=:email 
        WHERE StudentId=:sid";

        $updateQ = $dbh->prepare($updateSql);
        $updateQ->bindParam(':fname',$fname);
        $updateQ->bindParam(':mobile',$mobile);
        $updateQ->bindParam(':email',$email);
        $updateQ->bindParam(':sid',$sid);

        if($updateQ->execute()){
            $_SESSION['login'] = $fname;
            header("location: my-profile.php?updated=1");
            exit();
        } else {
            $error = "Failed to update. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Profile</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#2563eb",
                        accent: "#10b981",
                        danger: "#ef4444",
                    }
                }
            }
        }
    </script>

</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-200 p-4">

    <div class="backdrop-blur-xl bg-white/60 p-10 rounded-2xl shadow-2xl w-full max-w-lg border border-white/40">

        <h2 class="text-4xl font-bold text-gray-800 text-center mb-6">
            Edit Profile
        </h2>

        <?php if($error){ ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlentities($error); ?>
            </div>
        <?php } ?>

        <form method="POST">

            <label class="font-medium text-gray-700">Full Name</label>
            <input type="text" name="fullname" value="<?php echo htmlentities($user->FullName); ?>"
                class="w-full mb-4 px-4 py-3 rounded-lg bg-white/70 border border-gray-300 focus:ring-2 focus:ring-primary">
            
            <label class="font-medium text-gray-700">Mobile Number</label>
            <input type="text" name="mobileno" value="<?php echo htmlentities($user->MobileNumber); ?>"
                class="w-full mb-4 px-4 py-3 rounded-lg bg-white/70 border border-gray-300 focus:ring-2 focus:ring-primary">

            <label class="font-medium text-gray-700">Email ID</label>
            <input type="email" name="emailid" value="<?php echo htmlentities($user->EmailId); ?>"
                class="w-full mb-4 px-4 py-3 rounded-lg bg-white/70 border border-gray-300 focus:ring-2 focus:ring-primary">

            <button type="submit" name="updateProfile"
                class="w-full bg-primary text-white py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition">
                <i class="fa-solid fa-save mr-2"></i>
                Save Changes
            </button>

            <a href="my-profile.php"
                class="block text-center mt-5 text-primary font-medium hover:underline">
                ← Cancel & Go Back
            </a>
        </form>

    </div>

</body>
</html>
