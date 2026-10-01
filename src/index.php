<?php
// อ่านค่าพอร์ตหรือระบุเซิร์ฟเวอร์
$post_server = $_POST['server_type'] ?? '';
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';

// ตรวจสอบชนิดเว็บเซิร์ฟเวอร์จาก SERVER_SOFTWARE หรือ Environment Variable
$server_software = $_SERVER['SERVER_SOFTWARE'] ?? '';
$is_nginx = (getenv('SERVER_TYPE') === 'NGINX') || (stripos($server_software, 'nginx') !== false);

// อ่านค่าการเชื่อมต่อฐานข้อมูลจาก Railway Environment Variables
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'railway';

$status_msg = '';
$conn = new mysqli($host, $user, $pass, $dbname, $port);

if (!$conn->connect_error) {
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        mobile VARCHAR(50) NOT NULL
    )");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');

        if ($name && $email && $mobile) {
            $stmt = $conn->prepare("INSERT INTO users (name, email, mobile) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $name, $email, $mobile);
                $stmt->execute();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Form</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #fdfdfd;
            margin: 0;
            padding: 40px 20px;
            position: relative;
            min-height: 100vh;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        .watermark {
            position: fixed;
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-12deg);
            font-size: 130px;
            font-weight: 900;
            color: rgba(220, 225, 235, 0.45);
            z-index: 1;
            pointer-events: none;
            user-select: none;
            letter-spacing: 6px;
        }
        .form-wrapper {
            position: relative;
            z-index: 2;
            max-width: 440px;
            margin: 0 auto;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 4px;
        }
        .header-row h1 {
            margin: 0;
            font-size: 26px;
            font-weight: bold;
            color: #111;
        }
        .server-tag {
            font-size: 13px;
            color: #555;
        }
        .sub-text {
            color: #666;
            font-size: 13px;
            margin: 0 0 24px 0;
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #222;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #c9d5ea;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #eef3fc;
            font-size: 14px;
            outline: none;
        }
        .form-group input:focus {
            border-color: #0d6efd;
            background-color: #fff;
        }
        .btn-submit {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 8px 24px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 6px;
        }
        .btn-submit:hover {
            background-color: #0b5ed7;
        }
        .footer-status {
            margin-top: 24px;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="watermark" id="ui-watermark">APACHE</div>

<div class="form-wrapper">
    <div class="header-row">
        <h1>Contact Form</h1>
        <span class="server-tag">Server: <span id="ui-servertag">APACHE</span></span>
    </div>
    <p class="sub-text">Please fill this form and submit to add employee record to the database</p>

    <form method="POST" id="contactForm">
        <input type="hidden" name="server_type" id="input_server_type" value="APACHE">

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Mobile</label>
            <input type="text" name="mobile" required>
        </div>

        <button type="submit" class="btn-submit">Submit</button>
    </form>

    <div class="footer-status">
        Connected to MySQL server successfully via <span id="ui-conn">APACHE</span>!<br>
        <?php echo date('d/m/Y'); ?>
    </div>
</div>

<script>
    const port = window.location.port;
    const isNginx = (port === '82');
    const label = isNginx ? 'NGINX' : 'APACHE';

    document.getElementById('ui-watermark').innerText = label;
    document.getElementById('ui-servertag').innerText = label;
    document.getElementById('ui-conn').innerText = label;
    document.getElementById('input_server_type').value = label;

    // บังคับให้ form ส่งคำขอกลับมาที่พอร์ตปัจจุบันของตัวเองโดยตรง
    document.getElementById('contactForm').action = window.location.href;
</script>

</body>
</html>