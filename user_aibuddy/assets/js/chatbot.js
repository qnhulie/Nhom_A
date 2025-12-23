/* public/js/chatbot.js */

// --- CONFIGURATION ---
let currentPersonaId = 1;  // Default: Bestie
let currentTopicId = 1;    
let currentSessionId = null;
let currentImageBase64 = null; // Biến lưu chuỗi ảnh

// Base path cho API
const API_BASE = 'api/chatbot/';

// Voice Config
let isCallActive = false; 
let recognition;
let synth = window.speechSynthesis;
let silenceTimer;
let voices = []; // Mảng chứa danh sách giọng

// --- INIT --- 
document.addEventListener('DOMContentLoaded', () => {
    setupUIToggles();
    loadChatHistory(); 
    setupEventListeners();
    setupVoiceFeatures();
    setupImageUpload();
    loadPersonas(); 
    loadTopics();
    
    // Kích hoạt load giọng
    populateVoiceList();
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = populateVoiceList;
    }

    clearImage();
});

function populateVoiceList() {
    voices = synth.getVoices();
    const voiceSelect = document.getElementById('voice-select');
    
    if(!voiceSelect || voices.length === 0) return;

    // Xóa các option cũ (trừ option đầu tiên Default)
    // Lưu ý: View HTML đã có 1 option value="default", ta giữ nó lại
    voiceSelect.innerHTML = '<option value="default">Default AI Buddy</option>';
    
    // Lọc lấy các giọng tiếng Anh để không bị rối
    const englishVoices = voices.filter(v => v.lang.includes('en'));
    const listToUse = englishVoices.length > 0 ? englishVoices : voices;

    listToUse.forEach((voice) => {
        const option = document.createElement('option');
        // Làm ngắn tên hiển thị cho gọn dropdown
        const shortName = voice.name.replace('Microsoft', '').replace('Google', '').replace('English', '').replace('United States', 'US').trim();
        
        option.textContent = shortName;
        option.setAttribute('data-name', voice.name); // Lưu tên gốc để tìm lại
        voiceSelect.appendChild(option);
    });
}

function previewVoice() {
    // Hàm test giọng
    const msg = "This is my voice. I am ready to help you.";
    speakText(msg, true);
}

function speakText(text, force = false) {
    if (!isCallActive && !force) return;
    if (synth.speaking) synth.cancel();

    // 1. Clean Text
    const cleanText = text
        .replace(/([\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF])/g, '')
        .replace(/[*#_`~]/g, '') 
        .trim();

    if (!cleanText) return; 

    const utterance = new SpeechSynthesisUtterance(cleanText);
    
    // 2. Logic Chọn Giọng (MỚI)
    const voiceSelect = document.getElementById('voice-select');
    if (voiceSelect && voiceSelect.value !== 'default') {
        const selectedOption = voiceSelect.selectedOptions[0];
        const voiceName = selectedOption.getAttribute('data-name');
        const selectedVoice = voices.find(v => v.name === voiceName);
        if (selectedVoice) utterance.voice = selectedVoice;
    } else {
        // Fallback về giọng Google/Zira nếu để Default
        const preferredVoice = voices.find(v => v.name.includes("Google US English") || v.name.includes("Zira"));
        if (preferredVoice) utterance.voice = preferredVoice;
    }

    // 3. Sentiment & Rate (Giữ nguyên)
    const sentiment = analyzeSentiment(text);
    utterance.pitch = sentiment.pitch;
    utterance.rate = sentiment.rate;

    utterance.onstart = function() {
        const btn = document.getElementById('call-btn');
        if(btn) btn.classList.add('ai-speaking');
    };

    utterance.onend = function() {
        const btn = document.getElementById('call-btn');
        if(btn) btn.classList.remove('ai-speaking');
        if (isCallActive && !force) {
            setTimeout(() => { try { recognition.start(); } catch(e) {} }, 500); 
        }
    };

    synth.speak(utterance);
}

function previewVoice() {
    const msg = "This is a preview of my voice.";
    speakText(msg, true); // true = force speak even if not in call mode
}

function setupUIToggles() {
    const menuToggle = document.getElementById('menu-toggle');
    const toolsToggle = document.getElementById('tools-toggle');
    const sidebarLeft = document.getElementById('sidebar-left');
    const sidebarRight = document.getElementById('sidebar-right');
    
    // Nút mở lịch sử (Trái)
    if(menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebarLeft.classList.toggle('is-open');
            // Đóng bên phải nếu đang mở để đỡ rối
            if(sidebarRight) sidebarRight.classList.remove('is-open');
        });
    }
    
    // Nút mở Persona/Topic (Phải)
    if(toolsToggle) {
        toolsToggle.addEventListener('click', () => {
            sidebarRight.classList.toggle('is-open');
            if(sidebarLeft) sidebarLeft.classList.remove('is-open');
        });
    }
}

