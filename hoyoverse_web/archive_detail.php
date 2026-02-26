<?php
session_start();
include 'db.php';

// [MAID PROTOCOL] ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$u_id = $_SESSION['user_id'] ?? 0;
$c_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- [HEART SYSTEM V2] ระบบ 1 ไอดีต่อ 1 หัวใจ ---
if (isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
    $check = $conn->query("SELECT id FROM user_likes WHERE user_id = $u_id AND character_id = $c_id");
    
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM user_likes WHERE user_id = $u_id AND character_id = $c_id");
        $conn->query("UPDATE characters SET likes_count = GREATEST(0, likes_count - 1) WHERE id = $c_id");
        $status = 'unliked';
    } else {
        $conn->query("INSERT INTO user_likes (user_id, character_id) VALUES ($u_id, $c_id)");
        $conn->query("UPDATE characters SET likes_count = likes_count + 1 WHERE id = $c_id");
        $status = 'liked';
    }
    
    $new_count = $conn->query("SELECT likes_count FROM characters WHERE id = $c_id")->fetch_assoc()['likes_count'];
    echo json_encode(['status' => $status, 'count' => number_format($new_count)]);
    exit();
}

// 1. ดึงข้อมูลจดหมายเหตุ (14 คอลัมน์)
$stmt = $conn->prepare("SELECT * FROM characters WHERE id = ?");
$stmt->bind_param("i", $c_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) { header("Location: index.php"); exit(); }

// 2. ตรวจสอบสถานะหัวใจของผู้ใช้ปัจจุบัน
$user_has_liked = false;
if ($u_id > 0) {
    $like_check = $conn->query("SELECT id FROM user_likes WHERE user_id = $u_id AND character_id = $c_id");
    $user_has_liked = ($like_check->num_rows > 0);
}

