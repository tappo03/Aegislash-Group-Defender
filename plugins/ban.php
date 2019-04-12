<?php

if (stripos($msg, '/ban') === 0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    $args = explode(' ', $msg);
    if (isset($replyid)) {
        if (!$args[1]) {
            if (!isOk(ban($chatID, $replyuserid))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }

            if (isset($replyusername)) {
                $userbanned = '@' . $replyusername;
            } else {
                $userbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
            }
            $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $replyuserid"]];
            sm($chatID,sm($chatID, "Ho bannato $userbanned [$replyuserid] dal gruppo.", $menu));
        } elseif (count(explode("/", $args[1])) > 1) {
            $time = strtotime(str_replace("/", "-", $args[1]));
            if (isset($replyusername)) {
                $userbanned = '@' . $replyusername;
            } else {
                $userbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
            }
            if (isset($args[2])) {
                $reason = ' per ' . str_replace("$args[0] $args[1]", '', $msg);
            }
            if (!isOK(ban($chatID, $replyuserid, $time))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }
            $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $replyuserid"]];
            sm($chatID, "Ho bannato $userbanned [$replyuserid] dal gruppo fino al $args[1]" . $reason, $menu);
        } else {
            if (isset($args[1])) {
                $reason = ' per ' . str_replace("$args[0] ", '', $msg);
            }
            if (isset($replyusername)) {
                $userbanned = '@' . $replyusername;
            } else {
                $userbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
            }
            if (!isOk(ban($chatID, $replyuserid))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }
            $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $replyuserid"]];
            sm($chatID, "Ho bannato $userbanned [$replyuserid] dal gruppo" . $reason . ".", $menu);
        }
    } else {
        $args = explode(' ', $msg);
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
        if (!$args[2]) {
            if (!isOk(ban($chatID, $id))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }

            if (username($id)) {
                $userbanned = '@' . username($id);
            } else {
                $userbanned = '<a href="tg://user?id=' . $id . '">' . htmlspecialchars($id) . '</a>';
            }
            $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $id"]];
            sm($chatID, "Ho bannato $userbanned [$id] dal gruppo.", $menu);
        } elseif (count(explode("/", $args[2])) > 1) {
            $time = strtotime(str_replace("/", "-", $args[2]));
            if (username($id)) {
                $userbanned = '@' . username($id);
            } else {
                $userbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
            }
            if (isset($args[3])) {
                $reason = ' per ' . str_replace("$args[0] $args[1] $args[2]", '', $msg);
            }
            if (!isOK(ban($chatID, $id, $time))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }
            $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $id"]];
            sm($chatID, "Ho bannato $userbanned [$id] dal gruppo fino al $args[2]" . $reason, $menu);
        } else {
            if (isset($args[2])) {
                $reason = ' per ' . str_replace("$args[0] $args[1]", '', $msg);
            }
            if (username($id)) {
                $userbanned = '@' . username($id);
            } else {
                $userbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
            }
            if (!isOk(ban($chatID, $id))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }
            $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $id"]];
            sm($chatID, "Ho bannato $userbanned [$id] dal gruppo" . $reason . ".", $menu);
        }

    }
}
//unban
if (stripos($msg,"/unban")===0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    if (isset($replyid)) {
        if (!isOk(unban($chatID, $replyuserid))) {
            sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
            exit;
        }
        if (isset($replyusername)) {
            $usersbanned = '@' . $replyusername;
        } else {
            $usersbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
        }
        sm($chatID, "Ho sbannato $usersbanned [$replyuserid] dal gruppo.");
    } else {
        if (isset($cbdata)) {
            cb_reply($cbid, "Ok", false, $cbmid,$text . PHP_EOL . "<b>Utente sbannato.</b>");
        }
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
        if (!isOK(unban($chatID, $id))) {
            sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
            exit;
        }
        if (username($id)) {
            $usersbanned = '@' . username($id);
        } else {
            $usersbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
        }
        sm($chatID, "Ho sbannato $usersbanned [$id] dal gruppo.");
    }
}

//mute

