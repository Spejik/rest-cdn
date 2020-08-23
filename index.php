<?php

$version = file_get_contents(__DIR__ . "/VERSION");
echo json_encode(["message" => "Using REST-CDN {$version} (https://github.com/spejik/rest-cdn)"]);
