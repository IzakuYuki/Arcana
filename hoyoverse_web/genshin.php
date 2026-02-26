<?php
// [MAID PROTOCOL] เริ่มระบบและเช็คความปลอดภัย
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 1. จัดการหมวดหมู่ (Category Management)
$current_cat = isset($_GET['cat']) ? $_GET['cat'] : 'character';
$allowed_cats = ['character', 'item', 'affiliation'];
if (!in_array($current_cat, $allowed_cats)) { $current_cat = 'character'; }

// 2. ดึงข้อมูลเฉพาะของ Genshin Impact
$sql = "SELECT * FROM characters WHERE game = 'Genshin Impact' AND category = ? AND status = 'approved' ORDER BY id DESC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $current_cat);
    $stmt->execute();
    $result = $stmt->get_result();
}

// 3. ส่วนจัดการ AJAX Request สำหรับการสลับ Tab ไร้รอยต่อ
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $imgs = explode(',', $row['image']);
            $raw_icon = !empty($imgs[0]) ? trim($imgs[0]) : 'no-image.jpg';
            // แปลงช่องว่างในชื่อไฟล์ให้เบราว์เซอร์อ่านได้
            $safe_icon = str_replace(' ', '%20', $raw_icon);
            
            // ลอจิกการแสดงป้ายกำกับ (ปรับให้เข้ากับระบบของ Genshin)
            $meta_label = "";
            if ($current_cat === 'character') {
                $meta_label = htmlspecialchars($row['rarity'] . " | " . $row['element']);
            } elseif ($current_cat === 'item') {
                $meta_label = htmlspecialchars($row['rarity'] . " | " . $row['path_type']);
            } else {
                $meta_label = "ผู้นำ: " . htmlspecialchars($row['leader']);
            }

            echo '<a href="archive_detail.php?id=' . (int)$row['id'] . '" class="char-square-btn animate-in">';
            echo '  <div class="element-overlay">' . $meta_label . '</div>';
            echo '  <div class="char-img-box"><img src="Image/' . $safe_icon . '" loading="lazy"></div>';
            echo '  <div class="char-name-label">' . htmlspecialchars($row['name']) . '</div>';
            echo '</a>';
        }
    } else {
        echo '<div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">';
        echo '  <div style="font-size: 40px; margin-bottom: 15px; color: #3fd3ff;">✧</div>';
        echo '  <p style="color: #888; font-size: 16px; letter-spacing: 1px;">ยังไม่มีข้อมูลจดหมายเหตุในหมวดหมู่นี้</p>';
        echo '</div>';
    }
    exit();
}

