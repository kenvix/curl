<?php
require '../vendor/autoload.php';

$curl = new \Kenvix\curl\curl("https://bing.com/");
echo $curl->exec();