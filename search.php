<?php
define('DB_NAME', 'domainslibrary.db');
define('DB_PREFIX', 'all_');
$db = new SQLite3(DB_NAME);

$q = strip_tags($_GET['q']);

$page = (int)$_GET['page'];
if ($page <= 0 ) $page = 1;
$step = 30;
$from = ($page - 1) * $step;
$to = ($page - 1) + $step;

$stmt = $db->prepare('SELECT * from `' . DB_PREFIX . 'profiles` where `homepage` LIKE :homepage LIMIT ' . $from . ', ' . $to);
$stmt->bindValue(':homepage', '%' . $q . '%', SQLITE3_TEXT);

$result = $stmt->execute();
while ($row = $result->fetchArray())
{
    $domainslist[$row['profile_id']] = $row['homepage'];
}
$stmt->close();
$db->close();
if (!$domainslist) $domainslist = [];

$countlist = count($domainslist);
if ($countlist == $step) {
    $next = 1;
} elseif ($countlist > 0) {
    $next = 0;
} else {
    $next = -1;
}
?>
<!DOCTYPE html>
<html>
<head lang="ru">
    <meta charset="UTF-8">
    <title>База домашних страниц</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="/css/main.css" />
</head>
<body id="space-background">
<header>
    <nav id="header-nav">
        <div id="nav-menu">
            <ul id="nav-menu-list">
                <li class="nav-menu-item logo">
                    <a href="./">
                        <img src="/img/theme/ySzBWqrmyuA.jpg" />
                    </a>
                </li>
                <li class="nav-menu-item"><a href="/about.html">Обо мне</a></li>
                <li class="nav-menu-item active"><a href="/stuff.html">Поделки</a></li>
                <li class="nav-menu-item"><a href="/accounts.html">Аккаунты</a></li>
            </ul>
        </div>
    </nav>
</header>
<div class="header-delimeter"></div>
<section>
    <div class="content">
        <h2><a href="./">База домашних страниц</a> > search</h2>
        <form method="get">
            <input type="text" name="q" value="<?= $q ?>" placeholder="search"/>
            <input type="submit" value="Search" />
        </form>
        <ul>
            <?php foreach ($domainslist as $profile_id => $homepage) { ?>
                <li>
                    <span><a href="http://stackoverflow.com/users/<?= $profile_id ?>" target="_blank">user# <?= $profile_id ?></a>: </span><span><a href="<?= $homepage ?>" target="_blank"><?= $homepage ?></a></span>
                </li>
            <?php } ?>
        </ul>

        <br><br>
        <?php if ($page > 1) { ?><a href="?q=<?= $q ?>&page=<?= ($page - 1);?>"> < Prev</a>
            | <?= $page ?> |
        <?php } ?>
        <?php if ($next > 0) {?>
            <a href="?q=<?= $q ?>&page=<?= ($page + 1);?>">Next ></a>
        <?php } elseif ($next < 0) { ?>
            <span>Нет результатов</span>
        <?php } ?>
    </div>
</section>
<div class="header-delimeter"></div>
<!-- Yandex.Metrika counter --><script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter33791179 = new Ya.Metrika({ id:33791179, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="https://mc.yandex.ru/watch/33791179" style="position:absolute; left:-9999px;" alt="" /></div></noscript><!-- /Yandex.Metrika counter -->
</body>
</html>