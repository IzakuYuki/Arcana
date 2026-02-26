<?php
// ดึงไฟล์เชื่อมต่อฐานข้อมูล
include 'db.php';

// --- ระบบลงทะเบียนแบบไม่โหลดหน้าใหม่ (AJAX Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_register'])) {
    header('Content-Type: application/json'); // ตอบกลับเป็น JSON ให้ JavaScript อ่าน

    // รับค่าและป้องกัน SQL Injection
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    // 1. เช็กว่ารหัสผ่านและการยืนยันรหัสผ่านตรงกันไหม
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านไม่ตรงกัน กรุณาลองใหม่อีกครั้ง']);
        exit();
    } 
    
    // 2. เช็กว่ามีชื่อผู้ใช้นี้ในระบบแล้วหรือยัง
    $check_sql = "SELECT id FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว กรุณาตั้งชื่ออื่น']);
        exit();
    } 
    
    // 3. ถ้าข้อมูลผ่านหมด ให้บันทึกลงฐานข้อมูล
    $insert_sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')";
    
    if ($conn->query($insert_sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'ลงทะเบียนสำเร็จ! กำลังพาคุณไปหน้าเข้าสู่ระบบ...']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
        exit();
    }
}
// --------------------------------------------------------
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoYoverse - ลงทะเบียนบัญชีใหม่</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai', sans-serif; }
        body, html { height: 100%; width: 100%; overflow: hidden; background-color: #000; }

        /* พื้นหลังวิดีโอ */
        #bg-video {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            object-fit: cover; z-index: 1; filter: brightness(0.85); 
        }

        .login-container { position: relative; z-index: 10; height: 100%; display: flex; justify-content: center; align-items: center; padding: 20px; }

        /* กล่อง Glassmorphism */
        .login-box {
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 16px; padding: 40px 40px;
            width: 100%; max-width: 420px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15); text-align: center;
        }

        .login-logo { width: 200px; height: auto; display: block; margin: 0 auto 25px auto; }

        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; color: #333333; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
        .input-group input {
            width: 100%; padding: 12px 16px; background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(0, 0, 0, 0.15); border-radius: 8px; color: #111111;
            font-size: 15px; outline: none; transition: all 0.3s ease;
        }
        .input-group input:focus { border-color: #3fd3ff; box-shadow: 0 0 10px rgba(63, 211, 255, 0.3); background: #ffffff; }

        /* ปุ่มดวงตาแบบ Roblox */
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px; } 
        .toggle-password {
            position: absolute; right: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .toggle-password svg { stroke: #888888; transition: stroke 0.3s ease; }
        .toggle-password:hover svg { stroke: #3fd3ff; }

        .btn-login {
            width: 100%; padding: 15px; background: linear-gradient(45deg, #3fd3ff, #9b59ff);
            color: #ffffff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;
            cursor: pointer; margin-top: 15px; transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(45deg, #5ce0ff, #ad7aff);
            transform: translateY(-2px); box-shadow: 0 5px 15px rgba(92, 224, 255, 0.4);
        }

        .footer-links { margin-top: 20px; font-size: 14px; font-weight: 600; }
        .footer-links a { color: #555; text-decoration: none; transition: 0.3s ease; }
        .footer-links a:hover { color: #3fd3ff; }

        /* กล่องข้อความแจ้งเตือน AJAX */
        .alert { padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; font-weight: 600; display: none; }
        .alert-error { background-color: #ffe6e6; color: #ff4d4d; border: 1px solid #ff4d4d; display: block; }
        .alert-success { background-color: #e6ffe6; color: #00cc44; border: 1px solid #00cc44; display: block; }
    </style>
</head>
<body>

    <video autoplay muted loop id="bg-video">
        <source src="Video/Imaginary Tree.mp4" type="video/mp4">
    </video>

    <div class="login-container">
        <div class="login-box">
            
            <img src="Honaki Impact 3/Logo Hoyoverse Black.png" alt="HoYoverse Logo" class="login-logo">
            
            <div id="alertMessage"></div>
            
            <form id="registerForm">
                <div class="input-group">
                    <label for="username">ชื่อผู้ใช้ใหม่</label>
                    <input type="text" id="username" name="username" placeholder="ตั้งชื่อผู้ใช้..." required autocomplete="off">
                </div>
                
                <div class="input-group">
                    <label for="password">รหัสผ่าน</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="ตั้งรหัสผ่าน..." required>
                        <span class="toggle-password" onclick="togglePassword('password', 'eye-open-1', 'eye-closed-1')" title="แสดง/ซ่อนรหัสผ่าน">
                            <svg id="eye-open-1" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eye-closed-1" style="display: none;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirm_password">ยืนยันรหัสผ่านอีกครั้ง</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="กรอกรหัสผ่านอีกครั้ง..." required>
                        <span class="toggle-password" onclick="togglePassword('confirm_password', 'eye-open-2', 'eye-closed-2')" title="แสดง/ซ่อนรหัสผ่าน">
                            <svg id="eye-open-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eye-closed-2" style="display: none;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">ลงทะเบียนบัญชี</button>
            </form>

            <div class="footer-links">
                <a href="login.php">มีบัญชีอยู่แล้ว? กลับไปหน้าเข้าสู่ระบบ</a>
            </div>
        </div>
    </div>

    <script>
        // 1. ฟังก์ชันสลับไอคอนตาเปิด/ปิด
        function togglePassword(inputId, openIconId, closedIconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeOpen = document.getElementById(openIconId);
            const eyeClosed = document.getElementById(closedIconId);
            
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

        // 2. ระบบลงทะเบียนแบบ AJAX (ป้องกันหน้าเว็บโหลดใหม่)
        const registerForm = document.getElementById('registerForm');
        const alertBox = document.getElementById('alertMessage');
        
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault(); // เบรก! ไม่ให้เว็บรีเฟรชหน้าต่างใหม่
            
            const formData = new FormData(registerForm);
            formData.append('ajax_register', '1'); // แนบข้อความไปบอก PHP ว่านี่คือ AJAX นะ
            
            fetch('register.php', { method: 'POST', body: formData })
            .then(response => response.json()) 
            .then(data => {
                if (data.status === 'success') {
                    // ถ้าสำเร็จ ขึ้นสีเขียว แล้วหน่วงเวลา 1.5 วิ ค่อยเด้งไปหน้า login
                    alertBox.innerHTML = `<div class='alert alert-success'>${data.message}</div>`;
                    setTimeout(() => { window.location.href = 'login.php'; }, 1500);
                } else {
                    // ถ้าพลาด (ตั้งชื่อซ้ำ, รหัสผ่านไม่ตรง) ขึ้นสีแดงเตือนให้แก้
                    alertBox.innerHTML = `<div class='alert alert-error'>${data.message}</div>`;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>