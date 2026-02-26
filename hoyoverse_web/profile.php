<?php
session_start();
include 'db.php';

// [MAID PROTOCOL] ตรวจสอบสิทธิ์
if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
}

$u_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// ดึงข้อมูลผู้ใช้ล่าสุด
$user_res = $conn->query("SELECT * FROM users WHERE id = $u_id");
$user_data = $user_res->fetch_assoc();

// ดึงรายการตัวละครที่กดใจไว้แยกตามหมวดหมู่
$sql_fav = "SELECT c.* FROM characters c 
            JOIN user_likes ul ON c.id = ul.character_id 
            WHERE ul.user_id = ? ORDER BY ul.id DESC";
$stmt = $conn->prepare($sql_fav);
$stmt->bind_param("i", $u_id);
$stmt->execute();
$fav_result = $stmt->get_result();

$fav_groups = ['character' => [], 'item' => [], 'affiliation' => []];
while ($row = $fav_result->fetch_assoc()) {
    $fav_groups[$row['category']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>User ID Profile - <?php echo $username; ?></title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai'; }
        
        body { background: #000; color: #fff; overflow-x: hidden; }
        #bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -2; filter: brightness(0.3); }

        nav { background: rgba(0,0,0,0.85); height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 50px; border-bottom: 1px solid rgba(63, 211, 255, 0.4); backdrop-filter: blur(20px); position: sticky; top:0; z-index:1000; }
        .btn-frame { padding: 8px 25px; border: 2px solid #3fd3ff; color: #3fd3ff; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; }

        /* --- Hero / Banner Section (ลบฟิล์มดำออกตามสั่ง) --- */
        .hero { 
            height: 380px; 
            background: url('Image/<?php echo $user_data['banner_img'] ?? 'Home Honkai impact 3.png'; ?>'); 
            background-size: cover; 
            background-position: center <?php echo $user_data['banner_pos_y'] ?? '50%'; ?>; 
            position: relative;
            display: block;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* เพิ่มเส้นคั่นบางๆ ให้ดูคมชัด */
        }

        /* ปุ่มแก้ไขมุมขวาบน */
        .btn-edit-banner {
            position: absolute;
            top: 30px;
            right: 30px;
            padding: 10px 25px;
            background: rgba(0, 0, 0, 0.6); 
            border: 2px solid #3fd3ff;
            color: #3fd3ff;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            backdrop-filter: blur(5px);
            transition: 0.3s;
            z-index: 30;
            display: flex; align-items: center; gap: 10px;
        }
        .btn-edit-banner:hover { background: #3fd3ff; color: #000; box-shadow: 0 0 20px #3fd3ff; }

        /* กลุ่ม Identity ด้านล่างซ้าย */
        .profile-header-group {
            position: absolute;
            bottom: -50px; 
            left: 50px;
            display: flex;
            align-items: flex-end; 
            gap: 25px;
            z-index: 20;
        }

        .avatar-circle { 
            width: 160px; height: 160px; border-radius: 50%; border: 4px solid #3fd3ff; overflow: hidden; 
            background: #000; box-shadow: 0 0 40px rgba(63, 211, 255, 0.6); flex-shrink: 0;
        }
        .avatar-circle img { 
            width: 100%; height: 100%; object-fit: cover; 
            object-position: center <?php echo $user_data['avatar_pos_y'] ?? '50%'; ?>; 
        }

        .user-text-info { padding-bottom: 15px; }
        .user-text-info h1 { font-size: 48px; font-weight: 700; margin: 0; line-height: 1; text-shadow: 3px 3px 10px rgba(0,0,0,1); }
        .user-text-info .slogan { color: #3fd3ff; font-size: 15px; font-weight: 600; letter-spacing: 1.5px; margin-top: 8px; text-shadow: 2px 2px 5px rgba(0,0,0,1); background: rgba(0,0,0,0.4); display: inline-block; padding: 4px 12px; border-radius: 15px; }


        /* --- [FIXED] Seamless Archive Frame (ลบช่องว่าง Margin Top ออก) --- */
        .archive-frame { 
            max-width: 1200px; 
            min-height: 600px; 
            margin: 0 auto 100px auto; /* เปลี่ยนจาก 80px เป็น 0 เพื่อให้ติดกับ Hero */
            background: rgba(18, 18, 24, 0.85); 
            backdrop-filter: blur(35px); 
            border-radius: 0 0 40px 40px; /* ลบความโค้งด้านบนเพื่อให้เข้ากับรอยต่อ */
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-top: none; /* ลบขอบบนเพื่อความเนียน */
            display: flex; 
            overflow: hidden; 
            position: relative; 
            z-index: 10; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.6); 
        }
        
        .archive-sidebar { 
            width: 120px; padding: 80px 0 40px 0; /* เพิ่ม padding บนเพื่อหลบ Avatar ที่เกยลงมา */
            background: rgba(0, 0, 0, 0.4); 
            border-right: 1px solid rgba(255, 255, 255, 0.05); 
            display: flex; flex-direction: column; align-items: center; gap: 30px; 
        }
        .side-item { 
            width: 80px; height: 80px; background: rgba(255, 255, 255, 0.03); 
            border-radius: 20px; display: flex; flex-direction: column; 
            justify-content: center; align-items: center; cursor: pointer; 
            transition: 0.3s; border: 1px solid transparent; 
        }
        .side-item.active { border-color: #ff3399; background: rgba(255, 51, 153, 0.15); }
        .side-item span { font-size: 10px; margin-top: 8px; color: #888; font-weight: 600; text-transform: uppercase; }
        .side-item.active span { color: #ff3399; }
        .side-item svg { transition: 0.3s; fill: #666; }
        .side-item.active svg { fill: #ff3399; }
        
        .archive-main { flex-grow: 1; padding: 80px 50px 50px 50px; /* เพิ่ม padding บนเพื่อหลบชื่อที่เกยลงมา */ }
        .main-header { margin-bottom: 35px; border-left: 5px solid #ff3399; padding-left: 20px; }
        .main-header h2 { font-size: 22px; color: #fff; }

        .fav-grid { display: none; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 25px; animation: fadeIn 0.4s ease; }
        .fav-grid.active { display: grid; }
        
        .char-square-btn { 
            background: rgba(255, 255, 255, 0.04); border: 2px solid rgba(255, 255, 255, 0.08); 
            border-radius: 25px; overflow: hidden; aspect-ratio: 0.85/1.1; 
            position: relative; text-decoration: none; transition: 0.4s; 
        }
        .char-square-btn:hover { transform: translateY(-10px); border-color: #ff3399; box-shadow: 0 10px 30px rgba(255, 51, 153, 0.3); }
        .char-square-btn img { width: 100%; height: 100%; object-fit: cover; }
        .char-name-label { position: absolute; bottom: 0; width: 100%; background: linear-gradient(transparent, rgba(0,0,0,0.9)); padding: 25px 10px 15px; text-align: center; color: #fff; font-weight: 600; font-size: 14px; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <video autoplay muted loop playsinline id="bg-video">
        <source src="Video/Quantum%20Sea.mp4" type="video/mp4">
    </video>

    <nav>
        <div style="font-weight:700; color:#3fd3ff; letter-spacing: 1px;">USER ARCHIVE PROFILE</div>
        <a href="index.php" class="btn-frame">← กลับหน้าหลัก</a>
    </nav>

    <div class="hero">
        <a href="profile_edit.php" class="btn-edit-banner">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            แก้ไขโปรไฟล์
        </a>
        
        <div class="profile-header-group">
            <div class="avatar-circle">
                <img src="Image/<?php echo $user_data['profile_img'] ?? 'default_avatar.jpg'; ?>" alt="Avatar">
            </div>
            <div class="user-text-info">
                <h1><?php echo htmlspecialchars($username); ?></h1>
                <p class="slogan">COMMANDER OF HYPERION ARCHIVE</p>
            </div>
        </div>
    </div>

    <div class="archive-frame">
        <div class="archive-sidebar">
            <div class="side-item active" onclick="switchCat('character', this)">
                <svg width="30" height="30" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <span>Character</span>
            </div>
            <div class="side-item" onclick="switchCat('item', this)">
                <svg width="25" height="25" viewBox="0 0 24 24"><path d="M21 16.5C21 16.88 20.79 17.21 20.47 17.38L12.57 21.82C12.41 21.94 12.21 22 12 22C11.79 22 11.59 21.94 11.43 21.82L3.53 17.38C3.21 17.21 3 16.88 3 16.5V7.5C3 7.12 3.21 6.79 3.53 6.62L11.43 2.18C11.59 2.06 11.79 2 12 2C12.21 2 12.41 2.06 12.57 2.18L20.47 6.62C20.79 6.79 21 7.12 21 7.5V16.5Z"/></svg>
                <span>Items</span>
            </div>
            <div class="side-item" onclick="switchCat('affiliation', this)">
                <svg width="25" height="25" viewBox="0 0 24 24"><path d="M12 2L4.5 20.29L5.21 21L12 18L18.79 21L19.5 20.29L12 2Z"/></svg>
                <span>Affiliation</span>
            </div>
        </div>

        <div class="archive-main">
            <div class="main-header">
                <h2 id="cat-title">รายการโปรด: ตัวละคร</h2>
            </div>

            <div id="grid-character" class="fav-grid active">
                <?php if (!empty($fav_groups['character'])): foreach($fav_groups['character'] as $c): ?>
                    <?php $imgs = explode(',', $c['image']); ?>
                    <a href="archive_detail.php?id=<?php echo $c['id']; ?>" class="char-square-btn">
                        <img src="Image/<?php echo trim($imgs[0]); ?>" alt="Fav">
                        <p class="char-name-label"><?php echo $c['name']; ?></p>
                    </a>
                <?php endforeach; else: ?>
                    <p style="color: #666; grid-column: 1/-1;">ยังไม่มีตัวละครที่ถูกใจ...</p>
                <?php endif; ?>
            </div>

            <div id="grid-item" class="fav-grid">
                <?php if (!empty($fav_groups['item'])): foreach($fav_groups['item'] as $c): ?>
                    <?php $imgs = explode(',', $c['image']); ?>
                    <a href="archive_detail.php?id=<?php echo $c['id']; ?>" class="char-square-btn">
                        <img src="Image/<?php echo trim($imgs[0]); ?>" alt="Fav">
                        <p class="char-name-label"><?php echo $c['name']; ?></p>
                    </a>
                <?php endforeach; else: ?>
                    <p style="color: #666; grid-column: 1/-1;">ยังไม่มีไอเทมที่ถูกใจ...</p>
                <?php endif; ?>
            </div>

            <div id="grid-affiliation" class="fav-grid">
                <?php if (!empty($fav_groups['affiliation'])): foreach($fav_groups['affiliation'] as $c): ?>
                    <?php $imgs = explode(',', $c['image']); ?>
                    <a href="archive_detail.php?id=<?php echo $c['id']; ?>" class="char-square-btn">
                        <img src="Image/<?php echo trim($imgs[0]); ?>" alt="Fav">
                        <p class="char-name-label"><?php echo $c['name']; ?></p>
                    </a>
                <?php endforeach; else: ?>
                    <p style="color: #666; grid-column: 1/-1;">ยังไม่มีสังกัดที่ถูกใจ...</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function switchCat(cat, el) {
            document.querySelectorAll('.side-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');
            document.querySelectorAll('.fav-grid').forEach(g => g.classList.remove('active'));
            document.getElementById('grid-' + cat).classList.add('active');
            const titles = {'character':'ตัวละคร', 'item':'ไอเทม/อาวุธ', 'affiliation':'สังกัด/องค์กร'};
            document.getElementById('cat-title').innerText = 'รายการโปรด: ' + titles[cat];
        }
    </script>
</body>
</html>