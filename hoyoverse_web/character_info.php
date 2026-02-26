<?php
session_start();
include 'db.php';

// หากไม่มี ID ให้กลับไปหน้าหลักแทนหน้า detail ที่ยกเลิกไป
if (!isset($_GET['id']) || empty($_GET['id'])) { 
    header("Location: index.php"); 
    exit(); 
}

// ใช้ Prepared Statement ตามมาตรฐานความปลอดภัย (ป้องกัน SQL Injection)
$stmt = $conn->prepare("SELECT * FROM characters WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
$char = $result->fetch_assoc();
$stmt->close();

// หากไม่พบข้อมูลตัวละคร ให้กลับไปหน้าหลักเพื่อป้องกัน Undefined Variable
if (!$char) {
    header("Location: index.php");
    exit();
}

$images = explode(',', $char['image']);
// ป้องกัน Warning กรณีไม่มีรูป
$splashArt = isset($images[0]) && !empty(trim($images[0])) ? trim($images[0]) : 'default.png';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($char['name'], ENT_QUOTES, 'UTF-8'); ?> - Profile</title>
    <style>
        /* จัดดีไซน์แบบ HoYoWiki: มีแถบข้อมูล Attributes และ Lore ยาวๆ */
        body { background: #0b0b0d; color: #fff; font-family: 'Noto Sans Thai'; padding: 50px; }
        .info-header { display: flex; gap: 50px; margin-bottom: 50px; }
        .splash-art { width: 450px; border-radius: 20px; box-shadow: 0 0 50px rgba(255, 51, 153, 0.2); }
        .details h1 { font-size: 50px; margin-bottom: 20px; color: #ff3399; }
        .lore-box { background: rgba(255,255,255,0.03); padding: 40px; border-radius: 25px; line-height: 2; color: #ccc; }
        .btn-back { color: #3fd3ff; text-decoration: none; margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>
    <a href="javascript:history.back()" class="btn-back">← กลับไปหน้ารายชื่อ</a>
    <div class="info-header">
        <img src="Image/<?php echo htmlspecialchars($splashArt, ENT_QUOTES, 'UTF-8'); ?>" class="splash-art">
        <div class="details">
            <h1><?php echo htmlspecialchars($char['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>ธาตุ: <?php echo htmlspecialchars($char['element'], ENT_QUOTES, 'UTF-8'); ?> | ประเภท: <?php echo htmlspecialchars($char['path_type'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>
    <div class="lore-box">
        <h2>ปูมหลังเนื้อเรื่อง</h2>
        <p><?php echo nl2br(htmlspecialchars($char['lore_detail'], ENT_QUOTES, 'UTF-8')); ?></p>
    </div>
</body>
</html>