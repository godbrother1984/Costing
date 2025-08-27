// =======================================
// FG PRICING SYSTEM - FRONTEND API INTEGRATION
// Phase 2: Real API Connection
// =======================================

/**
 * API Configuration and Base Settings
 */
class APIConfig {
    static BASE_URL = '/api/v1';
    static TIMEOUT = 30000; // 30 seconds
    static RETRY_ATTEMPTS = 3;
    
    static ENDPOINTS = {
        // D365 Integration
        D365_PRODUCT: '/d365/products',
        D365_CUSTOMER: '/d365/customers', 
        D365_BOM: '/d365/bom',
        D365_SYNC: '/d365/sync',
        
        // Pricing Calculations
        PRICING_CALCULATE: '/pricing/calculate',
        PRICING_HISTORY: '/pricing/history',
        PRICING_SAVE: '/pricing/save',
        
        // Master Data
        LME_PRICES: '/lme-prices',
        FAB_COSTS: '/fab-costs',
        STANDARD_PRICES: '/standard-prices',
        SELLING_FACTORS: '/selling-factors',
        EXCHANGE_RATES: '/exchange-rates',
        
        // Email System
        EMAIL_QUOTE: '/email/quote',
        EMAIL_TEMPLATES: '/email/templates'
    };
}

/**
 * Enhanced API Client with Error Handling and Retry Logic
 */
class APIClient {
    constructor() {
        this.baseURL = APIConfig.BASE_URL;
        this.timeout = APIConfig.TIMEOUT;
        this.retryAttempts = APIConfig.RETRY_ATTEMPTS;
    }

    /**
     * Generic request method with retry logic
     */
    async request(method, endpoint, data = null, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        let lastError;

        for (let attempt = 1; attempt <= this.retryAttempts; attempt++) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), this.timeout);

                const config = {
                    method: method.toUpperCase(),
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    },
                    signal: controller.signal,
                    ...options
                };

                if (data && ['POST', 'PUT', 'PATCH'].includes(config.method)) {
                    config.body = JSON.stringify(data);
                }

                const response = await fetch(url, config);
                clearTimeout(timeoutId);

                if (!response.ok) {
                    throw new APIError(`HTTP ${response.status}: ${response.statusText}`, response.status, response);
                }

                const result = await response.json();
                
                // Log successful API calls
                this.logAPICall(method, endpoint, data, result, response.status, attempt);
                
                return result;

            } catch (error) {
                lastError = error;
                console.warn(`API Request attempt ${attempt}/${this.retryAttempts} failed:`, error.message);
                
                // Don't retry on certain errors
                if (error.status >= 400 && error.status < 500) {
                    break;
                }
                
                // Wait before retry (exponential backoff)
                if (attempt < this.retryAttempts) {
                    await this.delay(Math.pow(2, attempt) * 1000);
                }
            }
        }

        // All attempts failed
        this.logAPICall(method, endpoint, data, null, lastError.status || 0, this.retryAttempts, lastError.message);
        throw lastError;
    }

    /**
     * HTTP Methods
     */
    async get(endpoint, params = {}) {
        const queryString = Object.keys(params).length 
            ? '?' + new URLSearchParams(params).toString() 
            : '';
        return this.request('GET', endpoint + queryString);
    }

    async post(endpoint, data) {
        return this.request('POST', endpoint, data);
    }

    async put(endpoint, data) {
        return this.request('PUT', endpoint, data);
    }

    async delete(endpoint) {
        return this.request('DELETE', endpoint);
    }

    /**
     * Utility methods
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    logAPICall(method, endpoint, requestData, responseData, statusCode, attempt, error = null) {
        const logData = {
            timestamp: new Date().toISOString(),
            method,
            endpoint,
            statusCode,
            attempt,
            requestData: requestData ? JSON.stringify(requestData).substring(0, 500) : null,
            responseData: responseData ? JSON.stringify(responseData).substring(0, 500) : null,
            error: error
        };
        
        console.log('API Call Log:', logData);
        
        // Store in localStorage for debugging
        const logs = JSON.parse(localStorage.getItem('api_logs') || '[]');
        logs.push(logData);
        if (logs.length > 100) logs.shift(); // Keep only last 100 logs
        localStorage.setItem('api_logs', JSON.stringify(logs));
    }
}

/**
 * Custom API Error Class
 */
