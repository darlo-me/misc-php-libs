<?php
// this library does not prevent double-submissions as it is stateless
const CSRF_EXPIRY_DEFAULT = 3600; // 1 hour
function csrf_generate(string $key, string $sessionID, int $timestamp, int $expiry = CSRF_EXPIRY_DEFAULT): string {
    return $timestamp . 'Z' . $expiry . 'Z' . hash_hmac("sha256", $sessionID . $timestamp . $expiry, $key);
}

function csrf_verify(string $token, string $key, string $sessionID, int $timeNow) {
    $split = explode('Z', $token, 3); // if there is no Z, then it will do a PHP notice and return false
    $timestamp = (int)$split[0];
    $expiry    = (int)$split[1];
    
    if($timestamp + $expiry <= $timeNow) {
        return false;
    }
    
    return csrf_generate($key, $sessionID, $timestamp, $expiry) === $token;
}