// --- ADVANCED VOICE FUNCTIONS (FIXED) ---

function setupVoiceFeatures() {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      recognition = new SpeechRecognition();
      recognition.lang = 'en-US';
      recognition.interimResults = false;
      recognition.maxAlternatives = 1;

      // Khi bắt đầu nghe
      recognition.onstart = function() {
        const btn = document.getElementById('call-btn');
        if(btn) {
            btn.classList.add('mic-active');
            btn.innerHTML = '<i class="fa-solid fa-microphone-lines"></i>'; // Icon sóng âm
        }
        document.getElementById('message-input').placeholder = "Listening...";
        
        // Tự động ngắt nếu im lặng quá 5s
        clearTimeout(silenceTimer);
        silenceTimer = setTimeout(() => {
            if(isCallActive) recognition.stop();
        }, 5000);
      };

      // Khi kết thúc nghe (User ngừng nói hoặc timeout)
      recognition.onend = function() {
        // Nếu đang trong cuộc gọi mà chưa nhận được kết quả, bật lại mic (keep alive)
        // Tuy nhiên logic chính sẽ nằm ở onresult
        const btn = document.getElementById('call-btn');
        if (!isCallActive && btn) {
            btn.classList.remove('mic-active');
            btn.innerHTML = '<i class="fa-solid fa-headset"></i>';
            document.getElementById('message-input').placeholder = "Type your message...";
        }
      };

      // Khi nhận diện được giọng nói
      recognition.onresult = function(event) {
        clearTimeout(silenceTimer);
        const transcript = event.results[0][0].transcript;
        document.getElementById('message-input').value = transcript;
        
        // Tự động gửi tin nhắn
        sendMessage(); 
      };

      recognition.onerror = function(event) {
        console.error("Speech Error:", event.error);
        if (event.error === 'no-speech' && isCallActive) {
            // Nếu không nghe thấy gì trong chế độ gọi, thử lại
            // recognition.start(); // Cẩn thận loop vô hạn
        }
      };
    } else {
        alert("Your browser does not support voice features.");
    }
}

// Hàm Bật/Tắt chế độ gọi điện
function toggleCallMode() {
    const btn = document.getElementById('call-btn');
    const statusDiv = document.getElementById('call-status');

    if (!isCallActive) {
        // BẮT ĐẦU GỌI
        isCallActive = true;
        btn.style.backgroundColor = '#ff4b4b'; // Màu đỏ để báo hiệu đang gọi/ngắt
        btn.style.color = 'white';
        statusDiv.innerText = "Call Active - Listening...";
        try { recognition.start(); } catch (e) {}
    } else {
        // KẾT THÚC GỌI (HANG UP)
        isCallActive = false;
        if (synth.speaking) synth.cancel();
        try { recognition.stop(); } catch (e) {}
        
        btn.style.backgroundColor = '#f0f0f0';
        btn.style.color = 'var(--primary)';
        btn.classList.remove('mic-active');
        btn.innerHTML = '<i class="fa-solid fa-headset"></i>';
        statusDiv.innerText = "Tap the headset icon to speak";
    }
}

