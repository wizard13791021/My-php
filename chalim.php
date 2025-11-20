<?php

$bot_token = "7611961676:AAEH_B9N-cjiwZVMrzcW8WzbL3qpjV_ZFko";
$sudo_port = "-1003115717473";
$dev_id = "im_saeedi";
$dev_name = "im_saeedi";
$sudo_user = ["-1003115717473"];
$id_sender = "-1003115717473";

$bye_port = "";  
$time_end = "";
$days_remaining = 30;  

$file = "saeedrat-f5c04-firebase-adminsdk-fbsvc-179e4aa889.json";
$project_id = "saeedrat-f5c04";


$time_data_file = "data/time_data.json";

if (!file_exists("data")) {
    mkdir("data");
}


if (!file_exists($time_data_file)) {
    $time_data = [
        'purchase_date' => '',
        'expiry_date' => '',
        'days_remaining' => $days_remaining,
        'last_update' => '' 
    ];
    file_put_contents($time_data_file, json_encode($time_data));
} else {
    $time_data = json_decode(file_get_contents($time_data_file), true);
}
if (!empty($time_data['purchase_date'])) {
    $purchase_date = new DateTime($time_data['purchase_date']);
    $expiry_date = clone $purchase_date;
    $expiry_date->modify("+{$time_data['days_remaining']} days");
    $time_data['expiry_date'] = $expiry_date->format('Y/m/d');
    
    $current_date = new DateTime();
    $today = $current_date->format('Y/m/d');
    
    if ($time_data['last_update'] !== $today) {
        $interval = $current_date->diff($expiry_date);
        $days_remaining = $interval->days;
        
        if ($days_remaining < 0) {
            $days_remaining = 30;
        }
        
        $time_data['days_remaining'] = $days_remaining;
        $time_data['last_update'] = $today;

        file_put_contents($time_data_file, json_encode($time_data));
    }
}

$bye_port = $time_data['purchase_date'];
$time_end = $time_data['expiry_date'];
$days_remaining = $time_data['days_remaining'];

?>