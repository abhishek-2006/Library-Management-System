<?php
session_start();
error_reporting(E_ALL);
require('includes/config.php');

// Initialize variables
$bookid = 0;
$request_message = "";
$result = null;

// --- 1. Initial Data Fetch ---
if (isset($_GET['bookid']) && !empty($_GET['bookid'])) {
    $bookid = intval($_GET['bookid']);

    // SQL to fetch book details, joining with category and authors
    $sql = "SELECT tblbooks.*, tblcategory.CategoryName, tblauthors.AuthorName 
            FROM tblbooks
            JOIN tblcategory ON tblcategory.id = tblbooks.CatId
            JOIN tblauthors ON tblauthors.id = tblbooks.AuthorId
            WHERE tblbooks.id = :bookid";

    $query = $dbh->prepare($sql);
    $query->bindParam(':bookid', $bookid, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    // Handle case where book is not found
    if (!$result) {
        // Redirect to book catalog or a 404 page
        header('Location: index.php');
        exit;
    }
} else {
    // No book ID provided, redirect
    header('Location: index.php');
    exit;
}

// --- 2. Borrow Request Submission Handler ---
if (isset($_POST['request_borrow']) && isset($_SESSION['login'])) {
    $studentId = $_SESSION['db_id'];
    $bookId = $bookid;

    // Check if the student has any existing PENDING request for THIS specific book
    $sqlCheck = "SELECT id FROM tblrequests WHERE StudentID=:sid AND BookID=:bid AND Status='Pending'";
    $queryCheck = $dbh->prepare($sqlCheck);
    $queryCheck->bindParam(':sid', $studentId, PDO::PARAM_INT);
    $queryCheck->bindParam(':bid', $bookId, PDO::PARAM_INT);
    $queryCheck->execute();

    if ($queryCheck->rowCount() > 0) {
        $request_message = "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 p-3 rounded-lg'>You already have a **pending request** for this book. Please wait for the admin's action.</div>";
    } else {
        // Insert the new request into tblrequests
        $sqlInsert = "INSERT INTO tblrequests (StudentID, BookID, RequestDate, Status) 
        VALUES (:sid, :bid, NOW(), 'Pending')";
        $queryInsert = $dbh->prepare($sqlInsert);
        $queryInsert->bindParam(':sid', $studentId, PDO::PARAM_INT);
        $queryInsert->bindParam(':bid', $bookId, PDO::PARAM_INT);
        
        if ($queryInsert->execute()) {
            $request_message = "<div class='bg-green-100 border border-green-400 text-green-700 p-3 rounded-lg'>✅ Your borrow request has been successfully submitted! Admin will check your borrowing limit upon approval.</div>";
        } else {
            $request_message = "<div class='bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg'>❌ Error submitting request. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LMS | Book Details: <?php echo htmlentities($result->BookName); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .bg-primary-indigo { background-color: #4338CA; }
        .text-primary-indigo { color: #4338CA; }
        .border-primary-indigo { border-color: #4338CA; }
    </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col antialiased">
    <?php include('includes/header.php'); ?>

    <div class="flex-grow py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 border-b-2 border-primary-indigo pb-2 mb-8">
                Book Details: <span class="text-primary-indigo"><?php echo htmlentities($result->BookName); ?></span>
            </h1>

            <?php echo $request_message; ?>

            <div class="bg-white shadow-2xl rounded-xl overflow-hidden md:flex mt-6">

                <div class="md:w-1/3 p-6 flex justify-center items-start bg-gray-100">
                    <?php 
                        $image_path = "admin/assets/img/" . htmlentities($result->bookImage);
                        if (file_exists($image_path) && !empty($result->bookImage)) {
                            echo '<img src="' . $image_path . '" alt="' . htmlentities($result->BookName) . '" class="w-full max-h-96 object-contain rounded-lg shadow-lg border border-gray-300">';
                        } else {
                            // Placeholder for missing image
                            echo '<div class="w-full h-96 flex items-center justify-center bg-gray-200 rounded-lg text-gray-500 text-center border-2 border-dashed border-gray-400">No Image Available</div>';
                        }
                    ?>
                </div>

                <div class="md:w-2/3 p-8 space-y-6">
                    <div class="space-y-1 border-b pb-4">
                        <h2 class="text-4xl font-extrabold text-gray-900"><?php echo htmlentities($result->BookName); ?></h2>
                        <p class="text-xl text-primary-indigo font-medium">By: <?php echo htmlentities($result->AuthorName); ?></p>
                        <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-indigo-100 text-indigo-700">
                            <?php echo htmlentities($result->CategoryName); ?>
                        </span>
                    </div>

                    <?php 
                        $is_available = ($result->isIssued == 0);
                        $is_logged_in = isset($_SESSION['login']); 
                    ?>
                    
                    <div class="flex items-center space-x-4 p-4 rounded-lg 
                        <?php if ($is_available): ?>bg-green-100 border border-green-400 text-green-700
                        <?php else: ?>bg-red-100 border border-red-400 text-red-700<?php endif; ?>">
                        <i class="fas fa-<?php echo $is_available ? 'check-circle' : 'times-circle'; ?> text-2xl"></i>
                        <span class="text-lg font-bold">
                            Availability Status: 
                            <?php echo $is_available ? 'AVAILABLE' : 'ISSUED / UNAVAILABLE'; ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-gray-700">
                        <div class="detail-item">
                            <p class="font-semibold text-sm text-gray-500">ISBN Number</p>
                            <p class="text-lg"><?php echo htmlentities($result->ISBNNumber); ?></p>
                        </div>
                        <div class="detail-item">
                            <p class="font-semibold text-sm text-gray-500">Price</p>
                            <p class="text-lg font-bold text-green-600">₹ <?php echo htmlentities($result->BookPrice); ?></p>
                        </div>
                        <div class="detail-item">
                            <p class="font-semibold text-sm text-gray-500">Edition</p>
                            <p class="text-lg"><?php echo htmlentities($result->bookEdition); ?></p>
                        </div>
                        <div class="detail-item">
                            <p class="font-semibold text-sm text-gray-500">Total Stock</p>
                            <p class="text-lg"><?php echo htmlentities($result->bookCopies); ?></p>
                        </div>
                    </div>

                    <div class="pt-4 border-t">
                        <p class="font-semibold text-sm text-gray-500 mb-2">Book Summary</p>
                        <p class="text-gray-600 leading-relaxed">
                            <?php 
                                $description = htmlentities($result->BookDescription);
                                echo empty($description) ? "No detailed description is available for this book." : $description; 
                            ?>
                        </p>
                    </div>

                    <div class="pt-6">
                        <?php if (!$is_logged_in): ?>
                            <a href="login.php" class="w-full block text-center py-3 px-4 bg-primary-indigo text-white font-bold text-lg rounded-lg shadow-xl hover:bg-indigo-800 transition duration-300">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login to Send Borrow Request
                            </a>
                        <?php elseif (!$is_available): ?>
                            <button disabled class="w-full py-3 px-4 bg-gray-400 text-white font-bold text-lg rounded-lg cursor-not-allowed">
                                <i class="fas fa-ban mr-2"></i> Book is Currently Unavailable
                            </button>
                        <?php else: ?>
                            <form method="post">
                                <button type="submit" name="request_borrow" class="w-full py-3 px-4 bg-primary-indigo text-white font-bold text-lg rounded-lg shadow-xl hover:bg-indigo-800 transition duration-300 transform hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-indigo-300">
                                    <i class="fas fa-hand-paper mr-2"></i> Send Borrow Request
                                </button>
                                <p class="text-center text-sm text-gray-500 mt-2">Request must be approved by the library administrator. Your borrowing limit will be checked then.</p>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            </div>
    </div>

    <?php include('includes/footer.php');?>
    <script src="assets/js/jquery-3.7.1.min.js"></script> 
</body>
</html>