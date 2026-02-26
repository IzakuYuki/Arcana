<?php
session_start();
include 'db.php';

// [MAID PROTOCOL] เฉพาะแอดมินเท่านั้น
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$response_script = "";

// --- ระบบลบข้อมูล (Delete Protocol) ---
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM characters WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $response_script = "
            Swal.fire({
                icon: 'success',
                title: 'ลบข้อมูลสำเร็จ!',
                text: 'รายการจดหมายเหตุถูกกำจัดแล้วค่ะนายท่าน',
                confirmButtonText: 'รับทราบ'
            }).then(() => { window.location.href='admin_dashboard.php'; });";
    }
    $stmt->close();
}

$query = "SELECT * FROM characters ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoYoverse - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai'; }
        
        body, html { height: 100%; color: #fff; background: #000; overflow-x: hidden; }
        #bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -1; filter: brightness(0.3); }

        .swal2-popup { background: rgba(20, 20, 25, 0.95) !important; backdrop-filter: blur(15px); border: 1px solid rgba(63, 211, 255, 0.3); border-radius: 25px !important; color: #fff !important; }

        nav { background: rgba(0, 0, 0, 0.85); height: 70px; padding: 0 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #3fd3ff; backdrop-filter: blur(15px); position: sticky; top: 0; z-index: 100; }
        .btn-frame { padding: 8px 22px; background: rgba(63, 211, 255, 0.1); border: 2px solid #3fd3ff; color: #3fd3ff; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; margin-left: 10px; display: inline-block; }
        .btn-frame:hover { background: #3fd3ff; color: #000; box-shadow: 0 0 20px #3fd3ff; transform: translateY(-2px); }

        .container { max-width: 1200px; margin: 40px auto; padding: 35px; background: rgba(15, 15, 20, 0.75); border-radius: 30px; backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th { color: #3fd3ff; padding: 18px; text-align: left; border-bottom: 2px solid #3fd3ff; font-size: 15px; }
        td { padding: 18px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }
        
        .status-badge { padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-approved { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .status-pending { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }

        .action-group { display: flex; gap: 10px; }
        .btn-action { padding: 6px 14px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 12px; transition: 0.3s; }
        .btn-edit { border: 1px solid #3fd3ff; color: #3fd3ff; }
        .btn-edit:hover { background: #3fd3ff; color: #000; }
        .btn-delete { border: 1px solid #ff3399; color: #ff3399; }
        .btn-delete:hover { background: #ff3399; color: #fff; box-shadow: 0 0 15px #ff3399; }
    </style>
</head>
<body>
    <video autoplay muted loop id="bg-video">
        <source src="Video/Quantum Sea.mp4" type="video/mp4">
    </video>

    <nav>
        <div style="font-weight:700; color:#3fd3ff; letter-spacing: 1px;">DATABASE MANAGEMENT</div>
        <div>
            <a href="admin_approval.php" class="btn-frame" style="border-color:#ff3399; color:#ff3399;">ตรวจสอบคำขอ</a>
            <a href="index.php" class="btn-frame">กลับหน้าหลัก</a>
        </div>
    </nav>

    <div class="container">
        <h2 style="margin-bottom: 5px; color: #fff;">คลังข้อมูลทั้งหมดในระบบ</h2>
        <p style="color: #888; font-size: 13px; margin-bottom: 25px;">ท่านสามารถแก้ไขรายละเอียดหรือกำจัดรายการจดหมายเหตุที่ไม่ต้องการได้ที่นี่ค่ะ</p>

        <table>
            <thead>
                <tr>
                    <th>ชื่อรายการ</th>
                    <th>หมวดหมู่</th>
                    <th>สถานะ</th>
                    <th>ผู้บันทึก</th>
                    <th>จัดการข้อมูล</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><strong style="color: #fff;"><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td>
                        <span class="status-badge <?php echo ($row['status'] == 'approved') ? 'status-approved' : 'status-pending'; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['submitted_by']); ?></td>
                    <td>
                        <div class="action-group">
                            <a href="admin_edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit">แก้ไข</a>
                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-action btn-delete">ลบ</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        <?php if ($response_script) echo $response_script; ?>

        function confirmDelete(id) {
            Swal.fire({
                title: 'ยืนยันการทำลายข้อมูล?',
                text: "ข้อมูลนี้จะหายไปจากจดหมายเหตุถาวรนะคะนายท่าน!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff3399',
                cancelButtonColor: '#333',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `admin_dashboard.php?delete_id=${id}`;
                }
            })
        }
    </script>
</body>
</html>