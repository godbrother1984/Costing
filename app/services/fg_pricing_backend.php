<?php
// =======================================
// FG PRICING SYSTEM - PHASE 2 BACKEND
// Laravel Backend Structure
// =======================================

// 1. ROUTES (routes/web.php)
// =======================================
Route::prefix('api/v1')->group(function () {
    // D365 Integration Routes
    Route::get('/d365/products/{id}', [D365Controller::class, 'getProduct']);
    Route::get('/d365/customers/{id}', [D365Controller::class, 'getCustomer']);
    Route::get('/d365/bom/{fgCode}', [D365Controller::class, 'getBOM']);
    Route::post('/d365/sync', [D365Controller::class, 'syncData']);
    
    // Pricing Routes
    Route::post('/pricing/calculate', [PricingController::class, 'calculate']);
    Route::get('/pricing/history', [PricingController::class, 'getHistory']);
    Route::post('/pricing/save', [PricingController::class, 'save']);
    
    // Master Data Routes
    Route::apiResource('lme-prices', LMEPriceController::class);
    Route::apiResource('fab-costs', FabCostController::class);
    Route::apiResource('standard-prices', StandardPriceController::class);
    Route::apiResource('selling-factors', SellingFactorController::class);
    Route::apiResource('exchange-rates', ExchangeRateController::class);
    
    // Email Routes
    Route::post('/email/quote', [EmailController::class, 'sendQuote']);
    Route::get('/email/templates', [EmailController::class, 'getTemplates']);
});

// 2. D365 INTEGRATION SERVICE
// =======================================
class D365Service
{
    private $apiUrl;
    private $clientId;
    private $clientSecret;
    private $tenantId;
    private $accessToken;

    public function __construct()
    {
        $this->apiUrl = config('d365.api_url');
        $this->clientId = config('d365.client_id');
        $this->clientSecret = config('d365.client_secret');
        $this->tenantId = config('d365.tenant_id');
    }

    /**
     * Get OAuth Access Token from Microsoft
     */
    public function getAccessToken()
    {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials'
        ];

        $response = Http::asForm()->post($url, $data);
        
        if ($response->successful()) {
            $this->accessToken = $response->json()['access_token'];
            return $this->accessToken;
        }
        
        throw new Exception('Failed to get D365 access token');
    }

    /**
     * Get Product Information from D365
     */
    public function getProduct($productId)
    {
        $this->ensureAuthenticated();
        
        $url = "{$this->apiUrl}/data/InventTable";
        $response = Http::withToken($this->accessToken)
            ->get($url, [
                '$filter' => "ItemId eq '{$productId}'",
                '$select' => 'ItemId,ItemName,ItemGroupId,UnitId'
            ]);

        if ($response->successful()) {
            return $response->json()['value'][0] ?? null;
        }
        
        throw new Exception('Failed to fetch product from D365');
    }

    /**
     * Get Customer Information from D365  
     */
    public function getCustomer($customerId)
    {
        $this->ensureAuthenticated();
        
        $url = "{$this->apiUrl}/data/CustTable";
        $response = Http::withToken($this->accessToken)
            ->get($url, [
                '$filter' => "AccountNum eq '{$customerId}'",
                '$select' => 'AccountNum,Name,CustGroup'
            ]);

        if ($response->successful()) {
            return $response->json()['value'][0] ?? null;
        }
        
        throw new Exception('Failed to fetch customer from D365');
    }

    /**
     * Get BOM (Bill of Materials) from D365
     */
    public function getBOM($fgCode)
    {
        $this->ensureAuthenticated();
        
        $url = "{$this->apiUrl}/data/BOMTable";
        $response = Http::withToken($this->accessToken)
            ->get($url, [
                '$filter' => "ItemId eq '{$fgCode}'",
                '$expand' => 'BOMVersion($expand=BOMLine($select=ItemId,BOMQty,UnitId,LineNum))'
            ]);

        if ($response->successful()) {
            $bomData = $response->json()['value'][0] ?? null;
            return $this->processBOMData($bomData);
        }
        
        throw new Exception('Failed to fetch BOM from D365');
    }

    /**
     * Process BOM Data into standardized format
     */
    private function processBOMData($bomData)
    {
        if (!$bomData || !isset($bomData['BOMVersion'])) {
            return null;
        }

        $processedBOM = [];
        foreach ($bomData['BOMVersion'][0]['BOMLine'] as $line) {
            // Get item group to determine Main/Other classification
            $itemGroup = $this->getItemGroup($line['ItemId']);
            $itemType = $this->classifyMaterialType($itemGroup);
            
            $processedBOM[] = [
                'rmCode' => $line['ItemId'],
                'rmName' => $this->getItemName($line['ItemId']),
                'quantity' => $line['BOMQty'],
                'unit' => $line['UnitId'],
                'itemGroup' => $itemGroup,
                'type' => $itemType, // 'Main' or 'Other'
                'lineNumber' => $line['LineNum']
            ];
        }

        return $processedBOM;
    }

    /**
     * Get Item Group for classification
     */
    private function getItemGroup($itemId)
    {
        $product = $this->getProduct($itemId);
        return $product['ItemGroupId'] ?? 'UNKNOWN';
    }

    /**
     * Get Item Name
     */
    private function getItemName($itemId)
    {
        $product = $this->getProduct($itemId);
        return $product['ItemName'] ?? $itemId;
    }

    /**
     * Classify material type based on Item Group
     */
    private function classifyMaterialType($itemGroup)
    {
        // Get classification from database
        $mapping = ItemGroupMapping::where('item_group_code', $itemGroup)->first();
        return $mapping ? $mapping->calculation_type : 'Other';
    }

    private function ensureAuthenticated()
    {
        if (!$this->accessToken) {
            $this->getAccessToken();
        }
    }
}

