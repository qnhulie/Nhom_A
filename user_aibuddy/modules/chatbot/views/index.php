<div class="chat-layout-container">
    
    <div class="sidebar-left" id="sidebar-left">
        <div class="new-chat-wrapper">
            <button class="new-chat-btn-styled" onclick="startNewChat()">
                <span class="icon-box"><i class="fa-solid fa-plus"></i></span>
                <span class="text">New Conversation</span>
            </button>
        </div>

        <div class="sidebar-section">
            <h4 class="sidebar-title">Personas</h4>
            <div id="persona-list-container" class="persona-grid">
                </div>
        </div>

        <div class="sidebar-section flex-grow">
            <h4 class="sidebar-title">History</h4>
            <ul id="history-list">
                </ul>
        </div>
    </div>

    <div class="chat-main">
        <div class="chat-window" id="chat-window"></div>

        <div id="image-preview-container">
            <img id="image-preview" src="" alt="Preview">
            <button onclick="clearImage()" class="close-preview-btn">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <div class="chat-input-area">
            <button class="input-btn" id="image-btn" onclick="document.getElementById('image-upload').click()">
                <i class="fa-regular fa-image"></i>
            </button>
            <input type="file" id="image-upload" accept="image/*" style="display: none;">

            <textarea id="message-input" rows="1" placeholder="Type a message..."></textarea>
            
            <button class="input-btn" id="call-btn" title="Call Mode">
                <i class="fa-solid fa-headset"></i>
            </button>
            
            <button class="input-btn send-btn" id="send-btn">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
        <div id="call-status"></div>
    </div>

    <div class="sidebar-right" id="sidebar-right">
        
        <div class="widget-box">
            <h4 class="sidebar-title"><i class="fa-solid fa-tags"></i> Topics</h4>
            <div class="topic-pills" id="topic-list-container">
                <span class="pill">Loading...</span>
            </div>
        </div>

        <div class="widget-box">
            <h4 class="sidebar-title">Voice Customization</h4>
            
            <div class="voice-settings-box">
                <div class="voice-header">
                    <label class="voice-label">
                        <i class="fa-solid fa-microphone-lines"></i> Select Voice
                    </label>
                </div>
                
                <div class="select-wrapper">
                    <select id="voice-select" class="voice-dropdown">
                        <option value="default">Default AI Buddy</option>
                        </select>
                </div>

                <div class="locked-voices-list">
                    <div class="locked-voice-item">
                        <span><i class="fa-solid fa-lock"></i> Celebrity Voice (Pro)</span>
                    </div>
                    <div class="locked-voice-item">
                        <span><i class="fa-solid fa-lock"></i> Emotional AI (Pro)</span>
                    </div>
                </div>
                
                <button class="test-voice-btn active" onclick="previewVoice()">
                    <i class="fa-solid fa-volume-high"></i> Test Voice
                </button>
            </div>
        </div>

        <div class="widget-box">
            <h4 class="sidebar-title">Relax Mode</h4>
            <div class="premium-unlock-card" onclick="window.location.href='AIBuddy_Trial.php'">
                <div class="premium-icon">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div class="premium-info">
                    <strong>Unlock Audio</strong>
                    <span>Exclusive soundscapes</span>
                </div>
                <i class="fa-solid fa-chevron-right arrow-icon"></i>
            </div>
        </div>

    </div>
</div>