// Hàm xử lý Text-to-Speech thông minh (Cảm xúc & Lọc Emoji)
function speakText(text) {
    if (synth.speaking) synth.cancel();

    // 1. Phân tích cảm xúc dựa trên Emoji TRƯỚC KHI lọc
    const sentiment = analyzeSentiment(text);

    // 2. Lọc bỏ Emoji để không đọc "Sparkles", "Rocket"
    // Regex này loại bỏ hầu hết emoji và ký tự đặc biệt markdown
    const cleanText = text
        .replace(/([\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF])/g, '')
        .replace(/[*#_`~]/g, '') // Loại bỏ Markdown
        .trim();

    if (!cleanText) return; // Không có gì để đọc

    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'en-US'; 
    
    // 3. Áp dụng cấu hình giọng nói theo cảm xúc
    utterance.pitch = sentiment.pitch;
    utterance.rate = sentiment.rate;

    // Chọn giọng Google US English hoặc Microsoft Zira (Nữ)
    const voices = synth.getVoices();
    const preferredVoice = voices.find(v => v.name.includes("Google US English") || v.name.includes("Zira"));
    if (preferredVoice) utterance.voice = preferredVoice;

    // 4. Xử lý sự kiện khi AI ĐỌC XONG (Quan trọng cho cuộc gọi)
    utterance.onstart = function() {
        // Có thể thêm animation AI đang nói ở đây
        const btn = document.getElementById('call-btn');
        if(btn) btn.classList.add('ai-speaking');
    };

    utterance.onend = function() {
        const btn = document.getElementById('call-btn');
        if(btn) btn.classList.remove('ai-speaking');

        // [LOGIC QUAN TRỌNG] Nếu đang trong chế độ gọi, tự động bật mic lại
        if (isCallActive) {
            setTimeout(() => {
                try { recognition.start(); } catch(e) {}
            }, 500); // Nghỉ 0.5s rồi nghe tiếp
        }
    };

    // MỚI: Lấy giọng từ Dropdown
    const voiceSelect = document.getElementById('voice-select');
    const selectedOption = voiceSelect.selectedOptions[0];
    if(selectedOption) {
        const selectedName = selectedOption.getAttribute('data-name');
        const selectedVoice = voices.find(v => v.name === selectedName);
        if(selectedVoice) utterance.voice = selectedVoice;

    }           
    synth.speak(utterance);
}

// Hàm phân tích cảm xúc đơn giản từ Emoji
function analyzeSentiment(text) {
    let pitch = 1.0;
    let rate = 1.0;

    // Nhóm Vui / Hào hứng / High Energy
    if (/([😆😂🤣😄😃😁🤩😍🥰🚀🔥✨🎉💖💯])/.test(text)) {
        pitch = 1.15; // Cao hơn chút
        rate = 1.1;   // Nhanh hơn chút
    } 
    // Nhóm Buồn / Nghiêm túc / Low Energy
    else if (/([😢😭😞😔😟😕💔🥀😓😰])/.test(text)) {
        pitch = 0.85; // Trầm xuống
        rate = 0.9;   // Chậm lại
    }
    // Nhóm Bình tĩnh / Thư giãn (Therapist)
    else if (/([😌🧘‍♀️🌿☕🧠])/.test(text)) {
        pitch = 0.95; 
        rate = 0.95;
    }

    return { pitch, rate };
}

// --- IMAGE HANDLING FUNCTIONS (MỚI) ---
function setupImageUpload() {
    const fileInput = document.getElementById('image-upload');
    
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                currentImageBase64 = e.target.result; // Lưu chuỗi Base64
                // Hiển thị preview
                document.getElementById('image-preview').src = currentImageBase64;
                document.getElementById('image-preview-container').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}

function clearImage() {
    currentImageBase64 = null;
    document.getElementById('image-upload').value = ''; // Reset input
    document.getElementById('image-preview-container').style.display = 'none';
}

// --- CORE LOGIC ---

async function sendMessage() {
    const inputField = document.getElementById('message-input');
    const messageText = inputField.value.trim();
    if (!messageText && !currentImageBase64) return;

    // Hiển thị tin nhắn của User (Kèm ảnh nếu có)
    let userContent = messageText;
    if (currentImageBase64) {
        userContent += `<br><img src="${currentImageBase64}" style="max-width: 200px; border-radius: 8px; margin-top: 5px;">`;
    }
    appendMessage('user', userContent);

    // Reset input và ảnh
    inputField.value = ''; 
    inputField.style.height = 'auto';
    
    // Lưu lại base64 để gửi rồi clear biến global
    const imageToSend = currentImageBase64;
    clearImage(); // Xóa preview ngay sau khi gửi
    
    scrollToBottom();

    const loadingId = showTypingIndicator();

    try {
        const response = await fetch(API_BASE + 'send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: CURRENT_USER_ID,
                persona_id: currentPersonaId,
                topic_id: currentTopicId,
                message: messageText, // Có thể rỗng nếu chỉ gửi ảnh
                image: imageToSend,   // <--- GỬI THÊM TRƯỜNG NÀY
                session_id: currentSessionId 
            })
        });

        const data = await response.json();
        removeTypingIndicator(loadingId);

        if (response.ok && data.status === 200) {
            if (data.data) {
                if (!currentSessionId && data.data.session_id) {
                    currentSessionId = data.data.session_id;
                    loadChatHistory();
                }

                const aiResponse = data.data.response;
                appendMessage('ai', aiResponse, false);
                
                // Tự động đọc nếu đang gọi HOẶC nếu bạn muốn luôn đọc tin nhắn mới
                // Ở đây ta ưu tiên logic Call Mode
                if (isCallActive) {
                    speakText(aiResponse);
                }
            }
        } else {
            const errorMsg = data.message || "Unknown error";
            appendMessage('ai', `⚠️ ${errorMsg}`, true);
        }

    } catch (error) {
        removeTypingIndicator(loadingId);
        appendMessage('ai', `⚠️ Connection Error: ${error.message}`, true);
    }
    scrollToBottom();
}

