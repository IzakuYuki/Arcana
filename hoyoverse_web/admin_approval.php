<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$response_script = "";

// ระบบจัดการ อนุมัติ หรือ ลบ
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'approve') {
        $stmt = $conn->prepare("UPDATE characters SET status = 'approved' WHERE id = ?");
        $msg = "อนุมัติข้อมูลเรียบร้อยแล้วค่ะ!";
    } else {
        $stmt = $conn->prepare("DELETE FROM characters WHERE id = ?");
        $msg = "ลบข้อมูลเรียบร้อยแล้วค่ะ!";
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $response_script = "Swal.fire({ icon: 'success', title: 'ดำเนินการสำเร็จ', text: '$msg' }).then(() => { window.location.href='admin_approval.php'; });";
    }
    $stmt->close();
}

$query = "SELECT * FROM characters WHERE status = 'pending' ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoYoverse - Admin Approval</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai'; }
        body, html { height: 100%; color: #fff; background: #000; overflow-x: hidden; }
        #bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -1; filter: brightness(0.3); }
        nav { background: rgba(0, 0, 0, 0.8); height: 70px; padding: 0 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 51, 153, 0.4); backdrop-filter: blur(15px); position: sticky; top: 0; z-index: 100; }
        .btn-frame { padding: 8px 20px; background: rgba(255, 51, 153, 0.1); border: 2px solid #ff3399; color: #ff3399; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; }
        .btn-frame:hover { background: #ff3399; color: #fff; box-shadow: 0 0 15px #ff3399; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .glass-panel { background: rgba(20, 20, 25, 0.7); padding: 30px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; color: #ff3399; padding: 15px; border-bottom: 2px solid rgba(255, 51, 153, 0.3); }
        td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-size: 14px; }
        .action-btn { padding: 5px 15px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px; margin-right: 5px; transition: 0.2s; }
        .approve { background: #28a745; color: white; }
        .delete { background: #dc3545; color: white; }
        .approve:hover, .delete:hover { opacity: 0.8; transform: scale(1.05); }
    </style>
</head>
<body>
    <video autoplay muted loop id="bg-video"><source src="Video/Quantum Sea.mp4" type="video/mp4"></video>
    <nav>
        <div style="font-weight:600; color:#ff3399;">APPROVAL QUEUE</div>
        <a href="admin_dashboard.php" class="btn-frame">กลับหน้าจัดการ</a>
    </nav>
    <div class="container">
        <div class="glass-panel">
            <h2 style="margin-bottom: 20px; color: #3fd3ff;">รายการรอการตรวจสอบ</h2>
            <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อ</th>
                        <th>เกม</th>
                        <th>หมวดหมู่</th>
                        <th>ผู้ส่ง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['game']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['submitted_by']); ?></td>
                        <td>
                            <a href="admin_approval.php?action=approve&id=<?php echo $row['id']; ?>" class="action-btn approve">อนุมัติ</a>
                            <a href="admin_approval.php?action=delete&id=<?php echo $row['id']; ?>" class="action-btn delete" onclick="return confirm('ยืนยันการลบ?')">ลบ</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align:center; padding: 50px; color: #aaa;">ไม่มีรายการค้างอนุมัติในขณะนี้ค่ะนายท่าน</p>
            <?php endif; ?>
        </div>
    </div>
    <script><?php if ($response_script) echo $response_script; ?></script>
</body>
</html>