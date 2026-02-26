<?php
// ข้อมูลสำหรับเชื่อมต่อฐานข้อมูล (ค่าดั้งเดิมสำหรับการรันบน XAMPP)
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "hoyoverse_db"; 

// ซ่อน Warning ของ PHP ไม่ให้แสดงบนหน้าจอ (ปิดบังช่องโหว่)
error_reporting(0);

// ทำการเชื่อมต่อฐานข้อมูลตามโครงสร้างเดิม
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อก่อนเสมอ (ถ้าพังให้หยุดการทำงานทันที)
if ($conn->connect_error) {
    // บันทึก Error จริงลง Log เบื้องหลัง
    error_log("DB Error: " . $conn->connect_error);
    // แสดงข้อความแจ้งเตือนที่ปลอดภัยต่อผู้ใช้
    die("ระบบไม่สามารถเชื่อมต่อฐานข้อมูลได้ในขณะนี้ กรุณาตรวจสอบการตั้งค่าเซิร์ฟเวอร์");
}

// เมื่อเชื่อมต่อสำเร็จ จึงทำการตั้งค่าชุดตัวอักษร
$conn->set_charset("utf8mb4");

?>