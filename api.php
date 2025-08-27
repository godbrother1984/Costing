<?php
// =======================================
// FG PRICING SYSTEM - BACKEND API (SIMULATOR)
// =======================================

header('Content-Type: application/json');
sleep(0.5); // Simulate network delay

$requestDbFile = 'database.json';
$masterDbFile = 'master_data.json';

// Helper function to read from JSON database
function readDb($file) {
    if (!file_exists($file)) {
        file_put_contents($file, '{}'); 
        return [];
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

// Helper function to write to JSON database
function writeDb($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $json);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    // Request Actions
    case 'get_requests': getRequests($requestDbFile); break;
    case 'get_request_detail': getRequestDetail($requestDbFile); break;
    case 'save_draft': saveDraft($requestDbFile); break;
    
    // D365 Simulation Actions
    case 'search_d365': searchD365(); break;
    case 'search_rm': searchRawMaterial($masterDbFile); break;
    case 'search_customer': searchCustomer($masterDbFile); break;
    
    // Calculation Action
    case 'calculate_price': calculatePrice(); break;

    // Master Data Actions
    case 'get_lme_prices': getMasterData($masterDbFile, 'lme_prices'); break;
    case 'add_lme_price': addLmePrice($masterDbFile); break;
    case 'get_customer_groups': getMasterData($masterDbFile, 'customer_groups'); break;
    case 'add_customer_group': addCustomerGroup($masterDbFile); break;
    case 'get_fab_costs': getMasterData($masterDbFile, 'fab_costs'); break;
    case 'add_fab_cost': addFabCost($masterDbFile); break;
    case 'get_standard_prices': getMasterData($masterDbFile, 'standard_prices'); break;
    case 'add_standard_price': addStandardPrice($masterDbFile); break;
    case 'get_selling_factors': getMasterData($masterDbFile, 'selling_factors'); break;
    case 'add_selling_factor': addSellingFactor($masterDbFile); break;
    case 'get_exchange_rates': getMasterData($masterDbFile, 'exchange_rates'); break;
    case 'add_exchange_rate': addExchangeRate($masterDbFile); break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid API action']);
        break;
}

// --- Generic Master Data Getter ---
function getMasterData($file, $key) {
    $masterData = readDb($file);
    echo json_encode(['success' => true, 'data' => $masterData[$key] ?? []]);
}

// --- Request Functions ---
function getRequests($file) {
    $requests = readDb($file);
    echo json_encode(['success' => true, 'data' => $requests]);
}

function getRequestDetail($file) {
    $requestId = $_GET['requestId'] ?? null;
    $requests = readDb($file);
    foreach ($requests as $request) {
        if ($request['requestId'] === $requestId) {
            echo json_encode(['success' => true, 'data' => $request]);
            return;
        }
    }
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Request not found.']);
}

function saveDraft($file) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['customerId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Customer ID is required.']);
        return;
    }
    $requests = readDb($file);
    $requestId = $input['requestId'] ?? null;
    $isUpdate = false;
    $savedRequest = null;
    if ($requestId) {
        foreach ($requests as $index => $req) {
            if ($req['requestId'] === $requestId) {
                $requests[$index] = array_merge($requests[$index], $input);
                $isUpdate = true;
                $savedRequest = $requests[$index];
                break;
            }
        }
    } 
    if (!$isUpdate) {
        $newRequest = array_merge($input, [
            'requestId' => 'REQ-' . date('Ymd') . '-' . (count($requests) + 101),
            'customerName' => 'ลูกค้า (ใหม่)',
            'status' => 'Pending',
            'createdAt' => date('d/m/Y H:i')
        ]);
        $requests[] = $newRequest;
        $savedRequest = $newRequest;
    }
    writeDb($file, $requests);
    echo json_encode([
        'success' => true, 
        'message' => $isUpdate ? 'อัปเดตคำขอเรียบร้อยแล้ว' : 'บันทึกแบบร่างสำเร็จ',
        'data' => $savedRequest
    ]);
}

// --- D365 Simulation Functions ---
function searchD365() {
    $mockData = [
        'customer' => ['name' => 'บริษัท ABC จำกัด (ข้อมูลจาก API)'],
        'product' => ['drawingNo' => 'DRW-2025-API-01', 'partName' => 'แผ่นโลหะฐานเครื่องจักร (ข้อมูลจาก API)', 'model' => 'Model-X1-API'],
        'boq' => [
            ['rmCode' => 'RM-STEEL-01', 'description' => 'แผ่นเหล็กหนา 2mm', 'quantity' => 2.5, 'unit' => 'KG'],
            ['rmCode' => 'RM-SCREW-M6', 'description' => 'สกรู M6', 'quantity' => 8, 'unit' => 'PCS']
        ]
    ];
    echo json_encode(['success' => true, 'data' => $mockData]);
}

function searchRawMaterial($file) {
    $term = strtolower($_GET['term'] ?? '');
    if (strlen($term) < 2) {
        echo json_encode(['success' => true, 'data' => []]);
        return;
    }
    $rmDatabase = [
        'RM-STEEL-01' => ['description' => 'แผ่นเหล็กหนา 2mm (จาก D365)', 'unit' => 'KG'],
        'RM-SCREW-M6' => ['description' => 'สกรู M6x20mm (จาก D365)', 'unit' => 'PCS'],
        'RM-ALU-05' => ['description' => 'อลูมิเนียมเส้น (จาก D365)', 'unit' => 'Meter'],
        'RM-PAINT-01' => ['description' => 'สีกันสนิม สีเทา (จาก D365)', 'unit' => 'Litre']
    ];
    $matches = [];
    foreach ($rmDatabase as $code => $data) {
        if (stripos(strtolower($code), $term) !== false || stripos(strtolower($data['description']), $term) !== false) {
            $matches[] = ['code' => $code, 'description' => $data['description'], 'unit' => $data['unit']];
        }
    }
    echo json_encode(['success' => true, 'data' => $matches]);
}

