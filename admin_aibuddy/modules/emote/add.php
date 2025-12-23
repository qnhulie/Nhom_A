<?php
// modules/emotes/add.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$error = '';
$success = '';

// 2. XỬ LÝ FORM SUBMIT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['icon_name']);
    $symbol = trim($_POST['icon_symbol']);

    if (empty($name) || empty($symbol)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Insert vào database
        $stmt = $conn->prepare("INSERT INTO icon (IconName, IconSymbol) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $symbol);

        if ($stmt->execute()) {
            // Chuyển hướng về trang danh sách sau khi thêm thành công
            header("Location: index.php?msg=added");
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
    <title>Thêm Biểu tượng mới</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: var(--primary); }
        .form-control {
            width: 100%; padding: 12px;
            border: 1px solid #ddd; border-radius: 5px;
            font-size: 16px;
        }
        /* Style riêng cho input emoji để người dùng dễ nhìn */
        input[name="icon_symbol"] {
            font-size: 24px; /* Emoji to rõ */
            text-align: center;
            width: 100px;
        }
        .btn-submit {
            background: var(--primary); color: white;
            padding: 12px 25px; border: none; border-radius: 5px;
            cursor: pointer; font-weight: bold; font-size: 16px;
        }
        .btn-submit:hover { background: #0e3d4d; }
        .btn-cancel {
            background: #95a5a6; color: white;
            padding: 12px 25px; text-decoration: none; border-radius: 5px;
            font-weight: bold; margin-right: 10px;
        }
        .error-msg { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Thêm Biểu tượng cảm xúc</h2>
        <div class="user-profile">Admin: <?php echo $_SESSION['user_name']; ?></div>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h3 style="margin-bottom: 20px; color: var(--primary);">Nhập thông tin Icon</h3>
            
            <?php if($error): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Tên biểu tượng (VD: Joyful, Sad...)</label>
                    <input type="text" name="icon_name" class="form-control" placeholder="Nhập tên cảm xúc..." required>
                </div>

                <div class="form-group">
                    <label>Hình ảnh / Emoji</label>
                    <input type="text" name="icon_symbol" class="form-control" placeholder="😊" maxlength="10" required>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Mẹo: Nhấn <b>Windows + .</b> (trên Windows) hoặc <b>Cmd + Ctrl + Space</b> (trên Mac) để mở bảng Emoji.
                    </small>
                </div>

                <div style="margin-top: 30px;">
                    <a href="index.php" class="btn-cancel">Hủy bỏ</a>
                    <button type="submit" class="btn-submit">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>