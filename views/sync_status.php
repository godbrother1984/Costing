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
