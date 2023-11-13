<?php

require __DIR__ . '/vendor/autoload.php';
use Dtkahl\SimpleConfig\Config;

include_once("db.php");

$config = new Config(require("./config.php"));
setlocale(LC_ALL, 'ru_RU.UTF-8' );
date_default_timezone_set('Etc/GMT-3');
$db = new DB();

if (isset($_GET['bans_page'])) {
    $bans_page = $_GET['bans_page'] > 0 ? ($_GET['bans_page']-1) : 0;
} else $bans_page = 0;
if (isset($_GET['mutes_page'])) {
    $mutes_page = $_GET['mutes_page'] > 0 ? ($_GET['mutes_page']-1) : 0;
} else $mutes_page = 0;
if (isset($_GET['kicks_page'])) {
    $kicks_page = $_GET['kicks_page'] > 0 ? ($_GET['kicks_page']-1) : 0;
} else $kicks_page = 0;

if(isset($_GET['type'])) {
    $type = $_GET['type'];
} else $type = 'bans';

if(isset($_GET['bans_q'])) {
    $bans_q = $_GET['bans_q'];
} else $bans_q = '';
if(isset($_GET['mutes_q'])) {
    $mutes_q = $_GET['mutes_q'];
} else $mutes_q = '';
if(isset($_GET['kicks_q'])) {
    $kicks_q = $_GET['kicks_q'];
} else $kicks_q = '';

$bans = $db->getBans($bans_page, $bans_q);
$mutes = $db->getMutes($mutes_page, $mutes_q);
$kicks = $db->getKicks($kicks_page, $kicks_q);

$bans_count = $db->getBansCount($bans_q);
$mutes_count = $db->getMutesCount($mutes_q);
$kicks_count = $db->getKicksCount($kicks_q);

$bans_pages_count = ceil($bans_count / $db->items_limit);
$mutes_pages_count = ceil($mutes_count / $db->items_limit);
$kicks_pages_count = ceil($kicks_count / $db->items_limit);

$consoles = [
   'Консоль',
   'Судья',
];

$current_page = [
    'bans' => $bans_page,
    'mutes' => $mutes_page,
    'kicks' => $kicks_page,
][$type];
$pages_count = [
    'bans' => $bans_pages_count,
    'mutes' => $mutes_pages_count,
    'kicks' => $kicks_pages_count,
][$type];

function clean($text) {
    if ($text === null) return null;
    if (strstr($text, "\xa7") || strstr($text, "&")) {
        $text = preg_replace("/(?i)(\x{00a7}|&)[0-9A-FK-OR]/u", "", $text);
    }
    $text = htmlspecialchars($text, ENT_QUOTES);
    if (str_contains($text, "\\n")) {
        $text = preg_replace("/\\\\n/", "<br>", $text);
    }
    return $text;
}

function millis_to_date($millis) {
    $fmt = new IntlDateFormatter(
        'ru-RU',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Moscow',
        IntlDateFormatter::GREGORIAN,
        'LLLL d, y, HH:mm'
    );
    return $fmt->format($millis / 1000);
}

?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>WokaMC - Лист всех наказаний на сервере.</title>
    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
<!--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">-->

    <link rel="stylesheet" href="src/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="src/css/fonts.css">
    <link rel="stylesheet" href="src/css/table.css">
    <link rel="stylesheet" href="src/css/styles.css">
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/footer.css">
    <link rel="stylesheet" href="src/css/toast.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#ffffff">

    <style>
        .container-table100 {
            background: <?=$config->get('theme.fallback_color')?> !important;
            background: linear-gradient(90deg, rgba(2,0,36,1) 0%, <?=$config->get('theme.left_color')?> 0%, <?=$config->get('theme.right_color')?> 100%) !important;
        }
        .modal__tag {
            background: <?=$config->get('theme.tag_color.background')?>;
            border: 1px solid <?=$config->get('theme.tag_color.color')?>;
            border-radius: 20px;
            color: <?=$config->get('theme.tag_color.color')?>;
        }
        .modal {
            background: <?=$config->get('theme.modal.background')?>;
            border: solid;
            border-color: <?=$config->get('theme.modal.border.color')?>;
            border-width: <?=$config->get('theme.modal.border.width')?>;
        }
    </style>
</head>
<body>

