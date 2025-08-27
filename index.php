</div><!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FG Pricing System - โระบบคำนวณราคาสินค้า</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        /* เอา font awesome ออกและใช้ emoji ทั้งหมด */
        .fas, .far, .fab {
            display: none;
        }
        .fa-edit::before { content: "\f044"; }
        .fa-save::before { content: "\f0c7"; }
        .fa-chart-area::before { content: "\f1fe"; }
        .fa-plus::before { content: "\f067"; }
        .fa-user-circle::before { content: "\f2bd"; }
        .fa-arrow-up::before { content: "\f062"; }
        .fa-arrow-down::before { content: "\f063"; }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Kanit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: 400;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            background: linear-gradient(45deg, #495057, #6c757d);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(73, 80, 87, 0.2);
        }

        .logo-text h1 {
            font-family: 'Inter', sans-serif;
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(45deg, #495057, #6c757d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .logo-text p {
            font-family: 'Kanit', sans-serif;
            color: #666;
            font-size: 13px;
            font-weight: 400;
            margin-top: 2px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #555;
        }

        .sync-status {
            font-family: 'Inter', sans-serif;
            background: #d1e7dd;
            color: #0f5132;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Navigation */
        .nav-tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
            gap: 8px;
            overflow-x: auto;
        }

        .nav-tab {
            font-family: 'Inter', sans-serif;
            padding: 12px 24px;
            border-radius: 10px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            letter-spacing: -0.2px;
        }

        .nav-tab.active {
            background: linear-gradient(45deg, #495057, #6c757d);
            color: white;
            box-shadow: 0 3px 10px rgba(73, 80, 87, 0.2);
        }

        .nav-tab:hover:not(.active) {
            background: rgba(73, 80, 87, 0.08);
            color: #495057;
        }

        /* Main Content */
        .main-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 249, 250, 0.9) 100%);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .card-icon.primary { background: linear-gradient(45deg, #495057, #6c757d); }
        .card-icon.success { background: linear-gradient(45deg, #198754, #20c997); }
        .card-icon.warning { background: linear-gradient(45deg, #fd7e14, #ffc107); }
        .card-icon.info { background: linear-gradient(45deg, #0dcaf0, #0d6efd); }

        .card h3 {
            font-family: 'Inter', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            letter-spacing: -0.3px;
        }

        .card p {
            font-family: 'Kanit', sans-serif;
            color: #666;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-family: 'Inter', sans-serif;
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: #333;
            letter-spacing: -0.2px;
        }

        .form-control {
            font-family: 'Inter', sans-serif;
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 400;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            outline: none;
            border-color: #495057;
            box-shadow: 0 0 0 3px rgba(73, 80, 87, 0.1);
        }

        .btn {
            font-family: 'Inter', sans-serif;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: -0.2px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, #495057, #6c757d);
            color: white;
            box-shadow: 0 3px 10px rgba(73, 80, 87, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(73, 80, 87, 0.3);
        }

        .btn-success {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #495057;
            color: #495057;
        }

        .btn-outline:hover {
            background: #495057;
            color: white;
        }

        /* Quick Actions */
        .quick-actions {
            margin-top: 30px;
        }

        .quick-actions h3 {
            font-family: 'Inter', sans-serif;
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
            font-size: 20px;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            font-family: 'Inter', sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            display: block;
        }

        .action-btn h4 {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 16px;
            margin: 8px 0 4px 0;
            letter-spacing: -0.2px;
        }

        .action-btn p {
            font-family: 'Kanit', sans-serif;
            font-size: 13px;
            font-weight: 400;
            color: #666;
            margin: 0;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .action-btn i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #495057;
        }

        /* Data Table */
        .table-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(45deg, #495057, #6c757d);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: -0.2px;
        }

        .table td {
            font-family: 'Inter', sans-serif;
            padding: 15px;
            border-bottom: 1px solid #e1e5e9;
            font-size: 14px;
            font-weight: 400;
        }

        .table tr:hover {
            background: rgba(73, 80, 87, 0.05);
        }

        .status-badge {
            font-family: 'Inter', sans-serif;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .status-badge.connected {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-badge.syncing {
            background: #fff3cd;
            color: #664d03;
        }

        .status-badge.error {
            background: #f8d7da;
            color: #58151c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .nav-tabs {
                flex-wrap: wrap;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #495057;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
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
                ➕ คำขอราคาใหม่
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
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon primary">
                                📄
                            </div>
                            <div>
                                <h3>คำขอราคาวันนี้</h3>
                                <p>คำขอราคาที่สร้างในวันนี้</p>
                            </div>
                        </div>
                        <div style="font-size: 32px; font-weight: 700; color: #495057;">12</div>
                        <p style="color: #198754; font-size: 14px; margin-top: 10px;">
                            📈 +25% จากเมื่อวาน
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon success">
                                ⏰
                            </div>
                            <div>
                                <h3>เวลาเฉลี่ยในการตอบกลับ</h3>
                                <p>เวลาที่ใช้ในการคำนวณและส่งราคา</p>
                            </div>
                        </div>
                        <div style="font-size: 32px; font-weight: 700; color: #198754;">3.2 นาที</div>
                        <p style="color: #198754; font-size: 14px; margin-top: 10px;">
                            📉 -45% จากเดือนก่อน
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon warning">
                                ⚠️
                            </div>
                            <div>
                                <h3>Formula ชั่วคราว</h3>
                                <p>รอการอนุมัติและซิงค์กลับ D365</p>
                            </div>
                        </div>
                        <div style="font-size: 32px; font-weight: 700; color: #fd7e14;">5</div>
                        <p style="color: #dc3545; font-size: 14px; margin-top: 10px;">
                            ⏰ ต้องการการพิจารณา
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon info">
                                🔄
                            </div>
                            <div>
                                <h3>การซิงค์ล่าสุด</h3>
                                <p>ซิงค์ข้อมูลกับ Dynamics 365</p>
                            </div>
                        </div>
                        <div style="font-size: 18px; font-weight: 600; color: #0dcaf0;">5 นาทีที่แล้ว</div>
                        <p style="color: #198754; font-size: 14px; margin-top: 10px;">
                            ✅ สถานะปกติ
                        </p>
                    </div>
                </div>

                <div class="quick-actions">
                    <h3>⚡ การทำงานด่วน</h3>
                    <div class="action-buttons">
                        <a href="#" class="action-btn" onclick="showTab('new-request')">
                            ➕
                            <h4>สร้างคำขอราคาใหม่</h4>
                            <p>คำนวณราคาสินค้าใหม่</p>
                        </a>
                        <a href="#" class="action-btn" onclick="updateMasterData()">
                            🔄
                            <h4>อัพเดทข้อมูลราคา</h4>
                            <p>ซิงค์ข้อมูลล่าสุดจาก D365</p>
                        </a>
                        <a href="#" class="action-btn" onclick="showTab('master-data')">
                            📈
                            <h4>จัดการข้อมูลหลัก</h4>
                            <p>ตั้งค่า LME, Fab Cost, Factor</p>
                        </a>
                        <a href="#" class="action-btn" onclick="showEmailQueue()">
                            📧
                            <h4>คิวอีเมล</h4>
                            <p>ตรวจสอบคำขอราคาอัตโนมัติ</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- New Request Tab -->
            <div id="new-request" class="tab-content">
                <h2 style="margin-bottom: 25px; color: #333; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 28px; letter-spacing: -0.5px;">
                    🧮
                    สร้างคำขอราคาใหม่
                </h2>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <div class="form-group">
                        <label class="form-label">รหัสลูกค้า (Customer ID)</label>
                        <input type="text" class="form-control" id="customerId" placeholder="เช่น CUST001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">รหัสสินค้า (FG Code)</label>
                        <input type="text" class="form-control" id="fgCode" placeholder="เช่น FG001">
                    </div>
                </div>

                <button class="btn btn-outline" onclick="searchProductInfo()">
                    🔍 ค้นหาข้อมูลจาก D365
                </button>

                <div id="productInfo" style="margin-top: 20px; display: none;">
                    <div class="card">
                        <h3>ข้อมูลสินค้า</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div class="form-group">
                                <label class="form-label">ชื่อลูกค้า</label>
                                <input type="text" class="form-control" id="customerName" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">เลขที่แบบ (Drawing No.)</label>
                                <input type="text" class="form-control" id="drawingNo" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">ชื่อชิ้นงาน (Part Name)</label>
                                <input type="text" class="form-control" id="partName" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">โมเดล (Model)</label>
                                <input type="text" class="form-control" id="model" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <div class="form-group">
                        <label class="form-label">จำนวน (Quantity)</label>
                        <input type="number" class="form-control" id="quantity" placeholder="กรอกจำนวนที่ต้องการ">
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button class="btn btn-primary" onclick="calculatePrice()" style="font-size: 16px; padding: 15px 40px;">
                        🧮 คำนวณราคา
                    </button>
                </div>

                <div id="calculationResult" style="margin-top: 30px; display: none;">
                    <div class="card" style="border: 2px solid #198754; background: linear-gradient(135deg, #d1e7dd 0%, #f8f9fa 100%);">
                        <h3 style="color: #198754;">ผลการคำนวณราคา</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px;">
                            <div style="text-align: center;">
                                <p style="color: #666; font-size: 14px;">ราคาต่อหน่วย</p>
                                <div style="font-size: 24px; font-weight: 700; color: #198754;" id="unitPrice">฿ 0.00</div>
                            </div>
                            <div style="text-align: center;">
                                <p style="color: #666; font-size: 14px;">จำนวน</p>
                                <div style="font-size: 24px; font-weight: 700; color: #333;" id="totalQty">0 pcs</div>
                            </div>
                            <div style="text-align: center;">
                                <p style="color: #666; font-size: 14px;">ราคารวม</p>
                                <div style="font-size: 28px; font-weight: 700; color: #495057;" id="totalPrice">฿ 0.00</div>
                            </div>
                        </div>
                        <div style="margin-top: 20px; text-align: center;">
                            <button class="btn btn-success" onclick="sendQuoteEmail()">
                                📧 ส่งอีเมลใบเสนอราคา
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Master Data Tab -->
            <div id="master-data" class="tab-content">
                <h2 style="margin-bottom: 25px; color: #333; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 28px; letter-spacing: -0.5px;">
                    🗄️
                    จัดการข้อมูลหลัก
                </h2>

                <div class="nav-tabs" style="margin-bottom: 20px;">
                    <button class="nav-tab active" onclick="showMasterTab('lme-prices')">📈 LME Prices</button>
                    <button class="nav-tab" onclick="showMasterTab('fab-cost')">🔧 Fab Cost</button>
                    <button class="nav-tab" onclick="showMasterTab('std-prices')">📋 Standard Prices</button>
                    <button class="nav-tab" onclick="showMasterTab('factors')">🧮 Selling Factors</button>
                    <button class="nav-tab" onclick="showMasterTab('exchange')">💱 Exchange Rates</button>
                </div>

                <div id="lme-prices" class="master-tab-content">
                    <h3>📈 ราคา LME (London Metal Exchange)</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>กลุ่มลูกค้า</th>
                                    <th>กลุ่มวัตถุดิบ</th>
                                    <th>ประเภทโลหะ</th>
                                    <th>ราคา (USD/MT)</th>
                                    <th>อัพเดทล่าสุด</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>กลุ่ม A</td>
                                    <td>โลหะทองแดง</td>
                                    <td>Copper</td>
                                    <td>8,245.50</td>
                                    <td>26/08/2568 09:30</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>กลุ่ม B</td>
                                    <td>โลหะอลูมิเนียม</td>
                                    <td>Aluminum</td>
                                    <td>2,156.75</td>
                                    <td>26/08/2568 09:30</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" style="margin-top: 15px;">
                        ➕ เพิ่มราคา LME ใหม่
                    </button>
                </div>

                <div id="fab-cost" class="master-tab-content" style="display: none;">
                    <h3>🔧 ต้นทุน Fabrication</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>กลุ่มลูกค้า</th>
                                    <th>ประเภทงาน</th>
                                    <th>ต้นทุน (THB/Unit)</th>
                                    <th>หน่วย</th>
                                    <th>คำอธิบาย</th>
                                    <th>อัพเดทล่าสุด</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>กลุ่ม A</td>
                                    <td>การตัด (Cutting)</td>
                                    <td>150.00</td>
                                    <td>PCS</td>
                                    <td>ตัดด้วย Laser</td>
                                    <td>25/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>กลุ่ม A</td>
                                    <td>การเชื่อม (Welding)</td>
                                    <td>200.00</td>
                                    <td>M</td>
                                    <td>เชื่อม TIG</td>
                                    <td>25/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>กลุ่ม B</td>
                                    <td>การขึ้นรูป (Forming)</td>
                                    <td>120.00</td>
                                    <td>PCS</td>
                                    <td>ขึ้นรูปด้วย Press</td>
                                    <td>24/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>กลุ่ม C</td>
                                    <td>การประกอบ (Assembly)</td>
                                    <td>180.00</td>
                                    <td>SET</td>
                                    <td>ประกอบชิ้นส่วน</td>
                                    <td>23/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" style="margin-top: 15px;">
                        ➕ เพิ่มต้นทุน Fab ใหม่
                    </button>
                </div>

                <div id="std-prices" class="master-tab-content" style="display: none;">
                    <h3>📋 ราคามาตรฐานวัตถุดิบ</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>รหัสวัตถุดิบ</th>
                                    <th>ชื่อวัตถุดิบ</th>
                                    <th>กลุ่มลูกค้า</th>
                                    <th>ราคา (THB/Unit)</th>
                                    <th>หน่วย</th>
                                    <th>อัพเดทล่าสุด</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>RM001</td>
                                    <td>สกรูสแตนเลส M6</td>
                                    <td>กลุ่ม A</td>
                                    <td>15.50</td>
                                    <td>PCS</td>
                                    <td>25/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>RM002</td>
                                    <td>แหวนรอง M6</td>
                                    <td>กลุ่ม A</td>
                                    <td>2.25</td>
                                    <td>PCS</td>
                                    <td>25/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>RM003</td>
                                    <td>น็อตหัวหก M8</td>
                                    <td>กลุ่ม B</td>
                                    <td>18.75</td>
                                    <td>PCS</td>
                                    <td>24/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" style="margin-top: 15px;">
                        ➕ เพิ่มราคาวัตถุดิบใหม่
                    </button>
                </div>

                <div id="factors" class="master-tab-content" style="display: none;">
                    <h3>🧮 ตัวคูณราคาขาย (Selling Factors)</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Pattern</th>
                                    <th>ตัวคูณ</th>
                                    <th>คำอธิบาย</th>
                                    <th>อัพเดทล่าสุด</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Standard</td>
                                    <td>1.25</td>
                                    <td>การผลิตมาตรฐาน</td>
                                    <td>20/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Custom</td>
                                    <td>1.45</td>
                                    <td>การผลิตตามสั่ง</td>
                                    <td>20/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Premium</td>
                                    <td>1.60</td>
                                    <td>งานพิเศษคุณภาพสูง</td>
                                    <td>19/08/2568</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" style="margin-top: 15px;">
                        ➕ เพิ่ม Factor ใหม่
                    </button>
                </div>

                <div id="exchange" class="master-tab-content" style="display: none;">
                    <h3>💱 อัตราแลกเปลี่ยน</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>กลุ่มลูกค้า</th>
                                    <th>คู่เงิน</th>
                                    <th>อัตราแลกเปลี่ยน</th>
                                    <th>อัพเดทล่าสุด</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>กลุ่ม A</td>
                                    <td>USD/THB</td>
                                    <td>35.42</td>
                                    <td>26/08/2568 10:15</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>กลุ่ม B</td>
                                    <td>EUR/THB</td>
                                    <td>38.75</td>
                                    <td>26/08/2568 10:15</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>กลุ่ม C</td>
                                    <td>JPY/THB</td>
                                    <td>0.24</td>
                                    <td>26/08/2568 10:15</td>
                                    <td>
                                        <button class="btn" style="padding: 5px 10px; font-size: 12px;">
                                            ✏️
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" style="margin-top: 15px;">
                        ➕ เพิ่มอัตราแลกเปลี่ยนใหม่
                    </button>
                </div>
            </div>

            <!-- Sync Status Tab -->
            <div id="sync-status" class="tab-content">
                <h2 style="margin-bottom: 25px; color: #333; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 28px; letter-spacing: -0.5px;">
                    🔄
                    สถานะการซิงโครไนซ์ข้อมูล
                </h2>

                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon success">
                                🗄️
                            </div>
                            <div>
                                <h3>ข้อมูลผลิตภัณฑ์</h3>
                                <p>Products & FG Codes</p>
                            </div>
                        </div>
                        <div class="status-badge connected">เชื่อมต่อแล้ว</div>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                            ซิงค์ล่าสุด: 26/08/2568 09:45 | ข้อมูล: 1,248 รายการ
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon success">
                                👥
                            </div>
                            <div>
                                <h3>ข้อมูลลูกค้า</h3>
                                <p>Customer Master</p>
                            </div>
                        </div>
                        <div class="status-badge connected">เชื่อมต่อแล้ว</div>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                            ซิงค์ล่าสุด: 26/08/2568 08:30 | ข้อมูล: 156 รายการ
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon warning">
                                📋
                            </div>
                            <div>
                                <h3>สูตร/BOM</h3>
                                <p>Real-time API</p>
                            </div>
                        </div>
                        <div class="status-badge syncing">กำลังซิงค์</div>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                            API Status: Active | Response: 0.8s avg
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon info">
                                🏷️
                            </div>
                            <div>
                                <h3>Item Groups</h3>
                                <p>Classification Data</p>
                            </div>
                        </div>
                        <div class="status-badge connected">เชื่อมต่อแล้ว</div>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                            ซิงค์ล่าสุด: 26/08/2568 07:00 | ข้อมูล: 45 กลุ่ม
                        </p>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h3>รายการการซิงค์</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>วันเวลา</th>
                                    <th>ประเภทข้อมูล</th>
                                    <th>การดำเนินการ</th>
                                    <th>สถานะ</th>
                                    <th>รายละเอียด</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>26/08/2568 10:30</td>
                                    <td>Product Master</td>
                                    <td>Scheduled Sync</td>
                                    <td><span class="status-badge connected">สำเร็จ</span></td>
                                    <td>อัพเดท 15 รายการใหม่</td>
                                </tr>
                                <tr>
                                    <td>26/08/2568 10:25</td>
                                    <td>BOM Real-time</td>
                                    <td>API Call</td>
                                    <td><span class="status-badge connected">สำเร็จ</span></td>
                                    <td>ดึงสูตรสำหรับ FG001</td>
                                </tr>
                                <tr>
                                    <td>26/08/2568 09:45</td>
                                    <td>Customer Master</td>
                                    <td>Scheduled Sync</td>
                                    <td><span class="status-badge connected">สำเร็จ</span></td>
                                    <td>ไม่มีการเปลี่ยนแปลง</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button class="btn btn-primary" onclick="forceSyncAll()">
                        🔄 บังคับซิงค์ทั้งหมด
                    </button>
                    <button class="btn btn-outline" onclick="testConnection()">
                        🔌 ทดสอบการเชื่อมต่อ
                    </button>
                </div>
            </div>

            <!-- Settings Tab -->
            <div id="settings" class="tab-content">
                <h2 style="margin-bottom: 25px; color: #333; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 28px; letter-spacing: -0.5px;">
                    ⚙️
                    ตั้งค่าระบบ
                </h2>

                <div class="dashboard-cards" style="margin-bottom: 30px;">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon primary">
                                🖥️
                            </div>
                            <div>
                                <h3>การเชื่อมต่อ D365</h3>
                                <p>ตั้งค่า API และการยืนยันตัวตน</p>
                            </div>
                        </div>
                        <button class="btn btn-outline" onclick="configureD365()">กำหนดค่า</button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon success">
                                📧
                            </div>
                            <div>
                                <h3>ระบบอีเมล</h3>
                                <p>SMTP/IMAP และการแจ้งเตือน</p>
                            </div>
                        </div>
                        <button class="btn btn-outline" onclick="configureEmail()">กำหนดค่า</button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon warning">
                                🏷️
                            </div>
                            </div>
                            <div>
                                <h3>Item Group Mapping</h3>
                                <p>จับคู่กลุ่มวัตถุดิบกับประเภทการคำนวณ</p>
                            </div>
                        </div>
                        <button class="btn btn-outline" onclick="showItemGroupMapping()">จัดการ</button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon info">
                                👥
                            </div>
                            <div>
                                <h3>ผู้ใช้งาน</h3>
                                <p>จัดการสิทธิ์และบทบาท</p>
                            </div>
                        </div>
                        <button class="btn btn-outline" onclick="manageUsers()">จัดการ</button>
                    </div>
                </div>

                <div id="itemGroupMappingSection" style="display: none;">
                    <h3>การจับคู่ Item Group</h3>
                    <p style="margin-bottom: 20px; color: #666;">กำหนดว่า Item Group ใดควรใช้การคำนวณแบบ 'Main' (ใช้ราคา LME) หรือ 'Other' (ใช้ราคามาตรฐาน)</p>
                    
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>รหัส Item Group</th>
                                    <th>ชื่อกลุ่ม</th>
                                    <th>ประเภทการคำนวณ</th>
                                    <th>คำอธิบาย</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>COPPER</td>
                                    <td>โลหะทองแดง</td>
                                    <td>
                                        <select class="form-control" style="width: auto; padding: 5px;">
                                            <option value="main" selected>Main (LME)</option>
                                            <option value="other">Other (Std Price)</option>
                                        </select>
                                    </td>
                                    <td>วัตถุดิบหลักใช้ราคา LME</td>
                                    <td>
                                        <button class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">
                                            💾
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>SCREW</td>
                                    <td>สกรู/น๊อต</td>
                                    <td>
                                        <select class="form-control" style="width: auto; padding: 5px;">
                                            <option value="main">Main (LME)</option>
                                            <option value="other" selected>Other (Std Price)</option>
                                        </select>
                                    </td>
                                    <td>วัสดุช่วยใช้ราคาคงที่</td>
                                    <td>
                                        <button class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">
                                            💾
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ALUMINUM</td>
                                    <td>โลหะอลูมิเนียม</td>
                                    <td>
                                        <select class="form-control" style="width: auto; padding: 5px;">
                                            <option value="main" selected>Main (LME)</option>
                                            <option value="other">Other (Std Price)</option>
                                        </select>
                                    </td>
                                    <td>วัตถุดิบหลักใช้ราคา LME</td>
                                    <td>
                                        <button class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">
                                            💾
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentTab = 'dashboard';
        let currentMasterTab = 'lme-prices';

        // Tab Management
        function showTab(tabName) {
            // Hide all tab contents
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all nav tabs
            const navTabs = document.querySelectorAll('.nav-tab');
            navTabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked nav tab
            event.target.classList.add('active');
            
            currentTab = tabName;
        }

        // Master Data Tab Management
        function showMasterTab(tabName) {
            // Hide all master tab contents
            const masterTabs = document.querySelectorAll('.master-tab-content');
            masterTabs.forEach(tab => tab.style.display = 'none');
            
            // Remove active class from master nav tabs
            const masterNavTabs = document.querySelectorAll('#master-data .nav-tab');
            masterNavTabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected master tab
            document.getElementById(tabName).style.display = 'block';
            
            // Add active class to clicked master nav tab
            event.target.classList.add('active');
            
            currentMasterTab = tabName;
        }

        // Mock API Functions
        function searchProductInfo() {
            const customerId = document.getElementById('customerId').value;
            const fgCode = document.getElementById('fgCode').value;
            
            if (!customerId || !fgCode) {
                alert('กรุณากรอกรหัสลูกค้าและรหัสสินค้า');
                return;
            }
            
            // Show loading effect
            const button = event.target;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<div class="loading"></div> กำลังค้นหา...';
            button.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Mock data from D365
                document.getElementById('customerName').value = 'บริษัท ABC จำกัด';
                document.getElementById('drawingNo').value = 'DRW-2024-001';
                document.getElementById('partName').value = 'แผ่นโลหะฐานเครื่องจักร';
                document.getElementById('model').value = 'Model-X1';
                
                document.getElementById('productInfo').style.display = 'block';
                
                // Reset button
                button.innerHTML = originalHTML;
                button.disabled = false;
                
                showNotification('พบข้อมูลสินค้าจาก D365 แล้ว', 'success');
            }, 1500);
        }

        function calculatePrice() {
            const customerId = document.getElementById('customerId').value;
            const fgCode = document.getElementById('fgCode').value;
            const quantity = document.getElementById('quantity').value;
            
            if (!customerId || !fgCode || !quantity) {
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
                return;
            }
            
            // Show loading effect
            const button = event.target;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<div class="loading"></div> กำลังคำนวณ...';
            button.disabled = true;
            
            // Simulate price calculation
            setTimeout(() => {
                // Mock calculation result
                const unitPrice = 145.75;
                const totalPrice = unitPrice * parseInt(quantity);
                
                document.getElementById('unitPrice').textContent = `฿ ${unitPrice.toFixed(2)}`;
                document.getElementById('totalQty').textContent = `${quantity} pcs`;
                document.getElementById('totalPrice').textContent = `฿ ${totalPrice.toLocaleString('th-TH', {minimumFractionDigits: 2})}`;
                
                document.getElementById('calculationResult').style.display = 'block';
                
                // Reset button
                button.innerHTML = originalHTML;
                button.disabled = false;
                
                showNotification('คำนวณราคาเรียบร้อยแล้ว', 'success');
            }, 2000);
        }

        function sendQuoteEmail() {
            const button = event.target;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<div class="loading"></div> กำลังส่งอีเมล...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
                showNotification('ส่งอีเมลใบเสนอราคาเรียบร้อยแล้ว', 'success');
            }, 1500);
        }

        function updateMasterData() {
            showNotification('กำลังอัพเดทข้อมูลจาก D365...', 'info');
            
            setTimeout(() => {
                showNotification('อัพเดทข้อมูลเรียบร้อยแล้ว', 'success');
                
                // Update sync status
                const syncStatus = document.querySelector('.sync-status span');
                syncStatus.textContent = 'อัพเดทเมื่อกี้';
            }, 2000);
        }

        function forceSyncAll() {
            const button = event.target;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<div class="loading"></div> กำลังซิงค์...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
                showNotification('ซิงค์ข้อมูลทั้งหมดเรียบร้อยแล้ว', 'success');
            }, 3000);
        }

        function testConnection() {
            const button = event.target;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<div class="loading"></div> ทดสอบ...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
                showNotification('เชื่อมต่อ D365 สำเร็จ - Ping: 45ms', 'success');
            }, 1000);
        }

        function showEmailQueue() {
            showNotification('ฟีเจอร์นี้จะพัฒนาใน Phase 4', 'info');
        }

        function configureD365() {
            showNotification('ฟีเจอร์การตั้งค่า D365 จะพัฒนาใน Phase 2', 'info');
        }

        function configureEmail() {
            showNotification('ฟีเจอร์การตั้งค่าอีเมลจะพัฒนาใน Phase 3', 'info');
        }

        function showItemGroupMapping() {
            document.getElementById('itemGroupMappingSection').style.display = 'block';
            showNotification('แสดงการตั้งค่า Item Group Mapping', 'info');
        }

        function manageUsers() {
            showNotification('ฟีเจอร์จัดการผู้ใช้งานจะพัฒนาใน Phase 2', 'info');
        }

        // Notification System
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 10px;
                color: white;
                font-weight: 500;
                z-index: 1000;
                max-width: 300px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                transform: translateX(400px);
                transition: transform 0.3s ease;
            `;
            
            if (type === 'success') {
                notification.style.background = 'linear-gradient(45deg, #56ab2f, #a8e6cf)';
            } else if (type === 'error') {
                notification.style.background = 'linear-gradient(45deg, #ff416c, #ff4b2b)';
            } else if (type === 'warning') {
                notification.style.background = 'linear-gradient(45deg, #f7971e, #ffd200)';
            } else {
                notification.style.background = 'linear-gradient(45deg, #74b9ff, #0984e3)';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Initialize the system
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('ยินดีต้อนรับสู่ระบบ FG Pricing System', 'success');
            
            // Simulate real-time data updates
            setInterval(updateDashboardData, 30000); // Update every 30 seconds
        });

        function updateDashboardData() {
            // This would normally fetch real data from the backend
            console.log('Updating dashboard data...');
        }

        // Add some CSS for the master tab content
        const style = document.createElement('style');
        style.textContent = `
            .master-tab-content {
                animation: fadeInUp 0.3s ease;
            }
            
            .master-tab-content.active {
                display: block !important;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>