class APIError extends Error {
    constructor(message, status, response) {
        super(message);
        this.name = 'APIError';
        this.status = status;
        this.response = response;
    }
}

/**
 * D365 Integration Service
 */
class D365Service {
    constructor() {
        this.api = new APIClient();
    }

    /**
     * Get product information from D365
     */
    async getProduct(productId) {
        try {
            showLoadingOverlay('กำลังดึงข้อมูลสินค้าจาก D365...');
            
            const response = await this.api.get(`${APIConfig.ENDPOINTS.D365_PRODUCT}/${productId}`);
            
            if (response.success && response.data) {
                return {
                    itemId: response.data.ItemId,
                    itemName: response.data.ItemName,
                    itemGroupId: response.data.ItemGroupId,
                    unitId: response.data.UnitId,
                    standardCost: response.data.StandardCost || 0
                };
            }
            
            throw new Error('Product not found in D365');
        } catch (error) {
            console.error('Error fetching product from D365:', error);
            throw error;
        } finally {
            hideLoadingOverlay();
        }
    }

    /**
     * Get customer information from D365
     */
    async getCustomer(customerId) {
        try {
            showLoadingOverlay('กำลังดึงข้อมูลลูกค้าจาก D365...');
            
            const response = await this.api.get(`${APIConfig.ENDPOINTS.D365_CUSTOMER}/${customerId}`);
            
            if (response.success && response.data) {
                return {
                    accountNum: response.data.AccountNum,
                    name: response.data.Name,
                    custGroup: response.data.CustGroup,
                    email: response.data.Email || '',
                    phone: response.data.Phone || ''
                };
            }
            
            throw new Error('Customer not found in D365');
        } catch (error) {
            console.error('Error fetching customer from D365:', error);
            throw error;
        } finally {
            hideLoadingOverlay();
        }
    }

    /**
     * Get BOM (Bill of Materials) from D365
     */
    async getBOM(fgCode) {
        try {
            showLoadingOverlay('กำลังดึงข้อมูล BOM จาก D365...');
            
            const response = await this.api.get(`${APIConfig.ENDPOINTS.D365_BOM}/${fgCode}`);
            
            if (response.success && response.data) {
                return response.data.map(item => ({
                    rmCode: item.rmCode,
                    rmName: item.rmName,
                    quantity: parseFloat(item.quantity),
                    unit: item.unit,
                    itemGroup: item.itemGroup,
                    type: item.type, // 'Main' or 'Other'
                    lineNumber: item.lineNumber
                }));
            }
            
            throw new Error('BOM not found for this product');
        } catch (error) {
            console.error('Error fetching BOM from D365:', error);
            throw error;
        } finally {
            hideLoadingOverlay();
        }
    }

    /**
     * Trigger data synchronization
     */
    async syncData(syncType = 'all') {
        try {
            showLoadingOverlay('กำลังซิงโครไนซ์ข้อมูลจาก D365...');
            
            const response = await this.api.post(APIConfig.ENDPOINTS.D365_SYNC, {
                syncType: syncType,
                timestamp: new Date().toISOString()
            });
            
            return response;
        } catch (error) {
            console.error('Error syncing data from D365:', error);
            throw error;
        } finally {
            hideLoadingOverlay();
        }
    }
}

/**
 * Advanced Pricing Service
 */
class PricingService {
    constructor() {
        this.api = new APIClient();
        this.d365Service = new D365Service();
    }

    /**
     * Calculate advanced pricing with real data
     */
    async calculateAdvancedPrice(customerId, fgCode, quantity) {
        try {
            showLoadingOverlay('กำลังคำนวณราคาขั้นสูง...');
            
            const calculationData = {
                customerId: customerId,
                fgCode: fgCode,
                quantity: parseInt(quantity),
                calculationDate: new Date().toISOString().split('T')[0]
            };
            
            const response = await this.api.post(APIConfig.ENDPOINTS.PRICING_CALCULATE, calculationData);
            
            if (response.success && response.data) {
                const result = response.data;
                
                // Update the frontend with calculated results
                this.updatePricingDisplay(result);
                
                // Store calculation in app state
                AppState.lastCalculation = result;
                
                return result;
            }
            
            throw new Error('Failed to calculate pricing');
        } catch (error) {
            console.error('Error calculating pricing:', error);
            showNotification('เกิดข้อผิดพลาดในการคำนวณราคา: ' + error.message, 'error');
            throw error;
        } finally {
            hideLoadingOverlay();
        }
    }