// 3. จัดการรูปภาพและสังกัด
$image_list = !empty($data['image']) ? explode(',', $data['image']) : [];
$aff_list = !empty($data['affiliation']) ? explode(',', $data['affiliation']) : [];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['name']); ?> - Archive Detail</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai', sans-serif; }
        
        /* [FIXED VIDEO BG] ตั้งค่าเพื่อให้เห็นวิดีโอข้างหลัง */
        body, html { height: 100%; color: #fff; background: transparent; overflow-x: hidden; }
        #bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -2; filter: brightness(0.35); }

        nav { background: rgba(0, 0, 0, 0.85); height: 70px; padding: 0 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(63, 211, 255, 0.4); position: sticky; top: 0; z-index: 100; backdrop-filter: blur(20px); }
        .btn-back { padding: 8px 25px; background: rgba(63, 211, 255, 0.1); border: 2px solid #3fd3ff; color: #3fd3ff; border-radius: 30px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: #3fd3ff; color: #000; box-shadow: 0 0 20px #3fd3ff; transform: translateY(-2px); }

        .container { max-width: 1050px; margin: 40px auto; padding: 0 20px; }
        .detail-card { background: rgba(18, 18, 24, 0.8); border-radius: 40px; border: 1px solid rgba(255, 255, 255, 0.1); overflow: hidden; backdrop-filter: blur(35px); box-shadow: 0 40px 80px rgba(0,0,0,0.7); }

        /* Slider Image */
        .slider-container { width: 100%; height: 550px; position: relative; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .slide { position: absolute; width: 100%; height: 100%; opacity: 0; transition: 0.8s ease-in-out; object-fit: contain; }
        .slide.active { opacity: 1; }
        .slider-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(63, 211, 255, 0.15); border: 1px solid rgba(63, 211, 255, 0.3); color: #3fd3ff; width: 55px; height: 55px; cursor: pointer; font-size: 24px; z-index: 10; border-radius: 50%; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
        .slider-btn:hover { background: #3fd3ff; color: #000; box-shadow: 0 0 15px #3fd3ff; }

        .info-content { padding: 50px; }
        .header-section { border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 30px; margin-bottom: 35px; }
        .game-tag { display: inline-block; padding: 5px 18px; background: rgba(255, 51, 153, 0.15); border: 1px solid #ff3399; color: #ff3399; border-radius: 10px; font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 15px; }

        /* [HEART STYLE] */
        .heart-wrapper { display: flex; align-items: center; gap: 20px; }
        .heart-btn { background: rgba(255, 51, 153, 0.05); border: 2px solid #ff3399; color: #ff3399; width: 65px; height: 65px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; cursor: pointer; transition: 0.4s; outline: none; }
        .heart-btn.active { background: #ff3399; color: #fff; box-shadow: 0 0 20px #ff3399; }
        .heart-btn:hover { transform: scale(1.1); }
        .heart-count { font-size: 22px; font-weight: 700; text-shadow: 0 0 10px rgba(255, 51, 153, 0.5); }

        /* [14-COLUMN GRID] */
        .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 45px; }
        .meta-box { background: rgba(255, 255, 255, 0.04); padding: 25px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.08); }
        .meta-box label { display: block; font-size: 11px; color: #888; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1.5px; }
        .meta-box span { font-size: 18px; font-weight: 600; color: #fff; }

        /* [TAG STYLE] */
        .tag-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .badge { background: rgba(63, 211, 255, 0.1); border: 1px solid #3fd3ff; color: #3fd3ff; padding: 6px 15px; border-radius: 12px; font-size: 14px; font-weight: 600; }

        .lore-title { font-size: 22px; color: #ff3399; margin-bottom: 25px; border-left: 5px solid #ff3399; padding-left: 15px; font-weight: 600; }
        .lore-text { line-height: 2.1; color: #ccc; font-size: 17px; white-space: pre-line; }
    </style>
</head>
<body>

    <video autoplay muted loop playsinline id="bg-video">
        <source src="Video/Quantum%20Sea.mp4" type="video/mp4">
    </video>

    <nav>
        <div style="font-weight:700; color:#3fd3ff; letter-spacing: 1px;">ARCHIVE DETAIL COMMAND</div>
        <a href="javascript:history.back()" class="btn-back">← ย้อนกลับ</a>
    </nav>

    <div class="container">
        <div class="detail-card">
            
            <div class="slider-container">
                <?php if (count($image_list) > 0): ?>
                    <?php foreach($image_list as $index => $img): ?>
                        <img src="Image/<?php echo htmlspecialchars(trim($img)); ?>" class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                    <?php endforeach; ?>
                    <?php if (count($image_list) > 1): ?>
                        <button class="slider-btn btn-prev" onclick="changeSlide(-1)" style="left:20px;">❮</button>
                        <button class="slider-btn btn-next" onclick="changeSlide(1)" style="right:20px;">❯</button>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="Image/default.jpg" class="slide active">
                <?php endif; ?>
            </div>

            <div class="info-content">
                <div class="header-section">
                    <span class="game-tag"><?php echo htmlspecialchars($data['game']); ?></span>
                    <div class="heart-wrapper">
                        <h1 style="font-size:45px; flex-grow:1;"><?php echo htmlspecialchars($data['name']); ?></h1>
                        <button class="heart-btn <?php echo $user_has_liked ? 'active' : ''; ?>" id="heart-btn" onclick="toggleHeart()">❤</button>
                        <span class="heart-count" id="count-label"><?php echo number_format($data['likes_count']); ?></span>
                    </div>
                </div>

                <div class="meta-grid">
                    <?php if($data['category'] === 'character'): ?>
                        <div class="meta-box"><label>Rarity</label><span><?php echo htmlspecialchars($data['rarity']); ?></span></div>
                        <div class="meta-box"><label>Element / Combat</label><span><?php echo htmlspecialchars($data['element']); ?></span></div>
                        <div class="meta-box"><label>Path / Class</label><span><?php echo htmlspecialchars($data['path_type']); ?></span></div>
                        <div class="meta-box" style="grid-column: span 2;">
                            <label>Affiliation / สังกัด</label>
                            <div class="tag-list">
                                <?php foreach($aff_list as $aff): if(!empty(trim($aff))): ?>
                                    <span class="badge"><?php echo htmlspecialchars(trim($aff)); ?></span>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>

                    <?php elseif($data['category'] === 'affiliation'): ?>
                        <div class="meta-box"><label>Leader (ผู้นำ)</label><span><?php echo htmlspecialchars($data['leader']); ?></span></div>
                        <div class="meta-box"><label>Location (ที่ตั้ง)</label><span><?php echo htmlspecialchars($data['location']); ?></span></div>
                        <div class="meta-box"><label>Relationship</label><span><?php echo htmlspecialchars($data['relationship']); ?></span></div>

                    <?php elseif($data['category'] === 'item'): ?>
                        <div class="meta-box"><label>Item Type</label><span><?php echo htmlspecialchars($data['path_type']); ?></span></div>
                        <div class="meta-box"><label>Rarity</label><span><?php echo htmlspecialchars($data['rarity']); ?></span></div>
                    <?php endif; ?>
                </div>

                <div class="lore-section">
                    <div class="lore-title">จารึกประวัติและเนื้อเรื่องฉบับเต็ม</div>
                    <div class="lore-text"><?php echo nl2br(htmlspecialchars($data['lore_detail'])); ?></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- Image Slider ---
        let slideIdx = 0;
        const slides = document.querySelectorAll('.slide');
        function changeSlide(n) {
            if (slides.length <= 1) return;
            slides[slideIdx].classList.remove('active');
            slideIdx = (slideIdx + n + slides.length) % slides.length;
            slides[slideIdx].classList.add('active');
        }

        // --- AJAX Heart System ---
        function toggleHeart() {
            const btn = document.getElementById('heart-btn');
            const label = document.getElementById('count-label');
            const formData = new FormData();
            formData.append('action', 'toggle_like');
            formData.append('id', <?php echo $c_id; ?>);

            fetch(window.location.href, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'liked') btn.classList.add('active');
                else btn.classList.remove('active');
                label.innerText = data.count;
            });
        }
    </script>
</body>
</html>