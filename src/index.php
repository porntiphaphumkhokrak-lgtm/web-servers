<?php
$host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'railway';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

$server_type = getenv('SERVER_TYPE') ?: 'Apache';
$is_nginx = (stripos($server_type, 'nginx') !== false);

// สไตล์และสีตามเซิร์ฟเวอร์ (Nginx = เขียว, Apache = แดง)
$theme_color = $is_nginx ? '#28a745' : '#dc3545';
$server_title = $is_nginx ? 'Nginx Web Server' : 'Apache Web Server';
$env_text = $is_nginx ? 'Environment: Nginx + PHP 8.0-FPM + MySQL' : 'Environment: Apache + PHP 8.0 + MySQL';

$conn_status = "";
$conn_ok = false;
$pdo = null;

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $conn_ok = true;
    $conn_status = "Connected to MySQL Server successfully! (Host: <code style='color:#d63384;'>$host</code>, Port: <code style='color:#d63384;'>$port</code>)";
    
    // สร้างตารางอัตโนมัติหากยังไม่มี
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        mobile VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    $conn_status = "Connection failed: " . $e->getMessage();
}

// จัดการเมื่อกด Submit Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn_ok) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');

    if ($name && $email && $mobile) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $mobile]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// ดึงข้อมูลแสดงในตาราง (เรียงจากล่าสุด id มากไปน้อย)
$users = [];
if ($conn_ok) {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($server_title) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .badge {
            background-color: <?= $theme_color ?>;
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .alert-db {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            line-height: 1.5;
        }
        .card {
            border: 1px solid #e1e4e8;
            border-radius: 4px;
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header {
            background-color: #f6f8fa;
            border-bottom: 1px solid #e1e4e8;
            padding: 10px 16px;
            font-weight: bold;
            font-size: 14px;
        }
        .card-body {
            padding: 16px;
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .btn-submit {
            background-color: <?= $theme_color ?>;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #fff;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #6c757d;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <span class="badge"><?= $is_nginx ? 'Nginx Web Server' : 'Apache Web Server' ?></span>
        <h1>Contact Management</h1>
    </div>

    <div class="alert-db">
        <strong>Database Status:</strong> <?= $conn_status ?>
    </div>

    <div class="card">
        <div class="card-header">Add New Contact</div>
        <div class="card-body">
            <form method="POST">
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
                <button type="submit" class="btn-submit">Submit Contact</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Users List (from MySQL Database)</div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 30%;">Name</th>
                        <th style="width: 35%;">Email</th>
                        <th style="width: 25%;">Mobile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="4" style="text-align:center; color:#888;">No contacts found</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['id']) ?></td>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['mobile']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <?= htmlspecialchars($env_text) ?>
    </div>
</div>
</body>
</html>
