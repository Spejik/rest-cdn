<?php



function startsWith(string $str, string $start) 
{ 
    $len = strlen($start); 
    return (substr($str, 0, $len) === $start); 
}


function endsWith(string $str, string $end) 
{ 
    $len = strlen($end); 
    if ($len == 0) { 
        return true; 
    } 
    return (substr($str, -$len) === $end); 
} 


function getAlphanumeric(string $str): string
{
    return preg_replace("/[^a-zA-Z0-9]+/", "", $str);
}