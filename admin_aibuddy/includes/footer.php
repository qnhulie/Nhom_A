<footer class="main-footer">
    <div class="footer-left">
        <strong>Copyright &copy; <?php echo date("Y"); ?> <a href="#">AI Buddy Admin</a>.</strong> 
        All rights reserved.
    </div>
    <div class="footer-right">
        <b>Version</b> 1.0.0 | Designed by <span>You</span>
    </div>
</footer>

<style>
    /* CSS Riêng cho Footer */
    .main-footer {
        background: #fff;
        padding: 15px 30px;
        border-top: 1px solid #dee2e6;
        color: #869099;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 30px; /* Tạo khoảng cách với nội dung bên trên */
        width: 100%;
        box-sizing: border-box;
    }

    .main-footer a {
        color: var(--primary, #124559); /* Dùng màu chủ đạo của web */
        text-decoration: none;
        font-weight: 600;
    }

    .main-footer a:hover {
        text-decoration: underline;
    }

    .footer-right span {
        font-weight: 600;
        color: #333;
    }

    /* Responsive cho mobile */
    @media (max-width: 768px) {
        .main-footer {
            flex-direction: column;
            text-align: center;
            gap: 5px;
        }
    }
</style>

</body>
</html>