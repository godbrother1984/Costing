<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FG Pricing System - โระบบคำนวณราคาสินค้า</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    🧮
                </div>
                <div class="logo-text">
                    <h1>FG Pricing System</h1>
                    <p>ระบบคำนวณราคาสินค้า - เชื่อมต่อกับ Dynamics 365</p>
                </div>
            </div>
            <div class="user-info">
                <div class="sync-status">
                    <i class="fas fa-check-circle"></i>
                    <span>D365 Connected</span>
                </div>
                <div>
                    <i class="fas fa-user-circle" style="font-size: 18px; margin-right: 5px;"></i>
                    <span>Sales Team</span>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showTab('dashboard')">
                🏠 Dashboard
            </button>
            <button class="nav-tab" onclick="showTab('new-request')">
                ➕ คำขอราคา
            </button>
            <button class="nav-tab" onclick="showTab('master-data')">
                🗄️ ข้อมูลหลัก
            </button>
            <button class="nav-tab" onclick="showTab('sync-status')">
                🔄 สถานะการซิงค์
            </button>
            <button class="nav-tab" onclick="showTab('settings')">
                ⚙️ ตั้งค่า
            </button>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Include view files for each tab -->
            <?php include 'views/dashboard.php'; ?>
            <?php include 'views/new_request.php'; ?>
            <?php include 'views/master_data.php'; ?>
            <?php include 'views/sync_status.php'; ?>
            <?php include 'views/settings.php'; ?>
        </div>
    </div>

    <!-- Link to external JavaScript file -->
    <script src="assets/js/main.js"></script>
</body>
</html>
