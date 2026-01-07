<?php
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    // BORROW REQUEST ACTION HANDLING (ISSUE/REJECT)
    if(isset($_GET['req_id']) && isset($_GET['action']) && isset($_GET['bookid'])) {
        $requestId = intval($_GET['req_id']);
        $bookId = intval($_GET['bookid']);
        $action = $_GET['action'];

        // Start Transaction to ensure atomicity for issuing
        $dbh->beginTransaction();
        
        try {
            // 1. Fetch Issue Duration from settings
            $sql_settings = "SELECT SettingValue FROM tbllibrarysettings WHERE SettingName='IssueDurationDays'";
            $query_settings = $dbh->prepare($sql_settings);
            $query_settings->execute();
            $issueDurationDays = $query_settings->fetch(PDO::FETCH_ASSOC)['SettingValue'] ?? 15;

            if ($action == 'issue') {
                // 2. Fetch StudentID for the request from tblrequests
                $sql_fetch = "SELECT studentID FROM tblrequests WHERE id=:rid AND status=0";
                $query_fetch = $dbh->prepare($sql_fetch);
                $query_fetch->bindParam(':rid', $requestId, PDO::PARAM_INT);
                $query_fetch->execute();
                $request = $query_fetch->fetch(PDO::FETCH_OBJ);

                if ($request) {
                    $studentId = $request->studentID;   
                    $expectedReturnDate = date('Y-m-d H:i:s', strtotime("+$issueDurationDays days"));

                    // 3. Insert record into tblissuedbookdetails (Book Issuance)
                    $sql_issue = "
                        INSERT INTO tblissuedbookdetails (StudentID, BookID, IssuesDate, ReturnDate) 
                        VALUES (:sid, :bid, CURRENT_TIMESTAMP, :rd)";
                    $query_issue = $dbh->prepare($sql_issue);
                    $query_issue->bindParam(':sid', $studentId, PDO::PARAM_INT);
                    $query_issue->bindParam(':bid', $bookId, PDO::PARAM_INT);
                    $query_issue->bindParam(':rd', $ReturnDate, PDO::PARAM_STR);
                    $query_issue->execute();

                    // 4. Decrement available book copies in tblbooks
                    $sql_decrement = "UPDATE tblbooks SET bookCopies = bookCopies - 1 WHERE id=:bid AND bookCopies > 0";
                    $query_decrement = $dbh->prepare($sql_decrement);
                    $query_decrement->bindParam(':bid', $bookId, PDO::PARAM_INT);
                    $query_decrement->execute();
                    
                    if ($query_decrement->rowCount() == 0) {
                        throw new Exception("Book copy unavailable or already processed. Transaction rolled back.");
                    }

                    // 5. Update request status in tblrequests to Issued (status=1)
                    $sql_update = "UPDATE tblrequests SET Status='Approved', ApprovalDate=CURRENT_TIMESTAMP WHERE id=:rid AND Status='Pending'";
                    $query_update = $dbh->prepare($sql_update);
                    $query_update->bindParam(':rid', $requestId, PDO::PARAM_INT);
                    $query_update->execute();
                    
                    $dbh->commit();
                    $_SESSION['msg'] = "Book successfully issued and request approved!";
                } else {
                    throw new Exception("Borrow request not found or already processed.");
                }
            } 
            else if ($action == 'reject') {
                // Update request status in tblrequests to Rejected (status=2)
                $sql_update = "UPDATE tblrequests SET Status='Rejected', ApprovalDate=CURRENT_TIMESTAMP WHERE id=:rid AND Status='Pending'";
                $query_update = $dbh->prepare($sql_update);
                $query_update->bindParam(':rid', $requestId, PDO::PARAM_INT);
                $query_update->execute();
                
                $dbh->commit();
                $_SESSION['msg'] = "Borrow request rejected.";
            }

        } catch (Exception $e) {
            $dbh->rollBack();
            $_SESSION['error'] = "Action failed: " . $e->getMessage();
        }
        
        header('location:manage-borrow-requests.php'); 
        exit();
    }

    // --- Fetch ALL Borrow Requests (including bookImage) ---
    $sql_requests = "
        SELECT 
            r.id, 
            r.studentID, 
            r.BookID, 
            r.RequestDate, 
            r.Status,
            s.FullName AS StudentName,
            b.BookName,
            b.ISBNNumber,
            b.bookCopies,
            b.bookImage 
        FROM 
            tblrequests r 
        LEFT JOIN 
            tblstudents s ON r.studentID = s.StudentID 
        LEFT JOIN 
            tblbooks b ON r.BookID = b.id
        ORDER BY 
            r.RequestDate DESC";

    $query_requests = $dbh->prepare($sql_requests);
    $query_requests->execute();
    $requests = $query_requests->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Manage Borrow Requests</title>
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Add minimal styles for image display */
        .request-book-img {
            display: block;
            margin: auto;
            border: 1px solid #ddd;
            width: 40px; 
            height: 60px; 
            object-fit: cover; 
            border-radius: 4px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .text-center-img {
            text-align: center;
        }
        
        /* Message Styling (for PHP session messages) */
        .session-msg {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
        }
        .session-msg.success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .session-msg.error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        /* Reusing Modal Styles from last iteration in case CSS file is missing */
        .confirm-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .confirm-modal-actions .btn-modal-cancel {
            background-color: #ecf0f1;
            color: #34495e;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .confirm-modal-actions .btn-modal-cancel:hover {
            background-color: #d8e0e6;
        }

        .confirm-modal-actions .btn-modal-confirm {
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .confirm-modal-actions .btn-modal-confirm.approve,
        .confirm-modal-actions .btn-modal-confirm.issue {
            background-color: #27ae60; 
        }
        .confirm-modal-actions .btn-modal-confirm.approve:hover,
        .confirm-modal-actions .btn-modal-confirm.issue:hover {
            background-color: #2ecc71;
        }
        .confirm-modal-actions .btn-modal-confirm.reject {
            background-color: #c0392b; 
        }
        .confirm-modal-actions .btn-modal-confirm.reject:hover {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Manage Borrow Requests</h4>
            </div>

            <!-- Display Session Messages (Success/Error) -->
            <?php if(isset($_SESSION['msg'])) { ?>
                <div class="session-msg success">
                    <?php echo htmlentities($_SESSION['msg']);?>
                </div>
                <?php unset($_SESSION['msg']); } ?>
            <?php if(isset($_SESSION['error'])) { ?>
                <div class="session-msg error">
                    <?php echo htmlentities($_SESSION['error']);?>
                </div>
                <?php unset($_SESSION['error']); } ?>
            
            <div class="custom-table"> 
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Pending Borrow Requests Listing
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>IMAGE</th> 
                                        <th>STUDENT</th>
                                        <th>BOOK NAME</th>
                                        <th>ISBN</th>
                                        <th>COPIES AVAIL.</th>
                                        <th>REQUEST DATE</th>
                                        <th>STATUS</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $cnt = 1;
                                    if($requests && count($requests) > 0) {
                                        foreach($requests as $request) {
                                            $dbStatus = $request->Status;
                                            $isPending = ($request->Status === 'Pending');
                                            $isApproved = ($request->Status === 'Approved');
                                            $isRejected = ($request->Status === 'Rejected');
                                            
                                            $statusText = $dbStatus;
                                            $statusClass = 'pending';

                                            if ($isApproved) { 
                                                $statusText = 'ISSUED';
                                                $statusClass = 'approved';
                                            } elseif ($isRejected) {
                                                $statusText = 'REJECTED';
                                                $statusClass = 'rejected';
                                            }

                                            $rowClass = $isPending ? 'pending-request-row' : '';
                                            $isAvailable = ($request->bookCopies > 0 && $isPending);
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td><?php echo htmlentities($cnt);?></td>
                                        
                                        <!-- Book Image Display -->
                                        <td class="text-center-img">
                                            <?php if ($request->bookImage) { ?>
                                                <img src="assets/img/<?php echo htmlentities($request->bookImage); ?>" 
                                                    class="request-book-img" alt="Book Image">
                                            <?php } else { ?>
                                                <i class="fa fa-book-open fa-2x" style="color: #ccc;" title="No Image"></i>
                                            <?php } ?>
                                        </td>
                                        
                                        <td><?php echo htmlentities($request->StudentName ? $request->StudentName : 'ID: ' . $request->studentID);?></td>
                                        <td><?php echo htmlentities($request->BookName);?></td>
                                        <td><?php echo htmlentities($request->ISBNNumber);?></td>
                                        <td style="font-weight: bold; color: <?php echo $request->bookCopies > 0 ? 'green' : 'red'; ?>;">
                                            <?php echo htmlentities($request->bookCopies);?>
                                        </td>
                                        <td><?php echo htmlentities(date('Y-m-d', strtotime($request->RequestDate)));?></td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td class="action-btns">
                                            <!-- Action buttons for PENDING requests -->
                                            <?php if ($isPending) { ?>
                                                <?php if ($isAvailable) { ?>
                                                    <!-- ISSUE BUTTON (TRIGGERS MODAL) -->
                                                    <button class="btn btn-success btn-sm confirm-action-btn"
                                                        style="margin-top: 5px;"
                                                        data-action="issue"
                                                        data-id="<?php echo htmlentities($request->id);?>"
                                                        data-bookid="<?php echo htmlentities($request->BookID);?>"
                                                        data-title="<?php echo htmlentities($request->BookName);?>"
                                                        title="Issue Book and Approve Request">
                                                        <i class="fa fa-check"></i> Issue
                                                    </button>
                                                <?php } else { ?>
                                                    <button class="btn btn-danger btn-sm" disabled title="No copies available to issue">Out of Stock</button>
                                                <?php } ?>

                                                <!-- REJECT BUTTON (TRIGGERS MODAL) -->
                                                <button class="btn btn-danger btn-sm confirm-action-btn"
                                                    data-action="reject"
                                                    data-id="<?php echo htmlentities($request->id);?>"
                                                    data-bookid="<?php echo htmlentities($request->BookID);?>"
                                                    data-title="<?php echo htmlentities($request->BookName);?>"
                                                    title="Reject Request">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            <?php } else { ?>
                                                <!-- Button when request is already actioned -->
                                                <button class="btn btn-info btn-sm" style="margin-top: 12px;" disabled title="Request has been processed">Actioned</button>
                                            <?php } ?>
                                            </td>
                                    </tr>
                                    <?php $cnt++; }} else { ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No pending borrow requests found in the system.</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div> 
    <?php include('includes/footer.php');?>

    <!-- CONFIRMATION MODAL -->
    <div id="confirmActionModal" style="display:none; position:fixed; z-index:1001; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.6);">
        <div style="background-color:#fff; margin:15% auto; padding:25px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.25); width:90%; max-width:400px; text-align: center;">
            <h4 id="confirmModalTitle" style="color: #34495e; margin-bottom: 10px; font-weight: 700;">Confirm Action</h4>
            <p id="confirmModalBody" style="margin-bottom: 20px;">Are you sure you want to proceed with this action for book request?</p>
            
            <div class="confirm-modal-actions">
                <button id="modalCancelBtn" class="btn-modal-cancel">Cancel</button>
                <a href="#" id="modalConfirmLink">
                    <button class="btn-modal-confirm" id="modalConfirmBtn">Confirm</button>
                </a>
            </div>
        </div>
    </div>
    
    <!-- JAVASCRIPT FOR CONFIRMATION MODAL -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var confirmModal = document.getElementById('confirmActionModal');
            var confirmCancelBtn = document.getElementById('modalCancelBtn');
            var confirmLink = document.getElementById('modalConfirmLink');
            var confirmBtn = document.getElementById('modalConfirmBtn');
            var confirmBody = document.getElementById('confirmModalBody');

            function closeConfirmModal() { confirmModal.style.display = "none"; }
            confirmCancelBtn.onclick = closeConfirmModal;

            document.querySelectorAll('.confirm-action-btn').forEach(button => {
                button.addEventListener('click', function() {
                    var action = this.getAttribute('data-action');
                    var id = this.getAttribute('data-id');
                    var bookid = this.getAttribute('data-bookid');
                    var title = this.getAttribute('data-title');
                    
                    var actionText = (action === 'issue') ? 'ISSUE' : 'REJECT';
                    var colorClass = (action === 'issue') ? 'issue' : 'reject';
                    var verbText = (action === 'issue') ? 'issue and approve' : 'reject';

                    // Update Modal Content
                    confirmModal.querySelector('#confirmModalTitle').textContent = `Confirm ${actionText}`;
                    confirmBody.innerHTML = `Are you sure you want to ${verbText} the request for the book: <strong>${title}</strong>? This action is permanent.`;
                    
                    // Update Confirmation Link and Button Style
                    confirmLink.href = `manage-borrow-requests.php?req_id=${id}&action=${action}&bookid=${bookid}`;
                    confirmBtn.className = `btn-modal-confirm ${colorClass}`;

                    confirmModal.style.display = "block";
                });
            });
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == confirmModal) {
                    event.target.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>
<?php } ?>