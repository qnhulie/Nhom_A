<?php
// includes/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TỰ ĐỘNG LẤY ĐƯỜNG DẪN GỐC (Tránh lỗi link sai)
// Kết quả sẽ là: http://localhost/admin_aibuddy/
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $domainName . "/admin_aibuddy/";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | AI Buddy</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>