// 3. PRICING CALCULATION SERVICE
// =======================================
class PricingCalculationService
{
    /**
     * Calculate advanced pricing with real data
     */
    public function calculateAdvancedPrice($customerId, $fgCode, $quantity, $bomData)
    {
        // Get customer group for pricing
        $customer = Customer::where('customer_id', $customerId)->first();
        $customerGroup = $customer->customer_group ?? 'DEFAULT';

        // Calculate material costs
        $mainMaterialCost = $this->calculateMainMaterialCost($bomData, $customerGroup, $quantity);
        $otherMaterialCost = $this->calculateOtherMaterialCost($bomData, $customerGroup, $quantity);
        
        // Calculate fabrication cost
        $fabricationCost = $this->calculateFabricationCost($customerGroup, $quantity);
        
        // Get selling factor
        $pattern = $this->determinePattern($fgCode, $customer);
        $sellingFactor = $this->getSellingFactor($pattern);
        
        // Calculate final prices
        $totalMaterialCost = $mainMaterialCost + $otherMaterialCost;
        $totalCost = $totalMaterialCost + $fabricationCost;
        $unitPrice = ($totalCost / $quantity) * $sellingFactor;
        $totalPrice = $unitPrice * $quantity;

        return [
            'customerId' => $customerId,
            'fgCode' => $fgCode,
            'quantity' => $quantity,
            'customerGroup' => $customerGroup,
            'pattern' => $pattern,
            'breakdown' => [
                'mainMaterialCost' => $mainMaterialCost,
                'otherMaterialCost' => $otherMaterialCost,
                'fabricationCost' => $fabricationCost,
                'sellingFactor' => $sellingFactor
            ],
            'pricing' => [
                'unitPrice' => round($unitPrice, 2),
                'totalPrice' => round($totalPrice, 2)
            ],
            'calculatedAt' => now()
        ];
    }

    /**
     * Calculate main material cost (using LME prices)
     */
    private function calculateMainMaterialCost($bomData, $customerGroup, $quantity)
    {
        $totalCost = 0;
        
        foreach ($bomData as $item) {
            if ($item['type'] === 'Main') {
                $lmePrice = LMEPrice::where('customer_group', $customerGroup)
                    ->where('item_group_code', $item['itemGroup'])
                    ->latest()
                    ->first();
                
                if ($lmePrice) {
                    // Convert USD/MT to THB/Unit based on exchange rate
                    $exchangeRate = $this->getExchangeRate($customerGroup, 'USD/THB');
                    $pricePerUnit = ($lmePrice->price * $exchangeRate) / 1000; // Convert MT to KG
                    $totalCost += $pricePerUnit * $item['quantity'] * $quantity;
                }
            }
        }
        
        return $totalCost;
    }

    /**
     * Calculate other material cost (using standard prices)
     */
    private function calculateOtherMaterialCost($bomData, $customerGroup, $quantity)
    {
        $totalCost = 0;
        
        foreach ($bomData as $item) {
            if ($item['type'] === 'Other') {
                $standardPrice = StandardPrice::where('rm_code', $item['rmCode'])
                    ->where('customer_group', $customerGroup)
                    ->first();
                
                if ($standardPrice) {
                    $totalCost += $standardPrice->price * $item['quantity'] * $quantity;
                }
            }
        }
        
        return $totalCost;
    }

    /**
     * Calculate fabrication cost
     */
    private function calculateFabricationCost($customerGroup, $quantity)
    {
        $fabCost = FabCost::where('customer_group', $customerGroup)->first();
        return $fabCost ? $fabCost->cost_value * $quantity : 0;
    }

    /**
     * Get selling factor based on pattern
     */
    private function getSellingFactor($pattern)
    {
        $factor = SellingFactor::where('pattern', $pattern)->first();
        return $factor ? $factor->factor : 1.25; // Default factor
    }

    /**
     * Get exchange rate
     */
    private function getExchangeRate($customerGroup, $currencyPair)
    {
        $rate = ExchangeRate::where('customer_group', $customerGroup)
            ->where('currency_pair', $currencyPair)
            ->latest()
            ->first();
        
        return $rate ? $rate->rate : 35.0; // Default THB/USD rate
    }

