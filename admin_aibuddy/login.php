<?php
session_start();

// 1. KẾT NỐI DATABASE
$db_path = __DIR__ . '/config/db.php';
if (file_exists($db_path)) {
    require_once $db_path;
} else {
    require_once __DIR__ . '/../config/db.php';
}

// 2. CHUYỂN HƯỚNG NẾU ĐÃ LOGIN
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

// 3. XỬ LÝ ĐĂNG NHẬP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = trim($_POST['username']); // Lấy giá trị từ ô input name="username"
    $p = trim($_POST['password']); // Lấy giá trị từ ô input name="password"

    if (empty($u) || empty($p)) {
        $error = "Vui lòng điền đầy đủ thông tin!";
    } else {
        // --- QUAN TRỌNG: CẬP NHẬT QUERY THEO ẢNH BẠN GỬI ---
        // Tên bảng tôi để là `Admin`. Nếu bảng bạn tên khác (vd: `Admins`), hãy sửa lại chữ `Admin` ngay sau FROM.
        $sql = "SELECT AdminID, AdminName, AdminPassword, Role FROM Admin WHERE AdminName = ? LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // So sánh mật khẩu (AdminPassword)
            // Lưu ý: Nếu database bạn đang lưu pass thô (123456) thì dùng dòng if dưới.
            // Nếu đã mã hóa MD5/Bcrypt thì phải dùng password_verify().
            if ($p === $row['AdminPassword']) {
                
                // Đăng nhập đúng -> Lưu các cột vào Session
                $_SESSION['user_id'] = $row['AdminID'];       // Lưu AdminID
                $_SESSION['user_name'] = $row['AdminName'];   // Lưu AdminName
                $_SESSION['user_role'] = $row['Role'];        // Lưu Role (Mới thêm)
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Mật khẩu không đúng!";
            }
        } else {
            $error = "Tài khoản không tồn tại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: var(--primary);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            position: relative;
        }
        .login-card {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--primary); }
        .form-control {
            width: 100%; padding: 12px;
            border: 1px solid #ccc; border-radius: 6px;
        }
        .btn-submit {
            width: 100%; padding: 12px;
            background: var(--primary); color: #fff;
            border: none; border-radius: 6px; font-weight: bold; cursor: pointer;
        }
        .btn-submit:hover { background: var(--accent); color: var(--primary-dark); }
        .error-msg {
            color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;
        }
        .main-content {
        position: absolute; /* Tách khỏi flexbox center */
        bottom: 0;          /* Ghim xuống đáy */
        width: 100%;        /* Chiều rộng full màn hình */
        text-align: center; /* Căn giữa nội dung footer */
        padding-bottom: 10px;
    }
    </style>
</head>
<body>

    <div class="login-card">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="color: var(--primary);">ADMIN LOGIN</h2>
            <p style="color: #666;">Nhập thông tin Admin</p>
        </div>

        <?php if($error): ?>
            <div class="error-msg">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Tên Admin (AdminName)</label>
                <input type="text" name="username" class="form-control" placeholder="Nhập tên đăng nhập" required>
            </div>
            
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
            
            <button type="submit" class="btn-submit">Đăng nhập</button>
        </form>
    </div>
 
</body>
</html>