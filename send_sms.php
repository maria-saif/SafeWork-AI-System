<?php
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['worker']) && isset($data['obs']) && isset($data['loc'])) {
    
    $sid    = "AC2b0b8b0801a93b31b8118869fce1d87f"; 
    $token  = "a39499719dc6b9a473828fb130df614c"; 
    $from_number = "+13614703248"; 
    
    $to_number   = "+96897470709"; 

    $type = isset($data['type']) ? $data['type'] : 'violation';

    $message = "";

    switch ($type) {
        
        case 'violation':
            $message = "⚠️ [SafeWork - Live Violation]:\n"
                     . "Immediate HSE Breach Detected!\n"
                     . "Worker: " . $data['worker'] . "\n"
                     . "Violation: " . $data['obs'] . "\n"
                     . "Location: " . $data['loc'] . "\n"
                     . "Action Required ASAP.";
            break;

        case 'analytics':
            $message = "📊 [SafeWork - HSE Monthly Analytics]:\n"
                     . "Performance report generated for Worker: " . $data['worker'] . ".\n"
                     . "Compliance Score: " . $data['obs'] . "\n"
                     . "Total Violations This Shift: " . $data['loc'] . "\n"
                     . "Check the dashboard for details.";
            break;

        case 'evacuation':
            $message = "🚨 [EMERGENCY - SafeWork Evacuation]:\n"
                     . "Live Evacuation Order Triggered!\n"
                     . "Status: " . $data['obs'] . "\n"
                     . "Assembly Point Counter: " . $data['worker'] . "\n"
                     . "Zone/Area: " . $data['loc'] . "\n"
                     . "ALL PERSONNEL EVACUATE IMMEDIATELY.";
            break;
            
        default:
            $message = "🔔 [SafeWork Notification]:\n"
                     . "Update regarding worker: " . $data['worker'];
    }

    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'To'   => $to_number,
        'From' => $from_number,
        'Body' => $message
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 201 || $http_code == 200) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'تم بث الإشعار وتوصيل الرسالة الحقيقية بنجاح!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'فشل إرسال الرسالة عبر البوابة الدولية.', 
            'debug'  => json_decode($response, true)
        ]);
    }
    exit;
}

echo json_encode([
    'status' => 'error', 
    'message' => 'بيانات ناقصة.'
]);
exit;
?>