<?php
session_start();
error_reporting(0);
require('includes/config.php');

// Security check: Redirect if not logged in
if(strlen($_SESSION['alogin'])==0) { 
    header('location:index.php');
} else { 
    // Handle Delete Action
    if(isset($_GET['del'])) {
        $id=$_GET['del'];
        $sql = "delete from tblbooks WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query -> bindParam(':id',$id, PDO::PARAM_STR);
        $query -> execute();
        $_SESSION['delmsg']="Book deleted successfully";
        header('location:manage-books.php');
    }

    // --- Database Query for Books ---
    $sql = "SELECT tblbooks.BookName, tblcategory.CategoryName, tblauthors.AuthorName, tblpublishers.PublisherName, tblbooks.ISBNNumber, tblbooks.BookPrice, tblbooks.bookCopies, tblbooks.regDate, tblbooks.id as bookid, tblbooks.bookImage 
        FROM tblbooks 
        JOIN tblcategory ON tblcategory.id = tblbooks.CatId 
        JOIN tblauthors ON tblauthors.id = tblbooks.AuthorId 
        JOIN tblpublishers ON tblpublishers.id = tblbooks.PublisherId";
    $query = $dbh -> prepare($sql);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Manage books for Online Library Management System" />
    <meta name="author" content="" />
    <title>Online Library Management System | Manage Books</title>
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>
    <?php include('includes/header.php');?>
<div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="header-line">Manage Books</h4>
                </div>
            </div>

            <div class="row">
                <?php 
                // Display Error/Success Messages
                if($_SESSION['error']!="") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-danger" >
                            <strong>Error :</strong> 
                            <?php echo htmlentities($_SESSION['error']); $_SESSION['error']=""; ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if($_SESSION['msg']!="") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-success" >
                            <strong>Success :</strong> 
                            <?php echo htmlentities($_SESSION['msg']); $_SESSION['msg']=""; ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if($_SESSION['updatemsg']!="") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-success" >
                            <strong>Success :</strong> 
                            <?php echo htmlentities($_SESSION['updatemsg']); $_SESSION['updatemsg']=""; ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if($_SESSION['delmsg']!="") { ?>
                    <div class="col-md-6">
                        <div class="alert alert-success" >
                            <strong>Success :</strong> 
                            <?php echo htmlentities($_SESSION['delmsg']); $_SESSION['delmsg']=""; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel-default">
                        <div class="panel-heading">
                            Books Listing
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="custom-table" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th class="image-col-head">Image</th>
                                            <th>Book Name</th>
                                            <th>Category</th>
                                            <th>Author</th>
                                            <th>ISBN Number</th>
                                            <th>Publisher</th>
                                            <th>Copies</th>
                                            <th>Book Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $cnt=1;
                                        if($query->rowCount() > 0) {
                                            foreach($results as $result) { ?>
                                                <tr class="odd gradeX">
                                                    <td class="center"><?php echo htmlentities($cnt);?></td>
                                                    <td class="center image-col">
                                                        <?php if ($result->bookImage): ?>
                                                            <img src="assets/img/<?php echo htmlentities($result->bookImage);?>" 
                                                                alt="<?php echo htmlentities($result->BookName);?>" 
                                                                class="book-cover-thumb"
                                                                onerror="this.onerror=null; this.src='assets/img/default.jpg';" 
                                                            >
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>

                                                    <td class="center"><?php echo htmlentities($result->BookName);?></td>
                                                    <td class="center"><?php echo htmlentities($result->CategoryName);?></td>
                                                    <td class="center"><?php echo htmlentities($result->AuthorName);?></td>
                                                    <td class="center"><?php echo htmlentities($result->ISBNNumber);?></td>
                                                    <td class="center"><?php echo htmlentities($result->PublisherName)?></td>
                                                    <td class="center"><?php echo htmlentities($result->bookCopies)?></td>
                                                    <td class="center"><?php echo htmlentities($result->BookPrice);?></td>
                                                    <td><a href="edit-book.php?bookid=<?php echo htmlentities($result->bookid);?>"><button class="custom-btn btn-primary-theme"><i class="fa fa-edit "></i> Edit</button> 
                                                        <a href="manage-books.php?del=<?php echo htmlentities($result->bookid);?>" onclick="return confirm('Are you sure you want to delete?');" >  <button class="custom-btn btn-danger-theme"><i class="fa fa-pencil"></i> Delete</button>
                                                    </td>
                                                </tr>
                                        <?php $cnt++; } 
                                        } ?>                                     
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php');?>
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script> 
</body>
</html>
<?php } ?>