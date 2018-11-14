<?php
//12064
$num = (int)$_POST['arg'];

if ($num <= 0) exit(json_encode(array('responce' => 0)));

define('DB_NAME', 'domainslibrary.db');
define('DB_PREFIX', 'all_');


/**
 * @param $url
 * @return int|string
 */
function parseData($url)
{
    $data = @file_get_contents($url);

    if ($data !== false) {
        $str = <<<'TNN'
<span class="icon-site"></span>
TNN;
        $addition = 30; // строка с переносом до начала ссылки. Проще дописать цифрой, чем вставить в поиск
        $cut_from = strpos($data, $str) + strlen($str) + $addition;

        $data = substr($data, $cut_from);

        $str = '"';

        $cut_to = strpos($data, $str);

        $data = substr($data, 0, $cut_to);

        if (substr($data, 0, 4) == 'http') {
            // success
            $homepage = $data;
        } else {
            $homepage = 0;
        }
    } else {
        $homepage = 0;
    }

    return $homepage;
}

/**
 * @param $profile_id
 * @param $homepage
 */
function saveData($profile_id, $homepage)
{
    $db = new SQLite3(DB_NAME);

    /*if ($profile_id == 1) {
        $db->exec('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'profiles` (
          `profile_id` INTEGER PRIMARY KEY,
          `homepage` TEXT(255) NOT NULL
        )');
    }*/

    $info['profile_id'] = $profile_id;
    $info['homepage'] = $homepage;

    @$db->query('INSERT INTO `'.DB_PREFIX.'profiles` ( `profile_id`, `homepage` ) VALUES("'. $info['profile_id'] .'", "'. $info['homepage'] .'")');
    $insert_id = $db->lastInsertRowID();
    $db->close();

    return $insert_id;
}

$url = 'http://stackoverflow.com/users/' . $num;
//$url = 'http://homepagesparser.wf/pages/1.html';
$saved = 0;
$homepage = parseData($url);
if ($homepage) {
    $saved = saveData($num, $homepage);
    if (!$saved) $saved = -1;
}

++$num;
exit(json_encode(
    array(
        'responce' => $num,
        'saved' => $saved,
    )
));