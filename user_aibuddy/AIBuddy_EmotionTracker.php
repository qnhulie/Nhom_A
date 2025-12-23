<?php
session_start();
require_once 'config/db.php'; 

$userID = $_SESSION['userid'] ?? 0;
$currentUserName = null;
$isPremium = false; // Mặc định là khóa

// --- 0. KIỂM TRA QUYỀN TRUY CẬP (LOGIC MỚI) ---
if ($userID > 0) {
    // 1. Lấy tên user
    $stmt = $conn->prepare("SELECT UserName FROM users WHERE UserID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $currentUserName = $res['UserName'];
    }

    // 2. Kiểm tra gói cước đang sử dụng (PlanID > 1 là gói trả phí)
    // Chỉ lấy đơn hàng Active hoặc Completed gần nhất
    $planStmt = $conn->prepare("
        SELECT PlanID 
        FROM userorder 
        WHERE UserID = ? AND OrderStatus IN ('Completed', 'Active') 
        ORDER BY OrderID DESC 
        LIMIT 1
    ");
    $planStmt->bind_param("i", $userID);
    $planStmt->execute();
    $planRes = $planStmt->get_result()->fetch_assoc();

    // Nếu có đơn hàng VÀ PlanID > 1 (Gói Free là 1) -> Mở khóa
    if ($planRes && $planRes['PlanID'] > 1) {
        $isPremium = true;
    }
}
// ------------------------------------------------

// --- 0.1 LẤY DANH SÁCH ICON (CHỈ LẤY NẾU CÓ QUYỀN HOẶC ĐỂ HIỂN THỊ MỜ) ---
$icons = [];
$iconQuery = "SELECT * FROM icon ORDER BY IconID ASC";
$iconResult = $conn->query($iconQuery);
if ($iconResult) {
    while ($row = $iconResult->fetch_assoc()) {
        $icons[] = $row;
    }
}

// 1. XỬ LÝ AJAX REQUEST (API)
// --------------------------------------------------------

// A. API LƯU DỮ LIỆU (SAVE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    header('Content-Type: application/json');
    
    // CHECK BẢO MẬT: Nếu không phải Premium -> Chặn lưu
    if (!$isPremium) {
        echo json_encode(['status' => 'error', 'message' => 'Upgrade to Premium to use this feature!']);
        exit;
    }

    if ($userID == 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }
    $iconID = intval($_POST['score']); 
    $description = trim($_POST['note']); 
    $dateInput = $_POST['date']; 
    $entryTime = $dateInput . ' ' . date('H:i:s');

    $stmt = $conn->prepare("INSERT INTO emotionentry (UserID, IconID, EntryTime, EmotionDescription) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $userID, $iconID, $entryTime, $description);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

// B. API LẤY DỮ LIỆU (LOAD)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'load') {
    header('Content-Type: application/json');

    // CHECK BẢO MẬT: Nếu không phải Premium -> Không trả về dữ liệu cá nhân
    if (!$isPremium) {
        echo json_encode([]); 
        exit;
    }

    if ($userID == 0) {
        echo json_encode([]);
        exit;
    }

    // JOIN bảng icon để lấy thông tin mới nhất
    $stmt = $conn->prepare("SELECT e.IconID, e.EntryTime, e.EmotionDescription, i.IconName, i.IconSymbol 
                            FROM emotionentry e
                            LEFT JOIN icon i ON e.IconID = i.IconID
                            WHERE e.UserID = ? 
                            ORDER BY e.EntryTime DESC");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $dateOnly = date('Y-m-d', strtotime($row['EntryTime']));
        
        // Fallback nếu icon bị xóa
        $moodName = $row['IconName'] ?? 'Unknown';
        $moodSymbol = $row['IconSymbol'] ?? '❓';

        $data[] = [
            'score' => intval($row['IconID']),
            'mood'  => $moodSymbol . ' ' . $moodName,
            'symbol'=> $moodSymbol,
            'note'  => $row['EmotionDescription'],
            'date'  => $dateOnly
        ];
    }
    echo json_encode($data);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Buddy · Emotion Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* ... (GIỮ NGUYÊN CSS CŨ CỦA BẠN) ... */
    :root { --primary-dark: #01161e; --primary: #124559; --primary-light: #598392; --accent: #33c6e7; --light: #aec3b0; --background: #eff6e0; --white: #ffffff; --gray: #d9d9d9; --text: #353535; --shadow: 0 5px 15px rgba(0, 0, 0, .08); }
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Verdana, sans-serif; }
    body { background: var(--background); color: var(--text); }
    .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
    header { background-color: var(--white); padding: 15px 0; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 100; }
    .header-content { display: flex; justify-content: space-between; align-items: center; }
    .logo { font-size: 24px; font-weight: bold; color: var(--primary); display: flex; align-items: center; }
    .logo-icon { margin-right: 8px; font-size: 28px; }
    nav a { margin: 0 15px; text-decoration: none; color: var(--primary); font-weight: 500; transition: color 0.3s; }
    nav a:hover { color: var(--accent); }
    .signin-btn { background-color: var(--accent); color: var(--white); border: none; padding: 8px 20px; border-radius: 20px; font-weight: 600; cursor: pointer; transition: background-color 0.3s; }
    .hero { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: var(--white); padding: 80px 0; text-align: center; margin: 20px auto; border-radius: 10px; box-shadow: var(--shadow); }
    .hero h1 { font-size: 2.8rem; margin-bottom: 20px; }
    main { display: flex; gap: 30px; margin: 40px auto; position: relative; /* Để overlay hoạt động */ }
    .left { flex: 2; }
    .right { flex: 1; }
    .card { background: white; border-radius: 12px; padding: 25px; box-shadow: var(--shadow); margin-bottom: 25px; }
    
    .mood-row { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); 
        gap: 10px; 
        margin-bottom: 15px; 
    }
    .mood-btn { border: 1px solid var(--gray); border-radius: 10px; padding: 12px; font-weight: 600; cursor: pointer; background: white; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80px;}
    .mood-btn.active { outline: 2px solid var(--accent); background-color: #f0faff; border-color: var(--accent); }
    .mood-emoji { font-size: 24px; margin-bottom: 5px; }
    
    input, textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--gray); margin-top: 12px; }
    textarea { min-height: 120px; resize: vertical; }
    .actions { display: flex; gap: 10px; margin-top: 15px; align-items: center; }
    .btn { padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .btn-primary { background: var(--primary); color: white; border: none; }
    .btn-ghost { background: white; border: 1px solid var(--primary); color: var(--primary); }
    .entries { margin-top: 20px; display: grid; gap: 12px; max-height: 500px; overflow-y: auto; }
    .entry { border: 1px solid var(--gray); border-radius: 10px; padding: 12px; }
    .entry-head { display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 5px; }
    canvas { width: 100%; height: 240px; }
    .chart-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 10px; }
    .chart-tip { position: absolute; pointer-events: none; background: #ffffff; border: 1px solid #e6eef0; box-shadow: 0 10px 25px rgba(0, 0, 0, .10); border-radius: 10px; padding: 10px 12px; font-size: 12px; color: var(--text); min-width: 170px; max-width: 260px; opacity: 0; transform: translateY(6px); transition: opacity .12s ease, transform .12s ease; z-index: 5; }
    .chart-tip.show { opacity: 1; transform: translateY(0); }
    .user-greeting-badge { background-color: var(--background); color: var(--primary); padding: 8px 20px; border-radius: 20px; font-size: 15px; font-weight: 500; border: 1px solid var(--primary); display: inline-block; }
    .user-greeting-badge strong { color: var(--accent); font-weight: 700; }
    footer { background: var(--primary-dark); color: white; padding: 60px 0 20px; margin-top: 60px; }
    .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px; }
    .footer-column ul { list-style: none; }
    .copyright { text-align: center; border-top: 1px solid var(--primary); padding-top: 20px; color: var(--light); font-size: .9rem; }
    .social-links a { color: var(--accent); margin-right: 10px; font-size: 1.2rem; }
    @media(max-width:768px) { main { flex-direction: column; } .header-content { flex-direction: column; } nav { margin: 10px 0; } }

    /* CSS CHO LỚP PHỦ KHÓA (PREMIUM LOCK) */
    .premium-blur {
        filter: blur(5px);
        pointer-events: none; /* Chặn click vào nội dung bên dưới */
        opacity: 0.6;
        user-select: none;
    }
    .lock-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 10;
        text-align: center;
    }
    .lock-box {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        max-width: 400px;
        width: 90%;
        border-top: 5px solid var(--accent);
    }
    .lock-icon {
        font-size: 50px;
        color: var(--accent);
        margin-bottom: 20px;
    }
    .lock-btn {
        display: inline-block;
        background: var(--primary);
        color: white;
        padding: 12px 30px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        margin-top: 20px;
        transition: 0.3s;
    }
    .lock-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    /* Footer */

    footer {

      background-color: var(--primary-dark);

      color: var(--white);

      padding: 60px 0 20px;

      margin-top: 60px;

    }



    .footer-content {

      display: grid;

      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));

      gap: 40px;

      margin-bottom: 40px;

    }



    .footer-column h3 {

      font-size: 1.2rem;

      margin-bottom: 20px;

      color: var(--accent);

    }



    .footer-column ul {

      list-style: none;

    }



    .footer-column ul li {

      margin-bottom: 10px;

    }



    .footer-column ul li a {

      color: var(--light);

      text-decoration: none;

      transition: color 0.3s;

    }



    .footer-column ul li a:hover {

      color: var(--accent);

    }



    .copyright {

      text-align: center;

      padding-top: 20px;

      border-top: 1px solid var(--primary);

      color: var(--light);

      font-size: 0.9rem;

    }



    .social-links {

      display: flex;

      gap: 15px;

      margin-top: 15px;

    }



    .social-links a {

      color: var(--light);

      font-size: 1.2rem;

      transition: color 0.3s;

    }



    .social-links a:hover {

      color: var(--accent);

    }

  </style>
