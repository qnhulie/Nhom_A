<?php
// ================================================================
// MODULE: USER MANAGER - LIST
// File: modules/users/index.php
// ================================================================
session_start();

// 1. DATABASE CONNECTION (Go up 2 levels)
require_once __DIR__ . '/../../config/db.php';

// 2. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// 3. HANDLE USER DELETION
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM Users WHERE UserID = $id");
    // Refresh page to remove deleted row
    header("Location: index.php"); 
    exit();
}

// 4. HANDLE SEARCH
$search = "";
$where = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_safe = $conn->real_escape_string($search);
    
    if (is_numeric($search)) {
        // Search by ID or Phone
        $where = "WHERE UserID = $search_safe OR PhoneNumber LIKE '%$search_safe%'";
    } else {
        // Search by Name or Email
        $where = "WHERE UserName LIKE '%$search_safe%' OR UserEmail LIKE '%$search_safe%'";
    }
}

// Query list
$sql = "SELECT * FROM Users $where ORDER BY UserID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>User Management</h2>
        <div class="user-profile">
            <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
        </div>
    </div>

    <div class="main-content">
        
        <div class="card-box" style="margin-bottom: 20px;">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="form-control" style="flex: 1; padding: 10px;" 
                       placeholder="Enter ID, Name, Email or Phone..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary" style="background: var(--primary); color: white; border: none; padding: 0 20px; border-radius: 5px; cursor: pointer;">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
                <?php if($search): ?>
                    <a href="index.php" class="btn" style="background: #999; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">Clear Filter</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container card-box" style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--primary); color: white;">
                        <th style="padding: 15px;">ID</th>
                        <th style="padding: 15px;">Full Name</th>
                        <th style="padding: 15px;">Email</th>
                        <th style="padding: 15px;">Phone</th>
                        <th style="padding: 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px;">#<?php echo $row['UserID']; ?></td>
                                <td style="padding: 15px; font-weight: bold; color: var(--primary);">
                                    <?php echo $row['UserName']; ?>
                                </td>
                                <td style="padding: 15px;"><?php echo $row['UserEmail']; ?></td>
                                <td style="padding: 15px;"><?php echo $row['PhoneNumber']; ?></td>
                                <td style="padding: 15px; text-align: center;">
                                    <a href="views.php?id=<?php echo $row['UserID']; ?>" style="color: var(--accent); margin-right: 10px; font-size: 18px;" title="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="index.php?delete_id=<?php echo $row['UserID']; ?>" onclick="return confirm('Are you sure you want to delete this user?');" style="color: #e74c3c; font-size: 18px;" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: #999;">No data found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>