<?php
session_start();
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    // --- Message Action Handling (Mark Read/Unread) ---
    if(isset($_GET['status_action']) && isset($_GET['mid'])) {
        $messageId = intval($_GET['mid']);
        $newStatus = intval($_GET['status_action']); 

        if ($newStatus === 0 || $newStatus === 1) {
            $sql_update = "UPDATE tblcontactmessages SET status=:newstatus WHERE id=:mid";
            $query_update = $dbh->prepare($sql_update);
            $query_update->bindParam(':newstatus', $newStatus, PDO::PARAM_INT);
            $query_update->bindParam(':mid', $messageId, PDO::PARAM_INT);
            
            if($query_update->execute()) {
                $_SESSION['msg'] = "Message status updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating message status.";
            }
            header('location:contact-messages.php'); 
            exit();
        }
    }

    // --- Fetch ALL Messages ---
    $sql_messages = "
        SELECT 
            m.id, 
            m.studentID, 
            m.messageTitle, 
            m.messageDetails, 
            m.messageDate, 
            m.status,
            s.FullName as StudentName 
        FROM 
            tblcontactmessages m 
        LEFT JOIN 
            tblstudents s ON m.studentID = s.StudentID 
        ORDER BY 
            m.messageDate DESC";

    $query_messages = $dbh->prepare($sql_messages);
    $query_messages->execute();
    $messages = $query_messages->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Manage Contact Messages</title>
    
    <link href="assets/css/font-awesome.css" rel="stylesheet" /> 
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link href="assets/css/admin-messages.css" rel="stylesheet" />
    
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Student Contact Messages</h4>
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
                        <i class="fa fa-inbox"></i> Message Inbox
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 15%;">Student</th>
                                        <th style="width: 35%;">Message Content</th>
                                        <th style="width: 15%;">Date Received</th>
                                        <th style="width: 10%;">Status</th>
                                        <th style="width: 20%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $cnt = 1;
                                    if($messages && count($messages) > 0) {
                                        foreach($messages as $message) {
                                            $isUnread = $message->status == 1;
                                            $rowClass = $isUnread ? 'unread-message-row' : '';
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td><?php echo htmlentities($cnt);?></td>
                                        <td>
                                            <?php 
                                            echo htmlentities($message->StudentName ? $message->StudentName : 'Unknown Student');
                                            echo '<br><small style="font-weight:normal;">(ID: ' . htmlentities($message->studentID) . ')</small>';
                                            ?>
                                        </td>
                                        <td>
                                            <span class="message-title">
                                                <a href="javascript:void(0);" 
                                                   class="view-message-btn" 
                                                   data-title="<?php echo htmlentities($message->messageTitle);?>"
                                                   data-details="<?php echo htmlentities($message->messageDetails);?>"
                                                   data-student="<?php echo htmlentities($message->StudentName ? $message->StudentName : 'ID: ' . $message->studentID);?>"
                                                   data-date="<?php echo htmlentities(date('Y-m-d H:i', strtotime($message->messageDate)));?>">
                                                    <?php echo htmlentities($message->messageTitle);?>
                                                </a>
                                            </span>
                                            <span class="message-preview"><?php echo htmlentities(substr($message->messageDetails, 0, 70));?>...</span>
                                        </td>
                                        <td><?php echo htmlentities(date('Y-m-d H:i', strtotime($message->messageDate)));?></td>
                                        <td>
                                            <span class="status-badge <?php echo $isUnread ? 'new' : 'read'; ?>">
                                                <?php echo $isUnread ? 'NEW' : 'READ'; ?>
                                            </span>
                                        </td>
                                        <td class="action-btns">
                                            <button 
                                                class="btn btn-info btn-sm view-message-btn" 
                                                data-title="<?php echo htmlentities($message->messageTitle);?>"
                                                data-details="<?php echo htmlentities($message->messageDetails);?>"
                                                data-student="<?php echo htmlentities($message->StudentName ? $message->StudentName : 'ID: ' . $message->studentID);?>"
                                                data-date="<?php echo htmlentities(date('Y-m-d H:i', strtotime($message->messageDate)));?>"
                                                title="View Full Message">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                            
                                            <?php if ($isUnread) { ?>
                                                <a href="contact-messages.php?mid=<?php echo htmlentities($message->id);?>&status_action=0" 
                                                   onclick="return confirm('Mark this message as Read?');"
                                                   title="Mark as Read">
                                                    <button class="btn btn-success btn-sm"><i class="fa fa-check"></i> Read</button>
                                                </a>
                                            <?php } else { ?>
                                                <a href="contact-messages.php?mid=<?php echo htmlentities($message->id);?>&status_action=1" 
                                                   onclick="return confirm('Mark this message as Unread?');"
                                                   title="Mark as Unread">
                                                    <button class="btn btn-warning btn-sm"><i class="fa fa-envelope"></i> Unread</button>
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php $cnt++; }} else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No messages found in the system.</td>
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

    <div id="messageDetailModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fff; margin:10% auto; padding:25px; border:none; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); width:90%; max-width:600px;">
            <span id="closeModal" style="color:#aaa; float:right; font-size:32px; font-weight:lighter; cursor:pointer; line-height: 1;">&times;</span>
            <h4 id="modalTitle" style="color: #007bff; margin-bottom: 5px;"><i class="fa fa-paper-plane"></i> Message Details</h4>
            <hr style="margin-top: 10px;">
            <p style="margin-bottom: 5px;"><strong>From:</strong> <span id="modalStudent"></span></p>
            <p style="margin-bottom: 15px;"><strong>Date:</strong> <span id="modalDate"></span></p>
            <p style="font-weight: 600;">Subject:</p>
            <p id="modalSubject" style="padding: 10px; background-color: #f8f9fa; border-left: 3px solid #007bff; margin-bottom: 20px;"></p>
            
            <p style="font-weight: 600;">Message:</p>
            <p id="modalDetails" style="white-space: pre-wrap; background-color: #f4f4f4; padding: 15px; border-radius: 4px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;"></p>
            
            <button id="closeModalBottom" class="btn btn-default" style="float: right; margin-top: 20px;">Close</button>
            <div style="clear: both;"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('messageDetailModal');
            var closeBtn = document.getElementById('closeModal');
            var closeBtnBottom = document.getElementById('closeModalBottom');

            // Function to display the modal
            document.querySelectorAll('.view-message-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('modalStudent').textContent = this.getAttribute('data-student');
                    document.getElementById('modalDate').textContent = this.getAttribute('data-date');
                    document.getElementById('modalSubject').textContent = this.getAttribute('data-title');
                    document.getElementById('modalDetails').textContent = this.getAttribute('data-details');
                    modal.style.display = "block";
                });
            });

            // Function to close the modal
            function closeModalHandler() {
                modal.style.display = "none";
            }
            
            closeBtn.onclick = closeModalHandler;
            closeBtnBottom.onclick = closeModalHandler;

            window.onclick = function(event) {
                if (event.target == modal) {
                    closeModalHandler();
                }
            }
        });
    </script>
</body>
</html>
<?php } ?>