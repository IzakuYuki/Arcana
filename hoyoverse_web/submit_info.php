<?php
session_start();
include 'db.php';

// [MAID PROTOCOL] ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
}

$response_script = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. รับค่าพื้นฐาน
    $name = $_POST['name'] ?? '';
    $game = $_POST['game_select'] ?? '';
    $category = $_POST['category_select'] ?? '';
    $lore = $_POST['lore'] ?? '';
    $submitted_by = $_SESSION['username'];

    // 2. รับค่าที่แยกตามหมวดหมู่ (จัดสรรลง 14 คอลัมน์)
    $element = ''; $path_type = ''; $rarity = ''; $affiliation = '';
    $leader = ''; $location = ''; $relationship = '';

    if ($category === 'character') {
        $element = $_POST['char_element'] ?? '';
        $path_type = $_POST['char_path'] ?? '';
        $rarity = $_POST['char_rarity'] ?? '';
        $affiliation = $_POST['char_affiliation'] ?? ''; // รับค่าจาก Hidden Input ของระบบ Tag
    } elseif ($category === 'affiliation') {
        $leader = $_POST['faction_leader'] ?? '';
        $location = $_POST['faction_location'] ?? '';
        $relationship = $_POST['faction_relationship'] ?? '';
    } elseif ($category === 'item') {
        $path_type = $_POST['item_type'] ?? ''; 
        $rarity = $_POST['item_rarity'] ?? '';
    }

    // 3. ตรวจสอบข้อมูลซ้ำ
    $check_stmt = $conn->prepare("SELECT id FROM characters WHERE name = ? AND game = ? AND category = ?");
    $check_stmt->bind_param("sss", $name, $game, $category);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $response_script = "
            Swal.fire({
                icon: 'error',
                title: 'พบข้อมูลซ้ำ!',
                text: 'มีข้อมูลนี้อยู่ในฐานข้อมูลแล้วค่ะนายท่าน',
                confirmButtonText: 'รับทราบ'
            }).then(() => { window.history.back(); });";
        $check_stmt->close();
    } else {
        $check_stmt->close();
        
        $role = $_SESSION['role'] ?? 'user';
        $status = ($role === 'admin') ? 'approved' : 'pending';

        // 4. ระบบจัดการอัปโหลดภาพแบบป้องกันรูปซ้ำ (Smart De-duplication)
        $images = [];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
                if ($_FILES['images']['error'][$k] !== UPLOAD_ERR_OK) continue;
                
                $file_ext = strtolower(pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION));
                $file_mime = mime_content_type($tmp);

                if (in_array($file_mime, $allowed_types)) {
                    $file_hash = md5_file($tmp); 
                    $new_fn = $file_hash . "." . $file_ext;
                    $target_path = "Image/" . $new_fn;

                    if (!file_exists($target_path)) {
                        move_uploaded_file($tmp, $target_path);
                    }
                    $images[] = $new_fn;
                }
            }
        }
        $all_imgs = implode(",", $images);

        // 5. บันทึกข้อมูล (14 พารามิเตอร์)
        $sql = "INSERT INTO characters 
                (name, game, category, element, path_type, rarity, affiliation, leader, location, relationship, lore_detail, image, status, submitted_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssss", 
            $name, $game, $category, $element, $path_type, $rarity, $affiliation, 
            $leader, $location, $relationship, $lore, $all_imgs, $status, $submitted_by
        );
        
        if ($stmt->execute()) {
            $response_script = "
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกสำเร็จ!',
                    text: 'ข้อมูลเข้าสู่ระบบรอการตรวจสอบแล้วค่ะ',
                    confirmButtonText: 'ยอดเยี่ยม'
                }).then(() => { window.location.href='index.php'; });";
        } else {
            $response_script = "Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้ค่ะ', 'error');";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoYoverse - Submit Archive</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai'; }
        
        body, html { height: 100%; color: #fff; background: transparent; overflow-x: hidden; }
        #bg-video { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -1; filter: brightness(0.4); }

        .swal2-popup { background: rgba(20, 20, 25, 0.95) !important; backdrop-filter: blur(15px); border: 1px solid rgba(63, 211, 255, 0.3); border-radius: 25px !important; color: #fff !important; }
        .swal2-title { color: #3fd3ff !important; }

        nav { background: rgba(0, 0, 0, 0.75); height: 70px; padding: 0 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(63, 211, 255, 0.4); position: sticky; top: 0; z-index: 100; backdrop-filter: blur(15px); }
        .btn-frame { padding: 8px 25px; background: rgba(63, 211, 255, 0.1); border: 2px solid #3fd3ff; color: #3fd3ff; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; }
        .btn-frame:hover { background: #3fd3ff; color: #000; box-shadow: 0 0 20px #3fd3ff; transform: translateY(-2px); }

        .container { max-width: 850px; margin: 40px auto; padding-bottom: 50px; }
        .section-box { background: rgba(15, 15, 20, 0.7); padding: 35px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 30px; backdrop-filter: blur(20px); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .section-title { font-size: 19px; margin-bottom: 25px; color: #ff3399; border-left: 5px solid #ff3399; padding-left: 15px; font-weight: 600; }

        label { display: block; margin: 15px 0 8px; color: #3fd3ff; font-weight: 600; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 14px; background: rgba(0,0,0,0.6); border: 1px solid rgba(63,211,255,0.3); border-radius: 12px; color: #fff; outline: none; transition: 0.3s; }
        input:focus, select:focus, textarea:focus { border-color: #3fd3ff; box-shadow: 0 0 10px rgba(63,211,255,0.2); }
        
        .btn-submit { width: 100%; padding: 18px; background: linear-gradient(45deg, #3fd3ff, #9b59ff); border: 2px solid #fff; border-radius: 15px; font-weight: 700; color: #fff; cursor: pointer; transition: 0.4s; font-size: 16px; margin-top: 10px; }
        .btn-submit:hover { transform: scale(1.02); box-shadow: 0 0 25px rgba(63, 211, 255, 0.4); border-color: #3fd3ff; }

        /* --- สไตล์ของ Multi-Tag System --- */
        .tag-input-wrapper { display: flex; flex-wrap: wrap; align-items: center; padding: 10px; background: rgba(0, 0, 0, 0.6); border: 1px solid rgba(63, 211, 255, 0.3); border-radius: 12px; min-height: 52px; gap: 8px; position: relative; }
        .tag-badge { background: rgba(63, 211, 255, 0.15); border: 1px solid #3fd3ff; color: #fff; padding: 5px 12px; border-radius: 10px; display: flex; align-items: center; gap: 10px; font-size: 13px; animation: fadeIn 0.3s ease; }
        .tag-badge span { cursor: pointer; color: #ff3399; font-weight: bold; font-size: 18px; }
        #tag-input-field { flex: 1; min-width: 150px; background: transparent !important; border: none !important; box-shadow: none !important; }
        #tag-suggestions { position: absolute; top: 105%; left: 0; width: 100%; background: rgba(20, 20, 25, 0.98); border: 1px solid #3fd3ff; border-radius: 12px; z-index: 100; display: none; max-height: 180px; overflow-y: auto; backdrop-filter: blur(15px); }
        .suggestion-item { padding: 12px 18px; cursor: pointer; transition: 0.2s; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .suggestion-item:hover { background: rgba(63, 211, 255, 0.2); color: #3fd3ff; }

        #image-preview-container { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px; }
        .preview-img-box { width: 100px; height: 100px; border-radius: 15px; overflow: hidden; border: 2px solid #3fd3ff; background: #000; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
        .preview-img-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .category-group { display: none; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body>
    <video autoplay muted loop id="bg-video">
        <source src="Video/Imaginary Tree.mp4" type="video/mp4">
    </video>

    <nav>
        <div style="font-weight:600; color:#3fd3ff; letter-spacing: 1px;">HOYOVERSE ARCHIVE COMMAND</div>
        <a href="index.php" class="btn-frame">← กลับหน้าหลัก</a>
    </nav>

    <div class="container">
        <form action="" method="POST" enctype="multipart/form-data" id="submit-form">
            <div class="section-box">
                <div class="section-title">✦ ข้อมูลพื้นฐานการจัดเก็บ</div>
                <label>เลือกโปรเจกต์เกม</label>
                <select name="game_select" id="game_select" onchange="updateUI()" required>
                    <option value="Honkai Impact 3rd">Honkai Impact 3rd</option>
                    <option value="Genshin Impact">Genshin Impact</option>
                    <option value="Honkai: Star Rail">Honkai: Star Rail</option>
                    <option value="Zenless Zone Zero">Zenless Zone Zero</option>
                </select>
                <label>หมวดหมู่จดหมายเหตุ</label>
                <select name="category_select" id="category_select" onchange="updateUI()" required>
                    <option value="character">ตัวละคร (Character)</option>
                    <option value="item">ไอเทม / อาวุธ (Item)</option>
                    <option value="affiliation">สังกัด / องค์กร (Faction)</option>
                </select>
                <label>ชื่อ (หัวข้อหลัก)</label>
                <input type="text" name="name" placeholder="ระบุชื่อที่ต้องการบันทึก..." required>
            </div>

            <div id="sec-character" class="section-box category-group">
                <div class="section-title">❖ รายละเอียดตัวละครเฉพาะกิจ</div>
                <label>ความหายาก (Rarity)</label>
                <select name="char_rarity">
                    <option value="5 ดาว">5 ดาว</option><option value="4 ดาว">4 ดาว</option>
                    <option value="S-Rank">S-Rank</option><option value="A-Rank">A-Rank</option>
                    <option value="B-Rank">B-Rank</option>
                </select>
                <label id="lbl-char-element">ประเภทการต่อสู้ (Combat Type)</label>
                <input type="text" name="char_element" placeholder="ระบุธาตุหรือการโจมตี">
                <label id="lbl-char-path">สายอาชีพ / Path</label>
                <input type="text" name="char_path" placeholder="ระบุเส้นทางหรือประเภท">
                
                <label>สังกัด / องค์กรแม่ (Affiliation)</label>
                <div class="tag-input-wrapper" id="tag-container">
                    <div id="tags-list" style="display: flex; flex-wrap: wrap; gap: 8px;"></div>
                    <input type="text" id="tag-input-field" placeholder="พิมพ์ชื่อสังกัดแล้วกด Enter...">
                    <input type="hidden" name="char_affiliation" id="real-affiliation-input">
                    <div id="tag-suggestions"></div>
                </div>
            </div>

            <div id="sec-affiliation" class="section-box category-group">
                <div class="section-title">◈ ข้อมูลองค์กรและสังกัด</div>
                <label>ผู้นำสูงสุด (Leader)</label>
                <input type="text" name="faction_leader" placeholder="ระบุชื่อบุคคลสำคัญ">
                <label>ที่ตั้งฐานบัญชาการ (Location)</label>
                <input type="text" name="faction_location" placeholder="ระบุภูมิภาคหรือเมือง">
                <label>สถานะความสัมพันธ์</label>
                <select name="faction_relationship">
                    <option value="เป็นกลาง">เป็นกลาง (Neutral)</option>
                    <option value="พันธมิตร">พันธมิตร (Ally)</option>
                    <option value="ศัตรู">ศัตรู (Hostile)</option>
                </select>
            </div>

            <div id="sec-item" class="section-box category-group">
                <div class="section-title">✧ ข้อมูลคลังแสงและไอเทม</div>
                <label>ประเภททรัพยากร</label>
                <input type="text" name="item_type" placeholder="เช่น: Light Cone, Sword, Artifact">
                <label>ระดับความล้ำค่า</label>
                <select name="item_rarity">
                    <option value="6 ดาว">6 ดาว</option><option value="5 ดาว">5 ดาว</option>
                    <option value="4 ดาว">4 ดาว</option><option value="3 ดาว">3 ดาว</option>
                    <option value="S-Rank">S-Rank</option><option value="A-Rank">A-Rank</option>
                    <option value="B-Rank">B-Rank</option>
                </select>
            </div>

            <div class="section-box">
                <div class="section-title">✦ บันทึกเนื้อเรื่องและข้อมูลสื่อ</div>
                <label>เนื้อเรื่อง (Lore / History)</label>
                <textarea name="lore" rows="6" placeholder="ระบุประวัติหรือข้อมูลเชิงลึก..." required></textarea>
                <label>อัปโหลดสื่อภาพ (ระบบป้องกันภาพซ้ำทำงานอัตโนมัติ)</label>
                <input type="file" name="images[]" id="image-upload" multiple accept="image/*" onchange="previewImages()">
                <div id="image-preview-container"></div>
            </div>

            <button type="submit" class="btn-submit">ยืนยันการบันทึกเข้าสู่จดหมายเหตุหลัก</button>
        </form>
    </div>

    <script>
        <?php if ($response_script) echo $response_script; ?>

        // --- ระบบ Multi-Tag Logic ---
        const allFactions = ["Schicksal", "St. Freya Academy", "Astral Express", "Stellaron Hunters", "Cunning Hares", "Belobog", "Anti-Entropy", "Flame-Chasers", "Ouroboros"];
        let selectedTags = [];

        const tagInput = document.getElementById('tag-input-field');
        const tagsList = document.getElementById('tags-list');
        const suggestionsBox = document.getElementById('tag-suggestions');
        const realInput = document.getElementById('real-affiliation-input');

        function addTag(tag) {
            tag = tag.trim();
            if (tag === "" || selectedTags.includes(tag)) return;
            selectedTags.push(tag);
            renderTags();
            tagInput.value = "";
            suggestionsBox.style.display = 'none';
        }

        window.removeTag = function(tag) {
            selectedTags = selectedTags.filter(t => t !== tag);
            renderTags();
        }

        function renderTags() {
            tagsList.innerHTML = selectedTags.map(tag => `
                <div class="tag-badge">${tag} <span onclick="removeTag('${tag}')">×</span></div>
            `).join('');
            realInput.value = selectedTags.join(',');
        }

        tagInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTag(tagInput.value);
            }
        });

        tagInput.addEventListener('input', () => {
            const val = tagInput.value.toLowerCase();
            if (val.length < 1) { suggestionsBox.style.display = 'none'; return; }
            const matches = allFactions.filter(f => f.toLowerCase().includes(val) && !selectedTags.includes(f));
            if (matches.length > 0) {
                suggestionsBox.innerHTML = matches.map(m => `<div class="suggestion-item" onclick="addTag('${m}')">${m}</div>`).join('');
                suggestionsBox.style.display = 'block';
            } else { suggestionsBox.style.display = 'none'; }
        });

        document.addEventListener('click', (e) => {
            if (!document.getElementById('tag-container').contains(e.target)) { suggestionsBox.style.display = 'none'; }
        });

        // --- ระบบ UI & Preview Logic ---
        function updateUI() {
            const game = document.getElementById('game_select').value;
            const cat = document.getElementById('category_select').value;
            document.querySelectorAll('.category-group').forEach(el => el.style.display = 'none');
            
            if (cat === 'character') {
                document.getElementById('sec-character').style.display = 'block';
                const lblE = document.getElementById('lbl-char-element');
                const lblP = document.getElementById('lbl-char-path');
                if (game === "Honkai: Star Rail") { lblE.innerText = "ธาตุการโจมตี (Combat Type)"; lblP.innerText = "เส้นทางแห่งดวงดาว (Path)"; }
                else if (game === "Genshin Impact") { lblE.innerText = "พลังวิชั่น (Vision)"; lblP.innerText = "ชนิดอาวุธที่ใช้"; }
                else if (game === "Zenless Zone Zero") { lblE.innerText = "ธาตุประจำตัว (Attribute)"; lblP.innerText = "รูปแบบการต่อสู้ (Specialty)"; }
                else { lblE.innerText = "ธาตุพลังงาน"; lblP.innerText = "ประเภทตัวละคร"; }
            } else if (cat === 'affiliation') {
                document.getElementById('sec-affiliation').style.display = 'block';
            } else if (cat === 'item') {
                document.getElementById('sec-item').style.display = 'block';
            }
        }

        function previewImages() {
            const container = document.getElementById('image-preview-container');
            const files = document.getElementById('image-upload').files;
            container.innerHTML = '';
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const box = document.createElement('div');
                    box.className = 'preview-img-box';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    box.appendChild(img);
                    container.appendChild(box);
                }
                reader.readAsDataURL(file);
            });
        }
        updateUI();
    </script>
</body>
</html>