<?php
require '../vendor/autoload.php';

$curl = new \Kenvix\curl\curl("https://www.bing.com/");
$curl->head();
print_r($curl->getCookies());

echo "<br/>redirected {$curl->getRedirectNum()} times";
