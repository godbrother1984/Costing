<!-- New Request Tab -->
<div id="new-request" class="tab-content">
    
    <!-- View 1: Request List -->
    <div id="request-list-view">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: #333; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 28px; letter-spacing: -0.5px;">
                📋 รายการคำขอราคา
            </h2>
            <button class="btn btn-primary" onclick="showRequestDetail()">
                ➕ สร้างคำขอใหม่
            </button>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>เลขที่คำขอ</th>
                        <th>ลูกค้า</th>
                        <th>รหัสสินค้า/รายละเอียด</th>
                        <th>สถานะ</th>
                        <th>วันที่สร้าง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody id="request-list-table-body">
                    <!-- Request list will be loaded here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- View 2: Request Detail/Create Form -->
    <div id="request-detail-view" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="color: #333; display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 28px; letter-spacing: -0.5px;">
                🧮 สร้าง/แก้ไข คำขอราคา
            </h2>
            <button class="btn btn-outline" onclick="showRequestList()">
                ❮ กลับไปที่รายการ
            </button>
        </div>
        
        <input type="hidden" id="currentRequestId">

        <!-- UPDATED: Added autocomplete-container wrapper -->
        <div class="form-group autocomplete-container">
            <label class="form-label">รหัสลูกค้า (Customer ID)</label>
            <input type="text" class="form-control" id="customerId" placeholder="พิมพ์รหัสหรือชื่อลูกค้าเพื่อค้นหา..." onkeyup="handleCustomerSearch(event)" onfocus="handleCustomerSearch(event)">
        </div>

        <div class="form-group">
            <label class="form-label">ประเภทสินค้า</label>
            <div style="display: flex; gap: 20px; align-items: center; padding: 10px 0;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;"><input type="radio" name="productType" value="existing" checked onchange="toggleProductTypeFields()"> สินค้าเดิม (มีรหัส)</label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;"><input type="radio" name="productType" value="new" onchange="toggleProductTypeFields()"> สินค้าใหม่ (ไม่มีรหัส)</label>
            </div>
        </div>

        <!-- Fields for EXISTING product -->
        <div id="existing-product-fields">
            <div class="form-group">
                <label class="form-label">รหัสสินค้า (FG Code)</label>
                <input type="text" class="form-control" id="fgCode" placeholder="เช่น FG001">
            </div>
            <button class="btn btn-outline" onclick="searchProductInfo()">
                🔍 ค้นหาข้อมูลจาก D365
            </button>
            
            <div id="existing-boq-view" class="card" style="margin-top: 20px; display: none;">
                 <h3>สูตรการผลิตจากระบบ (BOQ)</h3>
                 <div class="table-container" style="margin-top: 15px;">
                     <table class="table">
                         <thead>
                             <tr>
                                 <th>รหัสวัตถุดิบ (RM Code)</th>
                                 <th>รายละเอียด</th>
                                 <th>จำนวน</th>
                                 <th>หน่วย</th>
                             </tr>
                         </thead>
                         <tbody id="existing-boq-table-body">
                             <!-- Rows will be populated from API -->
                         </tbody>
                     </table>
                 </div>
            </div>
        </div>

        <!-- Fields for NEW product -->
        <div id="new-product-fields" style="display: none;">
            <div class="form-group">
                <label class="form-label">คำอธิบายสินค้า (Product Description)</label>
                <textarea class="form-control" id="productDescription" rows="3" placeholder="ระบุรายละเอียดสินค้า เช่น ขนาด, วัสดุ, ชื่อเรียกที่ลูกค้าใช้"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">แนบไฟล์ Drawing (.pdf, .dwg, .jpg)</label>
                <input type="file" class="form-control" id="drawingFile" multiple onchange="updateFileList()">
                <div id="file-list" style="margin-top: 10px; font-size: 13px; color: #555;"></div>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                 <h3>สร้างสูตรการผลิตชั่วคราว (Temporary BOQ)</h3>
                 <div class="table-container" style="margin-top: 15px;">
                     <table class="table">
                         <thead>
                             <tr>
                                 <th style="width: 30%;">รหัสวัตถุดิบ (RM Code)</th>
                                 <th style="width: 40%;">รายละเอียด</th>
                                 <th style="width: 15%;">จำนวน</th>
                                 <th style="width: 10%;">หน่วย</th>
                                 <th style="width: 5%;">ลบ</th>
                             </tr>
                         </thead>
                         <tbody id="boq-table-body">
                             <!-- Rows will be added dynamically here -->
                         </tbody>
                     </table>
                 </div>
                 <button class="btn btn-outline" style="margin-top: 15px;" onclick="addBoqItem()">
                     ➕ เพิ่มวัตถุดิบ
                 </button>
            </div>

        </div>

        <div id="productInfo" style="margin-top: 20px; display: none;">
            <div class="card">
                <h3>ข้อมูลสินค้า (จาก D365)</h3>
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

        <div style="margin-top: 30px; text-align: center; display: flex; justify-content: center; gap: 15px;">
             <button class="btn btn-outline" onclick="saveDraft()" style="font-size: 16px; padding: 15px 40px;">
                💾 บันทึกแบบร่าง
            </button>
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
</div>
