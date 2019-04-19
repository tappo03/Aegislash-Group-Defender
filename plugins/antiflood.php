<?php
if (stripos($msg, '/settime ')===0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    $time = explode(' ',$msg)[1];
    if (!is_numeric($time) && $time < 11 && $time > 1) {
        sm($chatID,'Devi inserire un numero valido compreso fra 2 e 10.');
    }

    $imp['antiflood']['time'] = $time;
    $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1')->execute([json_encode($imp)]);
}
if (stripos($msg, '/setmaxmsg ')===0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    $messaggi= explode(' ',$msg)[1];
    if (!is_numeric($messaggi) && $messaggi < 11 && $messaggi > 1) {
        sm($chatID,'Devi inserire un numero valido compreso fra 2 e 10.');
    }

    $imp['antiflood']['messages'] = $messaggi;
    $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1')->execute([json_encode($imp)]);
}
if ($imp['antiflood']['actived'] == "on") {
    $maxtime = $imp['antiflood']['time']; // Numero massimo di secondi
    $maxmsg = $imp['antiflood']['messages']; //Numero massimo di messaggi inviabili entro $maxtime
    $unix = time();
    $db->query("CREATE TABLE IF NOT EXISTS antiflood (
	id int(0) AUTO_INCREMENT,
	chat_id int(32),
	time int(20),
    number int (20),
	PRIMARY KEY (id))");
    $q = $db->prepare("select * from antiflood where chat_id = ?");
    $q->execute([$userID]);
    if (!$q->rowCount()) {
        $db->prepare("insert into antiflood (chat_id, time, number) values (?, ?, 0)")->execute([$userID, $unix]);
    }
    $f = $q->fetch(PDO::FETCH_ASSOC);
    $lasttime = $f['time'];
    $number = $f['number'];
    $tempotrascorso = $unix - $f["time"];

    if ($tempotrascorso <= $maxtime) {
        $number++;
        if ($number >= $maxmsg) {
            $db->prepare("update antiflood set number = 0 where chat_id=?")->execute([$userID]);
            if (!isAdmin($chatID, $userID)) {
                if (isset($username)) {
                    $userbanned = '@' . $username;
                } else {
                    $userbanned = '<a href="tg://user?id=' . $userID . '">' . htmlspecialchars($nome) . '</a>';
                }
                if ($imp['antiflood']['pena'] == 'warn') {
                    $maxw = $imp['maxwarn'];
                    $pena = $imp['pena'];
                    $warns = warn($chatID, $userID);
                    $menu[] = [['text' => "✔Rimuovi warn", "callback_data" => "/unwarn $userID"]];
                    sm($chatID, "$userbanned [$userID] ha ricevuto un'ammonizione <b>$warns/$maxw</b> " . ' per flood', $menu);
                    $id = $userID;
                    if ($warns >= $maxw) {
                        setwarn($chatID, $id, 0);
                        if ($pena == 'ban') {
                            if (!isOK(ban($chatID, $id))) {
                                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                                exit;
                            }
                            $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $id"]];
                            sm($chatID, "$userbanned [$id] ha raggiunto il numero massimo di ammonizioni ed è stato bannato.", $menu);
                        } elseif ($pena == 'mute') {
                            if (!isOk(limita($chatID, $id))) {
                                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                                exit;
                            }
                            $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $id"]];
                            sm($chatID, "$userbanned [$id] ha raggiunto il numero massimo di ammonizioni ed è stato mutato.", $menu);
                        } elseif ($pena == 'kick') {
                            if (!isOk(ban($chatID, $id)) || !isOK(unban($chatID, $id))) {
                                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                                exit;
                            }
                            sm($chatID, "$userbanned [$id] ha raggiunto il numero massimo di ammonizioni ed è stato kickato.");
                        }
                    }

                } elseif ($imp['channels']['pena'] == 'ban') {
                    $id = $userID;
                    if (!isOk(ban($chatID, $id))) {
                        sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                        exit;
                    }
                    $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $id"]];
                    sm($chatID, "Ho bannato $userbanned [$id] dal gruppo per flood.", $menu);
                } elseif ($imp['channels']['pena'] == 'mute') {
                    if (!isOK(limita($chatID, $userID))) {
                        sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                        exit;
                    }
                    $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $userID"]];
                    sm($chatID, "Ho mutato $userbanned [$userID] per flood", $menu);
                } elseif ($imp['channels']['pena'] == 'kick') {
                    $id = $userID;
                    if (!isOk(ban($chatID, $id)) || !isOk(unban($chatID, $id))) {
                        sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                        exit;
                    }
                    sm($chatID, "Ho espulso $userbanned [$id] dal gruppo per flood.", $menu);
                }
            }
        } else {
            $db->prepare("update antiflood set number = ? where chat_id= $userID")->execute([$number]);
        }

    } else {
        $db->prepare("update antiflood set time = ? where chat_id= $userID")->execute([$unix]);
    }
}