    /**
     * Update pricing display on frontend
     */
    updatePricingDisplay(pricingData) {
        // Update main calculation results
        const elements = {
            mainMaterialCost: document.getElementById('mainMaterialCost'),
            otherMaterialCost: document.getElementById('otherMaterialCost'),
            fabricationCost: document.getElementById('fabricationCost'),
            sellingFactor: document.getElementById('sellingFactor'),
            unitPrice: document.getElementById('unitPrice'),
            totalPrice: document.getElementById('totalPrice')
        };

        if (elements.mainMaterialCost) {
            elements.mainMaterialCost.textContent = this.formatCurrency(pricingData.breakdown.mainMaterialCost);
        }
        if (elements.otherMaterialCost) {
            elements.otherMaterialCost.textContent = this.formatCurrency(pricingData.breakdown.otherMaterialCost);
        }
        if (elements.fabricationCost) {
            elements.fabricationCost.textContent = this.formatCurrency(pricingData.breakdown.fabricationCost);
        }
        if (elements.sellingFactor) {
            elements.sellingFactor.textContent = `${pricingData.breakdown.sellingFactor}x`;
        }
        if (elements.unitPrice) {
            elements.unitPrice.textContent = this.formatCurrency(pricingData.pricing.unitPrice);
        }
        if (elements.totalPrice) {
            elements.totalPrice.textContent = this.formatCurrency(pricingData.pricing.totalPrice);
        }

        // Show results section
        const resultsSection = document.getElementById('advancedResults') || document.getElementById('calculationResults');
        if (resultsSection) {
            resultsSection.style.display = 'block';
            resultsSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    /**
     * Save pricing calculation
     */
    async savePricingCalculation(pricingData, notes = '') {
        try {
            const saveData = {
                ...pricingData,
                notes: notes,
                savedAt: new Date().toISOString()
            };
            
            const response = await this.api.post(APIConfig.ENDPOINTS.PRICING_SAVE, saveData);
            return response;
        } catch (error) {
            console.error('Error saving pricing calculation:', error);
            throw error;
        }
    }

    /**
     * Format currency display
     */
    formatCurrency(amount, currency = 'THB') {
        return new Intl.NumberFormat('th-TH', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2
        }).format(amount || 0);
    }
}

/**
 * Master Data Service
 */
class MasterDataService {
    constructor() {
        this.api = new APIClient();
    }

    /**
     * Get LME Prices
     */
    async getLMEPrices(customerGroup = null) {
        try {
            const params = customerGroup ? { customerGroup } : {};
            return await this.api.get(APIConfig.ENDPOINTS.LME_PRICES, params);
        } catch (error) {
            console.error('Error fetching LME prices:', error);
            throw error;
        }
    }

    /**
     * Update LME Price
     */
    async updateLMEPrice(priceData) {
        try {
            showLoadingOverlay('กำลังอัพเดทราคา LME...');
            
            const response = await this.api.post(APIConfig.ENDPOINTS.LME_PRICES, priceData);
            
            if (response.success) {
                showNotification('อัพเดทราคา LME เรียบร้อย', 'success');
                await this.refreshLMETable();
            }
            
            return response;
        } catch (error) {
            console.error('Error updating LME price:', error);
            showNotification('เกิดข้อผิดพลาดในการอัพเดทราคา LME', 'error');
            throw error;
        } finally {
            hideLoadingOverlay();
        }
    }

    /**
     * Get Fabrication Costs
     */
    async getFabCosts(customerGroup = null) {
        try {
            const params = customerGroup ? { customerGroup } : {};
            return await this.api.get(APIConfig.ENDPOINTS.FAB_COSTS, params);
        } catch (error) {
            console.error('Error fetching fab costs:', error);
            throw error;
        }
    }

    /**
     * Update Fabrication Cost
     */
    async updateFabCost(costData) {
        try {
            showLoadingOverlay('กำลังอัพเดทต้นทุน Fabrication...');
            
            const response = await this.api.post(APIConfig.ENDPOINTS.FAB_COSTS, costData);
            
            if (response.success) {
                showNotification('อัพเดทต้นทุน Fabrication เรียบร้อย', 'success');
                await this.refreshFabCostTable();
            }
            
            return response;
        } catch (error) {
            console.error('Error updating fab cost:', error);
            showNotification('เกิดข้อผิดพลาดในการอัพเดทต้นทุน', 'error');
            throw error;
        } finally {
            hideLoadingOverlay();
        }
    }

