<?php
session_start();
include 'db.php';

// [MAID PROTOCOL] ตรวจสอบสิทธิ์: เฉพาะ Admin เท่านั้น
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$data = null; 
$response_script = "";

// 1. ตรวจสอบและดึงข้อมูลเดิมด้วย Prepared Statement
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM characters WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
    }
}

// หากไม่พบข้อมูล ให้ดีดกลับไปหน้า Dashboard ทันที
if (!$data) {
    header("Location: admin_dashboard.php");
    exit();
}

// 2. ระบบบันทึกการแก้ไข (Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $name = $_POST['name'] ?? '';
    $game = $_POST['game_select'] ?? '';
    $category = $_POST['category_select'] ?? '';
    $lore_detail = $_POST['lore'] ?? '';
    
    // กำหนดค่าเริ่มต้นเพื่อป้องกัน Undefined Variable
    $element = ''; $path_type = ''; $rarity = ''; $affiliation = '';
    $leader = ''; $location = ''; $relationship = '';

    // การคัดกรองข้อมูลตามหมวดหมู่ (Category Logic)
    if ($category === 'character') {
        $element     = $_POST['char_element'] ?? '';
        $path_type   = $_POST['char_path'] ?? '';
        $rarity      = $_POST['char_rarity'] ?? '';
        $affiliation = $_POST['char_affiliation'] ?? '';
    } elseif ($category === 'affiliation') {
        $leader       = $_POST['faction_leader'] ?? '';
        $location     = $_POST['faction_location'] ?? '';
        $relationship = $_POST['faction_relationship'] ?? '';
    } elseif ($category === 'item') {
        $path_type   = $_POST['item_type'] ?? ''; 
        $rarity      = $_POST['item_rarity'] ?? '';
    }

    // จัดการรูปภาพ (Security Hardened Upload)
    $image_name = $_POST['old_image'];
    if (!empty($_FILES['image']['name'])) {
        $tmp = $_FILES['image']['tmp_name'];
        
        // ตรวจสอบ MIME Type จริง (OO Style - No finfo_close required)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($tmp);
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (in_array($mime_type, $allowed_mimes)) {
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $file_hash = md5_file($tmp);
            $new_fn = $file_hash . "." . $file_ext;
            $target_path = "Image/" . $new_fn;

            if (!file_exists($target_path)) {
                move_uploaded_file($tmp, $target_path);
            }
            $image_name = $new_fn;
        } else {
            $response_script = "Swal.fire('Error', 'ประเภทไฟล์ไม่ได้รับอนุญาตค่ะนายท่าน', 'error');";
        }
    }

    // Update ข้อมูลด้วย Prepared Statement (12 placeholders + 1 WHERE)
    if (empty($response_script)) {
        $update_sql = "UPDATE characters SET 
                        name=?, game=?, category=?, element=?, path_type=?, 
                        rarity=?, affiliation=?, leader=?, location=?, relationship=?, 
                        lore_detail=?, image=? 
                      WHERE id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssssssssssi", 
            $name, $game, $category, $element, $path_type, 
            $rarity, $affiliation, $leader, $location, $relationship, 
            $lore_detail, $image_name, $id
        );

        if ($update_stmt->execute()) {
            $response_script = "
                Swal.fire({
                    icon: 'success',
                    title: 'แก้ไขข้อมูลสำเร็จ!',
                    text: 'ข้อมูลชุดใหม่ถูกบันทึกลงจดหมายเหตุแล้วค่ะนายท่าน',
                    confirmButtonText: 'รับทราบ'
                }).then(() => { window.location.href='admin_dashboard.php'; });";
        } else {
            $response_script = "Swal.fire('Error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูลค่ะ', 'error');";
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoYoverse - Admin Edit Dashboard</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai', sans-serif; }
        
        body, html { height: 100%; color: #fff; background: #0b0b0d; overflow-x: hidden; }
        #bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -1; filter: brightness(0.4); }

        .swal2-popup { background: rgba(20, 20, 25, 0.95) !important; backdrop-filter: blur(15px); border: 1px solid rgba(63, 211, 255, 0.3); border-radius: 25px !important; color: #fff !important; }
        .swal2-title { color: #3fd3ff !important; }

        nav { background: rgba(0, 0, 0, 0.75); height: 70px; padding: 0 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(63, 211, 255, 0.4); position: sticky; top: 0; z-index: 100; backdrop-filter: blur(15px); }
        .btn-frame { padding: 8px 25px; background: rgba(63, 211, 255, 0.1); border: 2px solid #3fd3ff; color: #3fd3ff; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; }
        .btn-frame:hover { background: #3fd3ff; color: #000; box-shadow: 0 0 20px #3fd3ff; transform: translateY(-2px); }

        .container { max-width: 800px; margin: 50px auto; padding: 0 20px; }
        .edit-box { background: rgba(15, 15, 20, 0.7); padding: 40px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); box-shadow: 0 20px 50px rgba(0,0,0,0.5); }

        h1 { font-size: 24px; margin-bottom: 30px; color: #3fd3ff; text-align: center; border-bottom: 2px solid #3fd3ff; padding-bottom: 10px; }

        label { display: block; margin: 15px 0 8px; color: #3fd3ff; font-weight: 600; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 12px 15px; background: rgba(0,0,0,0.5); border: 1px solid rgba(63,211,255,0.3); border-radius: 12px; color: #fff; outline: none; font-size: 16px; transition: 0.3s; appearance: none; -webkit-appearance: none; }
        input:focus, select:focus { border-color: #9b59ff; box-shadow: 0 0 10px rgba(155, 89, 255, 0.3); }
        
        .btn-save { width: 100%; padding: 16px; background: linear-gradient(45deg, #3fd3ff, #9b59ff); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; transition: 0.4s; margin-top: 30px; font-size: 16px; }
        .btn-save:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(63, 211, 255, 0.4); }

        .image-preview-group { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .image-preview { width: 120px; height: 120px; border-radius: 15px; object-fit: cover; border: 2px solid #3fd3ff; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .category-group { display: none; padding: 15px; background: rgba(255,255,255,0.03); border-radius: 15px; margin-top: 10px; }
    </style>
</head>
<body>

<video autoplay muted loop id="bg-video">
    <source src="Video/Quantum Sea.mp4" type="video/mp4">
</video>

<nav>
    <div style="font-weight:600; color:#3fd3ff; letter-spacing: 1px;">HOYOVERSE ARCHIVE - SYSTEM EDIT</div>
    <a href="admin_dashboard.php" class="btn-frame">← ยกเลิกการแก้ไข</a>
</nav>

<div class="container">
    <div class="edit-box">
        <h1>แก้ไขข้อมูลระดับความลับ</h1>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int)$data['id']; ?>">
            <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($data['image']); ?>">

            <label>ชื่อจดหมายเหตุ (Primary Name)</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($data['name']); ?>" required>

            <label>โปรเจกต์ต้นสังกัด</label>
            <select name="game_select" id="game_select" onchange="updateUI()">
                <option value="Honkai Impact 3rd" <?php echo ($data['game'] == 'Honkai Impact 3rd') ? 'selected' : ''; ?>>Honkai Impact 3rd</option>
                <option value="Genshin Impact" <?php echo ($data['game'] == 'Genshin Impact') ? 'selected' : ''; ?>>Genshin Impact</option>
                <option value="Honkai: Star Rail" <?php echo ($data['game'] == 'Honkai: Star Rail') ? 'selected' : ''; ?>>Honkai: Star Rail</option>
                <option value="Zenless Zone Zero" <?php echo ($data['game'] == 'Zenless Zone Zero') ? 'selected' : ''; ?>>Zenless Zone Zero</option>
            </select>

            <label>ประเภทจดหมายเหตุ</label>
            <select name="category_select" id="category_select" onchange="updateUI()">
                <option value="character" <?php echo ($data['category'] == 'character') ? 'selected' : ''; ?>>ตัวละคร (Character)</option>
                <option value="item" <?php echo ($data['category'] == 'item') ? 'selected' : ''; ?>>ไอเทม / อาวุธ (Item)</option>
                <option value="affiliation" <?php echo ($data['category'] == 'affiliation') ? 'selected' : ''; ?>>สังกัด / องค์กร (Faction)</option>
            </select>

            <div id="sec-character" class="category-group">
                <label>ระดับความหายาก (Rarity)</label>
                <select name="char_rarity">
                    <option value="5 ดาว" <?php echo ($data['rarity'] == '5 ดาว') ? 'selected' : ''; ?>>5 ดาว</option>
                    <option value="4 ดาว" <?php echo ($data['rarity'] == '4 ดาว') ? 'selected' : ''; ?>>4 ดาว</option>
                    <option value="S-Rank" <?php echo ($data['rarity'] == 'S-Rank') ? 'selected' : ''; ?>>S-Rank</option>
                    <option value="A-Rank" <?php echo ($data['rarity'] == 'A-Rank') ? 'selected' : ''; ?>>A-Rank</option>
                </select>
                <label id="lbl-char-element">พลังงานหลัก</label>
                <input type="text" name="char_element" value="<?php echo htmlspecialchars($data['element']); ?>">
                <label id="lbl-char-path">เส้นทาง / สายอาชีพ</label>
                <input type="text" name="char_path" value="<?php echo htmlspecialchars($data['path_type']); ?>">
                <label>สังกัดปัจจุบัน</label>
                <input type="text" name="char_affiliation" value="<?php echo htmlspecialchars($data['affiliation']); ?>">
            </div>

            <div id="sec-affiliation" class="category-group">
                <label>ผู้นำสูงสุด</label>
                <input type="text" name="faction_leader" value="<?php echo htmlspecialchars($data['leader']); ?>">
                <label>ฐานปฏิบัติการหลัก</label>
                <input type="text" name="faction_location" value="<?php echo htmlspecialchars($data['location']); ?>">
                <label>สถานะความสัมพันธ์</label>
                <select name="faction_relationship">
                    <option value="เป็นกลาง" <?php echo ($data['relationship'] == 'เป็นกลาง') ? 'selected' : ''; ?>>เป็นกลาง</option>
                    <option value="พันธมิตร" <?php echo ($data['relationship'] == 'พันธมิตร') ? 'selected' : ''; ?>>พันธมิตร</option>
                    <option value="ศัตรู" <?php echo ($data['relationship'] == 'ศัตรู') ? 'selected' : ''; ?>>ศัตรู</option>
                </select>
            </div>

            <div id="sec-item" class="category-group">
                <label>ประเภทไอเทม / Class</label>
                <input type="text" name="item_type" value="<?php echo htmlspecialchars($data['path_type']); ?>">
                <label>ระดับความทรงพลัง</label>
                <select name="item_rarity">
                    <option value="6 ดาว" <?php echo ($data['rarity'] == '6 ดาว') ? 'selected' : ''; ?>>6 ดาว</option>
                    <option value="5 ดาว" <?php echo ($data['rarity'] == '5 ดาว') ? 'selected' : ''; ?>>5 ดาว</option>
                    <option value="4 ดาว" <?php echo ($data['rarity'] == '4 ดาว') ? 'selected' : ''; ?>>4 ดาว</option>
                </select>
            </div>

            <label>ไฟล์ภาพประจำข้อมูล</label>
            <div class="image-preview-group">
                <?php 
                    $imgs = explode(',', $data['image']);
                    foreach($imgs as $img) {
                        if(!empty($img)) echo '<img src="Image/'.htmlspecialchars($img).'" class="image-preview" alt="Archive Image">';
                    }
                ?>
            </div>
            <label style="margin-top:20px; font-size:12px; color:#aaa;">(อัปโหลดไฟล์ใหม่เพื่อเปลี่ยนรูปภาพหลัก)</label>
            <input type="file" name="image" accept="image/*" onchange="previewNewImage(this)">
            <div id="new-preview-container" class="image-preview-group"></div>

            <label>บันทึกประวัติ (Lore & Description)</label>
            <textarea name="lore" rows="8"><?php echo htmlspecialchars($data['lore_detail']); ?></textarea>

            <button type="submit" class="btn-save">ยืนยันการบันทึกข้อมูล</button>
        </form>
    </div>
</div>

<script>
    <?php if ($response_script) echo $response_script; ?>

    function updateUI() {
        const game = document.getElementById('game_select').value;
        const cat = document.getElementById('category_select').value;
        document.querySelectorAll('.category-group').forEach(el => el.style.display = 'none');
        
        if (cat === 'character') {
            document.getElementById('sec-character').style.display = 'block';
            const lblE = document.getElementById('lbl-char-element');
            const lblP = document.getElementById('lbl-char-path');
            if (game === "Honkai: Star Rail") { lblE.innerText = "ธาตุ (Combat Type)"; lblP.innerText = "เส้นทาง (Path)"; }
            else if (game === "Genshin Impact") { lblE.innerText = "วิชั่น (Vision)"; lblP.innerText = "ประเภทอาวุธ"; }
            else if (game === "Zenless Zone Zero") { lblE.innerText = "ธาตุ (Attribute)"; lblP.innerText = "สไตล์ (Specialty)"; }
            else { lblE.innerText = "ธาตุ / ประเภทพลัง"; lblP.innerText = "ประเภทชุดสูท / สไตล์"; }
        } else if (cat === 'affiliation') {
            document.getElementById('sec-affiliation').style.display = 'block';
        } else if (cat === 'item') {
            document.getElementById('sec-item').style.display = 'block';
        }
    }

    function previewNewImage(input) {
        const container = document.getElementById('new-preview-container');
        container.innerHTML = '';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'image-preview';
                container.appendChild(img);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    // เรียกใช้ตอนโหลดหน้าเพื่อตั้งค่า UI เริ่มต้น
    updateUI();
</script>

</body>
</html>