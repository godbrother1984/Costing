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
