<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('includes/config.php');

// ======= SESSION CHECK =======
if (!isset($_SESSION['alogin']) || strlen($_SESSION['alogin']) == 0) {
    header('Location: ../../index.php');
    exit();
}

// ======= HANDLE CLEAR OR UPDATE FINE =======
$msg = $error = "";

if (isset($_POST['updateFine'])) {
    $fineId = $_POST['fineId'];
    $newFine = $_POST['newFine'];

    try {
        $sql = "UPDATE tblissuedbookdetails SET fine = :fine WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fine', $newFine, PDO::PARAM_INT);
        $query->bindParam(':id', $fineId, PDO::PARAM_INT);
        if ($query->execute()) {
            $msg = "Fine updated successfully.";
        } else {
            $error = "Failed to update fine.";
        }
    } catch (PDOException $e) {
        $error = "DB Error: " . $e->getMessage();
    }
}

if (isset($_POST['clearFine'])) {
    $fineId = $_POST['fineId'];
    try {
        $sql = "UPDATE tblissuedbookdetails SET fine = 0 WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $fineId, PDO::PARAM_INT);
        if ($query->execute()) {
            $msg = "Fine cleared successfully.";
        } else {
            $error = "Failed to clear fine.";
        }
    } catch (PDOException $e) {
        $error = "DB Error: " . $e->getMessage();
    }
}

// ======= FETCH ALL FINES =======
try {
    $sql = "
        SELECT ibd.id, s.StudentId, s.FullName, b.BookName, ibd.IssuesDate, ibd.ReturnDate, ibd.fine
        FROM tblissuedbookdetails ibd
        JOIN tblstudents s ON s.StudentId = ibd.StudentID
        JOIN tblbooks b ON b.id = ibd.BookId
        WHERE ibd.fine > 0
        ORDER BY ibd.ReturnDate DESC
    ";
    $query = $dbh->prepare($sql);
    $query->execute();
    $fines = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "DB Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Fines</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
</head>
<body>
<div class="container">
    <h2 class="page-title">Manage Student Fines</h2>

    <?php if ($msg) { echo "<div class='alert-success'>{$msg}</div>"; } ?>
    <?php if ($error) { echo "<div class='alert-error'>{$error}</div>"; } ?>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Book Name</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Fine (₹)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fines)) { $count=1; foreach ($fines as $row) { ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlentities($row['StudentId']); ?></td>
                    <td><?php echo htmlentities($row['FullName']); ?></td>
                    <td><?php echo htmlentities($row['BookName']); ?></td>
                    <td><?php echo htmlentities(date('d-M-Y', strtotime($row['IssuesDate']))); ?></td>
                    <td><?php echo htmlentities(date('d-M-Y', strtotime($row['ReturnDate']))); ?></td>
                    <td><?php echo htmlentities($row['fine']); ?></td>
                    <td>
                        <!-- Update Fine Form -->
                        <form method="post" style="display:inline-block;">
                            <input type="number" name="newFine" min="0" value="<?php echo $row['fine']; ?>" required />
                            <input type="hidden" name="fineId" value="<?php echo $row['id']; ?>" />
                            <button type="submit" name="updateFine" class="btn-small"><i class="fas fa-edit"></i></button>
                        </form>

                        <!-- Clear Fine Form -->
                        <form method="post" style="display:inline-block;">
                            <input type="hidden" name="fineId" value="<?php echo $row['id']; ?>" />
                            <button type="submit" name="clearFine" class="btn-small btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php } } else { ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">No fines found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
