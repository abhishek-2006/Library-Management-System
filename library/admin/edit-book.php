<?php
session_start();
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:index.php');
} else { 
    $bookid = intval($_GET['bookid']);

    // --- Handle Form Submission (Update) ---
    if(isset($_POST['update']))
    {
        $bookname=$_POST['bookname'];
        $category=$_POST['category'];
        $author=$_POST['author'];
        $publisher=$_POST['publisher'];
        $isbn=$_POST['isbn'];
        $price=$_POST['price'];
        $copies=$_POST['bookcopies'];
        $edition=$_POST['bookedition']; // NEW
        $description=$_POST['bookdescription']; // NEW
        
        // Handle Image Update (If needed, this only handles text fields)
        // For a complete solution, image upload/replace logic should be here.

        $sql="UPDATE tblbooks SET BookName=:bookname, CatId=:category, AuthorId=:author, PublisherId=:publisher, ISBNNumber=:isbn, BookPrice=:price, bookCopies=:copies, bookEdition=:edition, BookDescription=:description WHERE id=:bookid";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookname',$bookname,PDO::PARAM_STR);
        $query->bindParam(':category',$category,PDO::PARAM_STR);
        $query->bindParam(':author',$author,PDO::PARAM_STR);
        $query->bindParam(':publisher',$publisher,PDO::PARAM_STR); // BIND NEW
        $query->bindParam(':isbn',$isbn,PDO::PARAM_STR);
        $query->bindParam(':price',$price,PDO::PARAM_STR);
        $query->bindParam(':copies',$copies,PDO::PARAM_INT); // BIND NEW
        $query->bindParam(':edition',$edition,PDO::PARAM_STR); // BIND NEW
        $query->bindParam(':description',$description,PDO::PARAM_STR); // BIND NEW
        $query->bindParam(':bookid',$bookid,PDO::PARAM_INT); // Use INT for safety
        $query->execute();
        
        $_SESSION['updatemsg']="Book information updated successfully!";
        header('location:manage-books.php');
    }

    // --- Initial Data Fetch Query ---
    // Expanded query to include Publisher, bookImage, bookCopies, bookEdition, BookDescription
    $sql = "SELECT 
                b.BookName, b.ISBNNumber, b.BookPrice, b.bookImage, b.bookCopies, b.bookEdition, b.BookDescription, b.id as bookid,
                c.CategoryName, c.id as cid,
                a.AuthorName, a.id as athrid,
                p.PublisherName, p.id as pubid
            FROM tblbooks b 
            JOIN tblcategory c ON c.id = b.CatId 
            JOIN tblauthors a ON a.id = b.AuthorId
            JOIN tblpublishers p ON p.id = b.PublisherId
            WHERE b.id = :bookid";
    
    $query = $dbh -> prepare($sql);
    $query->bindParam(':bookid',$bookid,PDO::PARAM_INT);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Online Library Management System | Edit Book</title>
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Edit Book Details</h4>
            </div>

            <?php if(isset($_SESSION['error']) && $_SESSION['error']!="") { ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?php echo htmlentities($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php if(isset($_SESSION['msg']) && $_SESSION['msg']!="") { ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            <strong>Success:</strong> <?php echo htmlentities($_SESSION['msg']); unset($_SESSION['msg']); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="form-container-wrapper">
                    <div class="panel-default">
                        <div class="panel-heading">
                            Update Book Information
                        </div>
                        <div class="panel-body">
                            <form role="form" method="post" enctype="multipart/form-data">
                            <?php 
                            if($query->rowCount() > 0) {
                                foreach($results as $result) { ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group text-center">
                                            <h4>Current Cover Image</h4>
                                            <?php if (!empty($result->bookImage)): // Check if BLOB data is present ?>
                                                <img src="assets/img/<?php echo htmlentities($result->bookImage);?>" 
                                                    alt="<?php echo htmlentities($result->BookName);?>" 
                                                    class="img-thumbnail-lg" 
                                                    style="max-width: 150px; height: auto; display: block; margin: 10px auto 20px;">
                                            <?php else: ?>
                                                <div class="img-thumbnail-lg" style="max-width: 150px; height: 200px; line-height: 200px; border: 1px dashed #ccc; margin: 10px auto 20px;">
                                                    No Image
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="form-group">
                                                <label for="bookimage_upload">Change Book Cover Image</label>
                                                <div class="custom-file-upload-wrapper">
                                                    <input type="file" name="bookimage" id="bookimage_upload" class="custom-file-input"/>
                                                    <label for="bookimage_upload" class="custom-file-upload-label">
                                                        <i class="fa fa-upload"></i> Select File
                                                    </label>
                                                    <span id="file-chosen-text">No file chosen</span>
                                                </div>
                                            </div>
                                        </div>
                                    <hr/>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="bookname">Book Name <span style="color:red;">*</span></label>
                                        <input class="form-control" type="text" name="bookname" id="bookname" value="<?php echo htmlentities($result->BookName);?>" required />
                                    </div>

                                    <div class="form-group">
                                        <label for="category">Category <span style="color:red;">*</span></label>
                                        <select class="form-control" name="category" id="category" required="required">
                                            <option value="<?php echo htmlentities($result->cid);?>"> <?php echo htmlentities($catname=$result->CategoryName);?></option>
                                                <?php 
                                                $status=1;
                                                $sql1 = "SELECT id, CategoryName from tblcategory where Status=:status";
                                                $query1 = $dbh -> prepare($sql1);
                                                $query1-> bindParam(':status',$status, PDO::PARAM_STR);
                                                $query1->execute();
                                                $resultss=$query1->fetchAll(PDO::FETCH_OBJ);
                                                if($query1->rowCount() > 0) {
                                                    foreach($resultss as $row) { 
                                                        if($catname != $row->CategoryName) {
                                                            echo '<option value="'.htmlentities($row->id).'">'.htmlentities($row->CategoryName).'</option>';
                                                        }
                                                    }
                                                } ?> 
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="author">Author <span style="color:red;">*</span></label>
                                            <select class="form-control" name="author" id="author" required="required">
                                                <option value="<?php echo htmlentities($result->athrid);?>"> <?php echo htmlentities($athrname=$result->AuthorName);?></option>
                                                <?php 
                                                $sql2 = "SELECT id, AuthorName from tblauthors ";
                                                $query2 = $dbh -> prepare($sql2);
                                                $query2->execute();
                                                $result2=$query2->fetchAll(PDO::FETCH_OBJ);
                                                if($query2->rowCount() > 0) {
                                                    foreach($result2 as $ret) {
                                                        if($athrname != $ret->AuthorName) {
                                                            echo '<option value="'.htmlentities($ret->id).'">'.htmlentities($ret->AuthorName).'</option>';
                                                        }
                                                    }
                                                } ?> 
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="publisher">Publisher <span style="color:red;">*</span></label>
                                            <select class="form-control" name="publisher" id="publisher" required="required">
                                                <option value="<?php echo htmlentities($result->pubid);?>"> <?php echo htmlentities($pubname=$result->PublisherName);?></option>
                                                <?php 
                                                $sql3 = "SELECT id, PublisherName from tblpublishers ";
                                                $query3 = $dbh -> prepare($sql3);
                                                $query3->execute();
                                                $result3=$query3->fetchAll(PDO::FETCH_OBJ);
                                                if($query3->rowCount() > 0) {
                                                    foreach($result3 as $pub) {
                                                        if($pubname != $pub->PublisherName) {
                                                            echo '<option value="'.htmlentities($pub->id).'">'.htmlentities($pub->PublisherName).'</option>';
                                                        }
                                                    }
                                                } ?> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="isbn">ISBN Number <span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="isbn" id="isbn" value="<?php echo htmlentities($result->ISBNNumber);?>" required="required" />
                                            <p class="help-block">ISBN Must be unique.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Price <span style="color:red;">*</span></label>
                                            <input class="form-control" type="text" name="price" id="price" value="<?php echo htmlentities($result->BookPrice);?>" required="required" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bookcopies">Number of Copies <span style="color:red;">*</span></label>
                                            <input class="form-control" type="number" name="bookcopies" id="bookcopies" value="<?php echo htmlentities($result->bookCopies);?>" required="required" min="0" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bookedition">Book Edition</label>
                                            <input class="form-control" type="text" name="bookedition" id="bookedition" value="<?php echo htmlentities($result->bookEdition);?>" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="bookdescription">Book Description</label>
                                    <textarea class="form-control" name="bookdescription" id="bookdescription" rows="5"><?php echo htmlentities($result->BookDescription);?></textarea>
                                </div>
                            
                            <?php 
                                } 
                            } 
                            ?>
                                <button type="submit" name="update" class="custom-btn btn-primary-theme"><i class="fa fa-save"></i> Update Book </button>
                                <a href="manage-books.php" class="custom-btn btn-secondary-theme"><i class="fa fa-arrow-left"></i> Back to List</a>
                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('bookimage_upload');
        const fileText = document.getElementById('file-chosen-text');

        // Check if the elements exist before adding the listener
        if (fileInput && fileText) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    fileText.textContent = this.files[0].name;
                } else {
                    fileText.textContent = 'No file chosen';
                }
            });
        }
    });
</script>
</body>
</html>
<?php } ?>