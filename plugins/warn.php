<?php
if (stripos($msg, '/warn') === 0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    $args = explode(' ', $msg);
    $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=?');
    $q->execute([$chatID]);
    $res = $q->fetch(PDO::FETCH_ASSOC);
    $imp = json_decode($res['settings'], true);
    $maxw = $imp['maxwarn'];
    $pena = $imp['pena'];
    if (isset($replyuserid)) {
        $warns = warn($chatID, $replyuserid);
        if (isset($replyusername)) {
            $userbanned = '@' . $replyusername;
        } else {
            $userbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
        }
        if (isset($args[1])) {
            $reason = ' per ' . str_replace("$args[0]", '', $msg);
        }
        $menu[] = [['text' => "✔Rimuovi warn", "callback_data" => "/unwarn $replyuserid"]];
        sm($chatID, "$userbanned [$replyuserid] ha ricevuto un'ammonizione <b>$warns/$maxw</b> " . $reason, $menu);
        $id = $replyuserid;
    } else {
        if (isset($args[2])) {
            $reason = ' per ' . str_replace("$args[0] $args[1]", '', $msg);
        }
        if (stripos($args[1], "@") === 0) {
            $id = id($args[1]);
            if (!$id) {
                sm($chatID, 'Utente non trovato');
                exit;
            }
        } else {
            if (!is_numeric($args[1])) {
                sm($chatID, 'ID non valido');
                exit;
            }
            $id = $args[1];
        }
        if (username($id)) {
            $userbanned = '@' . username($id);
        } else {
            $userbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
        }
        $warns = warn($chatID, $id);
        $menu[] = [['text' => "✔Rimuovi warn", "callback_data" => "/unwarn $id"]];
        sm($chatID, "$userbanned [$id] ha ricevuto un'ammonizione <b>$warns/$maxw</b> " . $reason, $menu);
    }
    if ($warns >= $maxw) {
        setwarn($chatID,$id,0);
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
}
if (stripos($msg,"/unwarn")===0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=?');
    $q->execute([$chatID]);
    $res = $q->fetch(PDO::FETCH_ASSOC);
    $imp = json_decode($res['settings'], true);
    $maxw = $imp['maxwarn'];
    $pena = $imp['pena'];

    if (isset($replyid)) {
        $warns = unwarn($chatID,$replyuserid);
        if (!$warns) {
            sm($chatID,"L'utente ha già 0 warn", false, false, false, $msgid);
            exit;
        }
        if (isset($replyusername)) {
            $usersbanned = '@' . $replyusername;
        } else {
            $usersbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
        }
        sm($chatID, "Ho tolto un warn a $usersbanned [$replyuserid] $warns/$maxw");
    } else {
        $args = explode(' ',$msg);
        if (stripos($args[1], "@") === 0) {
            $id = id($args[1]);
            if (!$id) {
                sm($chatID, 'Utente non trovato');
                exit;
            }
        } else {
            if (!is_numeric($args[1])) {
                sm($chatID, 'ID non valido');
                exit;
            }
            $id = $args[1];
        }
        $warns = unwarn($chatID,$id);
        if (!$warns) {
            sm($chatID,"L'utente ha già 0 warn", false, false, false, $msgid);
            exit;
        }
        if (username($id)) {
            $usersbanned = '@' . username($id);
        } else {
            $usersbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
        }
        if (isset($cbdata)) {
            cb_reply($cbid, "Ok", false, $cbmid,$text . PHP_EOL . "<b>Warn rimosso $warns/$maxw</b>");
        } else {
            sm($chatID, "Ho tolto un warn a $usersbanned [$id] $warns/$maxw.");
        }
    }
}
if (stripos($msg,'/setmaxwarn')===0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    $args = explode( ' ',$msg);
    if (!isset($args[1]) || $args[1] < 2 || !is_numeric($args[1]) || $args[1] > 1000) {
        sm($chatID,'Numero di warn massimi non valido, deve essere maggiore o uguale a 2 e minore di 1000');
        exit;
    }
    $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=?');
    $q->execute([$chatID]);
    $res = $q->fetch(PDO::FETCH_ASSOC);
    $imp = json_decode($res['settings'], true);
    $imp['maxwarn'] = $args[1];
    $q2 = $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1');
    $q2->execute([json_encode($imp)]);
    sm($chatID,'Warn massimi impostati a ' . $args[1]);
}