<?php
error_reporting(E_ALL);
require('includes/config.php');

if(strlen($_SESSION['alogin'])==0) { 
    header('location:../../index.php');
    exit();
}
else {
    // --- 1. Fetch ALL Current Settings ---
    $settings = [];
    $sql_fetch = "SELECT SettingName, SettingValue FROM tbllibrarysettings";
    $query_fetch = $dbh->prepare($sql_fetch);
    $query_fetch->execute();
    $results = $query_fetch->fetchAll(PDO::FETCH_OBJ);

    if($results) {
        foreach($results as $row) {
            $settings[$row->SettingName] = $row->SettingValue;
        }
    }


    // --- 2. Form Submission Handling (POST) ---
    if(isset($_POST['update_settings'])) {
        try {
            // Retrieve new values from the form
            $appName = $_POST['appname'];
            $fineRate = $_POST['finerate'];
            $issueDays = $_POST['issuedurationdays'];
            $contactEmail = $_POST['librarycontactemail'];
            $hoursMF = $_POST['hours_mon_fri'];
            $hoursSat = $_POST['hours_saturday'];
            $closedNote = $_POST['closed_note'];

            // Prepare the values and keys for batch update
            $updates = [
                'AppName' => $appName,
                'FineRate' => $fineRate,
                'IssueDurationDays' => $issueDays,
                'LibraryContactEmail' => $contactEmail,
                'hours_mon_fri' => $hoursMF,
                'hours_saturday' => $hoursSat,
                'closed_note' => $closedNote,
            ];

            $dbh->beginTransaction();

            $update_sql = "UPDATE tbllibrarysettings SET SettingValue = :value WHERE SettingName = :name";
            $update_query = $dbh->prepare($update_sql);

            foreach ($updates as $name => $value) {
                $update_query->bindParam(':value', $value, PDO::PARAM_STR);
                $update_query->bindParam(':name', $name, PDO::PARAM_STR);
                $update_query->execute();
            }

            $dbh->commit();

            $_SESSION['msg'] = "System Settings updated successfully!";
            header('location:manage-settings.php'); 
            exit();

        } catch (PDOException $e) {
            $dbh->rollBack();
            $_SESSION['error'] = "Database Error: " . $e->getMessage();
        } catch (Exception $e) {
            $_SESSION['error'] = "An unexpected error occurred.";
        }
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Manage System Settings</title>
    
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <?php include('includes/header.php');?>
    
    <div class="content-wrapper">
        <div class="container">
            <div class="page-header-wrapper">
                <h4 class="header-line">Manage System Settings</h4>
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
                        System Configuration Parameters
                    </div>
                    <div class="panel-body">
                        <form name="managesettings" method="post">
                            
                            <div class="form-group">
                                <label for="appname">Application Name</label>
                                <input 
                                    class="form-control" 
                                    type="text" 
                                    id="appname" 
                                    name="appname" 
                                    value="<?php echo htmlentities($settings['AppName'] ?? ''); ?>" 
                                    required 
                                />
                            </div>

                            <div class="form-group">
                                <label for="finerate">Fine Rate (per day)</label>
                                <input 
                                    class="form-control" 
                                    type="number" 
                                    step="0.01"
                                    id="finerate" 
                                    name="finerate" 
                                    value="<?php echo htmlentities($settings['FineRate'] ?? 0.00); ?>" 
                                    required 
                                />
                            </div>

                            <div class="form-group">
                                <label for="issuedurationdays">Maximum Issue Duration (Days)</label>
                                <input 
                                    class="form-control" 
                                    type="number" 
                                    id="issuedurationdays" 
                                    name="issuedurationdays" 
                                    value="<?php echo htmlentities($settings['IssueDurationDays'] ?? 15); ?>" 
                                    required 
                                />
                            </div>

                            <div class="form-group">
                                <label for="librarycontactemail">Library Contact Email</label>
                                <input 
                                    class="form-control" 
                                    type="email" 
                                    id="librarycontactemail" 
                                    name="librarycontactemail" 
                                    value="<?php echo htmlentities($settings['LibraryContactEmail'] ?? ''); ?>" 
                                    required 
                                />
                            </div>

                            <hr style="margin: 20px 0;">
                            <h4>Library Hours & Notes</h4>

                            <div class="form-group">
                                <label for="hours_mon_fri">Hours (Mon - Fri)</label>
                                <input 
                                    class="form-control" 
                                    type="text" 
                                    id="hours_mon_fri" 
                                    name="hours_mon_fri" 
                                    value="<?php echo htmlentities($settings['hours_mon_fri'] ?? ''); ?>" 
                                />
                            </div>

                            <div class="form-group">
                                <label for="hours_saturday">Hours (Saturday)</label>
                                <input 
                                    class="form-control" 
                                    type="text" 
                                    id="hours_saturday" 
                                    name="hours_saturday" 
                                    value="<?php echo htmlentities($settings['hours_saturday'] ?? ''); ?>" 
                                />
                            </div>

                            <div class="form-group">
                                <label for="closed_note">Closed Note / Holiday Message</label>
                                <textarea 
                                    class="form-control" 
                                    id="closed_note" 
                                    name="closed_note" 
                                    rows="3"
                                ><?php echo htmlentities($settings['closed_note'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_settings" class="update-btn">
                                <i class="fa fa-cogs"></i> Update Settings
                            </button>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div> 
    </div> 
    <?php include('includes/footer.php');?>
</body>
</html>
<?php } ?>