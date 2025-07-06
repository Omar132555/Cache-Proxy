<?php 
require 'vendor/autoload.php';
use GuzzleHttp\Client;
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Extracting command line arguments
$options = getopt("",["port:","origin:","clear-cache"]);
$port = $options['port'] ?? 3000;
$origin = $options['origin'] ?? 'http://localhost:3000';

if (isset($options['clear-cache'])) {
    $cacheDir = __DIR__ . '/cache';
    if (file_exists($cacheDir)) {
        $files = glob($cacheDir . '/*.json');
        foreach ($files as $file) {
            unlink($file);
        }
        echo "Cache cleared" . count($files) . " files deleted.\n";
    } else {
        echo "Cache doesn't exist.\n";
    }
    exit(0); // Exit after clearing
}
//clearCache = isset($options['clear-cache']);
//Creating the Server With ReactPHP
$server = new React\Http\HttpServer(function (Psr\Http\Message\ServerRequestInterface $request) use ($origin){

    $Method = $request->getMethod();
    $Path = $request->getUri()->getPath();
    $Query = $request->getUri()->getQuery();
    $key = md5($Method . $Path . $Query);   //this key is the fingerprint for the cache files that which will be saved and mapped to its related request
    $cacheFile = __DIR__ . "/cache/$key.json";
    $fullUrl = $origin.$Path.($Query ? "?$Query" :'');
    //Check if this request is existing in cache, if exists retrun response
    if(!file_exists($cacheFile)){
        $content = file_get_contents($cacheFile);
        return React\Http\Message\Response::json(['content' => $content,
        'header' => ['X-Cache' => 'Hit']
        ]);
    }
    //if the request dosn't exist in cache,forward it to Origin Server, receive response, create a new cache file, save it, forward it to Client
    else{
        $client = new Client();
        $OriginResponse = $client->request($Method,$fullUrl,[ //Forward to Origin
            'body' => (string)$request->getBody(),
            'headers' =>$request->getHeaders()
        ]);
        $headers = $OriginResponse->getHeaders();
        file_put_contents($cacheFile,json_encode([ //Save to Cache
            'status' => $OriginResponse->getStatusCode(),
            'headers' => $headers,
            'body' => $OriginResponse->getBody()
        ]));
        return React\Http\Message\Response::json([ //Forward to Client
            'status' => $OriginResponse->getStatusCode(),
            'headers' => array_merge($headers,['X-Cache' => 'MISS']),
            'body' => (string)$OriginResponse->getBody()
        ]);
    }
    
});

$socket = new React\Socket\SocketServer("127.0.0.1:$port");
$server->listen($socket);
echo "Proxy running on http://127.0.0.1:$port\n";
