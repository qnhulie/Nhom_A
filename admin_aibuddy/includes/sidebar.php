<?php
// includes/sidebar.php
require_once __DIR__ . '/../config/db.php';
?>
<div class="sidebar">
    
    <div class="brand">
        <i class="fa-solid fa-robot"></i> 
        <span>AI Buddy</span>
    </div>

    <a href="<?php echo BASE_URL; ?>index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules') === false ? 'active' : ''; ?>"> 
        <i class="fa-solid fa-home"></i> <span>Overview</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>modules/users/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules/users') !== false ? 'active' : ''; ?>"> 
        <i class="fa-solid fa-users"></i>
        <span>Users</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>modules/plans/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules/plans') !== false ? 'active' : ''; ?>">  
        <i class="fa-solid fa-gem"></i>
        <span>Service Plans</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>modules/orders/index.php"  class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules/orders') !== false ? 'active' : ''; ?>"> 
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <span>Orders</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>modules/reports/index.php"class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules/reports') !== false ? 'active' : ''; ?>"> 
        <i class="fa-solid fa-envelope"></i>
        <span>Reports & Refunds</span>
    </a>

  <a href="<?php echo BASE_URL; ?>modules/emote/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules/emote') !== false ? 'active' : ''; ?>"> 
    <i class="fa-solid fa-face-smile"></i>
    <span>EmoteTracker Management</span>
</a>
    
    <details class="sidebar-submenu">
        <summary>
            <i class="fa-solid fa-robot"></i>
            <span>Chatbots</span>
        </summary>
        <a href="<?php echo BASE_URL; ?>modules/chatbot/views/dashboard.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules/chatbot/views/dashboard') !== false ? 'active' : ''; ?>">
            <i class="fa-solid fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo BASE_URL; ?>modules/chatbot/views/personas/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules/chatbot/views/personas') !== false ? 'active' : ''; ?>">
            <i class="fa-solid fa-users-cog"></i>
            <span>Personas</span>
        </a>
        <a href="<?php echo BASE_URL; ?>modules/chatbot/views/topics/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'modules/chatbot/views/topics') !== false ? 'active' : ''; ?>">
            <i class="fa-solid fa-list-alt"></i>
            <span>Topics</span>
        </a>
    </details>
    
   
    
    <a href="<?php echo BASE_URL; ?>logout.php" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
    </a>

</div>