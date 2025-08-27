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
        <button class="nav-tab" onclick="showMasterTab('customer-groups')">👥 Customer Groups</button>
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
                <tbody id="lme-prices-table-body">
                    <!-- Data will be loaded by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <div class="card" style="margin-top: 25px;">
            <h3>➕ เพิ่มราคา LME ใหม่</h3>
            <form id="add-lme-form" onsubmit="event.preventDefault(); addLmePrice();">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px;">
                    <div class="form-group">
                        <label for="lme-customer-group" class="form-label">กลุ่มลูกค้า</label>
                        <select id="lme-customer-group" class="form-control" required>
                            <option value="">-- เลือกกลุ่มลูกค้า --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="lme-item-group" class="form-label">กลุ่มวัตถุดิบ (Code)</label>
                        <input type="text" id="lme-item-group" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="lme-price" class="form-label">ราคา (USD/MT)</label>
                        <input type="number" id="lme-price" class="form-control" step="0.01" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    💾 บันทึกราคาใหม่
                </button>
            </form>
        </div>
    </div>

    <div id="fab-cost" class="master-tab-content" style="display: none;">
        <h3>🔧 ต้นทุน Fabrication</h3>
        <!-- Content for Fab Cost -->
    </div>

    <div id="std-prices" class="master-tab-content" style="display: none;">
        <h3>📋 ราคามาตรฐานวัตถุดิบ</h3>
        <!-- Content for Standard Prices -->
    </div>

    <div id="factors" class="master-tab-content" style="display: none;">
        <h3>🧮 ตัวคูณราคาขาย (Selling Factors)</h3>
        <!-- Content for Selling Factors -->
    </div>

    <div id="exchange" class="master-tab-content" style="display: none;">
        <h3>💱 อัตราแลกเปลี่ยน</h3>
        <!-- Content for Exchange Rates -->
    </div>

    <!-- UPDATED: Customer Groups View with 2-column layout -->
    <div id="customer-groups" class="master-tab-content" style="display: none;">
        <h3>👥 กลุ่มลูกค้า (Customer Groups)</h3>
        <div class="master-data-grid">
            <!-- Left Column: Group List -->
            <div class="group-list-container">
                <h4>รายการกลุ่ม</h4>
                <div id="customer-groups-list">
                    <!-- Group list items will be loaded here by JS -->
                </div>
                <div class="card" style="margin-top: 20px;">
                    <h5>➕ เพิ่มกลุ่มใหม่</h5>
                    <form id="add-customer-group-form" onsubmit="event.preventDefault(); addCustomerGroup();">
                         <div class="form-group" style="margin-top: 15px;">
                            <label for="customer-group-id" class="form-label">รหัสกลุ่ม</label>
                            <input type="text" id="customer-group-id" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="customer-group-name" class="form-label">ชื่อกลุ่ม</label>
                            <input type="text" id="customer-group-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="customer-group-desc" class="form-label">รายละเอียด</label>
                            <input type="text" id="customer-group-desc" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">💾 บันทึกกลุ่มใหม่</button>
                    </form>
                </div>
            </div>
            <!-- Right Column: Group Details -->
            <div id="group-details-view" class="group-details-container" style="display: none;">
                <h4 id="group-details-title">รายละเอียดกลุ่ม</h4>
                <p id="group-details-desc"></p>
                <h5>รายชื่อลูกค้าในกลุ่ม (<span id="customer-count">0</span>)</h5>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Customer Name</th>
                            </tr>
                        </thead>
                        <tbody id="group-customer-list">
                            <!-- Customer list for the selected group -->
                        </tbody>
                    </table>
                </div>
                <div class="card" style="margin-top: 20px;">
                    <h5>➕ เพิ่มลูกค้าในกลุ่มนี้</h5>
                    <form id="add-customer-to-group-form" onsubmit="event.preventDefault(); addCustomerToGroup();">
                        <input type="hidden" id="current-group-id">
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 15px;">
                             <div class="form-group">
                                <label for="new-customer-id" class="form-label">Customer ID</label>
                                <input type="text" id="new-customer-id" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new-customer-name" class="form-label">Customer Name</label>
                                <input type="text" id="new-customer-name" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">💾 เพิ่มลูกค้า</button>
                    </form>
                </div>
            </div>
             <div id="group-details-placeholder" class="group-details-container">
                <p style="text-align: center; color: #666; margin-top: 50px;">
                    กรุณาเลือกกลุ่มลูกค้าจากรายการด้านซ้ายเพื่อดูรายละเอียด
                </p>
            </div>
        </div>
    </div>
</div>