// --- API & UI HELPERS (Giữ nguyên các phần khác) ---

function setupEventListeners() {
    document.getElementById('send-btn').addEventListener('click', sendMessage);
    
    const callBtn = document.getElementById('call-btn');
    if(callBtn) {
        // Logic mới: Bấm 1 lần để Bật/Tắt chế độ gọi rảnh tay
        callBtn.addEventListener('click', toggleCallMode);
    }

    document.getElementById('message-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault(); 
            sendMessage();
        }
    });
}

function startNewChat() {
    currentSessionId = null;
    document.getElementById('chat-window').innerHTML = ''; 
    appendMessage('ai', "Hi there! I'm ready to listen. Pick a topic or just start chatting.", false);
    if (synth.speaking) synth.cancel();
}

async function selectTopic(element) {
    const topicId = element.getAttribute('data-id');
    currentTopicId = topicId;
    
    document.querySelectorAll('.topic-pills .pill').forEach(el => el.style.backgroundColor = 'var(--white)');
    element.style.backgroundColor = 'var(--light)'; 
    
    document.getElementById('chat-window').innerHTML = '';
    const loadingId = showTypingIndicator();

    try {
        const response = await fetch(API_BASE + 'init.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: CURRENT_USER_ID,
                persona_id: currentPersonaId,
                topic_id: topicId
            })
        });

        const data = await response.json();
        removeTypingIndicator(loadingId);

        if (response.status === 200) {
            currentSessionId = data.data.session_id;
            appendMessage('ai', data.data.response, false);
            loadChatHistory();
            if(isCallActive) speakText(data.data.response); // Đọc lời chào nếu đang gọi
        } else {
            appendMessage('ai', `⚠️ Error: ${data.message}`, true);
        }
    } catch (error) {
        removeTypingIndicator(loadingId);
        appendMessage('ai', `⚠️ Connection Error`, true);
    }
}

