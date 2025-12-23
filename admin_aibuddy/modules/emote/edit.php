<?php
// modules/emotes/edit.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// 1. Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// 2. GET ICON INFO TO EDIT
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM icon WHERE IconID = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Emote not found!";
    exit();
}

$row = $result->fetch_assoc();
$error = '';

// 3. HANDLE UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['icon_name']);
    $symbol = trim($_POST['icon_symbol']);

    if (empty($name) || empty($symbol)) {
        $error = "Please enter all required information!";
    } else {
        $stmt = $conn->prepare("UPDATE icon SET IconName = ?, IconSymbol = ? WHERE IconID = ?");
        $stmt->bind_param("ssi", $name, $symbol, $id);

        if ($stmt->execute()) {
            header("Location: index.php?msg=updated");
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
    <title>Edit Emote</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse CSS from add.php */
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
        input[name="icon_symbol"] {
            font-size: 24px; text-align: center; width: 100px;
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
        <h2>Edit Emote</h2>
        <div class="user-profile">Admin: <?php echo $_SESSION['user_name']; ?></div>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h3 style="margin-bottom: 20px; color: var(--primary);">Edit Emote ID: #<?php echo $row['IconID']; ?></h3>
            
            <?php if($error): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Emote Name</label>
                    <input type="text" name="icon_name" class="form-control" 
                           value="<?php echo htmlspecialchars($row['IconName']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Symbol / Emoji</label>
                    <input type="text" name="icon_symbol" class="form-control" 
                           value="<?php echo htmlspecialchars($row['IconSymbol']); ?>" maxlength="10" required>
                </div>

                <div style="margin-top: 30px;">
                    <a href="index.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">Update</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>