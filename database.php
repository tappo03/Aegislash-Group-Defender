
<?php

echo "<br />Database";
if (!isset($config)) {
    exit;
}
$tabella = $userbot;
$db = new PDO("mysql:host=" . $config["ip"] . ";dbname=" . $config['database'], $config['user'], $config['password']);
if ($_GET["install"]) {
    $users = $db->prepare('CREATE TABLE IF NOT EXISTS users (
chat_id bigint(0),
username varchar(200),
state varchar(200),
PRIMARY KEY (chat_id))');
    $users->execute();
    $groups = $db->prepare("CREATE TABLE IF NOT EXISTS groups (
chat_id bigint(0),
username varchar(50),
warns varchar(20000) DEFAULT '',
settings varchar(2000) DEFAULT '',
media_block varchar(2000) DEFAULT '',
welcome text DEFAULT '',
rules text DEFAULT '',
PRIMARY KEY (chat_id))");
    $groups->execute();
    echo "Database installato";
}
$tabella = 'groups';
if ($chatID < 0) {
    $q = $db->prepare("select * from $tabella where chat_id = ? LIMIT 1");
    $q->execute([$chatID]);
    if (!$q->rowCount()) {
        $db->prepare("insert into $tabella (chat_id, state, username) values ($chatID, '',?)")->execute([$usernamechat]);
        $settings = [
            'maxwarn' => 3,
            'pena' => 'ban',
            'link' => ['pena' => 'no','eliminazione' => false],
            'channels' => ['pena' => 'no','eliminazione' => false],
            'forwarded' => ['pena' => 'no','eliminazione' => false],
            'antiflood' => ['pena' => 'ban','actived' => 'no', 'messages' => 3, 'time' => 2],
            'welcome' => false,
            'rules' => false,
        ];

        $db->prepare("insert into groups (chat_id,username,settings) values ($chatID, ?,?)")->execute([$usernamechat,json_encode($settings)]);
    }
}
$tabella = 'users';
if ($userID ) {
    $q = $db->prepare("select * from $tabella where chat_id = ? LIMIT 1");
    $q->execute([$userID]);

    if (!$q->rowCount()) {
        if ($userID == $chatID) {
            $db->prepare("insert into $tabella (chat_id, state, username) values ($chatID, '',?)")->execute([$username]);
        } else {
            $db->prepare("insert into $tabella (chat_id, state, username) values ($userID, 'group', ?)")->execute([$username]);
        }
    } else {
        $u = $q->fetch(PDO::FETCH_ASSOC);
            if ($u['state'] == "group" && $chatID > 0) {
                $db->prepare("update $tabella set state = '' where chat_id = ? LIMIT 1")->execute([$chatID]);
            }
            if ($u['username'] != $username) {
                $db->prepare("update $tabella set username = ? where chat_id = ? LIMIT 1")->execute([$username,$userID]);
            }
        }
}