<?php include './components/header.php'; ?>

<div class="limiter">
    <div id="particles-js"></div>

        <div class="container-table100">
            <div class="wrap-table100">

                <div class="toolbar">
                    <form class="search" id="search" action="#">
                        <input type="hidden" name="type" value="<?=$type?>">
                        <input name="bans_q" type="search" placeholder="Поиск" data-type="bans" class="search__input <?php echo $type==='bans'?'active':'' ?>" value="<?=$bans_q?>">
                        <input name="mutes_q" type="search" placeholder="Поиск" data-type="mutes" class="search__input <?php echo $type==='mutes'?'active':'' ?>" value="<?=$mutes_q?>">
                        <input name="kicks_q" type="search" placeholder="Поиск" data-type="kicks" class="search__input <?php echo $type==='kicks'?'active':'' ?>" value="<?=$kicks_q?>">
                        <button>Oк</button>
                    </form>
                    <div class="buttons">
                        <a href="?type=bans" class="btn btn_link js-tab-btn <?=$type==='bans'?'active':''?>">Баны <span class="count"><?=$bans_count?></span></a>
                        <a href="?type=mutes" class="btn btn_link js-tab-btn <?=$type==='mutes'?'active':''?>">Муты <span class="count"><?=$mutes_count?></span></a>
                        <a href="?type=kicks" class="btn btn_link js-tab-btn <?=$type==='kicks'?'active':''?>">Кики <span class="count"><?=$kicks_count?></span></a>
                    </div>
                    <div class="hren"></div>
                </div>

                <div class="tables">
                    <div data-name="bans" class="js-tab-content animate__faster table100 <?=$type==='bans'?'active':''?>">
                        <table>
                            <thead>
                            <tr class="table100-head">
                                <th class="column1">Игрок</th>
                                <th class="column2">Арбитр</th>
                                <th class="column3">Причина</th>
                                <th class="column4">Дата</th>
                                <th class="column5">Окончание</th>
                                <th class="column6"></th>
                            </tr>
                            </thead>
                            <tbody id="bans">
                            <tr class="spacer"><td colspan="100"></td></tr>

                            <?php
                            foreach ($bans as $ban) { ?>
                                <tr class="js-row" data-type="bans" data-id="<?=$ban['id']?>">
                                    <td data-name="Игрок" class="column1 th-left">
                                        <div class="user">
                                            <img src="https://minotar.net/avatar/<?=$ban['name']?>/25" alt="<?=$ban['name']?>" class="avatar">
                                            <div class="name"><?=$ban['name']?></div>
                                        </div>
                                    </td>
                                    <td data-name="Арбитр" class="column2">
                                        <div class="user">
                                            <img src="<?=in_array($ban['banned_by_name'], $consoles) ? './src/img/console.png' : 'https://minotar.net/avatar/'.$ban["banned_by_name"].'/25' ?>" alt="<?=$ban['banned_by_name']?>" class="avatar">
                                            <div class="name"><?=$ban['banned_by_name']?></div>
                                        </div>
                                    </td>
                                    <td data-name="Причина" class="column3"><?=clean($ban['reason'])?></td>
                                    <td data-name="Дата" class="column4 date"><?=millis_to_date($ban['time'])?></td>
                                    <!--                                    <td data-name="Дата" class="column4">--><?//=$ban['removed_by_date']?><!--</td>-->
                                    <td data-name="Истекается" class="column5 date"><?php
                                        if($ban['until']) {
                                            echo millis_to_date($ban['until']);
                                        } else {
                                            echo 'Постоянный Бан';
                                        } ?>
                                    </td>
                                    <td class="column6"></td>
                                </tr>
                                <tr class="spacer"><td colspan="100"></td></tr>
                            <?php }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-name="mutes" class="js-tab-content animate__faster table100 <?=$type==='mutes'?'active':''?>">
                        <table>
                            <thead>
                            <tr class="table100-head">
                                <th class="column1">Игрок</th>
                                <th class="column2">Арбитр</th>
                                <th class="column3">Причина</th>
                                <th class="column4">Дата</th>
                                <th class="column5">Окончание</th>
                                <th class="column6"></th>
                            </tr>
                            </thead>
                            <tbody id="mutes">
                            <tr class="spacer"><td colspan="100"></td></tr>

                            <?php
                            foreach ($mutes as $mute) { ?>
                                <tr class="js-row" data-type="mutes" data-id="<?=$mute['id']?>">
                                    <td data-name="Игрок" class="column1 th-left">
                                        <div class="user">
                                            <img src="https://minotar.net/avatar/<?=$mute['name']?>/25" alt="<?=$mute['name']?>" class="avatar">
                                            <div class="name"><?=$mute['name']?></div>
                                        </div>
                                    </td>
                                    <td data-name="Арбитр" class="column2">
                                        <div class="user">
                                            <img src="<?=$mute['banned_by_name'] !== 'Консоль' ? 'https://minotar.net/avatar/'.$mute["banned_by_name"].'/25' : './src/img/console.png' ?>" alt="<?=$mute['banned_by_name']?>" class="avatar">

                                            <div class="name"><?=$mute['banned_by_name']?></div>
                                        </div>
                                    </td>
                                    <td data-name="Причина" class="column3"><?=clean($mute['reason'])?></td>
                                    <td data-name="Дата" class="column4 date"><?=millis_to_date($mute['time'])?></td>
                                    <td data-name="Истекается" class="column5 date"><?php
                                        if($mute['until']) {
                                            echo millis_to_date($mute['until']);
                                        } else {
                                            echo 'Никогда';
                                        } ?>
                                    </td>
                                    <td class="column6"></td>
                                </tr>
                                <tr class="spacer"><td colspan="100"></td></tr>
                            <?php }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-name="kicks" class="js-tab-content animate__faster table100 <?=$type==='kicks'?'active':''?> table100_kicks">
                        <table>
                            <thead>
                            <tr class="table100-head">
                                <th class="column1">Игрок</th>
                                <th class="column2">Арбитр</th>
                                <th class="column3">Причина</th>
                                <th class="column4">Дата</th>
                                <th class="column5"></th>
                                <th class="column6"></th>
                            </tr>
                            </thead>
                            <tbody id="kicks">
                            <tr class="spacer"><td colspan="100"></td></tr>

                            <?php
                            foreach ($kicks as $kick) { ?>
                                <tr class="js-row" data-type="kicks" data-id="<?=$kick['id']?>">
                                    <td data-name="Игрок" class="th-left column1">
                                        <div class="user">
                                            <img src="https://minotar.net/avatar/<?=$kick['name']?>/25" alt="<?=$kick['name']?>" class="avatar">
                                            <div class="name"><?=$kick['name']?></div>
                                        </div>
                                    </td>
                                    <td data-name="Арбитр" class="column2">
                                        <div class="user">
                                            <img src="<?=$kick['banned_by_name'] !== 'Консоль' ? 'https://minotar.net/avatar/'.$kick["banned_by_name"].'/25' : './src/img/console.png' ?>" alt="<?=$kick['banned_by_name']?>" class="avatar">

                                            <div class="name"><?=$kick['banned_by_name']?></div>
                                        </div>
                                    </td>
                                    <td data-name="Причина" class="column3"><?=clean($kick['reason'])?></td>
                                    <td data-name="Дата" class="column4 date"><?=millis_to_date($kick['time'])?></td>
                                    <td class="column5"></td>
                                    <td class="column6"></td>
                                </tr>
                                <tr class="spacer"><td colspan="100"></td></tr>
                            <?php }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="pagination">
                    <?php include 'components/pagination.php' ?>
                </div>
            </div>
        </div>
    </div>
<?php include './components/footer.php'; ?>

    <div id="results-container" class="hide"></div>
    <div class="modal-wrapper">
        <div id="modal" class="modal animate__faster">
            <div class="modal__header">
                <div class="modal__title">Hello #5</div>
                <div class="modal__tags">
                                    <span class="modal__tag">Hello</span>
                </div>
            </div>
            <div class="modal__body"></div>
            <div class="modal__footer">
                <button class="modal__dismiss">Ок</button>
            </div>
        </div>
    </div>

<!--    <div id="hello">made by ravilto</div>-->


    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js" integrity="sha512-zlWWyZq71UMApAjih4WkaRpikgY9Bz1oXIW5G0fED4vk14JjGlQ1UmkGM392jEULP8jbNMiwLWdM8Z87Hu88Fw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="./src/js/scripts.js"></script>
</body>
</html>
