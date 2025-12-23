<?php
// modules/plans/edit.php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Get ID from URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$error = '';

// Get old plan info
$sql = "SELECT * FROM Plan WHERE PlanID = $id";
$result = $conn->query($sql);
$plan = $result->fetch_assoc();

if (!$plan) {
    die("Service plan not found!");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['PlanName']);
    $price = floatval($_POST['PlanPrice']);
    $cycle = $conn->real_escape_string($_POST['BillingCycle']);
    $desc = $conn->real_escape_string($_POST['PlanDescription']);
    // Nhận dữ liệu video URL
    $videoUrl = $conn->real_escape_string($_POST['PlanVideoURL']);

    if (empty($name)) {
        $error = "Plan name cannot be empty!";
    } else {
        // Cập nhật câu lệnh UPDATE
        $sql_update = "UPDATE Plan SET 
                        PlanName='$name', 
                        PlanPrice=$price, 
                        BillingCycle='$cycle', 
                        PlanDescription='$desc',
                        PlanVideoURL='$videoUrl' 
                       WHERE PlanID=$id";
                       
        if ($conn->query($sql_update)) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Update error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service Plan</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Edit Service Plan</h2>
        <div class="user-profile">Admin</div>
    </div>

    <div class="main-content">
        <a href="index.php" style="color: var(--primary); text-decoration: none; display: inline-block; margin-bottom: 20px;">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>

        <div class="card-box" style="max-width: 600px; margin: 0 auto;">
            <?php if($error): ?>
                <div style="background: #ffe6e6; color: red; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Plan Name:</label>
                    <input type="text" name="PlanName" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" 
                           value="<?php echo htmlspecialchars($plan['PlanName']); ?>" required>
                </div>

                <div style="margin-bottom: 15px; display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Price (VND):</label>
                        <input type="number" name="PlanPrice" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" 
                               value="<?php echo $plan['PlanPrice']; ?>" required>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Billing Cycle:</label>
                        <select name="BillingCycle" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="Daily" <?php if($plan['BillingCycle'] == 'Daily') echo 'selected'; ?>>Daily</option>
                            <option value="Monthly" <?php if($plan['BillingCycle'] == 'Monthly') echo 'selected'; ?>>Monthly</option>
                            <option value="Anually" <?php if($plan['BillingCycle'] == 'Anually') echo 'selected'; ?>>Annually</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Video URL:</label>
                    <input type="text" name="PlanVideoURL" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" 
                           value="<?php echo htmlspecialchars($plan['PlanVideoURL'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description:</label>
                    <textarea name="PlanDescription" rows="5" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($plan['PlanDescription']); ?></textarea>
                </div>

                <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 16px;">
                    <i class="fa-solid fa-save"></i> Update Plan
                </button>
            </form>
        </div>
    </div>
</body>
</html>