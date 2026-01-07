<?php
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    // --- Request Action Handling (Approve/Reject) ---
    if(isset($_GET['req_id']) && isset($_GET['action'])) {
        $requestId = intval($_GET['req_id']);
        $actionStatus = ($_GET['action'] == 'approve') ? 1 : 2; // 1=Approved, 2=Rejected, 0=Pending

        $sql_update = "UPDATE tblbookrequests SET status=:status, actionDate=CURRENT_TIMESTAMP WHERE id=:rid AND status=0";
        $query_update = $dbh->prepare($sql_update);
        $query_update->bindParam(':status', $actionStatus, PDO::PARAM_INT);
        $query_update->bindParam(':rid', $requestId, PDO::PARAM_INT);
        
        if($query_update->execute()) {
            if ($actionStatus == 1) {
                $_SESSION['msg'] = "Book Request Approved successfully!";
            } else {
                $_SESSION['msg'] = "Book Request Rejected successfully!";
            }
        } else {
            $_SESSION['error'] = "Error updating request status. It might have already been processed.";
        }
        header('location:manage-book-requests.php'); 
        exit();
    }

    // Fetch ALL Book Requests
    $sql_requests = "
        SELECT 
            r.id, 
            r.studentID, 
            r.bookTitle, 
            r.bookISBN, 
            r.requestDate, 
            r.status,
            r.bookAuthor,
            r.publisher,
            r.reason,
            s.FullName AS StudentName 
        FROM 
            tblbookrequests r 
        LEFT JOIN 
            tblstudents s ON r.studentID = s.StudentID 
        ORDER BY 
            r.requestDate DESC";

    $query_requests = $dbh->prepare($sql_requests);
    $query_requests->execute();
    $requests = $query_requests->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Manage Book Requests</title>
    
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Manage Book Requests</h4>
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
            
            <div class="custom-table"> 
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Book Requests Listing
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>STUDENT</th>
                                        <th>BOOK TITLE</th>
                                        <th>ISBN</th>
                                        <th>AUTHOR/PUBLISHER</th>
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
                                            $statusText = 'PENDING';
                                            $statusClass = 'pending';

                                            if ($request->status == 1) {
                                                $statusText = 'APPROVED';
                                                $statusClass = 'approved';
                                            } elseif ($request->status == 2) {
                                                $statusText = 'REJECTED';
                                                $statusClass = 'rejected';
                                            }

                                            $rowClass = ($request->status == 0) ? 'pending-request-row' : '';
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td><?php echo htmlentities($cnt);?></td>
                                        <td>
                                            <?php 
                                            echo htmlentities($request->StudentName ? $request->StudentName : 'ID: ' . $request->studentID);
                                            ?>
                                        </td>
                                        <td><?php echo htmlentities($request->bookTitle);?></td>
                                        <td><?php echo htmlentities($request->bookISBN);?></td>
                                        <td><?php echo htmlentities($request->bookAuthor . " / " . $request->publisher);?></td>
                                        <td><?php echo htmlentities(date('Y-m-d', strtotime($request->requestDate)));?></td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td class="action-btns">
                                            <?php if ($request->status == 0) { ?>
                                                <button class="btn btn-success btn-sm confirm-action-btn" 
                                                    style="margin-top: 7px;"
                                                    data-action="approve"
                                                    data-id="<?php echo htmlentities($request->id);?>"
                                                    data-title="<?php echo htmlentities($request->bookTitle);?>"
                                                    title="Approve Request">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>

                                                <button class="btn btn-danger btn-sm confirm-action-btn"
                                                    style="margin-top: 7px;"
                                                    data-action="reject"
                                                    data-id="<?php echo htmlentities($request->id);?>"
                                                    data-title="<?php echo htmlentities($request->bookTitle);?>"
                                                    title="Reject Request">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>

                                            <?php } else { ?>
                                                <button class="btn btn-info btn-sm" style="margin-top: 7px;" title="Request has been processed">Actioned</button>
                                            <?php } ?>
                                            
                                            <button class="btn btn-default btn-sm view-reason-btn" 
                                                style="margin-top: 7px;"
                                                data-title="<?php echo htmlentities($request->bookTitle);?>"
                                                data-student="<?php echo htmlentities($request->StudentName ? $request->StudentName : 'ID: ' . $request->studentID);?>"
                                                data-isbn="<?php echo htmlentities($request->bookISBN);?>"
                                                data-reason="<?php echo htmlentities($request->reason);?>"
                                                title="View Reason">
                                                <i class="fa fa-info-circle"></i> Reason
                                            </button>
                                        </td>
                                    </tr>
                                    <?php $cnt++; }} else { ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No book requests found in the system.</td>
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
    <div id="reasonDetailModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fff; margin:10% auto; padding:25px; border:none; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.25); width:90%; max-width:550px;">
            <span id="closeReasonModalTop" style="color:#999; float:right; font-size:30px; font-weight:lighter; cursor:pointer; line-height: 1;">&times;</span>

            <h4 style="color: #34495e; margin-bottom: 5px; font-weight: 700;">
                <i class="fa fa-question-circle" style="color:#3498db; margin-right: 8px;"></i> Request Reason
            </h4>
            <hr style="margin-top: 10px;">

            <p style="margin-bottom: 5px;"><strong>Student:</strong> <span id="modalReasonStudent"></span></p>
            <p style="margin-bottom: 15px;"><strong>Book Title:</strong> <span id="modalReasonTitle"></span></p>
            <p style="margin-bottom: 20px;"><strong>ISBN:</strong> <span id="modalReasonISBN"></span></p>

            <p style="font-weight: 600;">Student's Reason for Request:</p>
            <p id="modalReasonDetails" style="white-space: pre-wrap; background-color: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #dee2e6; max-height: 200px; overflow-y: auto;"></p>

            <div style="text-align: right; margin-top: 20px;">
                <button id="closeReasonModalBottom" class="btn btn-default">Close</button>
            </div>
        </div>
    </div>

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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('reasonDetailModal');
        var closeBtnTop = document.getElementById('closeReasonModalTop');
        var closeBtnBottom = document.getElementById('closeReasonModalBottom');

        // Function to display the modal
        document.querySelectorAll('.view-reason-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('modalReasonStudent').textContent = this.getAttribute('data-student');
                document.getElementById('modalReasonTitle').textContent = this.getAttribute('data-title');
                document.getElementById('modalReasonISBN').textContent = this.getAttribute('data-isbn');
                document.getElementById('modalReasonDetails').textContent = this.getAttribute('data-reason');
                modal.style.display = "block";
            });
        });

        // Function to close the modal
        function closeModalHandler() {
            modal.style.display = "none";
        }
        
        closeBtnTop.onclick = closeModalHandler;
        closeBtnBottom.onclick = closeModalHandler;

        // Close when clicking outside the modal
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModalHandler();
            }
        }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Reason Modal Logic (Kept and simplified) ---
        var reasonModal = document.getElementById('reasonDetailModal');
        var reasonCloseTop = document.getElementById('closeReasonModalTop');
        var reasonCloseBottom = document.getElementById('closeReasonModalBottom');
        
        function closeReasonModal() { reasonModal.style.display = "none"; }
        reasonCloseTop.onclick = closeReasonModal;
        reasonCloseBottom.onclick = closeReasonModal;

        document.querySelectorAll('.view-reason-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Populate reason modal fields (using existing logic)
                document.getElementById('modalReasonStudent').textContent = this.getAttribute('data-student');
                document.getElementById('modalReasonTitle').textContent = this.getAttribute('data-title');
                document.getElementById('modalReasonISBN').textContent = this.getAttribute('data-isbn');
                document.getElementById('modalReasonDetails').textContent = this.getAttribute('data-reason');
                reasonModal.style.display = "block";
            });
        });

        // --- Confirmation Modal Logic (New) ---
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
                var title = this.getAttribute('data-title');
                
                var actionText = (action === 'approve') ? 'APPROVE' : 'REJECT';
                var colorClass = (action === 'approve') ? 'approve' : 'reject';

                // Update Modal Content
                confirmModal.querySelector('#confirmModalTitle').textContent = `Confirm ${actionText}`;
                confirmBody.innerHTML = `Are you sure you want to ${actionText} the request for the book: <strong>${title}</strong>?`;
                
                // Update Confirmation Link and Button Style
                confirmLink.href = `manage-book-requests.php?req_id=${id}&action=${action}`;
                confirmBtn.className = `btn-modal-confirm ${colorClass}`;

                confirmModal.style.display = "block";
            });
        });
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == reasonModal || event.target == confirmModal) {
                event.target.style.display = "none";
            }
        }
    });
    </script>
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>