-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 07:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hoyoverse_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `characters`
--

CREATE TABLE `characters` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `game` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `element` varchar(100) DEFAULT NULL,
  `path_type` varchar(100) DEFAULT NULL,
  `rarity` varchar(50) DEFAULT NULL,
  `affiliation` varchar(150) DEFAULT NULL,
  `leader` varchar(150) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `relationship` varchar(100) DEFAULT NULL,
  `lore_detail` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `submitted_by` varchar(100) NOT NULL,
  `likes_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `characters`
--

INSERT INTO `characters` (`id`, `name`, `game`, `category`, `element`, `path_type`, `rarity`, `affiliation`, `leader`, `location`, `relationship`, `lore_detail`, `image`, `status`, `submitted_by`, `likes_count`) VALUES
(1, 'Theresa Apocalypse', 'Honkai Impact 3rd', 'character', 'Lightning', 'PSY', 'S-Rank', 'Schicksal,St. Freya Academy', '', '', '', 'เทเรซ่า อะพอคคาลิปส์ คือโคลนหมายเลข A-310 ที่สร้างขึ้นโดยออตโต อะพอคคาลิปส์ โดยใช้ DNA ของคัลเลน คาสลาน่า ผสมกับยีนของฮงไก (วิชนุ) ทำให้เธอมีพละกำลังมหาศาลและร่างกายหยุดการเจริญเติบโตอยู่ที่อายุ 12 ปี เธอเป็นผู้ก่อตั้งสถาบันเซนต์เฟรย่าเพื่อฝึกฝนเหล่าวาลคีเรียด้วยความเมตตา แทนที่จะใช้เป็นเครื่องมือเหมือนองค์กรชิกซอลในอดีต ปัจจุบัน (ในเนื้อเรื่องส่วนหลัง) เธอได้รับตำแหน่งเป็นผู้บัญชาการ (Overseer) คนใหม่ขององค์กรชิกซอล', 'c9b7e465cc89cf89125e4d4272080380.png', 'approved', 'Arcana', 3),
(2, 'Bloodied Casket: Tough Love', 'Honkai Impact 3rd', 'item', '', 'Cross', '6 ดาว', '', '', '', '', 'เลื่อยยนต์พิฆาตที่ถูกซ่อนเอาไว้ภายใต้รูปลักษณ์ของโลงศพกางเขน อาวุธชิ้นนี้ถูกสร้างและดัดแปลงมาเพื่อให้เข้ากับสไตล์การต่อสู้ที่ดุดันของแวมไพร์สาว มันไม่เพียงแต่เป็นเครื่องมือที่ใช้ฉีกกระชากศัตรูที่ขวางทาง แต่ยังเป็นดั่งสัญลักษณ์แห่งคำสาบานสีเลือด และความรักอันทรหดที่เธอมีต่อกัปตัน (ผู้เล่น) ว่าจะคอยปกป้องเขาไปตลอดกาล', 'a20e3dcaf81726c7db6ea7a66e547d90.jpg', 'approved', 'IzakuYu', 1),
(5, 'Theresa Apocalypse', 'Honkai Impact 3rd', 'affiliation', '', '', '', '', 'Theresa Apocalypse', 'เมืองโซไค / ทำหน้าที่เป็นสาขาตะวันออกไกล', 'พันธมิตร', 'สถาบันเซนต์เฟรย่า (St. Freya Academy) ก่อตั้งขึ้นโดย เทเรซ่า อะพอคคาลิปส์ ทำหน้าที่เป็นศูนย์บัญชาการขององค์กรชิกซอล (Schicksal) สาขาตะวันออกไกล จุดประสงค์หลักของสถาบันนี้คือการฝึกฝนและบ่มเพาะเหล่าวาลคีเรียรุ่นใหม่ด้วยวิธีการที่มีมนุษยธรรม และให้ความสำคัญกับชีวิตของนักเรียนเป็นหลัก ซึ่งแตกต่างจากอุดมการณ์อันโหดร้ายและเย็นชาของศูนย์ใหญ่ชิกซอล สถาบันแห่งนี้จึงเป็นเสมือนบ้านหลังแรกและจุดเริ่มต้นการเดินทางของวาลคีเรียทีมหลักอย่าง เคียน่า, เมย์ และ โบรเนีย', '6bd91c3f9d524f0033d2302bccc66bb7.png', 'approved', 'Arcana', 1),
(6, 'Columbina', 'Genshin Impact', 'character', 'Hydro', 'Catalyst', '5 ดาว', 'Eleven Fatui Harbingers,Nod-Krai,Frost Moon', '', '', '', 'โคลัมบีน่า (Columbina) หรือเจ้าของโค้ดเนม \"Damselette\" คืออดีตผู้บริหารฟาตุยอันดับที่ 3 แห่งสเนซนายา รูปลักษณ์ภายนอกของเธอคือหญิงสาวที่ดูบอบบาง มักจะหลับตาและฮัมเพลงด้วยน้ำเสียงที่ไพเราะแต่ชวนหลอน\r\nแต่ข้อมูลอัปเดตล่าสุดเปิดเผยว่าชื่อที่แท้จริงของเธอคือ \"Hyposelenia\" เธอถือกำเนิดจากทะเลบรรพกาล และแท้จริงแล้วเธอคือหญิงสาวที่อยู่ในหน้าต่าง \"พรแห่งดวงจันทร์\" (Blessing of the Welkin Moon) ที่นักเดินทางคุ้นเคยกันดี! หลังจากที่เธอแยกตัวจากฟาตุยและเกิดการปะทะกับ Dottore เธอได้เดินทางข้ามเวลาและได้รับสืบทอดพลังจากสามพี่น้องแห่งดวงจันทร์ (Moon Sisters) จนในที่สุด โคลัมบีน่าก็ได้จุติใหม่กลายเป็นเทพธิดาแห่งดวงจันทร์องค์ใหม่ (Tri-lunar Goddess / Moon Goddess)', 'ddb4ca2c22732b25e9945506ab8765a4.jpg', 'approved', 'Arcana', 1),
(7, 'Nocturne\'s Curtain Cal', 'Genshin Impact', 'item', '', 'Catalyst', '5 ดาว', '', '', '', '', 'อาวุธเวทมนตร์โบราณที่มีรูปลักษณ์คล้ายกับม้วนคัมภีร์ศักดิ์สิทธิ์ที่แผ่ละอองแสงสีเงินอมน้ำเงินออกมาตลอดเวลา ตำนานกล่าวว่ามันไม่ได้ถูกตีขึ้นโดยช่างตีดาบคนใด แต่เป็นเศษเสี้ยวของ \"บันทึกบทเพลงสุดท้าย\" ของสามพี่น้องแห่งดวงจันทร์ (Moon Sisters) ก่อนที่ภัยพิบัติจะกลืนกินท้องฟ้าในยุคโบราณกาล\r\nเดิมทีมันถูกเก็บซ่อนไว้ในส่วนลึกสุดของซากปรักหักพังแห่ง Nod-Krai จนกระทั่ง โคลัมบีน่า (หรือในนาม Hyposelenia) ได้ใช้สายเลือดแห่งทะเลบรรพกาลปลุกพลังที่หลับใหลของมันให้ตื่นขึ้น อาวุธชิ้นนี้ทำหน้าที่เป็นเสมือนสื่อกลางที่เชื่อมต่อวิญญาณของเธอเข้ากับ \"ดวงจันทร์น้ำแข็ง (Frost Moon)\" โดยชื่อ \"Curtain Call (การปิดม่าน)\" นั้น สื่อถึงการบอกลาตัวตนเก่าในฐานะผู้บริหารฟาตุยอันดับที่ 3 และเปิดม่านการแสดงบทใหม่ในฐานะเทพธิดาแห่งดวงจันทร์ผู้กุมชะตาของโลก', '896c356dc03e0f7b92a64e983d5c7e18.png', 'approved', 'Arcana', 0),
(8, 'Hexenzirkel', 'Genshin Impact', 'affiliation', '', '', '', '', 'ปกครองร่วมกัน', 'ไม่เปิดเผยฐานที่มั่นหลัก', 'เป็นกลาง', 'สมาคมแม่มด (Hexenzirkel) คือองค์กรลับที่รวบรวมเหล่าสตรีผู้มีพลังเวทมนตร์ระดับสุดยอดเอาไว้ เป้าหมายหลักของพวกเธอคือการศึกษาความจริงของโลกและการสำรวจต้นไม้โลก (Irminsul) สมาชิกแต่ละคนจะใช้ \"โค้ดเนม\" แทนตัวเอง เช่น Alice (A) แม่ของคลี, Barbeloth (B) อาจารย์ของโมนา, Rhinedottir (R) หรือ Gold ผู้สร้างอัลเบโดและมังกรดูริน, Nicole (N) ผู้ชี้นำทางแห่งโชคชะตา, Andersdotter (M) นักเขียนนิทานชื่อดัง และ I. Ivanovna N. (J) ผู้เชี่ยวชาญด้านเวทมนตร์แห่งชีวิต\r\nในอดีตกาล สมาคมแม่มดเคยยกพลไปท้าดวลกับ Barbatos เทพแห่งลมที่ Mondstadt แต่ Barbatos เลือกที่จะยุติข้อพิพาทด้วยเสียงเพลงและสันติวิธี นับแต่นั้นมา สมาคมแม่มดจึงละทิ้งความบาดหมาง และเปลี่ยนมาจัด \"งานเลี้ยงน้ำชา\" ร่วมกันอย่างสงบสุขแทน แม้สมาชิกบางคนจะล้มหายตายจากไปตามกาลเวลา แต่เจตนารมณ์และเก้าอี้ของพวกเธอก็ยังคงถูกส่งต่อไปยังผู้สืบทอดเสมอ', '63de3db29cc5ec6407fd55a2dd45d9cb.png', 'approved', 'Arcana', 0),
(9, 'The Herta', 'Honkai: Star Rail', 'character', 'Ice', 'Erudition', '5 ดาว', 'Genius Society,สถานีอวกาศ Herta', '', '', '', 'The Herta หรือท่านเฮอร์ต้า คือสมาชิกผู้ทรงเกียรติหมายเลข 83 แห่งสมาคมอัจฉริยะ (Genius Society) และเป็นถึงภาคีแห่งเทพ (Emanator) ของเทพดาราแห่งปัญญา ตามปกติแล้วเธออาศัยอยู่ที่สุดชายขอบของจักรวาลอันห่างไกลและแทบจะไม่เคยปรากฏตัวให้ใครเห็น โดยมักจะใช้ \"หุ่นเชิด\" ที่สร้างเลียนแบบรูปลักษณ์วัยเยาว์ของตนเองออกไปจัดการธุระต่างๆ แทน\r\nเธอได้เผยโฉมที่แท้จริงซึ่งเต็มไปด้วยความงดงามและสติปัญญาอันไร้เทียมทาน การที่เธอปรากฏตัวด้วยร่างจริงย่อมหมายความว่ามีเรื่องสำคัญระดับจักรวาลที่ต้องจัดการด้วยตัวเอง ในการต่อสู้ เธอใช้ \"การตีความ (Interpretation)\" ของตนเองเพื่อขจัดอุปสรรค และแสวงหา \"แรงบันดาลใจ (Inspiration)\" เพื่อไขปริศนาที่แม้แต่เทพดาราก็อาจหาคำตอบไม่ได้', 'fed00c1f38f1c7fb988c0f4891354297.png', 'approved', 'Arcana', 1),
(10, 'Into the Unreachable Veil', 'Honkai: Star Rail', 'item', '', 'Erudition', '5 ดาว', '', '', '', '', 'Light Cone ประจำตัวชิ้นนี้ถูกออกแบบมาเพื่อ The Herta อย่างสมบูรณ์แบบ มันตอกย้ำถึงฐานะของเธอที่เป็นถึงสมาชิกหมายเลข 83 แห่งสมาคมอัจฉริยะ (Genius Society) และเป็นภาคีแห่งเทพ (Emanator) ของเทพดาราแห่งปัญญา\r\nชื่อของ Light Cone \"Into the Unreachable Veil\" หรือการก้าวล่วงเข้าสู่ม่านที่ไม่อาจเอื้อม สอดคล้องกับเป้าหมายสูงสุดของเธอในการแสวงหา \"แรงบันดาลใจ (Inspiration)\" เพื่อไขปริศนาและความจริงของจักรวาล ซึ่งเป็นคำถามที่ลึกล้ำเสียจนแม้แต่เหล่าเทพดารา (Aeons) ก็อาจจะยังไม่สามารถให้คำตอบได้ พลังของมันไม่เพียงแต่ช่วยเพิ่มโอกาสคริติคอลให้เธอ แต่เมื่อเธอปลดปล่อยท่าไม้ตาย มันยังช่วยขยายขีดความสามารถในการทำลายล้างของสกิลและท่าไม้ตายอย่างมหาศาล พร้อมทั้งช่วยฟื้นฟูแต้มสกิล (Skill Point) คืนให้กับทีมอีกด้วย', '7d743372a147338743c269bf80ade912.png', 'approved', 'Arcana', 0),
(11, 'Genius Society', 'Honkai: Star Rail', 'affiliation', '', '', '', '', 'Nous - เทพดาราแห่งปัญญา', 'กระจายตัวอยู่ทั่วจักรวาล (ไม่มีฐานที่มั่นหลักตายตัว แต่มีสถานที่สำคัญเช่น สถานีอวกาศ Herta หรือ ดาว Screwllum)', 'เป็นกลาง', 'สมาคมอัจฉริยะ (Genius Society) คือกลุ่มที่รวบรวมสุดยอดมันสมองระดับหัวกะทิของจักรวาลที่ได้รับการยอมรับและทอดพระเนตรจาก \"นูส (Nous)\" เทพดาราแห่งปัญญา (The Erudition) ก่อตั้งขึ้นโดย Zandar One Kuwabara สมาชิกแต่ละคนจะได้รับหมายเลขประจำตัวตามลำดับการเข้าร่วม\r\nแม้จะขึ้นชื่อว่าเป็น \"สมาคม\" แต่ในความเป็นจริง สมาชิกส่วนใหญ่มักจะมีความคิดที่แปลกประหลาด ปลีกวิเวก หมกมุ่นอยู่กับงานวิจัยของตนเอง และแทบจะไม่เคยติดต่อหรือร่วมมือกันเลย (บางคนถึงขั้นเป็นอันตรายต่อจักรวาล เช่น Polka Kakamond หมายเลข 4 ผู้ไล่ล่าสังหารสมาชิกคนอื่นๆ) อย่างไรก็ตาม ก็มีข้อยกเว้นที่หาได้ยากยิ่ง นั่นคือโปรเจกต์มหากาพย์อย่าง \"Simulated Universe (จักรวาลจำลอง)\" ที่เกิดจากความร่วมมือของอัจฉริยะถึง 4 คน ได้แก่ Herta, Ruan Mei, Screwllum และ Stephen Lloyd เพื่อจำลองและไขความลับการถือกำเนิดของเหล่าเทพดารา', 'acede823615edaeb0ff3b4d3dbb428bf.png', 'approved', 'Arcana', 0),
(12, 'Hoshimi Miyabi', 'Zenless Zone Zero', 'character', 'Frost', 'Anomaly', 'S-Rank', 'Section 6', '', '', '', 'โฮชิมิ มิยาบิ (Hoshimi Miyabi) คือผู้สืบทอดของตระกูลศิลปะการต่อสู้ที่มีชื่อเสียงในเมือง New Eridu เธอใช้อาวุธที่มีลักษณะคล้ายดาบซามูไรและสามารถผสานพลังเวทมนตร์เข้าไปในการโจมตีได้\r\nในการต่อสู้ เธอมีความเชี่ยวชาญด้านธาตุน้ำแข็งและสามารถสร้างความเสียหายน้ำแข็งได้อย่างมหาศาล โดยมีรูปแบบการเก็บสะสมสแต็กที่เรียกว่า \"Arcane Blade\" นอกจากนี้เมื่อเธอมีแต้ม \"Fallen Frost\" เพียงพอ เธอจะสามารถเก็บดาบเข้าฝักเพื่อเข้าสู่ท่า \"Shimotsuki Stance\" และชาร์จพลังเพื่อปลดปล่อยการฟันดาบที่รุนแรงได้ ในฐานะเอเจนต์สาย Anomaly เธอยังมีความสามารถในการช่วยเพิ่มอัตราการสะสมเกจความผิดปกติ (Anomaly Buildup Rate) ให้กับสมาชิกทุกคนในทีม เพื่อให้ศัตรูติดสถานะผิดปกติทางธาตุได้รวดเร็วยิ่งขึ้นอีกด้วย', '0330fa91c08ac5d3c750758305e5eabd.jpg', 'approved', 'Arcana', 2),
(13, 'Hailstorm Shrine', 'Zenless Zone Zero', 'item', '', 'Anomaly', 'S-Rank', '', '', '', '', 'Hailstorm Shrine เป็น W-Engine ประจำตัวของโฮชิมิ มิยาบิ ที่ถูกสร้างมาเพื่อรองรับวิชาดาบของตระกูลโฮชิมิโดยเฉพาะ เนื่องจากมิยาบิเป็นเอเจนต์สาย Anomaly (ผิดปกติ) ที่สร้างความเสียหายหลักจากการติดคริติคอล W-Engine ชิ้นนี้จึงมีความพิเศษตรงที่มอบโบนัสสเตตัสพื้นฐานเป็น \"อัตราคริ (CRIT Rate)\" ซึ่งหาได้ยากมากในหมวดหมู่อุปกรณ์สายนี้\r\nเมื่อผู้สวมใส่ทำการโจมตีด้วยท่า EX Special Attack หรือเมื่อสมาชิกในทีมสามารถทำให้เป้าหมายติดสถานะผิดปกติทางธาตุ (Attribute Anomaly) ได้สำเร็จ กลไกของ Hailstorm Shrine จะทำงานเพื่อเพิ่มความเสียหายคริติคอล (CRIT DMG) และขยายขีดจำกัดความเสียหายน้ำแข็ง (Ice DMG) ให้สูงขึ้นไปอีกขั้น ทำให้การวาดดาบในท่า Shimotsuki Stance ของเธอกลายเป็นการโจมตีที่ทั้งสวยงามและเยือกเย็นจนศัตรูใน Hollow ไม่อาจต้านทานได้', '519df6bcbc02f38e60af60b283198302.png', 'approved', 'Arcana', 0),
(14, 'Section 6', 'Zenless Zone Zero', 'affiliation', '', '', '', '', 'Hoshimi Miyabi', 'กองกำลังป้องกันเมือง New Eridu', 'พันธมิตร', 'Hollow Special Operations Section 6 (H.S.O.S. 6) หรือที่เหล่านักพร็อกซี่รู้จักกันในชื่อ \"Section 6\" คือหน่วยรบแนวหน้าชั้นยอดที่ขึ้นตรงต่อเมือง New Eridu พวกเขาคือผู้เชี่ยวชาญระดับหัวกะทิที่มักจะถูกส่งไปรับมือกับภัยพิบัติ Hollow ระดับรุนแรงสูงสุด ที่หน่วยงานทั่วไปหรือนักสำรวจอิสระไม่สามารถจัดการได้\r\nภายใต้การนำทีมของ โฮชิมิ มิยาบิ ผู้สืบทอดวิชาดาบอันเลื่องชื่อ สมาชิกในหน่วยนี้ล้วนแต่เป็นยอดฝีมือที่มีความสามารถเฉพาะตัวสูงปรี๊ด (แม้บางคนจะมีนิสัยแปลกๆ อย่างการหิวตลอดเวลาแบบโซคาคุก็ตาม) ภาพลักษณ์ภายนอกของ Section 6 อาจดูเป็นหน่วยงานทหารที่เข้มงวด ปฏิบัติตามกฎระเบียบเป๊ะๆ และมีอำนาจล้นมือ แต่แท้จริงแล้วพวกเขาคือโล่และดาบที่แข็งแกร่งที่สุด ในการปกป้องชาวเมือง New Eridu ให้ปลอดภัยจากภัยคุกคามของเหล่า Ethereal', 'f9cf84bdb18df1ad6da0de92609069d9.jpg', 'approved', 'Arcana', 0),
(15, 'Sunna', 'Zenless Zone Zero', 'character', 'Physical', 'Support', 'S-Rank', 'Angels of Delusion', '', '', '', 'Sunna (ซุนนะ) คือคอมโพเซอร์จอมซนและผู้แต่งเพลงประจำวงไอดอล \"Angels of Delusion\" เธอเป็นหญิงสาวผู้มีพรสวรรค์เปี่ยมล้นแต่มักจะเขินอายเมื่ออยู่หน้ากล้อง และชอบใช้เวลาซ่อนตัวอยู่หลังโน้ตเพลงมากกว่าการออกไปยืนเจิดจรัสอยู่หน้าไมโครโฟน\r\nแม้ภายนอกจะดูขี้อายและบอบบาง แต่ทำนองเพลงที่ไหลผ่านปลายปากกาของเธอกลับเป็น \"อาวุธ\" ชั้นยอดที่ช่วยให้เพื่อนร่วมวงอย่าง Aria และ Nangong Yu พิชิตใจผู้ชมบนเวทีได้เสมอ ในการต่อสู้ Sunna รับบทเป็นผู้คุมจังหวะจากแนวหลัง เธอใช้ทักษะการประสานเสียงเพื่อมอบบัฟเพิ่มพลังโจมตี (ATK) ให้ทั้งทีม พร้อมกับอัญเชิญคู่หูอย่าง \"Bubblegum\" ออกมาช่วยโจมตีและสร้างความได้เปรียบในสนามรบ เธอจึงเปรียบเสมือนหัวใจหลักที่คอยสนับสนุนให้การแสดงของทีมออกมาสมบูรณ์แบบที่สุด', '0aff72d39981610c1ebe62ff0d336c2a.jpg', 'approved', 'ww', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_img` varchar(255) DEFAULT 'default_avatar.jpg',
  `banner_img` varchar(255) DEFAULT 'default_banner.jpg',
  `banner_pos_y` varchar(10) DEFAULT '50%',
  `avatar_pos_y` varchar(10) DEFAULT '50%'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `profile_img`, `banner_img`, `banner_pos_y`, `avatar_pos_y`) VALUES
(1, 'Arcana', '123', 'admin', '2026-02-24 14:15:19', 'p_1.jpg', 'b_1.jpg', '35%', '50%'),
(2, 'IzakuYu', '123', 'user', '2026-02-24 15:25:37', 'default_avatar.jpg', 'default_banner.jpg', '50%', '50%'),
(3, 'ww', '123456', 'user', '2026-02-26 05:38:11', 'default_avatar.jpg', 'default_banner.jpg', '50%', '50%');

-- --------------------------------------------------------

--
-- Table structure for table `user_likes`
--

CREATE TABLE `user_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_likes`
--

INSERT INTO `user_likes` (`id`, `user_id`, `character_id`) VALUES
(11, 1, 1),
(9, 1, 2),
(12, 1, 5),
(13, 1, 6),
(14, 1, 9),
(15, 1, 12),
(17, 2, 1),
(18, 2, 12),
(19, 3, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_likes`
--
ALTER TABLE `user_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`character_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `characters`
--
ALTER TABLE `characters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_likes`
--
ALTER TABLE `user_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
