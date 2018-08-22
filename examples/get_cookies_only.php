<?php
require '../vendor/autoload.php';

$curl = new \Kenvix\curl\curl("https://www.bing.com/");
$curl->head();
var_dump($curl->getCookies());

echo "<br/>redirected {$curl->getRedirectNum()} times";
