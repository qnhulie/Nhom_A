<?php
// ================================================================
// MODULE: REPORTS DETAIL
// File: modules/reports/report.php
// ================================================================
session_start();
require_once __DIR__ . '/../../config/db.php';

// Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// --- PHP LOGIC HANDLING ---

// 1. Handle: Send Reply (Save to AdminResponse column)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_reply'])) {
    $report_id = intval($_POST['report_id']);
    $response_text = $conn->real_escape_string($_POST['admin_response']);
    
    // Only update response content, DO NOT change status (per requirement)
    $sql_update = "UPDATE report SET AdminResponse = '$response_text' WHERE ReportID = $report_id";
    
    if ($conn->query($sql_update)) {
        header("Location: report.php?msg=reply_success");
        exit();
    }
}

// 2. Handle: Mark as Resolved (Status -> Resolved)
if (isset($_GET['action']) && $_GET['action'] == 'resolve' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE report SET Status = 'Resolved' WHERE ReportID = $id");
    header("Location: report.php");
    exit();
}

// 3. Handle: Undo (Status -> Pending)
if (isset($_GET['action']) && $_GET['action'] == 'undo' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Revert status to Pending
    $conn->query("UPDATE report SET Status = 'Pending' WHERE ReportID = $id");
    header("Location: report.php");
    exit();
}

// Get report list
$sql = "SELECT * FROM report ORDER BY ReportTime DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* CSS FOR TABLE & CARD */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }
        
        /* Box containing table similar to Stat Card in Index */
        .table-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e0e0e0;
            overflow: hidden;
            margin-top: 20px;
        }

        .card-header {
            background-color: var(--primary, #124559);
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-responsive {
            padding: 20px;
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
        th { color: var(--primary, #124559); font-weight: 700; text-transform: uppercase; font-size: 12px; }
        tr:hover { background-color: #f9f9f9; }

        /* Badges */
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; white-space: nowrap; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-resolved { background: #d4edda; color: #155724; }
        
        /* Response Text Display */
        .response-text {
            font-size: 12px;
            color: #666;
            font-style: italic;
            display: block;
            margin-top: 5px;
            background: #f1f1f1;
            padding: 5px;
            border-radius: 4px;
            border-left: 3px solid #124559;
        }

        /* Buttons Action */
        .btn-action { 
            border: none; 
            background: none; 
            cursor: pointer; 
            font-size: 16px; 
            margin-right: 8px; 
            transition: 0.2s;
        }
        .btn-reply { color: #3498db; }
        .btn-resolve { color: #2ecc71; }
        .btn-undo { color: #e67e22; }
        
        .back-link { display: inline-block; color: var(--primary); text-decoration: none; font-weight: 600; margin-bottom: 10px; }

        /* --- MODAL (POPUP) CSS --- */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; 
            z-index: 2000; 
            left: 0; top: 0;
            width: 100%; height: 100%; 
            background-color: rgba(0,0,0,0.5); /* Overlay */
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .close-btn {
            position: absolute; top: 15px; right: 20px;
            font-size: 24px; cursor: pointer; color: #aaa;
        }
        .close-btn:hover { color: #333; }
        
        /* Form inside modal */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group textarea {
            width: 100%; padding: 10px;
            border: 1px solid #ddd; border-radius: 5px;
            resize: vertical; min-height: 100px;
            font-family: inherit;
        }
        .btn-submit {
            background-color: var(--primary, #124559);
            color: white; padding: 10px 20px;
            border: none; border-radius: 5px;
            cursor: pointer; font-weight: bold;
            width: 100%;
        }
        .btn-submit:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Report Management</h2>
        <div class="user-profile">
            <span>Welcome, <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
        </div>
    </div>

    <div class="main-content">
        
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

        <div class="table-card">
            <div class="card-header">
                List of Reports & Complaints
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="10%">User ID</th>
                            <th width="15%">Type</th>
                            <th width="30%">Report Content</th>
                            <th width="20%">Admin Response</th>
                            <th width="10%">Status</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['ReportID']; ?></td>
                                    <td><?php echo $row['UserID']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['ReportType']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['ReportContent']); ?>
                                        <div style="font-size: 11px; color: #999; margin-top: 4px;">
                                            <i class="fa-regular fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($row['ReportTime'])); ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <?php if (!empty($row['AdminResponse'])): ?>
                                            <div class="response-text">
                                                <i class="fa-solid fa-reply" style="font-size:10px;"></i> 
                                                <?php echo htmlspecialchars($row['AdminResponse']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #ccc; font-size: 12px;">No response yet</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($row['Status'] == 'Pending'): ?>
                                            <span class="status-badge status-pending">Pending</span>
                                        <?php else: ?>
                                            <span class="status-badge status-resolved">Resolved</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <button class="btn-action btn-reply" 
                                                onclick="openReplyModal(<?php echo $row['ReportID']; ?>, '<?php echo addslashes($row['AdminResponse']); ?>')" 
                                                title="Reply to report">
                                            <i class="fa-solid fa-comment-dots"></i>
                                        </button>

                                        <?php if($row['Status'] == 'Pending'): ?>
                                            <a href="report.php?action=resolve&id=<?php echo $row['ReportID']; ?>" 
                                               class="btn-action btn-resolve"
                                               onclick="return confirm('Confirm that this issue has been resolved?');"
                                               title="Mark as Resolved">
                                                <i class="fa-solid fa-check-circle"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="report.php?action=undo&id=<?php echo $row['ReportID']; ?>" 
                                               class="btn-action btn-undo"
                                               onclick="return confirm('Revert status back to Pending?');"
                                               title="Undo">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center; padding: 20px;">No reports found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div id="replyModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeReplyModal()">&times;</span>
            <h3 style="color: var(--primary, #124559); margin-top: 0;">Send Reply to User</h3>
            
            <form action="" method="POST">
                <input type="hidden" name="report_id" id="modal_report_id">
                
                <div class="form-group">
                    <label>Response Content:</label>
                    <textarea name="admin_response" id="modal_response_text" placeholder="Enter your response here..."></textarea>
                </div>

                <button type="submit" name="submit_reply" class="btn-submit">
                    <i class="fa-solid fa-paper-plane"></i> Save Response
                </button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("replyModal");
        const reportIdInput = document.getElementById("modal_report_id");
        const responseTextInput = document.getElementById("modal_response_text");

        // Function to open modal and fill data
        function openReplyModal(id, currentResponse) {
            modal.style.display = "flex";
            reportIdInput.value = id;
            responseTextInput.value = currentResponse; // If exists, show to edit
            responseTextInput.focus();
        }

        // Function to close modal
        function closeReplyModal() {
            modal.style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>
</html>