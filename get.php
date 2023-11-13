<?php

require __DIR__ . '/vendor/autoload.php';
include_once("db.php");

$db = new DB();

$bans = $db->getBans(0, '');
$mutes = $db->getMutes(0, '');
$kicks = $db->getKicks(0, '');

echo json_encode([
    'bans' => $bans,
    'mutes' => $mutes,
    'kicks' => $kicks,
]);