    /**
     * Determine pattern based on product and customer
     */
    private function determinePattern($fgCode, $customer)
    {
        // Logic to determine pattern (Standard, Custom, Premium)
        // This would be based on business rules
        return 'Standard'; // Default
    }
}

// 4. MAIN CONTROLLERS
// =======================================

/**
 * D365 Integration Controller
 */
class D365Controller extends Controller
{
    protected $d365Service;

    public function __construct(D365Service $d365Service)
    {
        $this->d365Service = $d365Service;
    }

    public function getProduct($id)
    {
        try {
            $product = $this->d365Service->getProduct($id);
            return response()->json(['success' => true, 'data' => $product]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCustomer($id)
    {
        try {
            $customer = $this->d365Service->getCustomer($id);
            return response()->json(['success' => true, 'data' => $customer]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getBOM($fgCode)
    {
        try {
            $bom = $this->d365Service->getBOM($fgCode);
            return response()->json(['success' => true, 'data' => $bom]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

/**
 * Pricing Calculation Controller
 */
class PricingController extends Controller
{
    protected $pricingService;

    public function __construct(PricingCalculationService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'customerId' => 'required|string',
            'fgCode' => 'required|string',
            'quantity' => 'required|numeric|min:1'
        ]);

        try {
            // Get BOM data from D365
            $d365Service = app(D365Service::class);
            $bomData = $d365Service->getBOM($validated['fgCode']);
            
            if (!$bomData) {
                return response()->json([
                    'success' => false, 
                    'message' => 'BOM not found for this product'
                ], 404);
            }

            // Calculate pricing
            $result = $this->pricingService->calculateAdvancedPrice(
                $validated['customerId'],
                $validated['fgCode'], 
                $validated['quantity'],
                $bomData
            );

            // Save calculation history
            PriceRequest::create([
                'customer_id' => $validated['customerId'],
                'fg_code' => $validated['fgCode'],
                'quantity' => $validated['quantity'],
                'unit_price' => $result['pricing']['unitPrice'],
                'total_price' => $result['pricing']['totalPrice'],
                'calculation_data' => json_encode($result),
                'created_by' => auth()->id() ?? 'system'
            ]);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

/**
 * Email Controller for sending quotes
 */
class EmailController extends Controller  
{
    public function sendQuote(Request $request)
    {
        $validated = $request->validate([
            'customerEmail' => 'required|email',
            'subject' => 'required|string',
            'template' => 'required|string',
            'pricingData' => 'required|array'
        ]);

        try {
            // Generate PDF quote
            $pdf = $this->generateQuotePDF($validated['pricingData']);
            
            // Send email with attachment
            Mail::to($validated['customerEmail'])
                ->send(new QuoteMail($validated, $pdf));

            return response()->json(['success' => true, 'message' => 'Quote sent successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function generateQuotePDF($pricingData)
    {
        // Generate PDF using Laravel-DomPDF or similar
        return PDF::loadView('emails.quote-pdf', compact('pricingData'))->output();
    }
}

// 5. DATABASE MODELS
// =======================================

/**
 * Price Request Model
 */
class PriceRequest extends Model
{
    protected $fillable = [
        'customer_id', 'fg_code', 'quantity', 'unit_price', 
        'total_price', 'calculation_data', 'created_by'
    ];

    protected $casts = [
        'calculation_data' => 'array'
    ];
}

/**
 * LME Price Model
 */
class LMEPrice extends Model
{
    protected $fillable = [
        'customer_group', 'item_group_code', 'commodity', 'price', 'currency'
    ];
}

/**
 * Fabrication Cost Model
 */
class FabCost extends Model
{
    protected $fillable = [
        'customer_group', 'cost_type', 'cost_value', 'unit', 'description'
    ];
}

/**
 * Standard Price Model  
 */
class StandardPrice extends Model
{
    protected $fillable = [
        'rm_code', 'customer_group', 'price', 'unit', 'effective_date'
    ];
}

/**
 * Selling Factor Model
 */
class SellingFactor extends Model
{
    protected $fillable = [
        'pattern', 'factor', 'description'
    ];
}

/**
 * Exchange Rate Model
 */
class ExchangeRate extends Model
{
    protected $fillable = [
        'customer_group', 'currency_pair', 'rate', 'effective_date'
    ];
}

/**
 * Item Group Mapping Model
 */
class ItemGroupMapping extends Model
{
    protected $fillable = [
        'item_group_code', 'calculation_type', 'description'
    ];
}

// 6. CONFIGURATION (.env variables)
// =======================================
/*
D365_API_URL=https://your-d365-instance.operations.dynamics.com/api/data/v9.2
D365_CLIENT_ID=your-azure-app-client-id
D365_CLIENT_SECRET=your-azure-app-client-secret  
D365_TENANT_ID=your-azure-tenant-id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fg_pricing
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
*/

?>