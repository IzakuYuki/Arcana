<?php
// เริ่มระบบ Session เพื่อเช็กสถานะการล็อกอิน
session_start();

// ถ้ายังไม่ได้ล็อกอิน ให้เด้งไปหน้า login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// ดึงสถานะสิทธิ์ของผู้ใช้
$role = $_SESSION['role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoYoverse - Home Dashboard</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');

        /* ตั้งค่าหน้าจอไม่ให้มี Scroll */
        html, body { 
            height: 100vh; 
            margin: 0; 
            padding: 0; 
            overflow: hidden; 
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai', sans-serif; }
        
        body {
            background: #000;
            color: #ffffff;
            display: flex;
            flex-direction: column;
        }

        /* --- วิดีโอพื้นหลัง (Video Background) --- */
        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            z-index: -1;
            filter: brightness(0.4) contrast(1.1);
        }

        /* --- Navigation Bar (Dark Glassmorphism) --- */
        nav {
            height: 70px;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 0 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(63, 211, 255, 0.3);
            flex-shrink: 0;
        }

        .nav-logo img { height: 35px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info p { font-size: 14px; color: #bbb; margin: 0; }
        .user-info span { font-weight: 600; color: #3fd3ff; }
        
        /* ปุ่มแบบมีกรอบ (Framed Button) */
        .btn-frame {
            padding: 8px 22px;
            background: rgba(63, 211, 255, 0.1);
            border: 2px solid #3fd3ff;
            color: #3fd3ff;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-frame:hover {
            background: #3fd3ff;
            color: #000;
            box-shadow: 0 0 15px #3fd3ff;
            transform: translateY(-2px);
        }

        /* ปุ่มโปรไฟล์ (สีชมพูนีออน) */
        .btn-profile {
            border-color: #ff3399;
            color: #ff3399;
            background: rgba(255, 51, 153, 0.1);
        }
        .btn-profile:hover {
            background: #ff3399;
            color: #fff;
            box-shadow: 0 0 20px #ff3399;
        }

        .btn-logout {
            border-color: #ff4d4d;
            color: #ff4d4d;
        }
        .btn-logout:hover {
            background: #ff4d4d;
            color: white;
            box-shadow: 0 0 15px rgba(255, 77, 77, 0.4);
        }

        /* --- Main Content --- */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
        }

        .main-content h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
            letter-spacing: 1px;
            text-shadow: 0 0 15px rgba(63, 211, 255, 0.5);
        }

        /* --- Gallery Container (Expanding Cards) --- */
        .gallery-container {
            display: flex;
            width: 95%; 
            max-width: 1750px;
            height: calc(100vh - 230px); 
            gap: 15px;
            margin: 0 auto;
        }

        .game-card {
            flex: 1;
            position: relative;
            overflow: hidden;
            border-radius: 35px;
            cursor: pointer;
            transition: all 0.7s cubic-bezier(0.25, 1, 0.3, 1);
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
            border: 3px solid rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }

        .game-card:hover {
            flex: 10; 
        }

        .game-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .game-card:hover img {
            transform: scale(1.05);
        }

        .game-card .content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 40px;
            background: linear-gradient(transparent, rgba(0,0,0,0.95));
            color: white;
            text-align: left;
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .game-card:hover .content {
            opacity: 1;
            transition-delay: 0.3s;
        }

        .game-card h2 {
            font-size: 28px;
            font-weight: 600;
            transform: translateY(20px);
            transition: transform 0.5s ease;
        }

        .game-card:hover h2 {
            transform: translateY(0);
        }

        /* ขอบเรืองแสงเฉพาะตัวของแต่ละเกม */
        .card-mihoyo:hover { border-color: #00e5ff; box-shadow: 0 0 30px rgba(0, 229, 255, 0.5); }
        .card-hi3:hover { border-color: #ff3399; box-shadow: 0 0 30px rgba(255, 51, 153, 0.5); }
        .card-genshin:hover { border-color: #ffcc00; box-shadow: 0 0 30px rgba(255, 204, 0, 0.5); }
        .card-hsr:hover { border-color: #9d50ff; box-shadow: 0 0 30px rgba(157, 80, 255, 0.5); }
        .card-zzz:hover { border-color: #ccff00; box-shadow: 0 0 30px rgba(204, 255, 0, 0.5); }

    </style>
</head>
<body>

    <video autoplay muted loop id="bg-video">
        <source src="Video/Quantum Sea.mp4" type="video/mp4">
    </video>

    <nav>
        <div class="nav-logo">
            <img src="Honaki Impact 3/Logo Hoyoverse White.png" alt="Logo">
        </div>
        <div class="user-info">
            <p>บัญชีของคุณ: <span><?php echo htmlspecialchars($_SESSION['username']); ?></span></p>
            
            <a href="profile.php" class="btn-frame btn-profile">โปรไฟล์ของฉัน</a>

            <?php if ($role === 'admin'): ?>
                <a href="admin_dashboard.php" class="btn-frame">จัดการระบบ</a>
            <?php endif; ?>
            
            <a href="logout.php" class="btn-frame btn-logout">ออกจากระบบ</a>
        </div>
    </nav>

    <div class="main-content">
        <h1>เลือกข้อมูลที่คุณต้องการ</h1>

        <div class="gallery-container">
            
            <a href="history.php" class="game-card card-mihoyo">
                <img src="Honaki Impact 3/Home HoYoverse.jpg" alt="miHoYo History">
                <div class="content"><h2>miHoYo History</h2></div>
            </a>

            <a href="honkai3.php" class="game-card card-hi3">
                <img src="Honaki Impact 3/Home Honkai impact 3.png" alt="Honkai 3rd">
                <div class="content"><h2>Honkai Impact 3rd</h2></div>
            </a>

            <a href="genshin.php" class="game-card card-genshin">
                <img src="Genshin Impact/Home Genshin impact.jpg" alt="Genshin Impact">
                <div class="content"><h2>Genshin Impact</h2></div>
            </a>

            <a href="starrail.php" class="game-card card-hsr">
                <img src="Honkai star rail/Honkai star rail.jpg" alt="Honkai Star Rail">
                <div class="content"><h2>Honkai: Star Rail</h2></div>
            </a>

            <a href="zzz.php" class="game-card card-zzz">
                <img src="Zenless Zone Zero/Home Zenless Zone Zero.jpg" alt="Zenless Zone Zero">
                <div class="content"><h2>Zenless Zone Zero</h2></div>
            </a>

        </div>
    </div>

</body>
</html>