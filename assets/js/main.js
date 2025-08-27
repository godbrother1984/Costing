// Global variables
let currentTab = 'dashboard';
let currentMasterTab = 'lme-prices';
let rmSearchTimer; // Timer for smart search debounce
let customerSearchTimer; // Timer for customer smart search
let customerGroupsData = []; // Cache for customer groups

// Tab Management
function showTab(tabName) {
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    const navTabs = document.querySelectorAll('.nav-tab');
    navTabs.forEach(tab => tab.classList.remove('active'));
    
    document.getElementById(tabName).classList.add('active');
    const activeButton = Array.from(navTabs).find(btn => btn.getAttribute('onclick').includes(`'${tabName}'`));
    if(activeButton) {
        activeButton.classList.add('active');
    }

    currentTab = tabName;

    if (tabName === 'new-request') {
        showRequestList();
    }
    if (tabName === 'master-data') {
        showMasterTab('lme-prices'); // Default to LME prices tab
    }
}

// Master Data Tab Management
function showMasterTab(tabName) {
    const masterTabs = document.querySelectorAll('.master-tab-content');
    masterTabs.forEach(tab => tab.style.display = 'none');
    
    const masterNavTabs = document.querySelectorAll('#master-data .nav-tab');
    masterNavTabs.forEach(tab => tab.classList.remove('active'));

    document.getElementById(tabName).style.display = 'block';
    event.target.classList.add('active');
    currentMasterTab = tabName;

    // Load data for the selected master tab
    const loadFunctions = {
        'lme-prices': () => { loadLmePrices(); loadCustomerGroupsForDropdown(); },
        'customer-groups': loadCustomerGroups,
        'fab-cost': loadFabCosts,
        'std-prices': loadStandardPrices,
        'factors': loadSellingFactors,
        'exchange': loadExchangeRates,
    };
    if (loadFunctions[tabName]) {
        loadFunctions[tabName]();
    }
}

// --- View management for New Request Tab ---
function showRequestList() {
    document.getElementById('request-list-view').style.display = 'block';
    document.getElementById('request-detail-view').style.display = 'none';
    loadRequests(); 
}

async function showRequestDetail(requestId = null) {
    document.getElementById('request-list-view').style.display = 'none';
    document.getElementById('request-detail-view').style.display = 'block';
    
    // Clear form
    const formIds = ['currentRequestId', 'customerId', 'productDescription', 'drawingFile', 'fgCode', 'quantity'];
    formIds.forEach(id => document.getElementById(id).value = '');
    document.getElementById('file-list').innerHTML = '';
    document.getElementById('productInfo').style.display = 'none';
    document.getElementById('calculationResult').style.display = 'none';
    document.getElementById('boq-table-body').innerHTML = '';
    document.getElementById('existing-boq-view').style.display = 'none';
    
    document.querySelector('input[name="productType"][value="existing"]').checked = true;
    toggleProductTypeFields();

    if (requestId) {
        showNotification(`กำลังโหลดข้อมูลสำหรับคำขอ #${requestId}`, 'info');
        try {
            const response = await fetch(`./api.php?action=get_request_detail&requestId=${requestId}`);
            const result = await response.json();
            if (result.success) {
                const data = result.data;
                document.getElementById('currentRequestId').value = data.requestId;
                document.getElementById('customerId').value = data.customerId;
                document.getElementById('quantity').value = data.quantity;
                
                if (data.productType === 'new') {
                     document.querySelector('input[name="productType"][value="new"]').checked = true;
                     document.getElementById('productDescription').value = data.productDescription;
                     if(data.boq && data.boq.length > 0) {
                         for (const item of data.boq) {
                             await addBoqItem(item.rmCode, item.quantity);
                         }
                     }
                } else {
                     document.querySelector('input[name="productType"][value="existing"]').checked = true;
                     document.getElementById('fgCode').value = data.fgCode;
                }
                toggleProductTypeFields();
            } else {
                 showNotification(result.message, 'error');
            }
        } catch (error) {
            showNotification('ไม่สามารถโหลดรายละเอียดคำขอได้', 'error');
        }
    } else {
        addBoqItem();
    }
}

function toggleProductTypeFields() {
    const productType = document.querySelector('input[name="productType"]:checked').value;
    const existingFields = document.getElementById('existing-product-fields');
    const newFields = document.getElementById('new-product-fields');
    const productInfo = document.getElementById('productInfo');
    const existingBoqView = document.getElementById('existing-boq-view');

    if (productType === 'existing') {
        existingFields.style.display = 'block';
        newFields.style.display = 'none';
    } else {
        existingFields.style.display = 'none';
        newFields.style.display = 'block';
        productInfo.style.display = 'none';
        existingBoqView.style.display = 'none';
    }
}