async function selectPersona(element) {
    document.querySelectorAll('.persona-card').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    
    const newPersonaId = element.getAttribute('data-id');
    currentPersonaId = newPersonaId;
    
    const personaName = element.querySelector('strong').innerText;
    appendSystemMessage(`Switched to <b>${personaName}</b>.`);
}

function loadChatHistory() {
    const listContainer = document.getElementById('history-list');
    fetch(`${API_BASE}history.php?user_id=${CURRENT_USER_ID}`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 200 && data.data) {
            listContainer.innerHTML = ''; 
            data.data.forEach(session => {
                const li = document.createElement('li');
                if (currentSessionId == session.SessionID) li.style.backgroundColor = '#eef';
                const titleSafe = session.Title ? session.Title.replace(/'/g, "\\'") : 'Untitled';
                li.innerHTML = `
                    <div class="chat-link" onclick="loadSession(${session.SessionID})">
                        ${session.Title || 'Untitled Chat'}
                    </div>
                    <div class="chat-actions">
                        <button class="action-btn" onclick="renameChat(${session.SessionID}, '${titleSafe}')"><i class="fa-solid fa-pen"></i></button>
                        <button class="action-btn delete" onclick="deleteChat(${session.SessionID})"><i class="fa-solid fa-trash"></i></button>
                    </div>
                `;
                listContainer.appendChild(li);
            });
        }
    });
}

async function loadSession(sessionId) {
    currentSessionId = sessionId;
    const chatWindow = document.getElementById('chat-window');
    chatWindow.innerHTML = '<p style="text-align:center; color:#888; margin-top:20px;">Loading conversation...</p>';
    
    try {
        const response = await fetch(`${API_BASE}messages.php?user_id=${CURRENT_USER_ID}&session_id=${sessionId}`);
        const data = await response.json();
        
        if (data.status === 200) {
            chatWindow.innerHTML = ''; 
            data.data.messages.forEach(msg => {
                const sender = msg.Sender === 'User' ? 'user' : 'ai';
                appendMessage(sender, msg.Content, false);
            });
            scrollToBottom();
            loadChatHistory();
        }
    } catch (error) {}
}

async function deleteChat(sessionId) {
    if (!confirm("Delete this chat?")) return;
    await fetch(API_BASE + 'session.php', { 
        method: 'DELETE', 
        body: JSON.stringify({ user_id: CURRENT_USER_ID, session_id: sessionId }) 
    });
    if (currentSessionId === sessionId) startNewChat(); 
    else loadChatHistory();
}

async function renameChat(sessionId, oldTitle) {
    const newTitle = prompt("New title:", oldTitle);
    if (newTitle && newTitle !== oldTitle) {
        await fetch(API_BASE + 'session.php', { 
            method: 'PUT', 
            body: JSON.stringify({ user_id: CURRENT_USER_ID, session_id: sessionId, title: newTitle }) 
        });
        loadChatHistory();
    }
}

function appendMessage(sender, text, isError) {
    const chatWindow = document.getElementById('chat-window');
    const div = document.createElement('div');
    if (sender === 'user') {
        div.className = 'message msg-user';
        div.innerHTML = `<p>${text}</p>`;
    } else {
        div.className = 'message msg-ai';
        if (isError) div.style.borderColor = 'red';
        div.innerHTML = `<span class="ai-avatar">${isError ? '⚠️' : '🤖'}</span><p>${text}</p>`;
    }
    chatWindow.appendChild(div);
}

function appendSystemMessage(htmlText) {
    const chatWindow = document.getElementById('chat-window');
    const div = document.createElement('div');
    div.style.textAlign = 'center'; div.style.fontSize = '0.85rem'; div.style.color = '#888'; div.style.margin = '10px 0';
    div.innerHTML = htmlText;
    chatWindow.appendChild(div);
    scrollToBottom();
}

