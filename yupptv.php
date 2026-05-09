<?php

define('SESSION_FILE', __DIR__ . '/session_id.json');
define('TENANT_CODE', 'yuppfast');
define('BOX_ID', 'c8b17f68-d9f4-5fa0-efb9-008b0dd7838d');
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36');

function refreshSessionId() {
    $url = 'https://yuppfast-api.revlet.net/service/api/v1/get/token?' . http_build_query([
        'tenant_code' => TENANT_CODE,
        'box_id' => BOX_ID,
        'product' => 'yuppfast',
        'device_id' => '5',
        'display_lang_code' => 'ENG',
        'device_sub_type' => 'Chrome,130.0.0.0,Windows',
        'client_app_version' => '1',
        'timezone' => 'Asia/Calcutta'
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    $data = json_decode($response, true);
    curl_close($ch);
    
    if ($data && isset($data['status']) && $data['status'] && isset($data['response']['sessionId'])) {
        $sessionId = $data['response']['sessionId'];
        date_default_timezone_set('Asia/Kolkata');
        
        $sessionData = [
            'sessionId' => $sessionId,
            'lastRefreshed' => date("Y-m-d H:i:s"),
        ];

        file_put_contents(SESSION_FILE, json_encode($sessionData, JSON_PRETTY_PRINT));
        return $sessionId;
    }

    error_log('Error: Unable to fetch session ID. Response: ' . $response);
    return false;
}

if (isset($_GET['id']) && $_GET['id'] === 'refresh_token') {
    header('Content-Type: text/plain');
    if (refreshSessionId()) {
        echo 'Session ID refreshed successfully.';
    } else {
        http_response_code(500);
        echo 'Failed to refresh Session ID.';
    }
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    die('Error: No channel ID specified.');
}

$id = $_GET['id'];

if (!file_exists(SESSION_FILE)) {
    if (!refreshSessionId()) {
        http_response_code(500);
        die('Error: Could not retrieve initial session ID.');
    }
}

$sessionData = json_decode(file_get_contents(SESSION_FILE), true);
if (!$sessionData || !isset($sessionData['sessionId'])) {
    http_response_code(500);
    die('Error: Invalid session data. Please hit ?id=refresh_token to regenerate.');
}

$sessionId = $sessionData['sessionId'];

$url = 'https://yuppfast-api.revlet.net/service/api/v1/page/stream?' . http_build_query(['path' => $id]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

$headers = [
    'accept: application/json, text/plain, */*',
    'box-id: ' . BOX_ID,
    'origin: https://www.yupptv.com',
    'referer: https://www.yupptv.com/',
    'session-id: ' . $sessionId,
    'tenant-code: ' . TENANT_CODE,
    'user-agent: ' . USER_AGENT,
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    die('cURL error: ' . curl_error($ch));
}

curl_close($ch);
$data = json_decode($response, true);

if ($data && isset($data['status']) && $data['status'] && !empty($data['response']['streams'])) {
    $m3u8_url = $data['response']['streams'][0]['url'];
    header('Location: ' . $m3u8_url);
    exit();
}

http_response_code(404);
echo 'Error: No valid stream found for the provided ID.<br>';
echo 'Raw Response: ' . htmlspecialchars($response);

?>
