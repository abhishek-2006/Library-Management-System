<?php
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    // 1. Fetch Categories, Authors and Publishers for Dropdowns
    
    // Fetch Authors
    $sql_authors = "SELECT id, AuthorName FROM tblauthors";
    $query_authors = $dbh->prepare($sql_authors);
    $query_authors->execute();
    $authors = $query_authors->fetchAll(PDO::FETCH_OBJ);

    // Fetch Categories
    $sql_categories = "SELECT id, CategoryName FROM tblcategory";
    $query_categories = $dbh->prepare($sql_categories);
    $query_categories->execute();
    $categories = $query_categories->fetchAll(PDO::FETCH_OBJ);

    //Fetch Publishers
    $sql_publishers = "SELECT id, PublisherName FROM tblpublishers";
    $query_publishers = $dbh->prepare($sql_publishers);
    $query_publishers->execute();
    $publishers = $query_publishers->fetchAll(PDO::FETCH_OBJ);

    // 2. Form Submission Handling
    if(isset($_POST['add'])) {
        $bookName = $_POST['bookname'];
        $catid = $_POST['category'];
        $authorid = $_POST['author'];
        $publisherid = $_POST['publisher'];
        $isbn = $_POST['isbn'];
        $price = $_POST['price'];
        $copies = $_POST['copies'];
        $isissued = 0;
        $bookEdition = $_POST['edition'];
        $bookDescription = $_POST['description'];

        // 2. File Upload Logic
        $bookImage = '';
        $target_dir = "assets/img/";
        $uploadOk = 1;
        
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $_SESSION['error'] = "Failed to create upload directory: " . $target_dir . ". Check permissions.";
                $uploadOk = 0;
            }
        }

        if ($uploadOk == 1 && !empty($_FILES["bookimage"]["name"])) {
            $imageFileType = strtolower(pathinfo($_FILES["bookimage"]["name"], PATHINFO_EXTENSION));
            $new_file_name = uniqid('book_') . "." . $imageFileType;
            $target_file = $target_dir . $new_file_name;

            // Basic checks (You should add more robust file type/size checks)
            if ($_FILES["bookimage"]["size"] > 5000000) { // 5MB limit
                $_SESSION['error'] = "Sorry, your image is too large.";
                $uploadOk = 0;
            }
            if($imageFileType != "jpg" && $imageFileType != "jpeg") {
                $_SESSION['error'] = "Sorry, only JPG/JPEG files are allowed.";
                $uploadOk = 0;
            }

            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["bookimage"]["tmp_name"], $target_file)) {
                    $bookImage = $new_file_name;
                } else {
                    $_SESSION['error'] = "Sorry, there was an error uploading your file.";
                    $uploadOk = 0;
                }
            }
        }
        
            // Check if ISBN already exists
            $check_sql = "SELECT id FROM tblbooks WHERE ISBNNumber=:isbn";
            $check_query = $dbh->prepare($check_sql);
            $check_query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
            $check_query->execute();

            if($check_query->rowCount() > 0) {
                $_SESSION['error'] = "Error: ISBN Number already exists.";
            } else {
                // Insert with bookImage field
                $insert_sql = "INSERT INTO tblbooks(BookName, CatId, AuthorId, PublisherId, ISBNNumber, BookPrice, bookCopies, bookImage, isIssued, bookEdition, BookDescription) 
                    VALUES(:bookname, :catid, :authorid, :publisherid, :isbn, :price, :copies, :bookimage, :isissued, :edition, :description)";
                
                $insert_query = $dbh->prepare($insert_sql);
                
                $insert_query->bindParam(':bookname', $bookName, PDO::PARAM_STR);
                $insert_query->bindParam(':catid', $catid, PDO::PARAM_INT);
                $insert_query->bindParam(':authorid', $authorid, PDO::PARAM_INT);
                $insert_query->bindParam(':publisherid', $publisherid, PDO::PARAM_INT);
                $insert_query->bindParam(':isbn', $isbn, PDO::PARAM_STR);
                $insert_query->bindParam(':price', $price, PDO::PARAM_STR);
                $insert_query->bindParam(':copies', $copies, PDO::PARAM_INT);
                $insert_query->bindParam(':bookimage', $bookImage, PDO::PARAM_STR);
                $insert_query->bindParam(':isissued', $isissued, PDO::PARAM_INT);
                $insert_query->bindParam(':edition', $bookEdition, PDO::PARAM_STR);
                $insert_query->bindParam(':description', $bookDescription, PDO::PARAM_STR);
                
                if($insert_query->execute()) {
                    $_SESSION['msg'] = "Book added successfully!";
                    header('location: add-book.php'); 
                    exit();
                } else {
                    $_SESSION['error'] = "Error adding book to database. Please try again.";
                }
            }
        }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Add Book</title>
    
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Add Book</h4>
            </div>

            <div class="message-wrapper">
                <?php if(isset($_SESSION['msg']) && !empty($_SESSION['msg'])) {?>
                    <div class="alert alert-success">
                        <?php echo htmlentities($_SESSION['msg']);?>
                    </div>
                <?php $_SESSION['msg']=""; } ?>

                <?php if(isset($_SESSION['error']) && !empty($_SESSION['error'])) {?>
                    <div class="alert alert-error">
                        <?php echo htmlentities($_SESSION['error']);?>
                    </div>
                <?php $_SESSION['error']=""; } ?>
            </div>
            
            <div class="form-container-wrapper large-form"> 
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Enter Book Details
                    </div>
                    <div class="panel-body">
                        <form name="addbook" method="post" enctype="multipart/form-data">
                            
                            <div class="form-group custom-file-upload">
                                <label for="bookimage_upload">Book Cover Image</label>
                                <div class="image-preview-container" id="image-preview-container" style="display: none;">
                                    <img id="image-preview" src="#" alt="Book Cover Preview" />
                                </div>
                                
                                <div class="custom-file-upload-wrapper">
                                    <input 
                                    type="file" 
                                    name="bookimage" 
                                    id="bookimage_upload" 
                                    class="custom-file-input" 
                                    accept=".jpg,.jpeg,.png,.gif"
                                    onchange="previewImage(this);" 
                                    />

                                    <label for="bookimage_upload" class="custom-file-upload-label">
                                        <i class="fas fa-cloud-upload-alt"></i> Select File
                                    </label>
                                    <span id="file-chosen-text">No file chosen</span>
                                </div>
                                <span class="help-text">Max 5MB. Allowed: JPG, JPEG, PNG, WEBP.</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="bookname">Book Name</label>
                                <input class="form-control" type="text" id="bookname" name="bookname" required />
                            </div>

                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    if($categories && count($categories) > 0) {
                                        foreach($categories as $category) {
                                    ?>
                                    <option value="<?php echo htmlentities($category->id);?>"><?php echo htmlentities($category->CategoryName);?></option>
                                    <?php }} ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="author">Author</label>
                                <select class="form-control" id="author" name="author" required>
                                    <option value="">Select Author</option>
                                    <?php 
                                    if($authors && count($authors) > 0) {
                                        foreach($authors as $author) {
                                    ?>
                                    <option value="<?php echo htmlentities($author->id);?>"><?php echo htmlentities($author->AuthorName);?></option>
                                    <?php }} ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="publisher">Publisher</label>
                                <select class="form-control" id="publisher" name="publisher" required>
                                    <option value="">Select Publisher</option>
                                    <?php 
                                    if($publishers && count($publishers) > 0) {
                                        foreach($publishers as $publisher) { ?>
                                        <option value="<?php echo htmlentities($publisher->id);?>"><?php echo htmlentities($publisher->PublisherName);?></option>
                                        <?php }} ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="isbn">ISBN Number (Must be Unique)</label>
                                <input class="form-control" id="isbn" type="text" name="isbn" required />
                            </div>

                            <div class="form-group">
                                <label for="price">Book Price (in INR)</label>
                                <input class="form-control" id="price" type="text" name="price" required />
                            </div>

                            <div class="form-group">
                                <label for="copies">Number of Copies</label>
                                <input class="form-control" type="number" id="copies" name="copies" required min="1" value="1" />
                            </div>

                            <div class="form-group">
                                <label for="edition">Book Edition</label>
                                <input class="form-control" id="edition" type="text" name="edition" />
                            </div>

                            <div class="form-group">
                                <label for="description">Book Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" name="add" class="update-btn">
                                <i class="fa fa-plus"></i> Add Book
                            </button>
                            
                            <a href="manage-books.php" class="back-link">
                                <i class="fa fa-arrow-left"></i> View Books
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div> 
    </div> 
    <?php include('includes/footer.php');?>

    <script>
    function previewImage(input) {
        var fileNameDisplay = document.getElementById('file-chosen-text');
        var previewContainer = document.getElementById('image-preview-container');
        var previewImage = document.getElementById('image-preview');

        if (input.files && input.files[0]) {
            // Update file name display
            fileNameDisplay.innerText = input.files[0].name;

            // Show and update image preview
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
            previewContainer.style.display = 'block'; // Show the preview box
        } else {
            fileNameDisplay.innerText = 'No file chosen';
            previewContainer.style.display = 'none'; // Hide the preview box
            previewImage.src = '#';
        }
    }
</script>
</body>
</html>
<?php } ?>