if (stripos($msg, '/mute') === 0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    $args = explode(' ', $msg);
    if (isset($replyid)) {
        if (!$args[1]) {
            if (!isOk(limita($chatID, $replyuserid))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }

            if (isset($replyusername)) {
                $userbanned = '@' . $replyusername;
            } else {
                $userbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
            }
            $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $replyuserid"]];
            sm($chatID,sm($chatID, "Ho mutato $userbanned [$replyuserid]", $menu));
        } elseif (count(explode("/", $args[1])) > 1) {
            $time = strtotime(str_replace("/", "-", $args[1]));
            if (isset($replyusername)) {
                $userbanned = '@' . $replyusername;
            } else {
                $userbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
            }
            if (isset($args[2])) {
                $reason = ' per ' . str_replace("$args[0] $args[1]", '', $msg);
            }
            if (!isOK(limita($chatID, $replyuserid, $time))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }
            $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $replyuserid"]];
            sm($chatID, "Ho mutato $userbanned [$replyuserid] fino al $args[1]" . $reason, $menu);
        } else {
            if (isset($args[1])) {
                $reason = ' per ' . str_replace("$args[0] ", '', $msg);
            }
            if (isset($replyusername)) {
                $userbanned = '@' . $replyusername;
            } else {
                $userbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
            }
            if (!isOk(ban($chatID, $replyuserid))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }
            $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $replyuserid"]];
            sm($chatID, "Ho mutato $userbanned [$replyuserid]" . $reason . ".", $menu);
        }
    } else {
        $args = explode(' ', $msg);
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
        if (!$args[2]) {
            if (!isOk(limita($chatID, $id))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }

            if (username($id)) {
                $userbanned = '@' . username($id);
            } else {
                $userbanned = '<a href="tg://user?id=' . $id . '">' . htmlspecialchars($id) . '</a>';
            }
            $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $id"]];
            sm($chatID, "Ho mutato $userbanned [$id].", $menu);
        } elseif (count(explode("/", $args[2])) > 1) {
            $time = strtotime(str_replace("/", "-", $args[2]));
            if (username($id)) {
                $userbanned = '@' . username($id);
            } else {
                $userbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
            }
            if (isset($args[3])) {
                $reason = ' per ' . str_replace("$args[0] $args[1] $args[2]", '', $msg);
            }
            if (!isOK(limita($chatID, $id, $time))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }
            $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $id"]];
            sm($chatID, "Ho mutato $userbanned [$id] fino al $args[2]" . $reason, $menu);
        } else {
            if (isset($args[2])) {
                $reason = ' per ' . str_replace("$args[0] $args[1]", '', $msg);
            }
            if (username($id)) {
                $userbanned = '@' . username($id);
            } else {
                $userbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
            }
            if (!isOk(limita($chatID, $id))) {
                sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                exit;
            }
            $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $id"]];
            sm($chatID, "Ho mutato $userbanned [$id]" . $reason . ".", $menu);
        }

    }
}

//Unmute

if (stripos($msg,"/unmute")===0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    if (isset($replyid)) {
        if (!isOk(limita($chatID, $replyuserid,0,true,true,true,true))) {
            sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
            exit;
        }
        if (isset($replyusername)) {
            $usersbanned = '@' . $replyusername;
        } else {
            $usersbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
        }
        sm($chatID, "Ho smutato $usersbanned [$replyuserid].");
    } else {
        if (isset($cbdata)) {
            cb_reply($cbid, "Ok", false, $cbmid,$text . PHP_EOL . "<b>Utente smutato.</b>");
        }
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
        if (!isOK(limita($chatID, $id,true,true,true,true,true))) {
            sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
            exit;
        }
        if (username($id)) {
            $usersbanned = '@' . username($id);
        } else {
            $usersbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
        }
        sm($chatID, "Ho smutato $usersbanned [$id].");


    }
}
if (stripos($msg,"/kick")===0) {
    if (!isAdmin($chatID, $userID)) {
        if (isset($cbdata)) {
            cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
        } else {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
        }
        exit;
    }
    if (isset($replyid)) {
        if (!isOk(ban($chatID, $replyuserid)) || !isOK(unban($chatID,$replyuserid))) {
            sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
            exit;
        }
        if (isset($replyusername)) {
            $usersbanned = '@' . $replyusername;
        } else {
            $usersbanned = '<a href="tg://user?id=' . $replyuserid . '">' . htmlspecialchars($replynome) . '</a>';
        }
        sm($chatID, "Ho kickato $usersbanned [$replyuserid].");
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
        if (!isOK(ban($chatID, $id)) || !isOk(unban($chatID,$id))) {
            sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
            exit;
        }
        if (username($id)) {
            $usersbanned = '@' . username($id);
        } else {
            $usersbanned = '<a href="tg://user?id=' . $id . '">' . $id . '</a>';
        }
        sm($chatID, "Ho kickato $usersbanned [$id].");


    }
}