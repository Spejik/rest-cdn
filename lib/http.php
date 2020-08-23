<?php

// PURPOSE:
//  see below


// Call this function to.. well.. allow only POST requests
function ALLOW_ONLY_POST() 
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') 
        die("Request must be POST, recieved {$_SERVER["REQUEST_METHOD"]}");
}


// Should NOT be disabled
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
    die("Request must be sent only over HTTPS");
}