// --- BOQ Management ---
async function addBoqItem(rmCode = '', quantity = 1) {
    const tableBody = document.getElementById('boq-table-body');
    const newRow = tableBody.insertRow();
    newRow.innerHTML = `
        <td>
            <div class="autocomplete-container">
                <input type="text" class="form-control rm-code-input" value="${rmCode}" placeholder="พิมพ์เพื่อค้นหา..." onkeyup="handleRmSearch(event)" onfocus="handleRmSearch(event)">
            </div>
        </td>
        <td><input type="text" class="form-control rm-desc-input" readonly></td>
        <td><input type="number" class="form-control" value="${quantity}" style="text-align: right;"></td>
        <td><input type="text" class="form-control rm-unit-input" readonly></td>
        <td><button class="btn btn-danger" onclick="removeBoqItem(this)">🗑️</button></td>
    `;
    if (rmCode) {
        await fetchAndFillRmDetails(newRow, rmCode);
    }
}

function removeBoqItem(button) {
    const row = button.closest('tr');
    row.parentNode.removeChild(row);
}

function displayExistingBoq(boqData) {
    const tableBody = document.getElementById('existing-boq-table-body');
    const boqView = document.getElementById('existing-boq-view');
    tableBody.innerHTML = ''; 

    if (boqData && boqData.length > 0) {
        boqData.forEach(item => {
            const row = tableBody.insertRow();
            row.innerHTML = `
                <td>${item.rmCode}</td>
                <td>${item.description}</td>
                <td style="text-align: right;">${item.quantity}</td>
                <td>${item.unit}</td>
            `;
        });
        boqView.style.display = 'block';
    } else {
        boqView.style.display = 'none';
    }
}