</head>

<body>
  <header>
    <div class="container header-content">
      <div class="logo"><span class="logo-icon">🤖</span> AI Buddy</div>
      <nav>
        <a href="AIBuddy_Homepage.php">Home</a>
        <a href="AIBuddy_Chatbot.php">Chatbot</a>
        <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>
        <a href="AIBuddy_Trial.php">Trial</a>
        <a href="AIBuddy_Profile.php">Profile</a>
        <a href="AIBuddy_About.php">About</a>
        <a href="AIBuddy_Contact.php">Contact</a>
      </nav>
      <?php if ($currentUserName): ?>
        <div class="user-greeting-badge">How's your day, <strong><?= htmlspecialchars($currentUserName) ?></strong>?</div>
      <?php else: ?>
        <button class="signin-btn" onclick="window.location.href='AIBuddy_SignIn.php'">Sign In</button>
      <?php endif; ?>
    </div>
  </header>

  <section class="hero">
    <div class="container hero-content">
      <h1>Emotion Tracker</h1>
      <p>Reflect, journal, and understand your emotional patterns.</p>
    </div>
  </section>

  <div class="container">
    
    <div style="position: relative;">
        
        <?php if (!$isPremium): ?>
            <div class="lock-overlay">
                <div class="lock-box">
                    <div class="lock-icon"><i class="fas fa-lock"></i></div>
                    <h2>Premium Feature</h2>
                    <p>Unlock Emotion Tracker to analyze your mood patterns and improve mental wellbeing.</p>
                    <p style="font-size: 0.9rem; color: #666; margin-top: 10px;">Available on Essential & Premium plans.</p>
                    <a href="AIBuddy_Trial.php" class="lock-btn">Upgrade Now</a>
                </div>
            </div>
        <?php endif; ?>

        <main class="<?= $isPremium ? '' : 'premium-blur' ?>">
          <div class="left">
            <div class="card">
              <h3>Quick Mood</h3>
              
              <div class="mood-row">
                <?php if (!empty($icons)): ?>
                    <?php foreach ($icons as $index => $icon): ?>
                        <?php 
                            $isActive = ($index == floor(count($icons) / 2)) ? 'active' : ''; 
                        ?>
                        <div class="mood-btn <?= $isActive ?>" data-score="<?= $icon['IconID'] ?>">
                            <span class="mood-emoji"><?= $icon['IconSymbol'] ?></span>
                            <span><?= htmlspecialchars($icon['IconName']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No emotions found.</p>
                <?php endif; ?>
              </div>

              <label>Select date</label>
              <input type="date" id="entryDate">
              <textarea id="note" placeholder="How are you feeling today? What happened..."></textarea>
              <div class="actions">
                <button class="btn btn-primary" id="saveBtn">Save</button>
                <button class="btn btn-ghost" id="clearBtn">Clear</button>
              </div>
              <div class="entries" id="entries"></div>
            </div>
          </div>

          <div class="right">
            <div class="card">
              <h3>Mood Chart (7 days)</h3>
              <div class="chart-nav">
                <button class="btn btn-ghost" id="prevWeek">← Prev</button>
                <strong id="weekLabel"></strong>
                <button class="btn btn-ghost" id="nextWeek">Next →</button>
              </div>
              <canvas id="moodChart" width="500" height="260"></canvas>
            </div>
          </div>
        </main>
    
    </div> </div>

  <footer>

    <div class="container">

      <div class="footer-content">

        <div class="footer-column">

          <h3>AI Buddy</h3>

          <p>Your companion for mental wellness with intelligent AI support and personalized care.</p>

          <div class="social-links">

            <a href="#"><i class="fab fa-facebook-f"></i></a>

            <a href="#"><i class="fab fa-twitter"></i></a>

            <a href="#"><i class="fab fa-instagram"></i></a>

            <a href="#"><i class="fab fa-linkedin-in"></i></a>

          </div>

        </div>

        <div class="footer-column">

          <h3>Quick Links</h3>

          <ul>

            <li><a href="AIBuddy_Homepage.php">Home</a></li>

            <li><a href="AIBuddy_Chatbot.php">Chatbot</a></li>

            <li><a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a></li>

            <li><a href="AIBuddy_Trial.php">Trial</a></li>

            <li><a href="AIBuddy_Contact.php">Contact</a></li>

          </ul>

        </div>

        <div class="footer-column">

          <h3>Legal</h3>

          <ul>

            <li><a href="AIBuddy_Terms of Service.php">Terms of Service</a></li>

            <li><a href="AIBuddy_PrivacyPolicy.php">Privacy Policy</a></li>

            <li><a href="#">Cookie Policy</a></li>

            <li><a href="#">Disclaimer</a></li>

          </ul>

        </div>

        <div class="footer-column">

          <h3>Contact</h3>

          <ul>

            <li><i class="fas fa-map-marker-alt"></i> 123 Wellness Street, Mindful District, CA 90210</li>

            <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>

            <li><i class="fas fa-envelope"></i> support@aibuddy.com</li>

            <li><i class="fas fa-clock"></i> Mon-Fri: 8:00 AM - 8:00 PM</li>

          </ul>

        </div>

      </div>

      <div class="copyright">

        <p>&copy; 2025 AI Buddy. All rights reserved. | Mental Health Companion</p>

      </div>

    </div>

  </footer>

  <script>
    /* ========= DYNAMIC DATA FROM PHP ========= */
    const rawIcons = <?php echo json_encode($icons); ?>;
    
    const moodEmoji = {};
    const moodColor = {};
    const defaultColors = ['#ff6b6b', '#6f8fd6', '#8aa6ff', '#33c6e7', '#8bd17c', '#62c370', '#e17055', '#fdcb6e', '#00b894'];

    rawIcons.forEach((icon, index) => {
        moodEmoji[icon.IconID] = icon.IconSymbol;
        moodColor[icon.IconID] = defaultColors[index % defaultColors.length];
    });

    /* ========= DOM ========= */
    const moods = document.querySelectorAll('.mood-btn');
    const dateEl = document.getElementById('entryDate');
    const noteEl = document.getElementById('note');
    const entriesEl = document.getElementById('entries');
    const canvas = document.getElementById('moodChart');
    const ctx = canvas.getContext('2d');
    const weekLabel = document.getElementById('weekLabel');
    const saveBtn = document.getElementById('saveBtn');
    const clearBtn = document.getElementById('clearBtn');
    const prevWeek = document.getElementById('prevWeek');
    const nextWeek = document.getElementById('nextWeek');

    /* ========= STATE ========= */
    let selected = document.querySelector('.mood-btn.active') || moods[0]; 
    let weekOffset = 0;
    let chartPoints = [];
    let tipEl = null;
    let globalData = [];

    dateEl.valueAsDate = new Date();

    /* ========= TOOLTIP ========= */
    function ensureTooltip() {
      if (tipEl) return;
      const wrap = canvas.parentElement;
      wrap.style.position = 'relative';
      tipEl = document.createElement('div');
      tipEl.className = 'chart-tip';
      wrap.appendChild(tipEl);
    }
    ensureTooltip();

    /* ========= EVENTS ========= */
    moods.forEach(btn => {
      btn.onclick = () => {
        moods.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selected = btn;
      };
    });

    // SỰ KIỆN LƯU DỮ LIỆU
    saveBtn.onclick = async () => {
      if (!selected) {
          alert("Please select a mood!");
          return;
      }
      const score = selected.dataset.score;
      const note = noteEl.value;
      const date = dateEl.value;

      const formData = new FormData();
      formData.append('action', 'save');
      formData.append('score', score);
      formData.append('note', note);
      formData.append('date', date);

      saveBtn.innerText = 'Saving...';
      saveBtn.disabled = true;

      try {
        const response = await fetch('AIBuddy_EmotionTracker.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
           noteEl.value = '';
           await fetchAndRender();
        } else {
           alert(result.message || 'Unknown error'); // Hiển thị thông báo lỗi (ví dụ: Upgrade required)
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Failed to connect to server.');
      } finally {
        saveBtn.innerText = 'Save';
        saveBtn.disabled = false;
      }
    };

    clearBtn.onclick = () => noteEl.value = '';
    prevWeek.onclick = () => { weekOffset--; drawChart(); };
    nextWeek.onclick = () => { weekOffset++; drawChart(); };

    /* ========= API FUNCTIONS ========= */
    async function fetchAndRender() {
        try {
            const response = await fetch('AIBuddy_EmotionTracker.php?action=load');
            globalData = await response.json();
            renderEntries(globalData);
            drawChart(); 
        } catch (error) {
            console.error("Cannot load data:", error);
            // entriesEl.innerHTML = '<p style="text-align:center; color:gray">Please log in to view history.</p>';
        }
    }

    /* ========= RENDER ENTRIES LIST ========= */
    function renderEntries(data) {
        if(!data || data.length === 0) {
            entriesEl.innerHTML = '<p style="text-align:center; color:gray">No data yet.</p>';
            return;
        }
        entriesEl.innerHTML = data.map(e => {
            const color = moodColor[e.score] || '#333';
            return `
            <div class="entry">
              <div class="entry-head">
                <span style="color:${color}">${e.mood}</span>
                <small>${e.date}</small>
              </div>
              <div style="white-space: pre-wrap;">${e.note || ''}</div>
            </div>
        `}).join('');
    }

    /* ========= CHART DRAWING (Giữ nguyên logic cũ) ========= */
    function drawChart() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      chartPoints = [];
      const data = globalData;

      const base = new Date();
      base.setDate(base.getDate() + weekOffset * 7);
      const currentDay = base.getDay(); 
      const distanceToMonday = (currentDay + 6) % 7;
      base.setDate(base.getDate() - distanceToMonday);

      const days = [...Array(7)].map((_, i) => {
        const d = new Date(base);
        d.setDate(base.getDate() + i);
        return d.toISOString().split('T')[0];
      });

      const start = new Date(days[0]);
      const end = new Date(days[6]);
      const now = new Date();
      const fmt = d => d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
      const nowStr = now.toISOString().split('T')[0];
      const isThisWeek = days.includes(nowStr);
      weekLabel.textContent = isThisWeek ? 'This week' : `${fmt(start)} – ${fmt(end)}`;

      const maxID = Math.max(...rawIcons.map(i => parseInt(i.IconID))) || 6;

      const info = days.map(day => {
        const items = data.filter(e => e.date === day);
        if (!items.length) return null;
        const avg = items.reduce((s, e) => s + e.score, 0) / items.length;
        return { avg, rep: items[0] }; 
      });

      const padX = 50;
      const padY = 40;
      const w = canvas.width - padX * 2;
      const h = canvas.height - padY * 2;

      const pts = info.map((d, i) => {
        const x = padX + (w / 6) * i;
        if (!d) return { x, y: null };
        const y = padY + h - ((d.avg - 1) / (maxID - 1)) * h; 
        return { x, y, v: d.avg, rep: d.rep, date: days[i] };
      });

      ctx.strokeStyle = 'rgba(180,190,195,.35)';
      ctx.lineWidth = 4;
      ctx.beginPath();
      pts.forEach((p, i) => {
        const y = p.y ?? padY + h * 0.5;
        if (i === 0) ctx.moveTo(p.x, y);
        else ctx.lineTo(p.x, y);
      });
      ctx.stroke();

      const valid = pts.filter(p => p.y !== null);
      if (valid.length < 2) {
          drawEmojis(valid);
          return;
      }

      ctx.lineWidth = 5;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';

      for (let i = 0; i < valid.length - 1; i++) {
        const p0 = valid[i - 1] || valid[i];
        const p1 = valid[i];
        const p2 = valid[i + 1];
        const p3 = valid[i + 2] || p2;

        const c1 = { x: p1.x + (p2.x - p0.x) / 6, y: p1.y + (p2.y - p0.y) / 6 };
        const c2 = { x: p2.x - (p3.x - p1.x) / 6, y: p2.y - (p3.y - p1.y) / 6 };

        const id1 = Math.round(p1.v);
        const id2 = Math.round(p2.v);
        const color1 = moodColor[id1] || '#999';
        const color2 = moodColor[id2] || '#999';

        const g = ctx.createLinearGradient(p1.x, p1.y, p2.x, p2.y);
        g.addColorStop(0, color1);
        g.addColorStop(1, color2);
        ctx.strokeStyle = g;

        ctx.beginPath();
        ctx.moveTo(p1.x, p1.y);
        ctx.bezierCurveTo(c1.x, c1.y, c2.x, c2.y, p2.x, p2.y);
        ctx.stroke();
      }

      drawEmojis(valid);
    }

    function drawEmojis(points) {
        points.forEach(p => {
            const s = Math.round(p.v);
            const size = 26; 
            const emoji = p.rep ? p.rep.symbol : (moodEmoji[s] || '❓');

            ctx.font = `${size}px Segoe UI Emoji`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(emoji, p.x, p.y);

            chartPoints.push({
              x: p.x,
              y: p.y,
              hitR: 18 * 18,
              emoji,
              moodText: p.rep ? p.rep.mood : '',
              note: p.rep ? (p.rep.note || "No note") : "",
              date: p.date
            });
        });
    }

    canvas.addEventListener('mousemove', e => {
      if (!tipEl) return;
      const r = canvas.getBoundingClientRect();
      const mx = (e.clientX - r.left) * (canvas.width / r.width);
      const my = (e.clientY - r.top) * (canvas.height / r.height);

      let hit = null;
      for (const p of chartPoints) {
        const dx = mx - p.x;
        const dy = my - p.y;
        if (dx * dx + dy * dy <= p.hitR) {
          hit = p;
          break;
        }
      }

      if (!hit) {
        tipEl.classList.remove('show');
        return;
      }

      tipEl.innerHTML = `
        <strong>${hit.emoji} ${hit.date}</strong>
        <div>${hit.note}</div>
      `;
      tipEl.style.left = Math.min(hit.x + 14, canvas.width - 260) + 'px';
      tipEl.style.top = Math.max(hit.y - 60, 8) + 'px';
      tipEl.classList.add('show');
    });

    canvas.addEventListener('mouseleave', () => {
      if (tipEl) tipEl.classList.remove('show');
    });

    fetchAndRender();
  </script>
</body>
</html>