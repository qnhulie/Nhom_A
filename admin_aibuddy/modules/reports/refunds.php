<?php
// ================================================================
// MODULE: REFUNDS DETAIL (UPDATED)
// File: modules/reports/refunds.php
// ================================================================
session_start();
require_once __DIR__ . '/../../config/db.php';

// Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// --- HANDLE FORM SUBMIT (APPROVE / REJECT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = intval($_POST['refund_id']);
    $response = trim($_POST['admin_response']);
    $action = $_POST['action']; // 'approve' or 'reject'
    
    $status = ($action === 'approve') ? 'Approved' : 'Rejected';
    
    $stmt = $conn->prepare("UPDATE refundrequest SET RefundStatus = ?, AdminResponse = ?, UpdatedDate = NOW() WHERE RefundID = ?");
    $stmt->bind_param("ssi", $status, $response, $id);
    
    if ($stmt->execute()) {
        header("Location: refunds.php?msg=updated");
        exit();
    }
}

// --- HANDLE UNDO ---
if (isset($_GET['action']) && $_GET['action'] == 'undo' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Reset to Pending and clear response
    $conn->query("UPDATE refundrequest SET RefundStatus = 'Pending', AdminResponse = NULL, UpdatedDate = NULL WHERE RefundID = $id");
    header("Location: refunds.php?msg=undo");
    exit();
}

// --- GET REFUND LIST (JOIN WITH USER & ORDER) ---
$sql = "
    SELECT r.*, u.UserName, u.UserEmail, o.OrderID 
    FROM refundrequest r
    JOIN userorder o ON r.TransactionID = o.OrderID
    JOIN users u ON o.UserID = u.UserID
    ORDER BY r.RequestDate DESC
"; 
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Refund Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .table-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; min-width: 1000px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        th { background-color: var(--primary, #124559); color: white; font-weight: 600; font-size: 14px; }
        td { color: #555; font-size: 14px; }
        tr:hover { background-color: #f9f9f9; }
        
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; display: inline-block; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .btn-action { 
            padding: 6px 10px; 
            border-radius: 4px; 
            font-size: 12px; 
            border: none; 
            cursor: pointer; 
            color: white; 
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-approve { background-color: #27ae60; }
        .btn-approve:hover { background-color: #219150; }
        
        .btn-reject { background-color: #e74c3c; }
        .btn-reject:hover { background-color: #c0392b; }

        .btn-undo { background-color: #f39c12; }
        .btn-undo:hover { background-color: #d35400; }
        
        .back-link { display: inline-block; margin-bottom: 15px; color: var(--primary); text-decoration: none; font-weight: 600; }
        
        /* --- MODAL CSS --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000;
            display: none; justify-content: center; align-items: center;
        }
        .modal-box {
            background: white; padding: 25px; border-radius: 8px;
            width: 400px; max-width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .modal-header { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
        .modal-body textarea {
            width: 100%; height: 100px; padding: 10px;
            border: 1px solid #ddd; border-radius: 5px;
            resize: vertical; font-family: inherit;
        }
        .modal-footer { margin-top: 20px; text-align: right; }
        .btn-close { background: #95a5a6; margin-right: 10px; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Refund Requests</h2>
        <div class="user-profile">
            <span>Admin: <?php echo $_SESSION['user_name']; ?></span>
        </div>
    </div>

    <div class="main-content">
        
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Type / Amount</th>
                        <th width="25%">Reason & Details</th>
                        <th>Date Sent</th>
                        <th>Admin Response</th>
                        <th>Status</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['RefundID']; ?></td>
                                
                                <td>
                                    <strong><?php echo htmlspecialchars($row['UserName']); ?></strong><br>
                                    <small style="color:#777;"><?php echo htmlspecialchars($row['UserEmail']); ?></small><br>
                                    <small>Order #<?php echo $row['OrderID']; ?></small>
                                </td>
                                
                                <td>
                                    <span style="color:#2980b9; font-weight:bold;"><?php echo htmlspecialchars($row['RefundType']); ?></span><br>
                                    <span style="color:#c0392b; font-weight:bold;"><?php echo number_format($row['RefundAmount']); ?> đ</span>
                                </td>

                                <td>
                                    <div style="max-height:80px; overflow-y:auto; font-size:13px;">
                                        <?php echo nl2br(htmlspecialchars($row['RefundDetails'])); ?>
                                    </div>
                                </td>

                                <td><?php echo date('d/m/Y H:i', strtotime($row['RequestDate'])); ?></td>
                                
                                <td style="font-size:13px; color:#555; font-style:italic;">
                                    <?php echo $row['AdminResponse'] ? htmlspecialchars($row['AdminResponse']) : '-'; ?>
                                </td>

                                <td>
                                    <?php 
                                    $statusClass = 'status-pending';
                                    if ($row['RefundStatus'] == 'Approved') $statusClass = 'status-approved';
                                    if ($row['RefundStatus'] == 'Rejected') $statusClass = 'status-rejected';
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo $row['RefundStatus']; ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if($row['RefundStatus'] == 'Pending'): ?>
                                        <button class="btn-action btn-approve" 
                                                onclick="openModal(<?php echo $row['RefundID']; ?>, 'approve')">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        
                                        <button class="btn-action btn-reject" 
                                                onclick="openModal(<?php echo $row['RefundID']; ?>, 'reject')">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    <?php else: ?>
                                        <a href="refunds.php?action=undo&id=<?php echo $row['RefundID']; ?>" 
                                           class="btn-action btn-undo"
                                           onclick="return confirm('Do you want to revert this request to pending status?');">
                                            <i class="fa-solid fa-rotate-left"></i> Undo
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center; padding: 20px;">No requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="responseModal">
        <div class="modal-box">
            <form method="POST" action="refunds.php">
                <input type="hidden" name="refund_id" id="modalRefundId">
                <input type="hidden" name="action" id="modalAction">
                
                <div class="modal-header" id="modalTitle">Process Request</div>
                
                <div class="modal-body">
                    <p style="margin-bottom:8px;">Admin Response:</p>
                    <textarea name="admin_response" placeholder="Enter reason for approval or rejection..." required></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-action btn-close" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-action btn-approve" id="modalSubmitBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, action) {
            document.getElementById('responseModal').style.display = 'flex';
            document.getElementById('modalRefundId').value = id;
            document.getElementById('modalAction').value = action;
            
            const title = document.getElementById('modalTitle');
            const btn = document.getElementById('modalSubmitBtn');
            
            if (action === 'approve') {
                title.innerText = 'Approve Refund Request';
                title.style.color = '#27ae60';
                btn.innerText = 'Approve Now';
                btn.className = 'btn-action btn-approve';
            } else {
                title.innerText = 'Reject Request';
                title.style.color = '#e74c3c';
                btn.innerText = 'Reject';
                btn.className = 'btn-action btn-reject';
            }
        }

        function closeModal() {
            document.getElementById('responseModal').style.display = 'none';
        }
    </script>

</body>
</html>