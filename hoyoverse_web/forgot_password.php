<?php
include 'db.php';

// --- ระบบ AJAX จัดการการลืมรหัสผ่าน ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];

    // ขั้นตอนที่ 1: ตรวจสอบว่ามีชื่อผู้ใช้นี้ในระบบไหม
    if ($action == 'check_user') {
        $username = $conn->real_escape_string($_POST['username']);
        $sql = "SELECT id FROM users WHERE username = '$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'พบชื่อผู้ใช้นี้ในระบบ กรุณาตั้งรหัสผ่านใหม่ด้านล่าง']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบชื่อผู้ใช้นี้ในระบบ! กรุณาตรวจสอบอีกครั้ง']);
        }
        exit();
    }

    // ขั้นตอนที่ 2: อัปเดตรหัสผ่านใหม่ลงฐานข้อมูล
    if ($action == 'reset_password') {
        $username = $conn->real_escape_string($_POST['username']);
        $new_password = $conn->real_escape_string($_POST['new_password']);
        $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

        if ($new_password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านใหม่ไม่ตรงกัน! กรุณาลองอีกครั้ง']);
            exit();
        }

        $update_sql = "UPDATE users SET password = '$new_password' WHERE username = '$username'";
        if ($conn->query($update_sql) === TRUE) {
            echo json_encode(['status' => 'success', 'message' => 'รีเซ็ตรหัสผ่านสำเร็จ! กำลังพากลับไปหน้าเข้าสู่ระบบ...']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoYoverse - ลืมรหัสผ่าน</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai', sans-serif; }
        body, html { height: 100%; width: 100%; overflow: hidden; background-color: #000; }

        /* =========================================
           CSS สำหรับวิดีโอพื้นหลัง (Video Background)
           ========================================= */
        #bg-video {
            position: fixed; 
            top: 0;
            left: 0;
            width: 100vw; 
            height: 100vh; 
            object-fit: cover; 
            z-index: 1;
            filter: brightness(0.85); 
        }

        .login-container { position: relative; z-index: 10; height: 100%; display: flex; justify-content: center; align-items: center; padding: 20px; }
        
        /* กล่อง Glassmorphism */
        .login-box {
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 16px; padding: 40px; width: 100%; max-width: 420px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15); text-align: center;
        }

        .login-logo { width: 180px; height: auto; display: block; margin: 0 auto 20px auto; }
        
        .title-text { color: #333; font-size: 20px; font-weight: 600; margin-bottom: 20px; }

        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; color: #333333; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
        .input-group input {
            width: 100%; padding: 12px 16px; background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(0, 0, 0, 0.15); border-radius: 8px; color: #111111; font-size: 15px; outline: none; transition: all 0.3s ease;
        }
        .input-group input:focus { border-color: #3fd3ff; box-shadow: 0 0 10px rgba(63, 211, 255, 0.3); background: #ffffff; }

        /* ปุ่มดวงตาแบบ Roblox */
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

        .footer-links { margin-top: 20px; font-size: 14px; font-weight: 600; display: flex; justify-content: center;}
        .footer-links a { color: #555; text-decoration: none; transition: 0.3s ease; }
        .footer-links a:hover { color: #3fd3ff; }

        .alert { padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; font-weight: 600; display: none; }
        .alert-error { background-color: #ffe6e6; color: #ff4d4d; border: 1px solid #ff4d4d; display: block; }
        .alert-success { background-color: #e6ffe6; color: #00cc44; border: 1px solid #00cc44; display: block; }

        /* ซ่อน Step 2 ไว้ก่อน */
        #step-2 { display: none; }
    </style>
</head>
<body>

    <video autoplay muted loop id="bg-video">
        <source src="Video/Quantum Sea.mp4" type="video/mp4">
    </video>

    <div class="login-container">
        <div class="login-box">
            <img src="Honaki Impact 3/Logo Hoyoverse Black.png" alt="HoYoverse Logo" class="login-logo">
            <div class="title-text">รีเซ็ตรหัสผ่าน</div>
            
            <div id="alertMessage"></div>
            
            <form id="checkUserForm">
                <div class="input-group">
                    <label for="username">ชื่อผู้ใช้บัญชีของคุณ</label>
                    <input type="text" id="username" name="username" placeholder="กรอกชื่อผู้ใช้..." required autocomplete="off">
                </div>
                <button type="submit" class="btn-login">ค้นหาบัญชี</button>
            </form>

            <form id="resetPasswordForm" id="step-2">
                <input type="hidden" id="verified_username" name="username"> <div class="input-group">
                    <label for="new_password">ตั้งรหัสผ่านใหม่</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" placeholder="รหัสผ่านใหม่..." required>
                        <span class="toggle-password" onclick="togglePassword('new_password', 'eye-open-1', 'eye-closed-1')">
                            <svg id="eye-open-1" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eye-closed-1" style="display: none;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง..." required>
                        <span class="toggle-password" onclick="togglePassword('confirm_password', 'eye-open-2', 'eye-closed-2')">
                            <svg id="eye-open-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eye-closed-2" style="display: none;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">บันทึกรหัสผ่านใหม่</button>
            </form>

            <div class="footer-links">
                <a href="login.php">จำรหัสผ่านได้แล้ว? กลับไปเข้าสู่ระบบ</a>
            </div>
        </div>
    </div>

    <script>
        // ระบบสลับไอคอนตาเปิด/ปิด
        function togglePassword(inputId, openIconId, closedIconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeOpen = document.getElementById(openIconId);
            const eyeClosed = document.getElementById(closedIconId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text'; eyeOpen.style.display = 'none'; eyeClosed.style.display = 'block'; 
            } else {
                passwordInput.type = 'password'; eyeOpen.style.display = 'block'; eyeClosed.style.display = 'none'; 
            }
        }

        const checkUserForm = document.getElementById('checkUserForm');
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const alertBox = document.getElementById('alertMessage');
        const verifiedUsernameInput = document.getElementById('verified_username');

        // ตั้งค่าเริ่มต้นให้ซ่อนฟอร์มที่ 2 (ตั้งรหัสผ่าน)
        resetPasswordForm.style.display = 'none';

        // 1. ส่งข้อมูลเช็กชื่อผู้ใช้
        checkUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(checkUserForm);
            formData.append('action', 'check_user');

            fetch('forgot_password.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alertBox.innerHTML = `<div class='alert alert-success'>${data.message}</div>`;
                    // เก็บชื่อผู้ใช้ลงในฟอร์มที่ 2
                    verifiedUsernameInput.value = document.getElementById('username').value;
                    // ซ่อนฟอร์ม 1 โชว์ฟอร์ม 2
                    checkUserForm.style.display = 'none';
                    resetPasswordForm.style.display = 'block';
                } else {
                    alertBox.innerHTML = `<div class='alert alert-error'>${data.message}</div>`;
                }
            }).catch(err => console.error(err));
        });

        // 2. ส่งข้อมูลรีเซ็ตรหัสผ่าน
        resetPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(resetPasswordForm);
            formData.append('action', 'reset_password');

            fetch('forgot_password.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alertBox.innerHTML = `<div class='alert alert-success'>${data.message}</div>`;
                    setTimeout(() => { window.location.href = 'login.php'; }, 1500);
                } else {
                    alertBox.innerHTML = `<div class='alert alert-error'>${data.message}</div>`;
                }
            }).catch(err => console.error(err));
        });
    </script>
</body>
</html>