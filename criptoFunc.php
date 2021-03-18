<?php
include_once "config.php";

function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}
function hexToStr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

function chipher($plaintext, $password){
    $method = 'aes-256-cbc';
    
    // IV must be exact 16 chars (128 bit)
    $iv = "8DCB7300E8BCA8E5";
    
    return strToHex(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
}

function deChipher($encrypted, $password) {
    $method = 'aes-256-cbc';
    
    // IV must be exact 16 chars (128 bit)
    $iv = "8DCB7300E8BCA8E5";

    return openssl_decrypt(hexToStr($encrypted), $method, $password, OPENSSL_RAW_DATA, $iv);
}

function hashPass($password)
{
    $method = 'sha256';
    return strToHex(substr(hash($method, $password, true), 0, 32));
}

function passDecrypt($list, $oneMonth)
{
    if (isLocal()) {
        $dateStr = "14 12 1972";
    } else {
        $json = json_decode(file_get_contents('https://worldtimeapi.org/api/timezone/Europe/Rome'));
        if ($oneMonth) {
            $lastCharIndex = 7;
        } else {
            $lastCharIndex = 16;
        }
        $dateStr = substr($json->{'datetime'},0,$lastCharIndex);
    }
        
    $secret = 'f775aaf9cfab2cd30fd0d0ad28c5c460';
    $hmac = hash_hmac('sha256',$dateStr,$secret);

    $returnList = $list;
    
    foreach ($list as $key => $encrypted) {
        $decript = deChipher($encrypted,hexToStr($hmac));
        $returnList[$key] = $decript;
    }
    
    return $returnList;
}
?>