<?php
session_start();
// Set error reporting to E_ALL
error_reporting(E_ALL); 
require('includes/config.php');

// 1. Security Check
if(strlen($_SESSION['alogin'])==0) { 	
    header('location:../../index.php');
    exit;
} else { 
    // 2. Handle Book Return Submission
    if(isset($_POST['return'])) {
        $rid=intval($_GET['rid']);
        $fine=$_POST['fine'];
        $rstatus=1;
        // Using Y-m-d H:i:s format for database
        $returnDate = date('Y-m-d H:i:s'); 

        $sql="UPDATE tblissuedbookdetails SET fine=:fine, ReturnStatus=:rstatus, ReturnDate=:returnDate WHERE id=:rid";
        $query = $dbh->prepare($sql);
        
        $query->bindParam(':rid',$rid,PDO::PARAM_STR);
        $query->bindParam(':fine',$fine,PDO::PARAM_STR);
        $query->bindParam(':rstatus',$rstatus,PDO::PARAM_STR);
        $query->bindParam(':returnDate',$returnDate,PDO::PARAM_STR); 
        
        $query->execute();

        $_SESSION['msg']="Book Returned successfully";
        header('location:manage-issued-books.php');
        exit;
    }

    // 3. Start HTML Content Generation
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Issued Book Details" />
    <meta name="author" content="" />
    <title>Online Library Management System | Issued Book Details</title>
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script>
    function getstudent() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "get_student.php",
            data:'studentid='+$("#studentid").val(),
            type: "POST",
            success:function(data){$("#get_student_name").html(data);
                $("#loaderIcon").hide();
            },
            error:function (){}
        });
    }

    function getbook() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "get_book.php",
            data:'bookid='+$("#bookid").val(),
            type: "POST",
            success:function(data) {
                $("#get_book_name").html(data);
                $("#loaderIcon").hide();
            },error:function (){}});}
    </script>
