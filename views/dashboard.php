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
            <a href="#" class="action-btn" onclick="showTab('new-request'); showRequestDetail();">
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
