<?php
$host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'railway';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

$server_type = getenv('SERVER_TYPE') ?: 'APACHE';
$is_nginx = (stripos($server_type, 'nginx') !== false);

// สีและสไตล์ตามที่ต้องการ:
// Apache: ม่วงเข้มเรืองแสง ขอบดำตัดคม
// Nginx: เขียวนีออนตัดเข้ม
if ($is_nginx) {
    $theme_color = '#00ff88'; // เขียวนีออน
    $text_on_theme = '#022c22'; // ข้อความสีเข้มตัดคม
    $badge_border = '2px solid #004d25';
    $glow_shadow = '0 0 15px #00ff88, 0 0 30px rgba(0, 255, 136, 0.4)';
    $server_title = 'Nginx Web Server';
    $env_text = 'Environment: Nginx + PHP 8.0-FPM + MySQL';
} else {
    $theme_color = '#581c87'; // ม่วงเข้ม
    $text_on_theme = '#ffffff'; // ข้อความสีขาวสว่าง
    $badge_border = '2px solid #000000'; // ขอบดำตัด
    $glow_shadow = '0 0 16px #a855f7, 0 0 32px rgba(168, 85, 247, 0.5)';
    $server_title = 'Apache Web Server';
    $env_text = 'Environment: Apache + PHP 8.0 + MySQL';
}

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
            background-color: #f8fafc;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
        }
        .badge {
            background-color: <?= $theme_color ?>;
            color: <?= $text_on_theme ?>;
            font-size: 13px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 6px;
            border: <?= $badge_border ?>;
            box-shadow: <?= $glow_shadow ?>;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .alert-db {
            background-color: #dcfce7;
            color: #14532d;
            padding: 12px 18px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 24px;
            border: 1px solid #bbf7d0;
            line-height: 1.5;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .card-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 18px;
            font-weight: 700;
            font-size: 14px;
            color: #334155;
        }
        .card-body {
            padding: 18px;
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #475569;
        }
        .form-group input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .btn-submit {
            background-color: <?= $theme_color ?>;
            color: <?= $text_on_theme ?>;
            border: <?= $badge_border ?>;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: <?= $glow_shadow ?>;
            transition: all 0.2s ease;
        }
        .btn-submit:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #334155;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            margin-top: 25px;
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
                        <tr><td colspan="4" style="text-align:center; color:#94a3b8;">No contacts found</td></tr>
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
