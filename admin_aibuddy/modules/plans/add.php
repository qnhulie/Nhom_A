<?php
// modules/plans/add.php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['PlanName']);
    $price = floatval($_POST['PlanPrice']);
    $cycle = $conn->real_escape_string($_POST['BillingCycle']);
    $desc = $conn->real_escape_string($_POST['PlanDescription']);
    // Nhận dữ liệu video URL
    $videoUrl = $conn->real_escape_string($_POST['PlanVideoURL']);

    if (empty($name)) {
        $error = "Tên gói không được để trống!";
    } else {
        // Cập nhật câu lệnh INSERT
        $sql = "INSERT INTO Plan (PlanName, PlanPrice, BillingCycle, PlanDescription, PlanVideoURL) 
                VALUES ('$name', $price, '$cycle', '$desc', '$videoUrl')";
        
        if ($conn->query($sql)) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Lỗi hệ thống: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Gói dịch vụ</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Thêm Gói mới</h2>
        <div class="user-profile">Admin</div>
    </div>

    <div class="main-content">
        <a href="index.php" style="color: var(--primary); text-decoration: none; display: inline-block; margin-bottom: 20px;">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>

        <div class="card-box" style="max-width: 600px; margin: 0 auto;">
            <?php if($error): ?>
                <div style="background: #ffe6e6; color: red; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tên gói dịch vụ:</label>
                    <input type="text" name="PlanName" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required placeholder="Ví dụ: Gói Premium">
                </div>

                <div style="margin-bottom: 15px; display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Giá (VND):</label>
                        <input type="number" name="PlanPrice" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required value="0">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Chu kỳ thanh toán:</label>
                        <select name="BillingCycle" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="Daily">Daily</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Anually">Anually</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Đường dẫn Video (YouTube URL):</label>
                    <input type="text" name="PlanVideoURL" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="https://www.youtube.com/watch?v=...">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Mô tả chi tiết:</label>
                    <textarea name="PlanDescription" rows="5" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                </div>

                <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 16px;">
                    <i class="fa-solid fa-save"></i> Lưu Gói Dịch Vụ
                </button>
            </form>
        </div>
    </div>
</body>
</html>