function showTypingIndicator() {
    const cw = document.getElementById('chat-window'); const id = 'l-'+Date.now();
    const d = document.createElement('div'); d.id=id; d.className='message msg-ai'; d.innerHTML='<span class="ai-avatar">🤖</span><p><i>Thinking...</i></p>';
    cw.appendChild(d); scrollToBottom(); return id;
}
function removeTypingIndicator(id) { const el=document.getElementById(id); if(el) el.remove(); }
function scrollToBottom() { const cw = document.getElementById('chat-window'); cw.scrollTop = cw.scrollHeight; }

// --- DYNAMIC DATA LOADING ---

function loadPersonas() {
    fetch(API_BASE + 'get_personas.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 200) {
                const container = document.getElementById('persona-list-container');
                container.innerHTML = ''; 
                
                data.data.forEach(p => {
                    const isActive = (p.PersonaID == currentPersonaId) ? 'active' : '';
                    
                    // --- XỬ LÝ UI KHÓA ---
                    let clickAttr = `onclick="selectPersona(this)"`;
                    let lockClass = '';
                    let badgeHtml = '';

                    // Nếu bị khóa
                    if (p.is_locked) {
                        clickAttr = `onclick="showUpgradeAlert('${p.PersonaName}')"`;
                        lockClass = 'locked-persona'; // Class CSS làm mờ
                        badgeHtml = `<i class="fa-solid fa-lock lock-icon"></i>`;
                    } 
                    // Nếu là Premium nhưng đã mở khóa (User VIP)
                    else if (p.IsPremium == 1) {
                         badgeHtml = `<i class="fa-solid fa-crown premium-icon"></i>`;
                    }
                    
                    const html = `
                        <div class="persona-card ${isActive} ${lockClass}" 
                             data-id="${p.PersonaID}" 
                             ${clickAttr}>
                             
                            <span class="icon">${p.Icon}</span>
                            <div class="info">
                                <strong>${p.PersonaName}</strong>
                                <span>${p.Description}</span>
                            </div>
                            ${badgeHtml}
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
                
                // MỞ RỘNG: Dùng data.user_plan để ẩn/hiện các tính năng khác
                updateUIBasedOnPlan(data.user_plan);
            }
        })
        .catch(err => {
            console.error("Load Personas Error:", err);
            // Fallback nếu lỗi JSON (để không trắng trang)
             document.getElementById('persona-list-container').innerHTML = '<p style="color:red; font-size:0.8rem">Error loading personas</p>';
        });
}

// Hàm cảnh báo nâng cấp
function showUpgradeAlert(name) {
    if(confirm(`🔒 ${name} is locked.\nUpgrade to Essential or Premium plan to unlock this persona!`)) {
        window.location.href = 'AIBuddy_Trial.php'; // Chuyển hướng trang mua gói
    }
}

// Hàm phụ trợ cập nhật các UI khác (Voice, v.v.)
function updateUIBasedOnPlan(planId) {
    const voiceBox = document.querySelector('.voice-settings-box');
    const premiumBadge = document.querySelector('.badge-premium');
    
    // Nếu là gói Free (1) -> Khóa Voice nâng cao
    if (planId <= 1) {
        if(voiceBox) voiceBox.classList.add('disabled-box');
        if(premiumBadge) premiumBadge.innerText = "PRO";
    } else {
        // Nếu đã mua gói -> Mở khóa Voice
        if(voiceBox) {
            voiceBox.classList.remove('disabled-box');
            // Enable dropdown
            const select = document.getElementById('voice-select');
            const btn = document.querySelector('.test-voice-btn');
            if(select) select.disabled = false;
            if(btn) btn.disabled = false;
        }
        if(premiumBadge) premiumBadge.innerText = "UNLOCKED";
    }
}

function loadTopics() {
    fetch(API_BASE + 'get_topics.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 200) {
                const container = document.getElementById('topic-list-container');
                container.innerHTML = ''; // Xóa chữ loading
                
                data.data.forEach(t => {
                    const html = `
                        <span class="pill" data-id="${t.TopicID}" onclick="selectTopic(this)">
                            ${t.TopicName}
                        </span>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            }
        })
        .catch(err => console.error("Load Topics Error:", err));
}