function searchCustomer($file) {
    $term = strtolower($_GET['term'] ?? '');
    if (strlen($term) < 2) {
        echo json_encode(['success' => true, 'data' => []]);
        return;
    }
    $masterData = readDb($file);
    $customers = $masterData['customers'] ?? [];
    $matches = [];
    foreach ($customers as $customer) {
        if (stripos(strtolower($customer['id']), $term) !== false || stripos(strtolower($customer['name']), $term) !== false) {
            $matches[] = $customer;
        }
    }
    echo json_encode(['success' => true, 'data' => $matches]);
}

// --- Calculation Function ---
function calculatePrice() {
    $input = json_decode(file_get_contents('php://input'), true);
    $quantity = $input['quantity'] ?? 0;
    $boq = $input['boq'] ?? [];
    if ($quantity <= 0 || empty($boq)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantity and BOQ are required.']);
        return;
    }
    $rmPriceDatabase = [
        'RM-STEEL-01' => 85.50, 'RM-SCREW-M6' => 2.75, 'RM-ALU-05' => 120.00, 'RM-PAINT-01' => 350.00
    ];
    $totalMaterialCost = 0;
    foreach ($boq as $item) {
        if (isset($rmPriceDatabase[$item['rmCode']])) {
            $totalMaterialCost += $rmPriceDatabase[$item['rmCode']] * $item['quantity'];
        }
    }
    $fabricationCostPerUnit = 150.00;
    $sellingFactor = 1.35;
    $totalCostPerUnit = $totalMaterialCost + $fabricationCostPerUnit;
    $unitPrice = $totalCostPerUnit * $sellingFactor;
    $totalPrice = $unitPrice * $quantity;
    $result = [
        'unitPrice' => round($unitPrice, 2), 'totalQty' => (int)$quantity, 'totalPrice' => round($totalPrice, 2)
    ];
    echo json_encode(['success' => true, 'data' => $result]);
}

// --- Master Data Functions ---
function addLmePrice($file) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['customer_group']) || empty($input['item_group_code']) || empty($input['price'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        return;
    }
    $masterData = readDb($file);
    $newPrice = [
        'id' => count($masterData['lme_prices'] ?? []) + 1,
        'customer_group' => $input['customer_group'],
        'item_group_code' => $input['item_group_code'],
        'commodity' => $input['commodity'] ?? $input['item_group_code'],
        'price' => (float)$input['price'],
        'updated_at' => date('d/m/Y H:i')
    ];
    $masterData['lme_prices'][] = $newPrice;
    writeDb($file, $masterData);
    echo json_encode(['success' => true, 'message' => 'LME price added successfully.', 'data' => $newPrice]);
}

function addCustomerGroup($file) {
    $input = json_decode(file_get_contents('php://input'), true);
     if (empty($input['id']) || empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Group ID and Name are required.']);
        return;
    }
    $masterData = readDb($file);
    $newGroup = [
        'id' => $input['id'],
        'name' => $input['name'],
        'description' => $input['description'] ?? '',
        'customers' => []
    ];
    $masterData['customer_groups'][] = $newGroup;
    writeDb($file, $masterData);
    echo json_encode(['success' => true, 'message' => 'Customer group added successfully.', 'data' => $newGroup]);
}

function addFabCost($file) {
    $input = json_decode(file_get_contents('php://input'), true);
    $masterData = readDb($file);
    $newItem = [
        'id' => count($masterData['fab_costs'] ?? []) + 1,
        'work_type' => $input['work_type'],
        'cost' => (float)$input['cost'],
        'unit' => $input['unit'],
        'description' => $input['description'] ?? ''
    ];
    $masterData['fab_costs'][] = $newItem;
    writeDb($file, $masterData);
    echo json_encode(['success' => true, 'message' => 'Fab cost added.', 'data' => $newItem]);
}

function addStandardPrice($file) {
    $input = json_decode(file_get_contents('php://input'), true);
    $masterData = readDb($file);
    $newItem = [
        'id' => count($masterData['standard_prices'] ?? []) + 1,
        'rm_code' => $input['rm_code'],
        'rm_name' => $input['rm_name'],
        'price' => (float)$input['price'],
        'unit' => $input['unit']
    ];
    $masterData['standard_prices'][] = $newItem;
    writeDb($file, $masterData);
    echo json_encode(['success' => true, 'message' => 'Standard price added.', 'data' => $newItem]);
}

function addSellingFactor($file) {
    $input = json_decode(file_get_contents('php://input'), true);
    $masterData = readDb($file);
    $newItem = [
        'id' => count($masterData['selling_factors'] ?? []) + 1,
        'pattern' => $input['pattern'],
        'factor' => (float)$input['factor'],
        'description' => $input['description'] ?? ''
    ];
    $masterData['selling_factors'][] = $newItem;
    writeDb($file, $masterData);
    echo json_encode(['success' => true, 'message' => 'Selling factor added.', 'data' => $newItem]);
}

function addExchangeRate($file) {
    $input = json_decode(file_get_contents('php://input'), true);
    $masterData = readDb($file);
    $newItem = [
        'id' => count($masterData['exchange_rates'] ?? []) + 1,
        'currency_pair' => $input['currency_pair'],
        'rate' => (float)$input['rate'],
        'updated_at' => date('d/m/Y H:i')
    ];
    $masterData['exchange_rates'][] = $newItem;
    writeDb($file, $masterData);
    echo json_encode(['success' => true, 'message' => 'Exchange rate added.', 'data' => $newItem]);
}
?>
