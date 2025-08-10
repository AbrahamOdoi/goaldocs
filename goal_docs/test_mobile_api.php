<?php

/**
 * GoalDocs Mobile API Test Script
 * 
 * This script tests the mobile API endpoints to ensure they're working correctly.
 * Run this script to verify the API functionality.
 */

// Configuration
$baseUrl = 'http://localhost:8000/api/mobile';
$testEmail = 'admin@example.com';
$testPassword = 'password';
$deviceId = 'test-device-' . uniqid();

// Test results
$tests = [];
$authToken = null;

echo "🧪 GoalDocs Mobile API Test Script\n";
echo "==================================\n\n";

// Helper function to make HTTP requests
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => 0];
    }
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Test 1: API Documentation
echo "1. Testing API Documentation...\n";
$result = makeRequest('http://localhost:8000/api/docs');
$tests['api_docs'] = $result['http_code'] === 200;
echo $tests['api_docs'] ? "✅ API Documentation accessible\n" : "❌ API Documentation failed\n";
echo "   HTTP Code: {$result['http_code']}\n\n";

// Test 2: Login
echo "2. Testing Login...\n";
$loginData = json_encode([
    'email' => $testEmail,
    'password' => $testPassword,
    'device_id' => $deviceId,
    'device_name' => 'Test Device',
    'device_type' => 'ios'
]);

$result = makeRequest($baseUrl . '/login', 'POST', $loginData, [
    'Content-Type: application/json'
]);

$tests['login'] = $result['http_code'] === 200 && isset($result['data']['success']) && $result['data']['success'];

if ($tests['login']) {
    $authToken = $result['data']['data']['token'];
    echo "✅ Login successful\n";
    echo "   Token received: " . substr($authToken, 0, 20) . "...\n";
} else {
    echo "❌ Login failed\n";
    echo "   HTTP Code: {$result['http_code']}\n";
    if (isset($result['data']['message'])) {
        echo "   Error: {$result['data']['message']}\n";
    }
}
echo "\n";

// Test 3: Get Profile (requires authentication)
echo "3. Testing Get Profile...\n";
if ($authToken) {
    $result = makeRequest($baseUrl . '/profile', 'GET', null, [
        'Authorization: Bearer ' . $authToken,
        'X-Device-ID: ' . $deviceId
    ]);
    
    $tests['profile'] = $result['http_code'] === 200 && isset($result['data']['success']) && $result['data']['success'];
    
    if ($tests['profile']) {
        echo "✅ Profile retrieved successfully\n";
        $user = $result['data']['data']['user'];
        echo "   User: {$user['name']} ({$user['email']})\n";
    } else {
        echo "❌ Profile retrieval failed\n";
        echo "   HTTP Code: {$result['http_code']}\n";
    }
} else {
    echo "⏭️  Skipping profile test (no auth token)\n";
    $tests['profile'] = false;
}
echo "\n";

// Test 4: Get Files (requires authentication)
echo "4. Testing Get Files...\n";
if ($authToken) {
    $result = makeRequest($baseUrl . '/files?per_page=5', 'GET', null, [
        'Authorization: Bearer ' . $authToken,
        'X-Device-ID: ' . $deviceId
    ]);
    
    $tests['files'] = $result['http_code'] === 200 && isset($result['data']['success']) && $result['data']['success'];
    
    if ($tests['files']) {
        echo "✅ Files retrieved successfully\n";
        $files = $result['data']['data']['files'];
        echo "   Files count: " . count($files) . "\n";
        echo "   Pagination: {$result['data']['data']['pagination']['current_page']}/{$result['data']['data']['pagination']['last_page']}\n";
    } else {
        echo "❌ Files retrieval failed\n";
        echo "   HTTP Code: {$result['http_code']}\n";
    }
} else {
    echo "⏭️  Skipping files test (no auth token)\n";
    $tests['files'] = false;
}
echo "\n";

// Test 5: Get Folders (requires authentication)
echo "5. Testing Get Folders...\n";
if ($authToken) {
    $result = makeRequest($baseUrl . '/folders', 'GET', null, [
        'Authorization: Bearer ' . $authToken,
        'X-Device-ID: ' . $deviceId
    ]);
    
    $tests['folders'] = $result['http_code'] === 200 && isset($result['data']['success']) && $result['data']['success'];
    
    if ($tests['folders']) {
        echo "✅ Folders retrieved successfully\n";
        $folders = $result['data']['data']['folders'];
        echo "   Folders count: " . count($folders) . "\n";
    } else {
        echo "❌ Folders retrieval failed\n";
        echo "   HTTP Code: {$result['http_code']}\n";
    }
} else {
    echo "⏭️  Skipping folders test (no auth token)\n";
    $tests['folders'] = false;
}
echo "\n";

