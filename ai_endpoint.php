<?php
// api/ai_endpoint.php
header('Content-Type: application/json; charset=utf-8');

$openai_api_url = "https://api.openai.com/v1/videos/generations"; 
$api_key = "ضع_هنا_كود_الـ_API_KEY_الخاص_بك"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    $hseData = [];

    if (($handle = fopen($file, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ","); 
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 4) {
                
                $prompt = "3D realistic animation of an industrial safety incident: " . $data[1] . " at " . $data[3] . ", oil and gas field environment, highly detailed hse monitoring style.";

                $ch = curl_init($openai_api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    "model" => "sora-v1", 
                    "prompt" => $prompt,
                    "duration" => 5
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $api_key
                ]);
                
                $response = curl_exec($ch);
                curl_close($ch);
                $result = json_encode($response);

                $hseData[] = [
                    'id' => trim($data[0]),
                    'observation' => trim($data[1]),
                    'risk_level' => trim($data[2]),
                    'location' => trim($data[3]),
                    'video_url' => $result['video_generated_url'] ?? "../assets/videos/fallback_simulation.mp4" 
                ];
            }
        }
        fclose($handle);
    }

    echo json_encode(['status' => 'success', 'data' => $hseData]);
    exit;
}