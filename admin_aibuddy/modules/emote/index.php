<?php
// modules/emote/index.php
session_start();
require_once __DIR__ . '/../../config/db.php'; // Ensure path to config/db.php is correct

// 1. Check Admin privileges
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// 2. HANDLE DELETE ICON
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    // Check if Icon is being used in emotionentry table to avoid constraint error
    $check = $conn->query("SELECT COUNT(*) as count FROM emotionentry WHERE IconID = $id");
    $row = $check->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_msg = "Cannot delete this emote because it is currently used in user data!";
    } else {
        $conn->query("DELETE FROM icon WHERE IconID = $id");
        header("Location: index.php?msg=deleted");
        exit();
    }
}

// 3. GET ICON LIST
$sql = "SELECT * FROM icon ORDER BY IconID ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emote Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* CSS specifically for large icon display */
        .emote-preview {
            font-size: 2.5rem; /* Larger icon */
            display: inline-block;
            transition: transform 0.2s;
        }
        .emote-preview:hover {
            transform: scale(1.2);
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Emote Management</h2>
        <div class="user-profile">Admin: <?php echo $_SESSION['user_name']; ?></div>
    </div>

    <div class="main-content">
        
        <?php if (isset($error_msg)): ?>
            <div class="alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert-success">
                <i class="fa-solid fa-check-circle"></i> Emote deleted successfully!
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 20px; text-align: right;">
            <a href="add.php" class="btn" style="background: var(--primary); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                <i class="fa-solid fa-plus"></i> Add New Emote
            </a>
        </div>

        <div class="table-container card-box" style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--primary); color: white;">
                        <th style="padding: 15px; width: 10%;">ID</th>
                        <th style="padding: 15px; width: 30%;">Emote Name</th>
                        <th style="padding: 15px; width: 30%; text-align: center;">Symbol / Image</th>
                        <th style="padding: 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px;">#<?php echo $row['IconID']; ?></td>
                                
                                <td style="padding: 15px; font-weight: bold; color: var(--primary);">
                                    <?php echo htmlspecialchars($row['IconName']); ?>
                                </td>
                                
                                <td style="padding: 15px; text-align: center;">
                                    <span class="emote-preview">
                                        <?php echo htmlspecialchars($row['IconSymbol']); ?>
                                    </span>
                                </td>
                                
                                <td style="padding: 15px; text-align: center;">
                                    <a href="edit.php?id=<?php echo $row['IconID']; ?>" style="color: var(--accent); margin-right: 15px; font-size: 18px;" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    <a href="index.php?delete_id=<?php echo $row['IconID']; ?>" 
                                       onclick="return confirm('WARNING: Are you sure you want to delete the emote \'<?php echo $row['IconName']; ?>\'?');" 
                                       style="color: #e74c3c; font-size: 18px;" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 30px; text-align: center; color: #7f8c8d;">
                                <i class="fa-regular fa-folder-open" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                No emotes found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>