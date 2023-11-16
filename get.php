<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require __DIR__ . '/vendor/autoload.php';
include_once("db.php");

$db = new DB();

$bans = $db->getBans();
$mutes = $db->getMutes();
$kicks = $db->getKicks();

echo json_encode([
    'bans' => $bans,
    'mutes' => $mutes,
    'kicks' => $kicks,
]);