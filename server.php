<?php
require './vendor/autoload.php';
header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['message' => "Hello World!"]);