    /**
     * Get Standard Prices
     */
    async getStandardPrices(customerGroup = null) {
        try {
            const params = customerGroup ? { customerGroup } : {};
            return await this.api.get(APIConfig.ENDPOINTS.STANDARD_PRICES, params);
        } catch (error) {
            console.error('Error fetching standard prices:', error);
            throw error;
        }
    }

    /**
     * Refresh table displays
     */
    async refreshLMETable() {
        try {
            const lmePrices = await this.getLMEPrices();
            this.updateTableDisplay('lme-prices-table', lmePrices.data);
        } catch (error) {
            console.error('Error refreshing LME table:', error);
        }
    }

    async refreshFabCostTable() {
        try {
            const fabCosts = await this.getFabCosts();
            this.updateTableDisplay('fab-costs-table', fabCosts.data);
        } catch (error) {
            console.error('Error refreshing fab cost table:', error);
        }
    }

    /**
     * Generic table update method
     */
    updateTableDisplay(tableId, data) {
        const tableBody = document.querySelector(`#${tableId} tbody`);
        if (!tableBody || !data) return;

        tableBody.innerHTML = '';
        
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = this.generateTableRowHTML(tableId, row);
            tableBody.appendChild(tr);
        });
    }

    /**
     * Generate table row HTML based on table type
     */
    generateTableRowHTML(tableId, rowData) {
        switch (tableId) {
            case 'lme-prices-table':
                return `
                    <td>${rowData.customer_group}</td>
                    <td>${rowData.item_group_code}</td>
                    <td>${rowData.commodity}</td>
                    <td>${parseFloat(rowData.price).toFixed(2)}</td>
                    <td>${this.formatDate(rowData.updated_at)}</td>
                    <td>
                        <button class="btn btn-sm" onclick="editLMEPrice(${rowData.id})">✏️</button>
                    </td>
                `;
            case 'fab-costs-table':
                return `
                    <td>${rowData.customer_group}</td>
                    <td>${rowData.cost_type}</td>
                    <td>฿${parseFloat(rowData.cost_value).toFixed(2)}</td>
                    <td>${rowData.unit}</td>
                    <td>${rowData.description || '-'}</td>
                    <td>${this.formatDate(rowData.updated_at)}</td>
                    <td>
                        <button class="btn btn-sm" onclick="editFabCost(${rowData.id})">✏️</button>
                    </td>
                `;
            default:
                return '<td colspan="100%">No data available</td>';
        }
    }

    /**
     * Format date for display
     */
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('th-TH', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

/**
 * Email Service for Quote Management
 */
class EmailService {
    constructor() {
        this.api = new APIClient();
    }

    /**
     * Send quote email
     */
    async sendQuoteEmail(emailData, pricingData) {
        try {
            showLoadingOverlay('กำลังส่งอีเมลใบเสนอราคา...');
            
            const payload = {
                to: emailData.customerEmail,
                subject: emailData.subject,
                template: emailData.template || 'standard-quote',
                pricingData: pricingData,
                customerData: {
                    name: emailData.customerName || 'ลูกค้า',
                    company: emailData.customerCompany || ''
                },
                additionalData: emailData.additionalData || {}
            };
            
            const response = await this.api.post(APIConfig.ENDPOINTS.EMAIL_QUOTE, payload);
            
            if (response.success) {
                showNotification('ส่งอีเมลใบเสนอราคาเรียบร้อย', 'success');
                
                // Update request status if applicable
                if (AppState.currentRequestId) {
                    await this.updateRequestStatus(AppState.currentRequestId, 'Quoted');
                }
            }
            
            return response;
        } catch (error) {
            console.error('Error sending quote email:', error);
            showNotification('เกิดข้อผิดพลาดในการส่งอีเมล: ' + error.message, 'error');
            throw error;
        } finally {
            hideLoadingOverlay();
        }
    }

    /**
     * Get available email templates
     */
    async getEmailTemplates() {
        try {
            return await this.api.get(APIConfig.ENDPOINTS.EMAIL_TEMPLATES);
        } catch (error) {
            console.error('Error fetching email templates:', error);
            throw error;
        }
    }

    /**
     * Update request status
     */
    async updateRequestStatus(requestId, status) {
        try {
            return await this.api.put(`${APIConfig.ENDPOINTS.PRICING_SAVE}/${requestId}`, { status });
        } catch (error) {
            console.error('Error updating request status:', error);
        }
    }
}

/**
 * Enhanced Application State Management
 */
class EnhancedAppState {
    constructor() {
        this.currentTab = 'dashboard';
        this.currentMasterTab = 'lme-prices';
        this.currentRequestId = null;
        this.selectedCustomer = null;
        this.selectedProduct = null;
        this.bomData = [];
        this.lastCalculation = null;
        this.apiLogs = [];
        
        // Initialize services
        this.d365Service = new D365Service();
        this.pricingService = new PricingService();
        this.masterDataService = new MasterDataService();
        this.emailService = new EmailService();
    }

    /**
     * Load customer and product data from D365
     */
    async loadFromD365() {
        const customerId = document.getElementById('customerId')?.value;
        const fgCode = document.getElementById('fgCode')?.value;
        
        if (!customerId || !fgCode) {
            showNotification('กรุณากรอกรหัสลูกค้าและรหัสสินค้า', 'error');
            return false;
        }

        try {
            // Load customer data
            const customerData = await this.d365Service.getCustomer(customerId);
            this.selectedCustomer = customerData;
            
            // Load product data
            const productData = await this.d365Service.getProduct(fgCode);
            this.selectedProduct = productData;
            
            // Load BOM data
            const bomData = await this.d365Service.getBOM(fgCode);
            this.bomData = bomData;
            
            // Update frontend display
            this.updateProductInfoDisplay(customerData, productData);
            this.updateBOMDisplay(bomData);
            
            showNotification('ดึงข้อมูลจาก D365 สำเร็จ', 'success');
            return true;
        } catch (error) {
            console.error('Error loading data from D365:', error);
            showNotification('ไม่สามารถดึงข้อมูลจาก D365 ได้: ' + error.message, 'error');
            return false;
        }
    }

    /**
     * Update product information display
     */
    updateProductInfoDisplay(customerData, productData) {
        const elements = {
            customerName: document.getElementById('customerName'),
            customerGroup: document.getElementById('customerGroup'),
            drawingNo: document.getElementById('drawingNo'),
            partName: document.getElementById('partName'),
            model: document.getElementById('model'),
            pattern: document.getElementById('pattern')
        };

        if (elements.customerName) elements.customerName.value = customerData.name || '';
        if (elements.customerGroup) elements.customerGroup.value = customerData.custGroup || '';
        if (elements.drawingNo) elements.drawingNo.value = `DRW-${new Date().getFullYear()}-${fgCode}`;
        if (elements.partName) elements.partName.value = productData.itemName || '';
        if (elements.model) elements.model.value = 'Model-' + (productData.itemId.split('-')[1] || 'X1');
        if (elements.pattern) elements.pattern.value = this.determinePattern(productData, customerData);

        // Show product info section
        const productInfoSection = document.getElementById('productInfoSection') || document.getElementById('productInfo');
        if (productInfoSection) {
            productInfoSection.style.display = 'block';
        }
    }

    /**
     * Update BOM display
     */
    updateBOMDisplay(bomData) {
        const bomTableBody = document.getElementById('bomTableBody');
        if (!bomTableBody) return;

        bomTableBody.innerHTML = '';

        bomData.forEach(item => {
            const row = bomTableBody.insertRow();
            row.innerHTML = `
                <td style="font-weight: 600;">${item.rmCode}</td>
                <td>${item.rmName}</td>
                <td><span class="material-badge ${item.type === 'Main' ? 'material-main' : 'material-other'}">${item.type}</span></td>
                <td style="text-align: right; font-weight: 600;">${item.quantity.toFixed(3)}</td>
                <td>${item.unit}</td>
                <td>${item.itemGroup}</td>
                <td style="text-align: right; color: #667eea;" id="price-${item.rmCode}">คำนวณ...</td>
                <td style="text-align: right; font-weight: 600; color: #28a745;" id="total-${item.rmCode}">คำนวณ...</td>
            `;
        });

        // Show BOM section
        const bomSection = document.getElementById('bomSection') || document.getElementById('bomData');
        if (bomSection) {
            bomSection.style.display = 'block';
        }
    }

    /**
     * Calculate advanced pricing
     */
    async calculateAdvancedPrice() {
        const quantity = document.getElementById('productionQty')?.value || document.getElementById('quantity')?.value;
        
        if (!quantity || quantity <= 0) {
            showNotification('กรุณากรอกจำนวนที่ต้องการผลิต', 'error');
            return;
        }

        if (!this.selectedCustomer || !this.selectedProduct) {
            showNotification('กรุณาดึงข้อมูลจาก D365 ก่อน', 'error');
            return;
        }

        try {
            const result = await this.pricingService.calculateAdvancedPrice(
                this.selectedCustomer.accountNum,
                this.selectedProduct.itemId,
                quantity
            );

            // Store result
            this.lastCalculation = result;
            
            // Update BOM prices if available
            if (result.bomDetails) {
                this.updateBOMPrices(result.bomDetails);
            }

            return result;
        } catch (error) {
            console.error('Error in advanced price calculation:', error);
            throw error;
        }
    }

    /**
     * Update BOM prices in display
     */
    updateBOMPrices(bomDetails) {
        bomDetails.forEach(item => {
            const priceElement = document.getElementById(`price-${item.rmCode}`);
            const totalElement = document.getElementById(`total-${item.rmCode}`);
            
            if (priceElement) {
                priceElement.textContent = `฿${item.unitPrice.toFixed(2)}`;
            }
            if (totalElement) {
                totalElement.textContent = `฿${item.totalPrice.toFixed(2)}`;
            }
        });
    }

    /**
     * Send advanced quote
     */
    async sendAdvancedQuote() {
        if (!this.lastCalculation) {
            showNotification('กรุณาคำนวณราคาก่อนส่งใบเสนอราคา', 'error');
            return;
        }

        const emailData = {
            customerEmail: this.selectedCustomer?.email || prompt('กรุณากรอกอีเมลลูกค้า:'),
            customerName: this.selectedCustomer?.name,
            customerCompany: this.selectedCustomer?.name,
            subject: `ใบเสนอราคา - ${this.selectedProduct?.itemName}`,
            template: 'standard-quote'
        };

        if (!emailData.customerEmail) {
            showNotification('กรุณากรอกอีเมลลูกค้า', 'error');
            return;
        }

        try {
            await this.emailService.sendQuoteEmail(emailData, this.lastCalculation);
            return true;
        } catch (error) {
            console.error('Error sending advanced quote:', error);
            return false;
        }
    }

    /**
     * Determine pattern based on product and customer
     */
    determinePattern(productData, customerData) {
        // Business logic to determine pattern
        if (customerData.custGroup === 'กลุ่ม A') return 'Premium';
        if (productData.itemName?.includes('Custom')) return 'Custom';
        return 'Standard';
    }

    /**
     * Save pricing data
     */
    async savePricingData() {
        if (!this.lastCalculation) {
            showNotification('ไม่มีข้อมูลการคำนวณราคาให้บันทึก', 'warning');
            return;
        }

        try {
            const response = await this.pricingService.savePricingCalculation(this.lastCalculation);
            if (response.success) {
                this.currentRequestId = response.data.id;
                showNotification('บันทึกข้อมูลการคำนวณราคาเรียบร้อย', 'success');
            }
        } catch (error) {
            console.error('Error saving pricing data:', error);
            showNotification('เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
        }
    }
}

// =======================================
// ENHANCED FRONTEND INTEGRATION
// =======================================

/**
 * Initialize enhanced application
 */
function initializeEnhancedApp() {
    // Replace global AppState with enhanced version
    window.AppState = new EnhancedAppState();
    
    // Initialize services
    window.d365Service = AppState.d365Service;
    window.pricingService = AppState.pricingService;
    window.masterDataService = AppState.masterDataService;
    window.emailService = AppState.emailService;
    
    // Replace existing functions with enhanced versions
    window.searchD365Data = () => AppState.loadFromD365();
    window.loadFromD365 = () => AppState.loadFromD365();
    window.calculateAdvancedPrice = () => AppState.calculateAdvancedPrice();
    window.sendAdvancedQuote = () => AppState.sendAdvancedQuote();
    window.savePricingData = () => AppState.savePricingData();
    
    // Master data functions
    window.updateMasterData = async () => {
        try {
            await AppState.d365Service.syncData();
            showNotification('ซิงค์ข้อมูลเรียบร้อย', 'success');
        } catch (error) {
            showNotification('เกิดข้อผิดพลาดในการซิงค์ข้อมูล', 'error');
        }
    };
    
    // Enhanced notification system
    window.showNotification = function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type} show`;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="${getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 500);
        }, 4000);
    };
    
    // Enhanced loading overlay
    window.showLoadingOverlay = function(message = 'กำลังโหลด...') {
        let overlay = document.querySelector('.loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            document.body.appendChild(overlay);
        }
        
        overlay.innerHTML = `
            <div style="text-align: center; color: white; background: rgba(102, 126, 234, 0.9); padding: 30px; border-radius: 15px; backdrop-filter: blur(10px);">
                <div style="width: 50px; height: 50px; border: 4px solid rgba(255,255,255,0.3); border-top: 4px solid white; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                <p style="margin: 0; font-size: 16px; font-weight: 500;">${message}</p>
            </div>
        `;
        overlay.style.display = 'flex';
    };
    
    window.hideLoadingOverlay = function() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 300);
        }
    };
    
    console.log('🚀 Enhanced FG Pricing System initialized with real API integration');
}

// =======================================
// UTILITY FUNCTIONS
// =======================================

function getNotificationIcon(type) {
    const icons = {
        'success': '✅',
        'error': '❌', 
        'warning': '⚠️',
        'info': 'ℹ️'
    };
    return icons[type] || 'ℹ️';
}

/**
 * Export PDF functionality
 */
async function exportToPDF() {
    if (!AppState.lastCalculation) {
        showNotification('ไม่มีข้อมูลให้ส่งออก', 'warning');
        return;
    }

    try {
        showLoadingOverlay('กำลังสร้าง PDF...');
        
        // This would integrate with a PDF generation service
        // For now, we'll create a simple HTML print view
        const printWindow = window.open('', '_blank');
        const pdfContent = generatePDFContent(AppState.lastCalculation);
        
        printWindow.document.write(pdfContent);
        printWindow.document.close();
        printWindow.print();
        
        showNotification('เปิดหน้าต่างสำหรับ Export PDF แล้ว', 'success');
    } catch (error) {
        console.error('Error exporting PDF:', error);
        showNotification('เกิดข้อผิดพลาดในการส่งออก PDF', 'error');
    } finally {
        hideLoadingOverlay();
    }
}

function generatePDFContent(calculationData) {
    return `
        <!DOCTYPE html>
        <html>
        <head>
            <title>ใบเสนอราคา</title>
            <style>
                body { font-family: 'Sarabun', sans-serif; margin: 40px; }
                .header { text-align: center; margin-bottom: 30px; }
                .details { margin: 20px 0; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                th { background-color: #f5f5f5; }
                .total { font-size: 18px; font-weight: bold; color: #2c5aa0; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>ใบเสนอราคา</h1>
                <p>วันที่: ${new Date().toLocaleDateString('th-TH')}</p>
            </div>
            
            <div class="details">
                <p><strong>ลูกค้า:</strong> ${AppState.selectedCustomer?.name || '-'}</p>
                <p><strong>สินค้า:</strong> ${AppState.selectedProduct?.itemName || '-'}</p>
                <p><strong>จำนวน:</strong> ${calculationData.quantity} ชิ้น</p>
            </div>
            
            <table>
                <tr><th>รายการ</th><th>จำนวนเงิน (บาท)</th></tr>
                <tr><td>ต้นทุนวัตถุดิบหลัก</td><td>${calculationData.breakdown.mainMaterialCost.toLocaleString()}</td></tr>
                <tr><td>ต้นทุนวัตถุดิบอื่นๆ</td><td>${calculationData.breakdown.otherMaterialCost.toLocaleString()}</td></tr>
                <tr><td>ต้นทุนการผลิต</td><td>${calculationData.breakdown.fabricationCost.toLocaleString()}</td></tr>
                <tr class="total"><td>รวมทั้งสิ้น</td><td>${calculationData.pricing.totalPrice.toLocaleString()}</td></tr>
            </table>
            
            <p style="margin-top: 30px; font-size: 12px; color: #666;">
                * ใบเสนอราคานี้มีอายุ 30 วันนับจากวันที่ออกใบเสนอราคา<br>
                * ราคาไม่รวม VAT 7%
            </p>
        </body>
        </html>
    `;
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEnhancedApp);
} else {
    initializeEnhancedApp();
}