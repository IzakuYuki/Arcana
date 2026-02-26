<?php
session_start();
include 'db.php';

// [MAID PROTOCOL] ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$u_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// ดึงข้อมูลปัจจุบัน (ดึงค่าตำแหน่งมาแสดงผลใน Slider)
$stmt = $conn->prepare("SELECT profile_img, banner_img, banner_pos_y, avatar_pos_y FROM users WHERE id = ?");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_dir = "Image/";
    $updates = [];
    $types = "";
    $params = [];

    // 1. จัดการอัปโหลดรูปโปรไฟล์ (Avatar) - ระบบกันรูปซ้ำ 100%
    if (isset($_FILES['new_profile']) && $_FILES['new_profile']['name'] !== '') {
        $ext = strtolower(pathinfo($_FILES['new_profile']['name'], PATHINFO_EXTENSION));
        $new_name = "p_".$u_id.".".$ext;
        
        // [MAID CLEANING] ลบไฟล์ p_ID ทุกนามสกุลเก่าทิ้งก่อนบันทึกใหม่
        array_map('unlink', glob($target_dir . "p_" . $u_id . ".*"));

        if(move_uploaded_file($_FILES['new_profile']['tmp_name'], $target_dir.$new_name)) {
            $updates[] = "profile_img = ?"; $types .= "s"; $params[] = $new_name;
        }
    }

    // 2. จัดการอัปโหลดรูปปก (Banner) - ระบบกันรูปซ้ำ 100%
    if (isset($_FILES['new_banner']) && $_FILES['new_banner']['name'] !== '') {
        $ext = strtolower(pathinfo($_FILES['new_banner']['name'], PATHINFO_EXTENSION));
        $new_name = "b_".$u_id.".".$ext;
        
        // [MAID CLEANING] ลบไฟล์ b_ID ทุกนามสกุลเก่าทิ้ง
        array_map('unlink', glob($target_dir . "b_" . $u_id . ".*"));

        if(move_uploaded_file($_FILES['new_banner']['tmp_name'], $target_dir.$new_name)) {
            $updates[] = "banner_img = ?"; $types .= "s"; $params[] = $new_name;
        }
    }

    // 3. บันทึกตำแหน่งรูปภาพ (%) เพื่อความเนียนข้ามหน้าจอ
    if (isset($_POST['banner_pos_y'])) {
        $updates[] = "banner_pos_y = ?"; $types .= "s"; $params[] = $_POST['banner_pos_y'] . "%";
    }
    if (isset($_POST['avatar_pos_y'])) {
        $updates[] = "avatar_pos_y = ?"; $types .= "s"; $params[] = $_POST['avatar_pos_y'] . "%";
    }

    if (!empty($updates)) {
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $types .= "i"; $params[] = $u_id;
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: profile.php?status=success"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjust Identity - <?php echo htmlspecialchars($username); ?></title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai'; }
        
        body { background: #000; color: #fff; overflow-x: hidden; }
        
        /* วิดีโอพื้นหลัง */
        #bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -2; filter: brightness(0.35); }

        nav { background: rgba(10, 10, 15, 0.85); height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 50px; border-bottom: 1px solid rgba(63, 211, 255, 0.3); backdrop-filter: blur(20px); position: sticky; top:0; z-index:1000; }
        
        .btn-frame { padding: 10px 25px; border: 1px solid #3fd3ff; color: #3fd3ff; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; cursor: pointer; background: rgba(63, 211, 255, 0.05); }
        .btn-frame:hover { background: #3fd3ff; color: #000; box-shadow: 0 0 20px rgba(63, 211, 255, 0.6); transform: translateY(-2px); }

        .container { max-width: 800px; margin: 60px auto; padding: 50px; background: rgba(15, 15, 20, 0.7); border-radius: 30px; border: 1px solid rgba(63, 211, 255, 0.2); backdrop-filter: blur(25px); box-shadow: 0 20px 50px rgba(0,0,0,0.8); }
        h2 { color: #fff; margin-bottom: 40px; text-align: center; font-size: 26px; text-transform: uppercase; text-shadow: 0 0 10px rgba(63, 211, 255, 0.5); }

        .edit-section { margin-bottom: 45px; background: rgba(0, 0, 0, 0.4); padding: 30px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.05); }
        .section-title { font-size: 16px; color: #3fd3ff; border-left: 4px solid #3fd3ff; padding-left: 15px; margin-bottom: 25px; font-weight: 600; text-transform: uppercase; }

        /* Preview Boxes */
        .preview-box { width: 100%; height: 220px; overflow: hidden; border-radius: 15px; border: 2px solid rgba(255, 255, 255, 0.1); background: #0a0a0a; position: relative; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; object-position: center <?php echo htmlspecialchars($user_data['banner_pos_y'] ?? '50%'); ?>; }

        .avatar-preview-box { width: 160px; height: 160px; border-radius: 50%; border: 3px solid rgba(255, 255, 255, 0.2); overflow: hidden; margin: 0 auto; background: #000; position: relative; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); }
        .avatar-preview-box img { width: 100%; height: 100%; object-fit: cover; object-position: center <?php echo htmlspecialchars($user_data['avatar_pos_y'] ?? '50%'); ?>; }

        .file-upload-wrapper { width: 100%; text-align: center; margin-top: 20px; position: relative; }
        .file-upload-wrapper input[type="file"] { position: absolute; left:0; top:0; width:100%; height:100%; opacity:0; cursor:pointer; }
        .file-upload-btn { display: inline-block; width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px dashed rgba(63, 211, 255, 0.5); border-radius: 12px; color: #ccc; font-size: 13px; transition: 0.3s; }
        .file-upload-wrapper:hover .file-upload-btn { border-color: #3fd3ff; color: #fff; }

        /* [FIXED SLIDER] แก้ไข appearance และเพิ่ม Thumb */
        .pos-slider { 
            width: 100%; height: 4px; background: rgba(255,255,255,0.1); border-radius: 5px; 
            outline: none; appearance: none; -webkit-appearance: none; margin-top: 20px; 
        }
        .pos-slider::-webkit-slider-thumb { 
            -webkit-appearance: none; appearance: none; width: 18px; height: 18px; 
            background: #3fd3ff; border-radius: 50%; cursor: pointer; 
            box-shadow: 0 0 10px #3fd3ff; transition: 0.2s; 
        }
        .pos-slider::-webkit-slider-thumb:hover { transform: scale(1.3); }
    </style>
</head>
<body>
    <video autoplay muted loop playsinline id="bg-video">
        <source src="Video/Quantum%20Sea.mp4" type="video/mp4">
    </video>

    <nav>
        <div style="font-weight:700; color:#fff; letter-spacing: 2px;">ADJUST IDENTITY</div>
        <a href="profile.php" class="btn-frame">ยกเลิกและกลับหน้าโปรไฟล์</a>
    </nav>

    <div class="container">
        <h2>ปรับแต่งโปรไฟล์ <span>จดหมายเหตุ</span></h2>
        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="edit-section">
                <div class="section-title">รูปภาพปก (Banner Adjust)</div>
                <div class="preview-box"><img src="Image/<?php echo htmlspecialchars($user_data['banner_img'] ?? 'default_banner.jpg'); ?>?v=<?php echo time(); ?>" id="banner-p"></div>
                <div class="file-upload-wrapper">
                    <input type="file" name="new_banner" id="in-banner" accept="image/*">
                    <div class="file-upload-btn">คลิกเพื่อเปลี่ยนรูปพื้นหลัง</div>
                </div>
                <input type="range" name="banner_pos_y" class="pos-slider" min="0" max="100" value="<?php echo (int)str_replace('%', '', $user_data['banner_pos_y'] ?? '50'); ?>" oninput="update('banner-p', this.value)">
            </div>

            <div class="edit-section" style="text-align: center;">
                <div class="section-title">รูปโปรไฟล์ (Avatar Adjust)</div>
                <div class="avatar-preview-box"><img src="Image/<?php echo htmlspecialchars($user_data['profile_img'] ?? 'default_avatar.jpg'); ?>?v=<?php echo time(); ?>" id="avatar-p"></div>
                <div class="file-upload-wrapper">
                    <input type="file" name="new_profile" id="in-profile" accept="image/*">
                    <div class="file-upload-btn">คลิกเพื่อเปลี่ยนรูปโปรไฟล์</div>
                </div>
                <input type="range" name="avatar_pos_y" class="pos-slider" min="0" max="100" value="<?php echo (int)str_replace('%', '', $user_data['avatar_pos_y'] ?? '50'); ?>" oninput="update('avatar-p', this.value)">
            </div>

            <button type="submit" class="btn-frame" style="background:#3fd3ff; color:#000; width:100%; padding:18px; font-size:15px;">บันทึกข้อมูลและวิเคราะห์รูปภาพ</button>
        </form>
    </div>

    <script>
        function update(id, val) { document.getElementById(id).style.objectPosition = "center " + val + "%"; }
        function preview(input, id) {
            if (input.files && input.files[0]) {
                var r = new FileReader();
                r.onload = e => document.getElementById(id).src = e.target.result;
                r.readAsDataURL(input.files[0]);
            }
        }
        document.getElementById('in-banner').onchange = function() { preview(this, 'banner-p'); };
        document.getElementById('in-profile').onchange = function() { preview(this, 'avatar-p'); };
    </script>
</body>
</html>