<style type="text/css">
        /* General Page Background (Subtle Dark/Light Contrast) */
        body {
            background-color: #f7f9fb; /* Very light, slightly cool gray */
        }
        
        /* Base Container */
        .custom-center-container {
            width: 70%;
            max-width: 850px;
            margin: 30px auto; /* Added margin for visual lift */
        }

        /* Panel Styling: The Card */
        .custom-panel {
            border: none; /* Removed hard border for a cleaner look */
            border-radius: 12px; /* Smoother corners */
            margin-bottom: 30px;
            background-color: #ffffff;
            /* Stronger, more modern elevation */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Panel Heading: The Theme Accent */
        .custom-panel-heading {
            padding: 1.25rem 1.5rem;
            color: #ffffff; 
            /* Striking Teal/Cyan Solid Color */
            background-color: #00bcd4; 
            border-bottom: 3px solid #00a4b8; /* Darker bottom line for depth */
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .custom-panel-body {
            padding: 2rem;
        }

        /* Detail Group Styling: Clean separation */
        .custom-form-group {
            padding: 1rem 0;
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #eef1f5; /* Faint separator line */
        }
        
        .custom-panel-body .custom-form-group:last-of-type {
            border-bottom: none;
        }

        .custom-form-group label {
            font-weight: 600; 
            color: #495057; /* Darker label for better contrast */
            flex-shrink: 0;
            margin-right: 2rem; 
            font-size: 1.05rem;
        }

        .detail-value {
            color: #212529; /* Near black for excellent readability */
            font-size: 1.1rem;
            text-align: right; 
            flex-grow: 1; 
            word-break: break-word;
        }
        
        /* Fine Input Styling: Warning Highlight */
        .custom-input-text {
            width: 140px;
            height: 42px;
            padding: 0.5rem 0.75rem;
            font-size: 1.1rem;
            color: #dc3545; /* Bootstrap Danger Red */
            background-color: #fff;
            border: 2px solid #dc3545; 
            border-radius: 6px;
            font-weight: 700;
            text-align: right;
            transition: all 0.2s;
        }
        .custom-input-text:focus {
             border-color: #00bcd4; /* Accent color on focus */
             box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.3);
        }

        /* Button Styling: Solid and Accented */
        .custom-btn {
            display: inline-block;
            padding: 12px 28px;
            margin-top: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary-theme {
            color: #ffffff;
            background-color: #00bcd4; /* Accent Color */
        }
        .btn-primary-theme:hover {
            background-color: #00a4b8; /* Darker Accent on hover */
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        /* Success Message */
        .success-message {
            padding: 1rem;
            border-radius: 6px;
            margin-top: 25px;
            background-color: #e0f7fa; /* Light cyan background */
            color: #006064; /* Dark teal text */
            border: 1px solid #b2ebf2;
            font-size: 1rem;
            font-weight: 500;
        }
    </style>
    
</head>
<body>
<?php include('includes/header.php');?>
<div class="content-wrapper">
    <div class="container">
        <div class="page-header-wrapper">
            <h4 class="header-line">Issued Book Details</h4>
        </div>
        <div class="row">

<?php 
// 4. Database Query Execution
$rid=intval($_GET['rid']);
$sql = "SELECT tblstudents.FullName,tblbooks.BookName,tblbooks.ISBNNumber,tblissuedbookdetails.IssuesDate,tblissuedbookdetails.ReturnDate,tblissuedbookdetails.id as rid,tblissuedbookdetails.fine,tblissuedbookdetails.ReturnStatus from tblissuedbookdetails join tblstudents on tblstudents.StudentId=tblissuedbookdetails.StudentId join tblbooks on tblbooks.id=tblissuedbookdetails.BookId where tblissuedbookdetails.id=:rid";
$query = $dbh -> prepare($sql);
$query->bindParam(':rid',$rid,PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;

// 5. Conditional Display Logic
if($query->rowCount() > 0) { ?>
<div class="custom-center-container"> 
    <div class="custom-panel custom-panel-info">
        <div class="custom-panel-heading">
            Issued Book Details
        </div>
        <div class="custom-panel-body">
            <form role="form" method="post">
<?php
    foreach($results as $result) { ?> 
                <div class="custom-form-group">
                    <label>Student Name :</label>
                    <span class="detail-value"><?php echo htmlentities($result->FullName ?? ''); ?></span>
                </div>

                <div class="custom-form-group">
                    <label>Book Name :</label>
                    <span class="detail-value"><?php echo htmlentities($result->BookName ?? ''); ?></span>
                </div>

                <div class="custom-form-group">
                    <label>ISBN :</label>
                    <span class="detail-value"><?php echo htmlentities($result->ISBNNumber ?? ''); ?></span>
                </div>

                <div class="custom-form-group">
                    <label>Book Issued Date :</label>
                    <span class="detail-value"><?php echo htmlentities($result->IssuesDate ?? ''); ?></span>
                </div>

                <div class="custom-form-group">
                    <label>Book Returned Date :</label>
                    <span class="detail-value">
                    <?php 
                        if(empty($result->ReturnDate)) {
                            echo "Not Return Yet";
                        } else {
                            echo htmlentities($result->ReturnDate ?? '');
                        }
                        ?>
                    </span>
                </div>

                <div class="custom-form-group">
                    <label for="fine">Fine (in INR) :</label>
                    <?php 
                    if(empty($result->fine) || $result->fine==0) {?>
                        <input class="custom-input-text" type="text" name="fine" id="fine" value="0.00" required />
                        <?php } else {
                            echo '<span class="detail-value">'.htmlentities($result->fine ?? '0.00').'</span>';
                        } ?>
                </div>

                <?php if($result->ReturnStatus==0){?>
                    <button type="submit" name="return" id="submit" class="custom-btn btn-primary-theme">Return Book </button>
                <?php } else { ?>
                    <div class="success-message">Book already returned on: <?php echo htmlentities($result->ReturnDate ?? ''); ?></div>
                <?php } ?>
                
                <?php } // End of foreach?>
</form>
</div> 
</div> 
</div> 
<?php } else { ?>
    <div class="custom-center-container">
        <div class="custom-panel custom-panel-info">
            <div class="custom-panel-body">
                <div class="error-message">Error: Issued Book Details not found.</div>
            </div>
        </div>
    </div>
<?php } ?>

</div> </div> </div> <?php include('includes/footer.php');?>
<script src="assets/js/bootstrap.js"></script> 

</body>
</html>
<?php } ?>
