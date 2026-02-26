<?php
// เริ่มระบบ Session เพื่อเก็บข้อมูลการล็อกอิน
session_start();
include 'db.php';

// --- ระบบตรวจรหัสผ่านแบบไม่โหลดหน้าใหม่ (AJAX Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_login'])) {
    header('Content-Type: application/json'); 
    
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $sql = "SELECT id, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['username'] = $username;
            echo json_encode(['status' => 'success', 'message' => 'เข้าสู่ระบบสำเร็จ! กำลังพาคุณเข้าสู่คลังข้อมูล...']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านไม่ถูกต้อง! กรุณลองใหม่อีกครั้ง']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบชื่อผู้ใช้นี้ในระบบ!']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoYoverse - ล็อกอิน</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai', sans-serif; }
        body, html { height: 100%; width: 100%; overflow: hidden; background-color: #000; }

        /* พื้นหลังสไลด์ (ทำงานทันที) */
        /* ปรับให้สว่างขึ้น */
        #bg-slider {
                    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
                    background-size: cover; background-position: center;
                    transition: opacity 1s ease-in-out; z-index: 1; 
                    filter: brightness(0.9); /* ปรับจาก 0.7 เป็น 0.9 (สว่างขึ้นมาก) */
                    }

        /* ปรับให้เลเยอร์บังจอมืดน้อยลง */
        #intro-overlay {
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0, 0, 0, 0.05); /* ปรับให้ใสขึ้นมากเหลือแค่ 5% */
                    display: flex; justify-content: center; align-items: center;
                    z-index: 9999; cursor: pointer;
                    transition: all 1s ease;
                        }
        .intro-content { text-align: center; }
        .intro-logo-img { 
            width: 250px; margin-bottom: 25px; 
            filter: drop-shadow(0 0 15px rgba(255,255,255,0.3));
            animation: pulse 2.5s infinite ease-in-out;
        }
        .click-text { 
            color: #ffffff; font-size: 14px; letter-spacing: 5px; 
            font-weight: 600; text-shadow: 0 0 10px rgba(0,0,0,0.5);
            animation: blink 2s infinite;
        }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        .fade-out { opacity: 0 !important; visibility: hidden !important; }

        /* --- กล่องล็อกอิน (ซ่อนไว้รอคลิก) --- */
        .login-container { 
            position: relative; z-index: 10; height: 100%; display: flex; 
            justify-content: center; align-items: center; padding: 20px; 
        }
        .login-box {
            background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 20px; padding: 50px 40px; 
            width: 100%; max-width: 420px; text-align: center;
            /* ตั้งค่าเริ่มต้นให้ซ่อน */
            opacity: 0; transform: translateY(30px);
            transition: all 1s cubic-bezier(0.2, 1, 0.3, 1);
            pointer-events: none; /* ป้องกันการกดโดนก่อนเปิด */
        }
        /* เมื่อคลิกแล้วจะโชว์กล่อง */
        .show-login { 
            opacity: 1 !important; 
            transform: translateY(0) !important; 
            pointer-events: auto !important; 
        }

        .login-logo { width: 220px; height: auto; display: block; margin: 0 auto 35px auto; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; color: #333333; font-weight: 600; font-size: 14px; margin-bottom: 8px; }
        .input-group input {
            width: 100%; padding: 14px 16px; background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(0, 0, 0, 0.15); border-radius: 8px; color: #111111; font-size: 16px; outline: none; transition: all 0.3s ease;
        }
        .input-group input:focus { border-color: #3fd3ff; box-shadow: 0 0 10px rgba(63, 211, 255, 0.3); background: #ffffff; }

        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px; } 
        .toggle-password { position: absolute; right: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .toggle-password svg { stroke: #888888; transition: stroke 0.3s ease; }
        .toggle-password:hover svg { stroke: #3fd3ff; }

        .btn-login {
            width: 100%; padding: 15px; background: linear-gradient(45deg, #3fd3ff, #9b59ff);
            color: #ffffff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: all 0.3s ease;
        }
        .btn-login:hover { background: linear-gradient(45deg, #5ce0ff, #ad7aff); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(92, 224, 255, 0.4); }

        .footer-links { margin-top: 25px; display: flex; justify-content: space-between; font-size: 14px; }
        .footer-links a { color: #555555; text-decoration: none; transition: 0.3s ease; font-weight: 600; }
        .footer-links a:hover { color: #3fd3ff; }

        .alert { padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; font-weight: 600; display: none; }
        .alert-error { background-color: #ffe6e6; color: #ff4d4d; border: 1px solid #ff4d4d; display: block; }
        .alert-success { background-color: #e6ffe6; color: #00cc44; border: 1px solid #00cc44; display: block; }
    </style>
</head>
<body>

    <div id="bg-slider"></div>

    <div id="intro-overlay" onclick="enterSite()">
    </div>

    <div class="login-container">
        <div id="loginBox" class="login-box">
            <img src="Honaki Impact 3/Logo Hoyoverse Black.png" alt="HoYoverse Logo" class="login-logo">
            <div id="alertMessage"></div>
            
            <form id="loginForm">
                <div class="input-group">
                    <label for="username">ชื่อผู้ใช้</label>
                    <input type="text" id="username" name="username" placeholder="กรอกชื่อผู้ใช้..." required autocomplete="off">
                </div>
                
                <div class="input-group">
                    <label for="password">รหัสผ่าน</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่าน..." required>
                        <span class="toggle-password" onclick="togglePassword()" title="แสดง/ซ่อนรหัสผ่าน">
                            <svg id="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eye-closed" style="display: none;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
            </form>

            <div class="footer-links">
                <a href="forgot_password.php">ลืมรหัสผ่าน?</a>
                <a href="register.php">ลงทะเบียนบัญชีใหม่</a>
            </div>
        </div>
    </div>

    <script>
        // --- 1. ฟังก์ชันเปิดหน้าล็อกอิน ---
        function enterSite() {
            const overlay = document.getElementById('intro-overlay');
            const loginBox = document.getElementById('loginBox');
            
            overlay.classList.add('fade-out'); // ซ่อนหน้าจอทางเข้า
            loginBox.classList.add('show-login'); // โชว์กล่องล็อกอินด้วยแอนิเมชัน
        }

        // --- 2. ระบบพื้นหลังสไลด์ (ทำงานอัตโนมัติตั้งแต่โหลดหน้า) ---
        const images = [
            'Honaki Impact 3/Honkai impact 3.png',
            'Genshin Impact/Genshin impact.jpg',
            'Honkai star rail/Home Honkai star rail.jpg',
            'Zenless Zone Zero/Zenless Zone Zero.jpg',
        ];
        let currentIndex = 0; 
        const bgSlider = document.getElementById('bg-slider');
        
        if (images.length > 0) {
            bgSlider.style.backgroundImage = `url('${images[0]}')`;
            function changeBackground() {
                currentIndex = (currentIndex + 1) % images.length;
                bgSlider.style.opacity = 0;
                setTimeout(() => { 
                    bgSlider.style.backgroundImage = `url('${images[currentIndex]}')`; 
                    bgSlider.style.opacity = 1; 
                }, 1000);
            }
            if (images.length > 1) { setInterval(changeBackground, 6000); }
        }

        // --- 3. สลับไอคอนดวงตา ---
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }

        // --- 4. ล็อกอิน AJAX ---
        const loginForm = document.getElementById('loginForm');
        const alertBox = document.getElementById('alertMessage');
        
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(loginForm);
            formData.append('ajax_login', '1'); 
            
            fetch('login.php', { method: 'POST', body: formData })
            .then(response => response.json()) 
            .then(data => {
                if (data.status === 'success') {
                    alertBox.innerHTML = `<div class='alert alert-success'>${data.message}</div>`;
                    setTimeout(() => { window.location.href = 'index.php'; }, 1500);
                } else {
                    alertBox.innerHTML = `<div class='alert alert-error'>${data.message}</div>`;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>