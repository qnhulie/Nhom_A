<!DOCTYPE html>
<html lang="en"> 
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Buddy - Chatbot (MVC)</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="../../../public/css/chatbot.css">
</head>

<body>
  <header>
    <div class="container header-content">
      <div class="logo">
        <span class="logo-icon">🤖</span>
        AI Buddy
      </div>
      <nav>
        <a href="../../../AIBuddy_Homepage.php">Home</a>
        <a href="index.php" style="color:var(--accent);">Chatbot</a>
        <a href="../../../AIBuddy_EmotionTracker.php">Emotion Tracker</a>
        <a href="../../../AIBuddy_Trial.php">Trial</a>
        <a href="../../../AIBuddy_Profile.php">Profile</a>
        <a href="../../../AIBuddy_About.php">About</a>
        <a href="../../../AIBuddy_Contact.php">Contact</a>
      </nav>
      <div class="header-toggles">
        <button id="tools-toggle" class="mobile-toggle-btn" aria-label="Toggle tools">
          <i class="fa-solid fa-wand-magic-sparkles"></i>
        </button>
        <button id="menu-toggle" class="mobile-toggle-btn" aria-label="Toggle menu">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </div>
  </header>

  <div class="chat-layout-container">

    <aside class="sidebar-left" id="sidebar-left">
      <button class="new-chat-btn" onclick="startNewChat()">
        <i class="fa-solid fa-plus"></i> Start a new chat
      </button>

        <div class="persona-list" id="persona-list-container">
          <h4>Choose Persona</h4>
          <p style="text-align:center; color:#888;">Loading...</p> 
        </div>

        <div class="chat-history-list">
            <h4>Chat History</h4>
            <ul id="history-list">
              <li><p style="color:#888; font-size:0.9rem; padding:10px;">Loading history...</p></li>
            </ul>
        </div>
    </aside>

    <main class="chat-main">
      <div class="chat-window" id="chat-window">
        <div class="message msg-ai">
          <span class="ai-avatar">🤖</span>
          <p>Hi there! I'm AI Buddy. How are you feeling today?</p>
        </div>
      </div>

      <div id="image-preview-container" style="display: none; padding: 10px 20px; background: #fff; border-top: 1px solid #eee;">
          <div style="position: relative; display: inline-block;">
              <img id="image-preview" src="" style="max-height: 80px; border-radius: 8px; border: 1px solid #ddd;">
              <button onclick="clearImage()" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; cursor: pointer;">&times;</button>
          </div>
      </div>

      <div class="chat-input-area">
        <input type="file" id="image-upload" accept="image/*" style="display: none;">

        <button class="input-btn" title="Upload Image" onclick="document.getElementById('image-upload').click()">
          <i class="fa-solid fa-paperclip"></i>
        </button>
        
        <textarea id="message-input" placeholder="Type your message..." rows="1"></textarea>
        
        <button class="input-btn" id="call-btn" title="Click to speak" style="...">
          <i class="fa-solid fa-headset"></i>
        </button>
        
        <button class="input-btn" id="send-btn" title="Send">
          <i class="fa-solid fa-paper-plane"></i>
        </button>
      </div>
      
      <div id="call-status" style="text-align: center; font-size: 12px; margin-top: 5px; color: #666; height: 20px;">
        Tap the headset icon to speak
      </div>
    </main>

    <aside class="sidebar-right" id="sidebar-right">
      <div class="widget-box">
        <h4>Topic Suggestions</h4>
        <div class="topic-pills" id="topic-list-container">
            <span style="font-size:0.8rem; color:#888;">Loading topics...</span>
        </div>
      </div>

      <div class="widget-box">
        <h4><i class="fa-solid fa-brain"></i> Feeling stressed?</h4>
        <p>Try this 1-minute quick breathing exercise:</p>
        <div class="mini-breathing-circle">
          Breathe in...
        </div>
        <p style="margin-top:15px;">Or listen to a relaxation audio (Premium):</p>
        <a href="../../../AIBuddy_Trial.php" class="premium-audio-link">
          <i class="fa-solid fa-headphones"></i> Open Brainwave Audio
        </a>
      </div>
      
      <div class="widget-box">
        <h4><i class="fa-solid fa-volume-high"></i> Voice Guidance (Premium)</h4>
        <div class="voice-setting">
          <label for="voice-pack">Select Voice</label>
          <select id="voice-pack" disabled>
            <option>Female (Soft)</option>
            <option>Male (Warm)</option>
          </select>
        </div>
        <p style="font-size:0.9rem; text-align:center;">
          <a href="../../../Prototype_Focus.html">Upgrade to Premium</a> to unlock
        </p>
      </div>
    </aside>

  </div>

  <script src="../../../public/js/chatbot.js"></script>

</body>
</html>