$display_title = ($current_cat === 'item') ? "คลังแสงและอาวุธ (Weapons)" : (($current_cat === 'affiliation') ? "เมืองและองค์กร (Factions)" : "รายชื่อนักเดินทาง (Characters)");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genshin Impact Archive - HoYoverse Command</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600;700&display=swap');
        
        :root {
            /* [THEME] สีฟ้าสว่างของ Genshin Impact */
            --primary: #3fd3ff; 
            --secondary: #9b59ff;
            --bg-dark: #050508;
            --glass: rgba(18, 18, 24, 0.75);
            --border: rgba(63, 211, 255, 0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai', sans-serif; }
        body, html { height: 100%; color: #ffffff; background: var(--bg-dark); overflow-x: hidden; }
        
        /* วิดีโอพื้นหลัง */
        #bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -1; filter: brightness(0.35) saturate(1.2); }
        
        /* Navigation */
        nav { background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(25px); height: 75px; padding: 0 60px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border); position: sticky; top: 0; z-index: 1000; box-shadow: 0 5px 30px rgba(0,0,0,0.6); }
        
        .btn-home {
            padding: 10px 28px;
            background: rgba(63, 211, 255, 0.08);
            border: 1.5px solid var(--primary);
            color: var(--primary);
            border-radius: 40px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .btn-home:hover { background: var(--primary); color: #000; box-shadow: 0 0 25px var(--primary); transform: scale(1.05); }

        /* Hero Banner (ใช้รูปปกของ Genshin Impact) */
        .hero { 
            height: 350px; 
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.95)), url('Genshin Impact/Home Genshin impact.jpg'); 
            background-size: cover; 
            background-position: center 30%; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            border-bottom: 1px solid var(--border);
        }
        .hero h1 { font-size: 48px; font-weight: 700; text-shadow: 0 0 30px rgba(63, 211, 255, 0.8); margin-bottom: 10px; letter-spacing: 2px; }
        .hero .slogan { color: var(--primary); font-size: 16px; font-weight: 600; letter-spacing: 4px; text-transform: uppercase; }
        
        /* Container & Glassmorphism */
        .archive-frame { max-width: 1250px; min-height: 600px; margin: -50px auto 100px auto; background: var(--glass); backdrop-filter: blur(40px); border-radius: 40px; border: 1px solid var(--border); display: flex; overflow: hidden; position: relative; z-index: 10; box-shadow: 0 25px 60px rgba(0,0,0,0.7), inset 0 0 30px rgba(255,255,255,0.02); }
        
        /* Sidebar Navigation */
        .archive-sidebar { width: 130px; padding: 50px 0; background: rgba(0, 0, 0, 0.5); border-right: 1px solid rgba(255, 255, 255, 0.05); display: flex; flex-direction: column; align-items: center; gap: 25px; }
        .side-item { width: 85px; height: 85px; background: rgba(255, 255, 255, 0.03); border-radius: 22px; display: flex; flex-direction: column; justify-content: center; align-items: center; cursor: pointer; transition: 0.4s; text-decoration: none; border: 1px solid transparent; }
        .side-item:hover { background: rgba(255, 255, 255, 0.08); transform: translateY(-3px); }
        .side-item.active { border-color: var(--primary); background: rgba(63, 211, 255, 0.15); box-shadow: 0 0 20px rgba(63, 211, 255, 0.2); transform: scale(1.05); }
        .side-item span { font-size: 11px; margin-top: 10px; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .side-item.active span { color: var(--primary); }
        
        .archive-main { flex-grow: 1; padding: 50px 60px; }
        .main-header { margin-bottom: 40px; border-left: 5px solid var(--primary); padding-left: 20px; }
        .main-header h2 { font-size: 24px; font-weight: 600; letter-spacing: 1px; }
        
        /* Grid Display System */
        .char-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 30px; }
        
        .char-square-btn { background: rgba(0, 0, 0, 0.4); border: 2px solid rgba(255, 255, 255, 0.08); border-radius: 25px; overflow: hidden; aspect-ratio: 0.8/1; position: relative; text-decoration: none; transition: 0.4s; display: flex; flex-direction: column; box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
        .char-square-btn:hover { transform: translateY(-12px); border-color: var(--primary); box-shadow: 0 15px 35px rgba(63, 211, 255, 0.35); }
        
        .char-img-box { width: 100%; height: 100%; background: #080808; }
        .char-img-box img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .char-square-btn:hover .char-img-box img { transform: scale(1.08); }
        
        .element-overlay { position: absolute; top: 15px; left: 15px; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(8px); color: var(--primary); padding: 6px 12px; border-radius: 10px; font-size: 11px; font-weight: 700; z-index: 2; border: 1px solid rgba(63, 211, 255, 0.4); text-transform: uppercase; }
        .char-name-label { position: absolute; bottom: 0; width: 100%; background: linear-gradient(transparent, rgba(0,0,0,0.95) 40%, #000); padding: 30px 15px 15px; text-align: center; font-size: 15px; font-weight: 600; color: #fff; letter-spacing: 0.5px; z-index: 2; }
        
        /* Floating Action Button */
        .floating-btn { position: fixed; bottom: 40px; right: 40px; width: 70px; height: 70px; background: linear-gradient(135deg, var(--primary), #9b59ff); border-radius: 50%; display: flex; justify-content: center; align-items: center; box-shadow: 0 10px 30px rgba(63, 211, 255, 0.4); z-index: 1000; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: 2px solid #fff; text-decoration: none; }
        .floating-btn:hover { transform: scale(1.15) rotate(90deg); box-shadow: 0 0 40px var(--primary); }
        
        /* Animations */
        .animate-in { animation: fadeInUp 0.6s ease forwards; opacity: 0; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .loading-overlay { opacity: 0.2; pointer-events: none; transition: 0.4s; filter: blur(2px); }

        .char-square-btn:nth-child(1) { animation-delay: 0.05s; }
        .char-square-btn:nth-child(2) { animation-delay: 0.1s; }
        .char-square-btn:nth-child(3) { animation-delay: 0.15s; }
        .char-square-btn:nth-child(4) { animation-delay: 0.2s; }
        .char-square-btn:nth-child(5) { animation-delay: 0.25s; }
    </style>
</head>
<body>
    <video autoplay muted loop playsinline id="bg-video">
        <source src="Video/Imaginary%20Tree.mp4" type="video/mp4">
    </video>

    <nav>
        <div style="font-size: 20px; font-weight: 700; color: #3fd3ff; letter-spacing: 2px;">HOYOVERSE <span style="color:#fff; font-weight:300;">ARCHIVE</span></div>
        <a href="index.php" class="btn-home">กลับหน้าหลัก</a>
    </nav>

    <div class="hero">
        <h1>Genshin Impact Archive</h1>
        <p class="slogan">"Every journey has its final day. Don't rush"</p>
    </div>

    <div class="archive-frame">
        <div class="archive-sidebar">
            <div class="side-item <?php echo ($current_cat === 'character') ? 'active' : ''; ?>" onclick="switchCat('character', this)">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="<?php echo ($current_cat === 'character') ? '#3fd3ff' : '#666'; ?>"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <span>Travelers</span>
            </div>
            <div class="side-item <?php echo ($current_cat === 'item') ? 'active' : ''; ?>" onclick="switchCat('item', this)">
                <svg width="25" height="25" viewBox="0 0 24 24" fill="<?php echo ($current_cat === 'item') ? '#3fd3ff' : '#666'; ?>"><path d="M21 16.5C21 16.88 20.79 17.21 20.47 17.38L12.57 21.82C12.41 21.94 12.21 22 12 22C11.79 22 11.59 21.94 11.43 21.82L3.53 17.38C3.21 17.21 3 16.88 3 16.5V7.5C3 7.12 3.21 6.79 3.53 6.62L11.43 2.18C11.59 2.06 11.79 2 12 2C12.21 2 12.41 2.06 12.57 2.18L20.47 6.62C20.79 6.79 21 7.12 21 7.5V16.5Z"/></svg>
                <span>Weapons</span>
            </div>
            <div class="side-item <?php echo ($current_cat === 'affiliation') ? 'active' : ''; ?>" onclick="switchCat('affiliation', this)">
                <svg width="25" height="25" viewBox="0 0 24 24" fill="<?php echo ($current_cat === 'affiliation') ? '#3fd3ff' : '#666'; ?>"><path d="M12 2L4.5 20.29L5.21 21L12 18L18.79 21L19.5 20.29L12 2Z"/></svg>
                <span>Factions</span>
            </div>
        </div>

        <div class="archive-main">
            <div class="main-header"><h2 id="cat-title"><?php echo htmlspecialchars($display_title); ?></h2></div>
            <div class="char-grid" id="data-container">
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $imgs = explode(',', $row['image']);
                        $raw_icon = !empty($imgs[0]) ? trim($imgs[0]) : 'no-image.jpg';
                        $safe_icon = str_replace(' ', '%20', $raw_icon); 
                        
                        $meta_label = ($current_cat === 'character') ? $row['rarity'] . " | " . $row['element'] : (($current_cat === 'item') ? $row['rarity'] . " | " . $row['path_type'] : "ผู้นำ: " . $row['leader']);
                        ?>
                        <a href="archive_detail.php?id=<?php echo (int)$row['id']; ?>" class="char-square-btn animate-in">
                            <div class="element-overlay"><?php echo htmlspecialchars($meta_label); ?></div>
                            <div class="char-img-box"><img src="Image/<?php echo htmlspecialchars($safe_icon); ?>" loading="lazy"></div>
                            <div class="char-name-label"><?php echo htmlspecialchars($row['name']); ?></div>
                        </a>
                        <?php
                    }
                } else {
                    echo '<div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">';
                    echo '  <div style="font-size: 40px; margin-bottom: 15px; color: #3fd3ff;">✧</div>';
                    echo '  <p style="color: #888; font-size: 16px; letter-spacing: 1px;">ยังไม่มีข้อมูลจดหมายเหตุในหมวดหมู่นี้</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <a href="submit_info.php" class="floating-btn" title="เพิ่มข้อมูลใหม่">
        <svg viewBox="0 0 24 24" width="32" height="32" fill="#fff"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
    </a>

    <script>
    function switchCat(cat, element) {
        const container = document.getElementById('data-container');
        const title = document.getElementById('cat-title');
        
        container.classList.add('loading-overlay');
        
        // ชี้ไปที่ไฟล์ genshin.php
        fetch(`genshin.php?cat=${cat}&ajax=1`)
            .then(response => response.text())
            .then(data => {
                container.innerHTML = data;
                container.classList.remove('loading-overlay');
                
                // อัปเดตหัวข้อให้ตรงกับ Genshin Impact
                const titles = { 
                    'character': 'รายชื่อนักเดินทาง (Characters)', 
                    'item': 'คลังแสงและอาวุธ (Weapons)', 
                    'affiliation': 'เมืองและองค์กร (Factions)' 
                };
                title.innerText = titles[cat];
                
                // จัดการสถานะ Sidebar สีฟ้า (Primary)
                document.querySelectorAll('.side-item').forEach(el => {
                    el.classList.remove('active');
                    el.querySelector('svg').setAttribute('fill', '#666');
                });
                element.classList.add('active');
                element.querySelector('svg').setAttribute('fill', '#3fd3ff'); 
                
                window.history.pushState({cat: cat}, '', `genshin.php?cat=${cat}`);
            })
            .catch(err => { 
                console.error(err); 
                container.classList.remove('loading-overlay'); 
            });
    }
    </script>
</body>
</html>