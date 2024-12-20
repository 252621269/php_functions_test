<?php
use Workerman\Worker;
use phpseclib3\Crypt\AES;
require_once __DIR__ . '/../vendor/autoload.php';

// 创建一个监听8666端口的WebSocket服务器
$ws_worker = new Worker("websocket://0.0.0.0:8666");

// 当有新连接时
$ws_worker->onConnect = function ($connection) {
    echo "New connection\n";
};

// 当收到消息时
$ws_worker->onMessage = function ($connection, $data) {
    echo "Received encrypted message: $data\n";

    $aes = new AES('cbc');
    $aes->setKey('secretkey1234567'); // 使用16字节的密钥
    $aes->setIV('1234567890123456'); // 确保IV在加密和解密时相同

    // 解码base64编码的数据
    $decodedData = base64_decode($data);

    // 解密消息
    try {
        $decryptedData = $aes->decrypt($decodedData);
        echo "Decrypted message: $decryptedData\n";

        // 加密响应
        $encryptedResponse = base64_encode($aes->encrypt("Server received: $decryptedData"));
        $connection->send($encryptedResponse);
    } catch (Exception $e) {
        echo "Decryption failed: " . $e->getMessage() . "\n";
    }
};

// 当连接关闭时
$ws_worker->onClose = function ($connection) {
    echo "Connection closed\n";
};

// 运行所有的worker
Worker::runAll();