// Test 6: Search (requires authentication)
echo "6. Testing Search...\n";
if ($authToken) {
    $result = makeRequest($baseUrl . '/search?query=test&type=all', 'GET', null, [
        'Authorization: Bearer ' . $authToken,
        'X-Device-ID: ' . $deviceId
    ]);
    
    $tests['search'] = $result['http_code'] === 200 && isset($result['data']['success']) && $result['data']['success'];
    
    if ($tests['search']) {
        echo "✅ Search working successfully\n";
        $results = $result['data']['data'];
        echo "   Files found: " . (isset($results['files']) ? count($results['files']) : 0) . "\n";
        echo "   Folders found: " . (isset($results['folders']) ? count($results['folders']) : 0) . "\n";
    } else {
        echo "❌ Search failed\n";
        echo "   HTTP Code: {$result['http_code']}\n";
    }
} else {
    echo "⏭️  Skipping search test (no auth token)\n";
    $tests['search'] = false;
}
echo "\n";

// Test 7: Get Stats (requires authentication)
echo "7. Testing Get Stats...\n";
if ($authToken) {
    $result = makeRequest($baseUrl . '/stats', 'GET', null, [
        'Authorization: Bearer ' . $authToken,
        'X-Device-ID: ' . $deviceId
    ]);
    
    $tests['stats'] = $result['http_code'] === 200 && isset($result['data']['success']) && $result['data']['success'];
    
    if ($tests['stats']) {
        echo "✅ Stats retrieved successfully\n";
        $stats = $result['data']['data'];
        echo "   Total files: {$stats['files']['total']}\n";
        echo "   Total folders: {$stats['folders']['total']}\n";
        echo "   Storage used: " . number_format($stats['files']['storage_used'] / 1024 / 1024, 2) . " MB\n";
    } else {
        echo "❌ Stats retrieval failed\n";
        echo "   HTTP Code: {$result['http_code']}\n";
    }
} else {
    echo "⏭️  Skipping stats test (no auth token)\n";
    $tests['stats'] = false;
}
echo "\n";

// Test 8: Refresh Session (requires authentication)
echo "8. Testing Refresh Session...\n";
if ($authToken) {
    $result = makeRequest($baseUrl . '/refresh-session', 'POST', null, [
        'Authorization: Bearer ' . $authToken,
        'X-Device-ID: ' . $deviceId
    ]);
    
    $tests['refresh_session'] = $result['http_code'] === 200 && isset($result['data']['success']) && $result['data']['success'];
    
    if ($tests['refresh_session']) {
        echo "✅ Session refreshed successfully\n";
    } else {
        echo "❌ Session refresh failed\n";
        echo "   HTTP Code: {$result['http_code']}\n";
    }
} else {
    echo "⏭️  Skipping session refresh test (no auth token)\n";
    $tests['refresh_session'] = false;
}
echo "\n";

// Test 9: Logout (requires authentication)
echo "9. Testing Logout...\n";
if ($authToken) {
    $result = makeRequest($baseUrl . '/logout', 'POST', null, [
        'Authorization: Bearer ' . $authToken,
        'X-Device-ID: ' . $deviceId
    ]);
    
    $tests['logout'] = $result['http_code'] === 200 && isset($result['data']['success']) && $result['data']['success'];
    
    if ($tests['logout']) {
        echo "✅ Logout successful\n";
    } else {
        echo "❌ Logout failed\n";
        echo "   HTTP Code: {$result['http_code']}\n";
    }
} else {
    echo "⏭️  Skipping logout test (no auth token)\n";
    $tests['logout'] = false;
}
echo "\n";

// Test 10: Invalid Login
echo "10. Testing Invalid Login...\n";
$invalidLoginData = json_encode([
    'email' => 'invalid@example.com',
    'password' => 'wrongpassword',
    'device_id' => $deviceId,
    'device_name' => 'Test Device',
    'device_type' => 'ios'
]);

$result = makeRequest($baseUrl . '/login', 'POST', $invalidLoginData, [
    'Content-Type: application/json'
]);

$tests['invalid_login'] = $result['http_code'] === 401;
if ($tests['invalid_login']) {
    echo "✅ Invalid login correctly rejected\n";
} else {
    echo "❌ Invalid login not properly handled\n";
    echo "   HTTP Code: {$result['http_code']}\n";
}
echo "\n";

// Summary
echo "📊 Test Summary\n";
echo "===============\n";
$passedTests = array_sum($tests);
$totalTests = count($tests);

foreach ($tests as $testName => $passed) {
    $status = $passed ? "✅" : "❌";
    echo "$status $testName\n";
}

echo "\n";
echo "Results: $passedTests/$totalTests tests passed\n";

if ($passedTests === $totalTests) {
    echo "🎉 All tests passed! The Mobile API is working correctly.\n";
} else {
    echo "⚠️  Some tests failed. Please check the Laravel server and database.\n";
}

echo "\n";
echo "🔧 Troubleshooting Tips:\n";
echo "1. Make sure Laravel server is running: php artisan serve\n";
echo "2. Check database connection and migrations\n";
echo "3. Verify user credentials exist in database\n";
echo "4. Check Laravel logs: tail -f storage/logs/laravel.log\n";
echo "5. Clear cache if needed: php artisan cache:clear\n";

?>
