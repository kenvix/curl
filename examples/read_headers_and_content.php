<?php
require '../vendor/autoload.php';

$curl = new \Kenvix\curl\curl("https://bing.com/");
$curl->exec();
var_dump($curl->getHeaders());

echo "<br/>redirected {$curl->getRedirectNum()} times";