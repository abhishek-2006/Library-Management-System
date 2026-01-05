<?php
session_start();
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
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
        $edition=$_POST['bookedition'];
        $description=$_POST['bookdescription'];
        
        $sql="UPDATE tblbooks SET BookName=:bookname, CatId=:category, AuthorId=:author, PublisherId=:publisher, ISBNNumber=:isbn, BookPrice=:price, bookCopies=:copies, bookEdition=:edition, BookDescription=:description WHERE id=:bookid";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookname',$bookname,PDO::PARAM_STR);
        $query->bindParam(':category',$category,PDO::PARAM_STR);
        $query->bindParam(':author',$author,PDO::PARAM_STR);
        $query->bindParam(':publisher',$publisher,PDO::PARAM_STR);
        $query->bindParam(':isbn',$isbn,PDO::PARAM_STR);
        $query->bindParam(':price',$price,PDO::PARAM_STR);
        $query->bindParam(':copies',$copies,PDO::PARAM_INT);
        $query->bindParam(':edition',$edition,PDO::PARAM_STR);
        $query->bindParam(':description',$description,PDO::PARAM_STR);
        $query->bindParam(':bookid',$bookid,PDO::PARAM_INT);
        $query->execute();
        
        $_SESSION['updatemsg']="Book information updated successfully!";
        header('location:manage-books.php');
    }

    // --- Initial Data Fetch Query ---
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
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Edit Book</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, sans-serif;
            color: #334155;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .header-line {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a5f;
            border-bottom: 3px solid #3b82f6;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 30px;
        }
        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        /* Card Layout */
        .edit-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .card-header {
            background: #1e3a5f;
            color: #fff;
            padding: 20px 30px;
            font-size: 18px;
            font-weight: 600;
        }
        .card-body {
            padding: 40px;
        }

        /* Two Column Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 40px;
        }
        @media (max-width: 850px) {
            .form-grid { grid-template-columns: 1fr; }
        }

        /* Image Styling */
        .img-preview-container {
            text-align: center;
            background-color: #fcfcfc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 25px;
            height: fit-content;
        }
        .image-label {
            display: block;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }
        .preview-box {
            width: 100%;
            margin-bottom: 30px;
            display: block;
        }
        .img-preview {
            width: 100%;
            max-width: 260px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        .no-image {
            width: 100%;
            height: 300px;
            background: #f1f5f9;
            border: 2px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            border-radius: 8px;
        }

        /* Input Controls */
        .cust-form-group {
            margin-bottom: 25px;
            width: 100%;
        }
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }
        .required { color: #ef4444; }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .help-block { font-size: 12px; color: #94a3b8; margin-top: 5px; }

        /* Row of inputs */
        .input-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .input-row .form-group { flex: 1; min-width: 200px; }
        
        /* File Upload */
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: block;
            margin-top: 10px;
        }
        .file-upload-btn {
            background-color: #e2e8f0;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 12px;
            border-radius: 6px;
            width: 100%;
            cursor: pointer;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            display: block;
            transition: all 0.2s;
        }
        .file-upload-btn:hover {
            background-color: #cbd5e1;
            color: #1e3a5f;
        }
        .file-upload-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        #file-chosen-text {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }

        /* Buttons */
        .btn-actions {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 15px;
        }
        .custom-btn {
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            border: none;
            transition: transform 0.1s, opacity 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .custom-btn:active { transform: scale(0.98); }
        .btn-primary-theme {
            background-color: #3b82f6;
            color: #fff;
        }
        .btn-primary-theme:hover { background-color: #2563eb; }
        .btn-secondary-theme {
            background-color: #64748b;
            color: #fff;
        }
        .btn-secondary-theme:hover { background-color: #475569; }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <h4 class="header-line">Edit Book Details</h4>

            <!-- Notifications -->
            <?php if(isset($_SESSION['error']) && $_SESSION['error']!="") { ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?php echo htmlentities($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php } ?>
            <?php if(isset($_SESSION['msg']) && $_SESSION['msg']!="") { ?>
                <div class="alert alert-success">
                    <strong>Success:</strong> <?php echo htmlentities($_SESSION['msg']); unset($_SESSION['msg']); ?>
                </div>
            <?php } ?>

            <div class="edit-card">
                <div class="card-header">
                    <i class="fas fa-book-open"></i> Update Book Information
                </div>
                <div class="card-body">
                    <form role="form" method="post" enctype="multipart/form-data">
                        <?php 
                        if($query->rowCount() > 0) {
                            foreach($results as $result) { ?>
                            
                            <div class="form-grid">
                                <div class="img-preview-container">
                                    <span class="image-label">Current Cover Image</span>
                                    
                                    <div class="preview-box">
                                        <?php if (!empty($result->bookImage)): ?>
                                            <img src="assets/img/<?php echo htmlentities($result->bookImage);?>" 
                                                 alt="<?php echo htmlentities($result->BookName);?>" 
                                                 class="img-preview">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <span><i class="fas fa-image fa-3x"></i><br>No Image</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="cust-form-group" style="margin-bottom: 0;">
                                        <label style="text-align: left;">Change Cover Image</label>
                                        <div class="file-upload-wrapper">
                                            <span class="file-upload-btn"><i class="fa fa-upload"></i> Select File</span>
                                            <input type="file" name="bookimage" id="bookimage_upload" />
                                        </div>
                                        <span id="file-chosen-text">No file chosen</span>
                                    </div>
                                </div>

                                <!-- Main Form Fields -->
                                <div>
                                    <div class="form-group">
                                        <label for="bookname">Book Name <span class="required">*</span></label>
                                        <input class="form-control" type="text" name="bookname" id="bookname" value="<?php echo htmlentities($result->BookName);?>" required />
                                    </div>

                                    <div class="input-row">
                                        <div class="form-group">
                                            <label for="category">Category <span class="required">*</span></label>
                                            <select class="form-control" name="category" id="category" required>
                                                <option value="<?php echo htmlentities($result->cid);?>"><?php echo htmlentities($catname=$result->CategoryName);?></option>
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
                                            <label for="author">Author <span class="required">*</span></label>
                                            <select class="form-control" name="author" id="author" required>
                                                <option value="<?php echo htmlentities($result->athrid);?>"><?php echo htmlentities($athrname=$result->AuthorName);?></option>
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
                                    </div>

                                    <div class="form-group">
                                        <label for="publisher">Publisher <span class="required">*</span></label>
                                        <select class="form-control" name="publisher" id="publisher" required>
                                            <option value="<?php echo htmlentities($result->pubid);?>"><?php echo htmlentities($pubname=$result->PublisherName);?></option>
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

                                    <div class="input-row">
                                        <div class="form-group">
                                            <label for="isbn">ISBN Number <span class="required">*</span></label>
                                            <input class="form-control" type="text" name="isbn" id="isbn" value="<?php echo htmlentities($result->ISBNNumber);?>" required />
                                            <p class="help-block">ISBN must be unique.</p>
                                        </div>
                                        <div class="form-group">
                                            <label for="price">Price (₹) <span class="required">*</span></label>
                                            <input class="form-control" type="text" name="price" id="price" value="<?php echo htmlentities($result->BookPrice);?>" required />
                                        </div>
                                    </div>

                                    <div class="input-row">
                                        <div class="form-group">
                                            <label for="bookcopies">Copies <span class="required">*</span></label>
                                            <input class="form-control" type="number" name="bookcopies" id="bookcopies" value="<?php echo htmlentities($result->bookCopies);?>" required min="0" />
                                        </div>
                                        <div class="form-group">
                                            <label for="bookedition">Edition</label>
                                            <input class="form-control" type="text" name="bookedition" id="bookedition" value="<?php echo htmlentities($result->bookEdition);?>" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="bookdescription">Book Description</label>
                                        <textarea class="form-control" name="bookdescription" id="bookdescription" rows="5"><?php echo htmlentities($result->BookDescription);?></textarea>
                                    </div>
                                </div>
                            </div>

                        <?php 
                            } 
                        } 
                        ?>
                        
                        <div class="btn-actions">
                            <button type="submit" name="update" class="custom-btn btn-primary-theme">
                                <i class="fas fa-save"></i> Update Book </button>
                            <a href="manage-books.php" class="custom-btn btn-secondary-theme">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php');?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('bookimage_upload');
        const fileText = document.getElementById('file-chosen-text');
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