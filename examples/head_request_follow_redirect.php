<?php
require '../vendor/autoload.php';

$curl = new \Kenvix\curl\curl("https://www.bing.com/");
var_dump($curl->head());


echo "<br/>redirected {$curl->getRedirectNum()} times";
