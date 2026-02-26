<?php
session_start();
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>miHoYo History - Tech Otakus Save the World</title>
    <link rel="icon" type="image/png" href="Honaki Impact 3/The Queen is not pleased with this..png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Thai', sans-serif; }
        
        body, html { height: 100%; width: 100%; color: #ffffff; line-height: 1.8; background: #000; }

        /* --- วิดีโอพื้นหลัง --- */
        #bg-video {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            object-fit: cover; z-index: -1; filter: brightness(0.3); 
        }

        /* --- Navigation Bar --- */
        nav {
            background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(15px);
            height: 70px; padding: 0 50px; display: flex;
            justify-content: space-between; align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky; top: 0; z-index: 100;
        }
        .nav-logo img { height: 35px; }
        .btn-back {
            padding: 8px 22px; background: rgba(63, 211, 255, 0.1);
            border: 1.5px solid #3fd3ff; color: #3fd3ff; border-radius: 30px;
            text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s;
        }
        .btn-back:hover { background: #3fd3ff; color: #000; box-shadow: 0 0 20px #3fd3ff; }

        /* --- Hero Section (Red Circle Area) --- */
        .hero {
            height: 400px;
            background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.8)), url('Honaki Impact 3/BG HoYoverse.jpeg');
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; margin-bottom: 50px;
        }
        .hero h1 { font-size: 55px; letter-spacing: 3px; text-shadow: 0 0 20px rgba(0,0,0,0.8); }
        .hero p { color: #3fd3ff; font-weight: 600; letter-spacing: 5px; }

        /* --- Timeline Container --- */
        .container { max-width: 1000px; margin: 0 auto 100px auto; padding: 0 20px; }

        /* --- Timeline Item (Blue Circle Area) --- */
        .timeline-item {
            background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 25px;
            margin-bottom: 30px; overflow: hidden; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .timeline-header {
            padding: 30px 40px; display: flex; justify-content: space-between;
            align-items: center; cursor: pointer;
        }
        .timeline-header:hover { background: rgba(63, 211, 255, 0.08); }

        .time-info { display: flex; align-items: center; gap: 30px; }
        .year-text { color: #3fd3ff; font-weight: 700; font-size: 24px; min-width: 120px; }
        .event-text { font-size: 20px; font-weight: 600; color: #fdfdfd; }

        .arrow { width: 24px; height: 24px; fill: #3fd3ff; transition: 0.5s ease; }

        /* Details Section */
        .timeline-details { max-height: 0; overflow: hidden; transition: max-height 0.8s ease; background: rgba(0, 0, 0, 0.2); }
        .details-inner { padding: 40px; border-top: 1px solid rgba(255,255,255,0.05); }

        .sub-header { color: #3fd3ff; font-size: 18px; font-weight: 600; margin-bottom: 10px; border-left: 4px solid #3fd3ff; padding-left: 15px; }
        .details-inner p { color: #d1d1d1; font-size: 16px; margin-bottom: 25px; text-align: justify; }
        .details-inner ul { margin-bottom: 25px; list-style: none; padding-left: 20px; }
        .details-inner li { color: #d1d1d1; margin-bottom: 10px; position: relative; }
        .details-inner li::before { content: '✦'; position: absolute; left: -20px; color: #3fd3ff; }

        .details-inner img {
            width: 100%; border-radius: 15px; border: 1px solid rgba(63, 211, 255, 0.2);
            box-shadow: 0 15px 40px rgba(0,0,0,0.6); margin-top: 10px;
        }

        /* Active State */
        .timeline-item.active { border-color: #3fd3ff; box-shadow: 0 0 30px rgba(63, 211, 255, 0.15); }
        .timeline-item.active .timeline-details { max-height: 2000px; }
        .timeline-item.active .arrow { transform: rotate(180deg); }

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
        <a href="index.php" class="btn-back">กลับหน้าหลัก</a>
    </nav>

    <div class="hero">
        <h1>miHoYo History</h1>
        <p>TECH OTAKUS SAVE THE WORLD</p>
    </div>

    <div class="container">

        <div class="timeline-item">
            <div class="timeline-header" onclick="toggleItem(this)">
                <div class="time-info">
                    <span class="year-text">ปี 2011</span>
                    <span class="event-text">รุ่งอรุณแห่งความฝันในหอพัก</span>
                </div>
                <svg class="arrow" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
            </div>
            <div class="timeline-details">
                <div class="details-inner">
                    <div class="sub-header">จุดเริ่มต้น</div>
                    <p>ไม่ได้เกิดขึ้นในออฟฟิศหรูหรา แต่เริ่มขึ้นในหอพักนักศึกษาของ <strong>มหาวิทยาลัย Shanghai Jiao Tong</strong> โดยนักศึกษาคณะวิทยาการคอมพิวเตอร์ 3 คน ได้แก่ <strong>Cai Haoyu (Hugh Tsai), Liu Wei (Forrest Liu) และ Luo Yuhao</strong> พวกเขาแชร์ความหลงใหลเดียวกันในวัฒนธรรม ACG (Anime, Comic, Games) และมีความเชื่อมั่นว่า "เทคโนโลยี" สามารถยกระดับเนื้อหาแอนิเมชันให้เข้าถึงหัวใจคนได้</p>
                    
                    <div class="sub-header">อุปสรรคและทุนสร้าง</div>
                    <p>พวกเขาเริ่มต้นด้วยทุนสนับสนุนจากภาครัฐสำหรับสตาร์ทอัพเพียง <strong>100,000 หยวน (ประมาณ 5 แสนบาท)</strong> และใช้ชื่อสโลแกนว่า <strong>"Tech Otakus Save the World"</strong> ซึ่งในตอนนั้นผู้คนส่วนใหญ่ยังไม่เข้าใจในความหมายของคำว่า "Otaku" ในเชิงบวกนัก แต่พวกเขามุ่งมั่นที่จะพิสูจน์ว่าความรักในสิ่งที่ทำบวกกับความเชี่ยวชาญด้านเทคโนโลยีจะสร้างสิ่งที่ยิ่งใหญ่ได้</p>
                    <img src="Honaki Impact 3/miHoyo.jpeg" alt="miHoYo Founders">
                </div>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-header" onclick="toggleItem(this)">
                <div class="time-info">
                    <span class="year-text">ปี 2012</span>
                    <span class="event-text">FlyMe2theMoon และลูกสาวคนแรก</span>
                </div>
                <svg class="arrow" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
            </div>
            <div class="timeline-details">
                <div class="details-inner">
                    <div class="sub-header">การปรากฏตัวครั้งแรก</div>
                    <p>หลังจากการลองผิดลองถูก พวกเขาปล่อยเกมแนว Action-Puzzle ชื่อ <strong>FlyMe2theMoon</strong> ลงบนระบบ iOS ซึ่งเป็นโปรเจกต์ที่ทำให้โลกได้รู้จักกับ <strong>Kiana Kaslana</strong> ตัวละครผมสีเงินที่เปรียบเสมือน "ลูกสาวคนแรก" และกลายเป็นสัญลักษณ์ (Icon) หลักของบริษัทมาจนถึงปัจจุบัน</p>
                    
                    <div class="sub-header">แรงบันดาลใจ</div>
                    <p>ชื่อเกมได้รับแรงบันดาลใจโดยตรงจากเพลงปิดของอนิเมะเรื่อง <strong>Neon Genesis Evangelion</strong> ซึ่งเป็นแรงขับเคลื่อนสำคัญในการสร้างสรรค์งานของทีม แม้ในตอนนั้นทีมงานจะมีขนาดจิ๋ว แต่คุณภาพของงานศิลปะและดนตรีก็ได้แสดงให้เห็นถึง "จิตวิญญาณ" ที่แตกต่างจากเกมมือถือทั่วไปในยุคนั้น</p>
                    <img src="Honaki Impact 3/FlyMe2theMoon.jpg" alt="FlyMe2theMoon">
                </div>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-header" onclick="toggleItem(this)">
                <div class="time-info">
                    <span class="year-text">ปี 2014</span>
                    <span class="event-text">Guns Girl Z – ความสำเร็จและการลงหลักปักฐาน</span>
                </div>
                <svg class="arrow" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
            </div>
            <div class="timeline-details">
                <div class="details-inner">
                    <div class="sub-header">ก้าวกระโดดครั้งสำคัญ</div>
                    <p>พวกเขาเปิดตัว <strong>Guns Girl Z (Houkai Gakuen 2)</strong> ในรูปแบบ Side-scrolling Shooter 2D เกมนี้ประสบความสำเร็จอย่างถล่มทลายในประเทศจีนและเริ่มขยายไปสู่ญี่ปุ่นและเอเชียตะวันออกเฉียงใต้</p>
                    
                    <div class="sub-header">การสร้างจักรวาล</div>
                    <p>เกมนี้คือรากฐานสำคัญของ <strong>จักรวาล Honkai</strong> โดยเริ่มมีการแนะนำตัวละครหลักอย่าง Raiden Mei และ Bronya Zaychik ความสำเร็จนี้ทำให้ miHoYo สามารถย้ายออกจากหอพักและจัดตั้งบริษัทอย่างเป็นทางการ พร้อมจ้างพนักงานเพิ่มขึ้นเพื่อเตรียมตัวสำหรับโปรเจกต์ที่ "เป็นไปไม่ได้" ในสายตาคนอื่น</p>
                    <img src="Honaki Impact 3/Houkai Gakuen 2.jpg" alt="Guns Girl Z Success">
                </div>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-header" onclick="toggleItem(this)">
                <div class="time-info">
                    <span class="year-text">ปี 2016</span>
                    <span class="event-text">Honkai Impact 3rd – ท้าทายขีดจำกัด</span>
                </div>
                <svg class="arrow" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
            </div>
            <div class="timeline-details">
                <div class="details-inner">
                    <div class="sub-header">การปฏิวัติเทคโนโลยี</div>
                    <p>miHoYo เดิมพันครั้งใหญ่ด้วยการพัฒนา <strong>Action RPG 3D</strong> เต็มรูปแบบบนมือถือ ด้วยความเชื่อที่ว่า "เทคโนโลยีต้องนำหน้า" พวกเขาจึงพัฒนาเทคนิค <strong>Cell-shading</strong> ที่ทำให้ภาพ 3D ดูสวยงามลุ่มลึกเหมือนอนิเมะคุณภาพสูง</p>
                    
                    <div class="sub-header">มากกว่าแค่เกม</div>
                    <p>ขึ้นชื่อเรื่องการเล่าเรื่องที่เข้มข้น และการก่อตั้งทีม <strong>HOYO-MiX</strong> เพื่อสร้างสรรค์ดนตรีประกอบ รวมถึงการผลิตแอนิเมชันขนาดสั้นที่กลายเป็นกระแสไวรัลไปทั่วโลก (เช่น "Final Lesson") ส่งผลให้ชื่อของ miHoYo กลายเป็นแบรนด์ที่แฟนคลับทั่วโลกให้การยอมรับสูงสุด</p>
                    <img src="Honaki Impact 3/Home Honkai impact 3.png" alt="Honkai Impact 3rd Breakthrough">
                </div>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-header" onclick="toggleItem(this)">
                <div class="time-info">
                    <span class="year-text">ปี 2020</span>
                    <span class="event-text">Genshin Impact – การปฏิวัติวงการ Open World</span>
                </div>
                <svg class="arrow" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
            </div>
            <div class="timeline-details">
                <div class="details-inner">
                    <div class="sub-header">เดิมพันหมดหน้าตัก</div>
                    <p>ทุ่มงบประมาณพัฒนาและตลาดรวมกว่า <strong>100 ล้านดอลลาร์สหรัฐ</strong> เพื่อสร้างเกม Open World ที่รองรับ <strong>Cross-platform</strong> ซึ่งเป็นสิ่งที่ท้าทายและมีความเสี่ยงสูงมากหากล้มเหลว</p>
                    
                    <div class="sub-header">ความสำเร็จระดับโลก</div>
                    <p>Genshin Impact กลายเป็นปรากฏการณ์ระดับโลกทันที โดยคืนทุนได้ภายในเวลาไม่ถึง 2 สัปดาห์ เกมนำเสนอโลกที่กว้างใหญ่ ระบบธาตุที่ลุ่มลึก และดนตรีที่ผสมผสานวัฒนธรรมทั่วโลก เป็นการประกาศว่า "Tech Otakus" ได้ก้าวขึ้นสู่ระดับสูงสุดของอุตสาหกรรมแล้ว</p>
                    <img src="Genshin Impact/Home Genshin impact.jpg" alt="Genshin Impact World">
                </div>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-header" onclick="toggleItem(this)">
                <div class="time-info">
                    <span class="year-text">ปี 2023-24</span>
                    <span class="event-text">ยุคสมัยของ HoYoverse และก้าวสู่อนาคต</span>
                </div>
                <svg class="arrow" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
            </div>
            <div class="timeline-details">
                <div class="details-inner">
                    <div class="sub-header">การรีแบรนด์ระดับโลก</div>
                    <p>miHoYo ประกาศใช้แบรนด์ <strong>HoYoverse</strong> สำหรับตลาดสากล เพื่อมุ่งเน้นการสร้างประสบการณ์ความบันเทิงที่ครอบคลุมทั้งเกม, อนิเมะ, นิยาย, และเทคโนโลยีล้ำสมัย</p>
                    
                    <div class="sub-header">ความสำเร็จต่อเนื่อง</div>
                    <ul>
                        <li><strong>2023:</strong> เปิดตัว <strong>Honkai: Star Rail</strong> คว้ารางวัล Game of the Year จากหลายสถาบัน</li>
                        <li><strong>2024:</strong> เปิดตัว <strong>Zenless Zone Zero</strong> เน้นสไตล์ Urban Fantasy</li>
                    </ul>

                    <div class="sub-header">วิสัยทัศน์ในอนาคต</div>
                    <p>ลงทุนในเทคโนโลยี AI, Cloud Gaming และการวิจัยด้าน <strong>พลังงานนิวเคลียร์ฟิวชัน</strong> เพื่อเป้าหมายในการสร้าง "โลกเสมือนจริงที่มีผู้ใช้งานหลักพันล้านคนภายในปี 2030" ตามปณิธานที่จะใช้เทคโนโลยีสร้างความสุขให้ผู้คนทั่วโลก</p>
                    <img src="Honaki Impact 3/Home HoYoverse.jpg" alt="HoYoverse Vision 2030">
                </div>
            </div>
        </div>

    </div>

    <script>
        function toggleItem(header) {
            const item = header.parentElement;
            item.classList.toggle('active');
        }
    </script>
</body>
</html>