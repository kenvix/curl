<?php
require '../vendor/autoload.php';

$curl = new \Kenvix\curl\curl("https://www.bing.com/");
echo $curl->execRaw();