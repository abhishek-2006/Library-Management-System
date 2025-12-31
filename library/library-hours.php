<?php
session_start();
error_reporting(0);
require('includes/config.php'); 

// Ensure only logged-in students can view this page
if(strlen($_SESSION['login'])==0) { 
    header('location:index.php');
    exit(); 
} else {
    $currentPage = 'library-hours.php'; 

    // --- FETCH LIBRARY HOURS FROM DB ---
    // Reads data from the tbllibrarysettings table using the keys inserted by the SQL script
    $sql = "SELECT SettingName, SettingValue FROM tbllibrarysettings WHERE SettingName IN ('hours_mon_fri', 'hours_saturday', 'closed_note')";
    $query = $dbh->prepare($sql);
    $query->execute();
    $settings = $query->fetchAll(PDO::FETCH_KEY_PAIR);

    // Retrieve values, using defaults if not yet set by Admin
    $mon_fri_hours = isset($settings['hours_mon_fri']) ? htmlentities($settings['hours_mon_fri']) : '9:00 AM to 6:00 PM';
    $sat_hours     = isset($settings['hours_saturday']) ? htmlentities($settings['hours_saturday']) : '9:00 AM to 1:00 PM';
    $closed_note   = isset($settings['closed_note']) ? htmlentities($settings['closed_note']) : 'The Library remains **Closed** on Sundays and all public holidays.';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1997/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Modern LMS | Library Hours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJz9T4xWWMaHjQW8gXWlYwN9Nf9s8bC4Q4S4x3J8l0zF0Z9Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container-custom">
            
            <h4 class="header-title-main">Library Timings & Contact</h4>
            
            <div class="dynamic-content-box">
                <h5 class="content-box-title"><i class="fas fa-clock"></i> General Operating Hours</h5>
                
                <div class="hour-detail">
                    <span class="day-label">Monday - Friday:</span>
                    <span class="time-value"><?php echo $mon_fri_hours; ?></span>
                </div>

                <div class="hour-detail">
                    <span class="day-label">Saturday:</span>
                    <span class="time-value"><?php echo $sat_hours; ?></span>
                </div>
                
                <p class="special-note">
                    <i class="fas fa-info-circle"></i> <?php echo $closed_note; ?>
                </p>

                <h5 class="content-box-title mt-4"><i class="fas fa-phone-alt"></i> Contact Information</h5>
                <p class="contact-info"><i class="fas fa-phone-square-alt"></i> Phone: (0123) 456-7890</p>
                <p class="contact-info"><i class="fas fa-envelope-square"></i> Email: library@college.edu</p>
                
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>