// --- File list display ---
function updateFileList() {
    const input = document.getElementById('drawingFile');
    const fileListDiv = document.getElementById('file-list');
    fileListDiv.innerHTML = '';
    if (input.files.length > 0) {
        const list = document.createElement('ul');
        for (const file of input.files) {
            const listItem = document.createElement('li');
            listItem.textContent = `📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            list.appendChild(listItem);
        }
        fileListDiv.appendChild(list);
    }
}


// =================================================
// == API Functions ==
// =================================================

async function loadRequests() {
    const tableBody = document.getElementById('request-list-table-body');
    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">กำลังโหลดข้อมูล...</td></tr>';

    try {
        const response = await fetch('./api.php?action=get_requests');
        const result = await response.json();
        tableBody.innerHTML = '';

        if (result.success && result.data.length > 0) {
            result.data.forEach(req => {
                const row = tableBody.insertRow();
                const statusClass = req.status.toLowerCase();
                row.innerHTML = `
                    <td>${req.requestId}</td>
                    <td>${req.customerName}</td>
                    <td>${req.productIdentifier}</td>
                    <td><span class="status-badge ${statusClass}">${req.status}</span></td>
                    <td>${req.createdAt}</td>
                    <td><button class="btn btn-outline" style="padding: 5px 10px; font-size: 12px;" onclick="showRequestDetail('${req.requestId}')">✏️ ดู/แก้ไข</button></td>
                `;
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">ไม่พบรายการคำขอราคา</td></tr>';
        }
    } catch (error) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; color: red;">ไม่สามารถโหลดข้อมูลได้</td></tr>';
    }
}

async function saveDraft() {
    const customerId = document.getElementById('customerId').value;
    if (!customerId) {
        showNotification('กรุณากรอกรหัสลูกค้าเพื่อบันทึกแบบร่าง', 'warning');
        return;
    }
    
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<div class="loading"></div> กำลังบันทึก...';
    button.disabled = true;

    let boqData = [];
    const productType = document.querySelector('input[name="productType"]:checked').value;
    if (productType === 'new') {
        const tableBody = document.getElementById('boq-table-body');
        for (const row of tableBody.rows) {
            const rmCode = row.querySelector('.rm-code-input').value;
            const quantity = parseFloat(row.querySelector('input[type="number"]').value);
            if (rmCode && quantity > 0) {
                boqData.push({ rmCode: rmCode, quantity: quantity });
            }
        }
    }

    const draftData = {
        requestId: document.getElementById('currentRequestId').value,
        customerId: customerId,
        productType: productType,
        fgCode: document.getElementById('fgCode').value,
        productDescription: document.getElementById('productDescription').value,
        quantity: document.getElementById('quantity').value,
        boq: boqData,
        attachedFiles: Array.from(document.getElementById('drawingFile').files).map(f => f.name)
    };

    try {
        const response = await fetch('./api.php?action=save_draft', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(draftData)
        });
        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            document.getElementById('currentRequestId').value = result.data.requestId;
        } else {
            showNotification(`เกิดข้อผิดพลาด: ${result.message}`, 'error');
        }

    } catch (error) {
        showNotification('ไม่สามารถเชื่อมต่อ API เพื่อบันทึกได้', 'error');
    } finally {
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}

// --- Smart Search Functions ---
function handleRmSearch(event) {
    clearTimeout(rmSearchTimer);
    const inputElement = event.target;
    rmSearchTimer = setTimeout(() => {
        searchRmCode(inputElement);
    }, 300); 
}

async function searchRmCode(inputElement) {
    const row = inputElement.closest('tr');
    const container = row.querySelector('.autocomplete-container');
    const term = inputElement.value;

    closeAllLists();
    if (term.length < 2) return;

    try {
        const response = await fetch(`./api.php?action=search_rm&term=${term}`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            const list = document.createElement('div');
            list.setAttribute('class', 'autocomplete-items');
            container.appendChild(list);

            result.data.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.innerHTML = `<strong>${item.code}</strong> - ${item.description}`;
                itemDiv.addEventListener('click', function() {
                    inputElement.value = item.code;
                    row.querySelector('.rm-desc-input').value = item.description;
                    row.querySelector('.rm-unit-input').value = item.unit;
                    closeAllLists();
                });
                list.appendChild(itemDiv);
            });
        }
    } catch (error) {
        console.error("Search error:", error);
    }
}

function handleCustomerSearch(event) {
    clearTimeout(customerSearchTimer);
    const inputElement = event.target;
    customerSearchTimer = setTimeout(() => {
        searchCustomer(inputElement);
    }, 300);
}

async function searchCustomer(inputElement) {
    const container = inputElement.closest('.autocomplete-container');
    const term = inputElement.value;

    closeAllLists();
    if (term.length < 2) return;

    try {
        const response = await fetch(`./api.php?action=search_customer&term=${term}`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            const list = document.createElement('div');
            list.setAttribute('class', 'autocomplete-items');
            container.appendChild(list);

            result.data.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.innerHTML = `<strong>${item.id}</strong> - ${item.name}`;
                itemDiv.addEventListener('click', function() {
                    inputElement.value = item.id;
                    closeAllLists();
                });
                list.appendChild(itemDiv);
            });
        }
    } catch (error) {
        console.error("Customer search error:", error);
    }
}


async function fetchAndFillRmDetails(row, rmCode) {
    try {
        const response = await fetch(`./api.php?action=search_rm&term=${rmCode}`);
        const result = await response.json();
        if (result.success && result.data.length > 0) {
            const item = result.data.find(d => d.code === rmCode); // Find exact match
            if(item) {
                row.querySelector('.rm-desc-input').value = item.description;
                row.querySelector('.rm-unit-input').value = item.unit;
            }
        }
    } catch (error) {
        console.error("Fetch details error:", error);
    }
}

function closeAllLists(elmnt) {
    const items = document.querySelectorAll('.autocomplete-items');
    for (let i = 0; i < items.length; i++) {
        if (elmnt != items[i] && elmnt != items[i].parentNode.querySelector('input')) {
            items[i].parentNode.removeChild(items[i]);
        }
    }
}

document.addEventListener("click", function (e) {
    closeAllLists(e.target);
});


async function searchProductInfo() {
    const customerId = document.getElementById('customerId').value;
    const fgCode = document.getElementById('fgCode').value;
    
    if (!customerId || !fgCode) {
        showNotification('กรุณากรอกรหัสลูกค้าและรหัสสินค้า', 'error');
        return;
    }
    
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<div class="loading"></div> กำลังค้นหา...';
    button.disabled = true;

    try {
        const response = await fetch(`./api.php?action=search_d365&customerId=${customerId}&fgCode=${fgCode}`);
        const result = await response.json();

        if (result.success) {
            const data = result.data;
            document.getElementById('customerName').value = data.customer.name;
            document.getElementById('drawingNo').value = data.product.drawingNo;
            document.getElementById('partName').value = data.product.partName;
            document.getElementById('model').value = data.product.model;
            
            document.getElementById('productInfo').style.display = 'block';
            
            displayExistingBoq(data.boq);
            
            showNotification('พบข้อมูลสินค้าและ BOQ จาก D365 แล้ว', 'success');
        } else {
            showNotification(`เกิดข้อผิดพลาด: ${result.message}`, 'error');
            document.getElementById('productInfo').style.display = 'none';
            document.getElementById('existing-boq-view').style.display = 'none';
        }
    } catch (error) {
        showNotification('ไม่สามารถเชื่อมต่อ API ได้', 'error');
        console.error('API Error:', error);
    } finally {
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}

async function calculatePrice() {
    const quantity = document.getElementById('quantity').value;
    if (!quantity || quantity <= 0) {
        showNotification('กรุณากรอกจำนวน', 'error');
        return;
    }

    const productType = document.querySelector('input[name="productType"]:checked').value;
    let boqData = [];

    if (productType === 'existing') {
        const tableBody = document.getElementById('existing-boq-table-body');
        for (const row of tableBody.rows) {
            boqData.push({
                rmCode: row.cells[0].innerText,
                quantity: parseFloat(row.cells[2].innerText)
            });
        }
    } else { // 'new' product
        const tableBody = document.getElementById('boq-table-body');
         for (const row of tableBody.rows) {
            boqData.push({
                rmCode: row.querySelector('.rm-code-input').value,
                quantity: parseFloat(row.querySelector('input[type="number"]').value)
            });
        }
    }

    if (boqData.length === 0) {
        showNotification('ไม่พบข้อมูล BOQ สำหรับการคำนวณ', 'error');
        return;
    }
    
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<div class="loading"></div> กำลังคำนวณ...';
    button.disabled = true;

    try {
        const response = await fetch('./api.php?action=calculate_price', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                quantity: quantity,
                boq: boqData
            })
        });
        const result = await response.json();

        if (result.success) {
            const data = result.data;
            document.getElementById('unitPrice').textContent = `฿ ${data.unitPrice.toLocaleString('th-TH', {minimumFractionDigits: 2})}`;
            document.getElementById('totalQty').textContent = `${data.totalQty} pcs`;
            document.getElementById('totalPrice').textContent = `฿ ${data.totalPrice.toLocaleString('th-TH', {minimumFractionDigits: 2})}`;
            
            document.getElementById('calculationResult').style.display = 'block';
            showNotification('คำนวณราคาเรียบร้อยแล้ว', 'success');
        } else {
            showNotification(`เกิดข้อผิดพลาด: ${result.message}`, 'error');
        }
    } catch (error) {
        showNotification('ไม่สามารถเชื่อมต่อ API ได้', 'error');
        console.error('API Error:', error);
    } finally {
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}

// --- Master Data Functions ---
async function loadLmePrices() {
    const tableBody = document.getElementById('lme-prices-table-body');
    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">กำลังโหลดข้อมูล...</td></tr>';

    try {
        const response = await fetch('./api.php?action=get_lme_prices');
        const result = await response.json();
        tableBody.innerHTML = '';

        if (result.success && result.data.length > 0) {
            result.data.forEach(price => {
                const row = tableBody.insertRow();
                row.innerHTML = `
                    <td>${price.customer_group}</td>
                    <td>${price.item_group_code}</td>
                    <td>${price.commodity}</td>
                    <td>${price.price.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td>${price.updated_at}</td>
                    <td><button class="btn" style="padding: 5px 10px; font-size: 12px;">✏️</button></td>
                `;
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">ไม่พบข้อมูลราคา LME</td></tr>';
        }
    } catch (error) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; color: red;">ไม่สามารถโหลดข้อมูลได้</td></tr>';
    }
}

async function addLmePrice() {
    const form = document.getElementById('add-lme-form');
    const priceData = {
        customer_group: form.elements['lme-customer-group'].value,
        item_group_code: form.elements['lme-item-group'].value,
        price: form.elements['lme-price'].value,
    };

    if (!priceData.customer_group || !priceData.item_group_code || !priceData.price) {
        showNotification('กรุณากรอกข้อมูลให้ครบถ้วน', 'warning');
        return;
    }

    try {
        const response = await fetch('./api.php?action=add_lme_price', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(priceData)
        });
        const result = await response.json();
        if (result.success) {
            showNotification(result.message, 'success');
            form.reset();
            loadLmePrices(); // Refresh the table
        } else {
            showNotification(`เกิดข้อผิดพลาด: ${result.message}`, 'error');
        }
    } catch (error) {
        showNotification('ไม่สามารถเชื่อมต่อ API ได้', 'error');
    }
}

async function loadCustomerGroups() {
    const listContainer = document.getElementById('customer-groups-list');
    listContainer.innerHTML = 'กำลังโหลดข้อมูล...';

    try {
        const response = await fetch('./api.php?action=get_customer_groups');
        const result = await response.json();
        listContainer.innerHTML = '';

        if (result.success && result.data.length > 0) {
            customerGroupsData = result.data; // Cache the data
            customerGroupsData.forEach(group => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'group-list-item';
                itemDiv.textContent = `${group.name} (${group.customers.length} ลูกค้า)`;
                itemDiv.onclick = () => showGroupDetails(group.id);
                listContainer.appendChild(itemDiv);
            });
        } else {
            listContainer.innerHTML = '<div class="group-list-item">ไม่พบข้อมูล</div>';
        }
    } catch (error) {
        listContainer.innerHTML = '<div class="group-list-item" style="color: red;">โหลดข้อมูลล้มเหลว</div>';
    }
}

function showGroupDetails(groupId) {
    document.getElementById('group-details-placeholder').style.display = 'none';
    document.getElementById('group-details-view').style.display = 'block';

    // Highlight selected item
    document.querySelectorAll('.group-list-item').forEach(item => item.classList.remove('active'));
    event.target.classList.add('active');

    const group = customerGroupsData.find(g => g.id === groupId);
    if (!group) return;

    document.getElementById('group-details-title').textContent = `รายละเอียดกลุ่ม: ${group.name}`;
    document.getElementById('group-details-desc').textContent = group.description;
    document.getElementById('customer-count').textContent = group.customers.length;
    document.getElementById('current-group-id').value = group.id;

    const customerTableBody = document.getElementById('group-customer-list');
    customerTableBody.innerHTML = '';
    if (group.customers.length > 0) {
        group.customers.forEach(customer => {
            const row = customerTableBody.insertRow();
            row.innerHTML = `<td>${customer.id}</td><td>${customer.name}</td>`;
        });
    } else {
        customerTableBody.innerHTML = '<tr><td colspan="2" style="text-align: center;">ยังไม่มีลูกค้าในกลุ่มนี้</td></tr>';
    }
}

async function addCustomerGroup() {
    const form = document.getElementById('add-customer-group-form');
    const groupData = {
        id: form.elements['customer-group-id'].value,
        name: form.elements['customer-group-name'].value,
        description: form.elements['customer-group-desc'].value,
    };

    if (!groupData.id || !groupData.name) {
        showNotification('กรุณากรอกรหัสและชื่อกลุ่ม', 'warning');
        return;
    }

    try {
        const response = await fetch('./api.php?action=add_customer_group', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(groupData)
        });
        const result = await response.json();
        if (result.success) {
            showNotification(result.message, 'success');
            form.reset();
            loadCustomerGroups(); // Refresh the list
        } else {
            showNotification(`เกิดข้อผิดพลาด: ${result.message}`, 'error');
        }
    } catch (error) {
        showNotification('ไม่สามารถเชื่อมต่อ API ได้', 'error');
    }
}

async function addCustomerToGroup() {
    const form = document.getElementById('add-customer-to-group-form');
    const customerData = {
        groupId: form.elements['current-group-id'].value,
        customerId: form.elements['new-customer-id'].value,
        customerName: form.elements['new-customer-name'].value,
    };

    if (!customerData.groupId || !customerData.customerId || !customerData.customerName) {
        showNotification('กรุณากรอกข้อมูลลูกค้าให้ครบถ้วน', 'warning');
        return;
    }
    // In a real app, you would send this to the API
    showNotification(`จำลองการเพิ่ม ${customerData.customerId} ในกลุ่ม ${customerData.groupId}`, 'success');
    form.reset();
    // Here you would refresh the customer list for the current group
}

async function loadCustomerGroupsForDropdown() {
    const select = document.getElementById('lme-customer-group');
    select.innerHTML = '<option value="">-- กำลังโหลด --</option>';
     try {
        const response = await fetch('./api.php?action=get_customer_groups');
        const result = await response.json();
        select.innerHTML = '<option value="">-- เลือกกลุ่มลูกค้า --</option>';

        if (result.success && result.data.length > 0) {
            result.data.forEach(group => {
                const option = document.createElement('option');
                option.value = group.name;
                option.textContent = group.name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        select.innerHTML = '<option value="">-- โหลดข้อมูลล้มเหลว --</option>';
    }
}


// --- Other Functions ---

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
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    showNotification('ยินดีต้อนรับสู่ระบบ FG Pricing System', 'success');
    loadRequests(); // Load initial request list
});
