<?php
session_start();
error_reporting(E_ALL);
include('includes/config.php');

if(strlen($_SESSION['login'])==0) { 
    header('location:index.php');
    exit(); 
} else {
    $currentPage = 'my-profile.php';
    $msg = $error = "";

    $sid = $_SESSION['stdid'];
    
    // Process form submission for profile update
    if(isset($_POST['update'])) {
        $fname = $_POST['fullname'];
        $mobileno = $_POST['mobileno'];
        $emailid = $_POST['emailid']; 

        // 1. Check if the new email is already registered to another user
        $checkSql = "SELECT StudentId FROM tblstudents WHERE EmailId=:emailid AND StudentId != :sid";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':emailid', $emailid, PDO::PARAM_STR);
        $checkQuery->bindParam(':sid', $sid, PDO::PARAM_STR);
        $checkQuery->execute();

        if($checkQuery->rowCount() > 0) {
            $error = "Update failed: This email address is already registered to another user.";
        } else {
            // 2. Execute the Update Query
            $sql = "UPDATE tblstudents SET FullName=:fname, MobileNumber=:mobileno, EmailId=:emailid WHERE StudentId=:sid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':fname', $fname, PDO::PARAM_STR);
            $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
            $query->bindParam(':emailid', $emailid, PDO::PARAM_STR);
            $query->bindParam(':sid', $sid, PDO::PARAM_STR);
            
            if ($query->execute()) {
                $_SESSION['login'] = $fname; // Update the session display name
                $msg = "Profile updated successfully!";
            } else {
                $error = "Update failed. Please try again.";
            }
        }
    }
    
    // Fetch current student details
    $sql = "SELECT StudentId, FullName, EmailId, MobileNumber, RegDate, Status FROM tblstudents WHERE StudentId=:sid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':sid', $sid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    // If no user found (safe check)
    if(!$result) {
        $error = "Error: User profile data could not be retrieved.";
    }

    // --- FINE VIEW LOGIC ---
    $fineResults = [];
    $fineRate = '₹10.00';

    // ... around line 80 ...
    if (isset($_GET['view']) && $_GET['view'] == 'fine') {
        
        $sqlFine = "
            SELECT
                tblbooks.BookName,
                tblissuedbookdetails.IssuesDate,
                tblissuedbookdetails.ReturnDate,
                tblissuedbookdetails.fine        
            FROM
                tblissuedbookdetails
            JOIN
                tblbooks ON tblbooks.id = tblissuedbookdetails.BookId
            WHERE
                tblissuedbookdetails.StudentID = :sid
                AND tblissuedbookdetails.RetrunStatus = 1  
                AND tblissuedbookdetails.fine > 0          
            ORDER BY
                tblissuedbookdetails.ReturnDate DESC
        ";

        $queryFine = $dbh->prepare($sqlFine);
        $queryFine->bindParam(':sid', $sid, PDO::PARAM_STR);
        $queryFine->execute(); 
        $fineResults = $queryFine->fetchAll(PDO::FETCH_ASSOC);
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Modern LMS | My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css"/>
    
    <!-- Custom CSS for Navigation and Responsive Table -->
    <style>
        .profile-nav-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .nav-tab {
            padding: 12px 20px;
            text-decoration: none;
            color: #555;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            margin: 0 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-tab:hover {
            color: #007bff;
            border-bottom: 3px solid #b3d9ff;
        }
        .nav-tab.active {
            color: #007bff;
            border-bottom: 3px solid #007bff;
            font-weight: 700;
        }
        
        .fine-table-container {
            overflow-x: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }
        .fine-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px; /* Ensure table is readable on small screens */
        }
        .fine-table th, .fine-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .fine-table th {
            background-color: #f4f7fa;
            color: #333;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .fine-table tbody tr:last-child td {
            border-bottom: none;
        }
        .total-fine-row {
            font-weight: 700;
            background-color: #e6f7ff;
            border-top: 2px solid #007bff;
        }
        .text-late { color: #dc3545; font-weight: 600; }
        .text-fine { color: #28a745; font-weight: 700; }

        @media screen and (max-width: 600px) {
            .profile-nav-tabs {
                flex-direction: column;
                align-items: stretch;
            }
            .nav-tab {
                justify-content: center;
                border-bottom: none;
            }
            .nav-tab.active {
                border-bottom: 3px solid #007bff;
            }
        }
    </style>
    
    <script>
        // Client-side validation for mobile number
        function checkMobile() {
            var mobilenum = document.getElementById('mobileno').value;
            // The pattern="\d{10}" in HTML handles basic validation, 
            // but this client-side check provides an immediate alert (if not using custom modal)
            if (mobilenum.length !== 10) {
                // Since alert() is discouraged, use console log or assume a custom modal is triggered by the HTML pattern
                console.error("Mobile number must be 10 digits long.");
                // For a standard PHP environment, we might use return false, but ideally, this check is handled visually by the browser due to the pattern attribute.
                return true; 
            }
            return true;
        }
    </script>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container-custom">
            
            <h4 class="header-title-main">Student Account Dashboard</h4>
            
            <div class="profile-nav-tabs">
                <a href="my-profile.php" class="nav-tab <?php if(!isset($_GET['view']) || $_GET['view'] == 'profile') echo 'active'; ?>">
                    <i class="fas fa-user-circle"></i> Profile Details
                </a>
                <a href="my-profile.php?view=fine" class="nav-tab <?php if(isset($_GET['view']) && $_GET['view'] == 'fine') echo 'active'; ?>">
                    <i class="fas fa-money-bill-wave"></i> Fines & Dues
                </a>
            </div>

            <?php if($msg){?>
                <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
            <?php } ?>
            <?php if($error){?>
                <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
            <?php } ?>
            
            
            <?php if (!isset($_GET['view']) || $_GET['view'] == 'profile'): // --- START PROFILE VIEW --- ?>

                <div class="form-container-box max-w-lg mx-auto">
                    <p class="intro-text">
                        Review your current account information below. Click 'Edit Profile' to make changes.
                    </p>

                    <?php if($result): ?>
                    
                    <div class="profile-view-box">
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-id-badge"></i> Student ID:</span>
                            <span class="info-value"><?php echo htmlentities($result->StudentId);?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-user"></i> Full Name:</span>
                            <span class="info-value"><?php echo htmlentities($result->FullName);?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-envelope"></i> Email ID:</span>
                            <span class="info-value"><?php echo htmlentities($result->EmailId);?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-phone"></i> Mobile Number:</span>
                            <span class="info-value"><?php echo htmlentities($result->MobileNumber);?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-calendar-alt"></i> Registration Date:</span>
                            <span class="info-value"><?php echo htmlentities(date('d-M-Y', strtotime($result->RegDate)));?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-lock"></i> Account Status:</span>
                            <span class="info-value status-<?php echo ($result->Status == 1) ? 'active' : 'blocked'; ?>-badge">
                                <?php echo ($result->Status == 1) ? 'Active' : 'Blocked'; ?>
                            </span>
                        </div>

                        <label for="edit-toggle" class="btn-submit-primary mt-6 cursor-pointer">
                            <i class="fas fa-edit"></i> Edit Profile
                        </label>
                    </div>
                    
                    <input type="checkbox" id="edit-toggle" hidden>
                    <div class="profile-edit-form-wrapper">
                        <hr class="my-6 border-gray-200">
                        <h5 class="text-lg font-bold text-gray-700 mb-4">Update Your Information</h5>
                        
                        <form method="post" name="signup" action="my-profile.php" onsubmit="return checkMobile();" class="contact-form">
                            
                            <div class="form-group">
                                <label for="studentid">Student ID (Roll Number):</label>
                                <input type="text" id="studentid" value="<?php echo htmlentities($result->StudentId);?>" readonly class="form-input form-input-readonly">
                            </div>

                            <div class="form-group">
                                <label for="fullname">Full Name <span class="required-star">*</span>:</label>
                                <input type="text" id="fullname" name="fullname" value="<?php echo htmlentities($result->FullName);?>" required placeholder="Enter your full name" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="emailid">Email ID <span class="required-star">*</span>:</label>
                                <input type="email" id="emailid" name="emailid" value="<?php echo htmlentities($result->EmailId);?>" required placeholder="Enter your new email address" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="mobileno">Mobile Number <span class="required-star">*</span>:</label>
                                <input type="text" id="mobileno" name="mobileno" value="<?php echo htmlentities($result->MobileNumber);?>" required maxlength="10" pattern="\d{10}" title="Mobile number must be 10 digits" class="form-input" placeholder="Enter your 10-digit mobile number">
                            </div>

                            <div class="flex justify-between items-center mt-4">
                                <button type="submit" name="update" class="btn-submit-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <label for="edit-toggle" class="text-danger-red hover:text-red-700 cursor-pointer font-medium">
                                    Cancel Edit
                                </label>
                            </div>
                            
                        </form>
                    </div>
                    
                    <?php endif; ?>
                </div>

            <?php elseif (isset($_GET['view']) && $_GET['view'] == 'fine'): // --- START FINE VIEW --- ?>

                <div class="max-w-4xl mx-auto">
                    <h5 class="text-xl font-bold text-gray-800 mb-4">Overdue and Late Return Fine History</h5>
                    <p class="intro-text">
                        This table shows all books that were returned after their expected return date. The fine rate is: <?php echo htmlentities($fineRate); ?> per day.
                    </p>

                    <div class="fine-table-container mt-6">
                        <table class="fine-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Book Name</th>
                                    <th>Issue Date</th>
                                    <th>Expected Return</th>
                                    <th>Actual Return</th>
                                    <th>Days Late</th>
                                    <th>Fine (<?php echo htmlentities($fineRate); ?>/day)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $count = 1;
                                $totalFine = 0;
                                if (!empty($fineResults)): 
                                    foreach ($fineResults as $row):
                                        $fine = $row['DaysLate'] * $fineRate;
                                        $totalFine += $fine;
                                ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo htmlentities($row['BookName']); ?></td>
                                        <td><?php echo htmlentities(date('d-M-Y', strtotime($row['IssuesDate']))); ?></td>
                                        <td><?php echo htmlentities(date('d-M-Y', strtotime($row['ExpectedReturnDate']))); ?></td>
                                        <td><?php echo htmlentities(date('d-M-Y', strtotime($row['ReturnDate']))); ?></td>
                                        <td class="text-late"><?php echo htmlentities($row['DaysLate']); ?></td>
                                        <td class="text-fine"><?php echo number_format($fine, 2); ?></td>
                                    </tr>
                                <?php 
                                    endforeach; 
                                else:
                                ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 30px; color: #555;">
                                            <i class="fas fa-hand-holding-usd fa-2x mb-2" style="color: #007bff;"></i>
                                            <p>No late return fines recorded for your account.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($fineResults)): ?>
                                <tr class="total-fine-row">
                                    <td colspan="6" style="text-align: right;">Total Fine Due (Accumulated):</td>
                                    <td class="text-fine"><?php echo number_format($totalFine, 2); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; // --- END FINE VIEW --- ?>
            
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>
