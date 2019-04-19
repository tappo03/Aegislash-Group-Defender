<?php
if (isset($update['message'])) {
    if (isset($update['message']['entities']) || isset($update['message']['caption_entities'])) {
        if (isset($update['message']['caption_entities'])) {
            $msg = $didascalia;
            $update['message']['entities'] = $update['message']['caption_entities'];
        }
        $link = false;
        $mention = false;
        foreach ($update['message']['entities'] as $entity) {
            if ($entity['type'] == "url" || $entity['type'] == "text_link" && !$link) {
                if ($entity['type'] == "text_link") {
                    $men = $entity['url'];
                } else {
                    $men = substr($msg, $entity['offset'], $entity['length']);
                }
                if (stripos($men, "t.me") !== false || stripos($men, "telegram.me") !== false || stripos($men, "tg://resolve?domain=") !== false && $imp["channels"] != "no") {
                    $prefix = ["http://", "https://", "t.me/", "telegram.me/", "tg://resolve?domain="];
                    $dm = str_replace($prefix, '', strtolower($men));
                    if (stripos($dm, "/") !== 0) {
                        $dm = explode("/", $dm)[0];
                    }
                    if (stripos($dm, "?") !== 0) {
                        $dm = explode("?", $dm)[0];
                    }
                    if ($imp['channels']['pena'] != 'no'|| $imp['channels']['eliminazione']) {
                    $canale = json_decode(file_get_contents("https://api.telegram.org/$api/getChat?chat_id=@$dm"), true);
                    $id = $canale["result"]["id"];
                    if ($id < 0) {
                        $mention = true;
                    }
                    }
                } else {
                    $link = true;
                }
            } elseif ($entity['type'] == "mention" && !$mention) {
                $men = substr($msg, $entity['offset'], $entity['length']);
                if ($imp['channels']['pena'] != "no" || $imp['channels']['eliminazione']) {
                    $canale = json_decode(file_get_contents("https://api.telegram.org/$api/getChat?chat_id=$men"), true);
                    $id = $canale["result"]["id"];
                    if ($id < 0) {
                        $mention = true;
                    }
                }
            }

        }
    }
    if ($link && ($imp['link']['pena'] != 'no' or $imp['link']['eliminazione'])) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($username)) {
                $userbanned = '@' . $username;
            } else {
                $userbanned = '<a href="tg://user?id=' . $userID . '">' . htmlspecialchars($nome) . '</a>';
            }
            if ($imp['link']['eliminazione']) {
                dm($chatID,$msgid);
                if ($imp['link']['pena'] == 'no') {
                    sm($chatID,$userbanned ." Non è consentito l'invio di link");
                }
            }
            if ($imp['link']['pena'] == 'Warn') {
                $maxw = $imp['maxwarn'];
                $pena = $imp['pena'];
                $warns = warn($chatID, $userID);
                $menu[] = [['text' => "✔Rimuovi warn", "callback_data" => "/unwarn $userID"]];
                sm($chatID, "$userbanned [$userID] ha ricevuto un'ammonizione <b>$warns/$maxw</b> " . ' per invio di link non consentito', $menu);
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

            } elseif ($imp['link']['pena'] == 'Ban') {
                $id = $userID;
                if (!isOk(ban($chatID, $id))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $id"]];
                sm($chatID, "Ho bannato $userbanned [$id] dal gruppo per invio di link non consentito.", $menu);
            } elseif ($imp['link']['pena'] == 'Mute') {
                if (!isOK(limita($chatID, $userID))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $userID"]];
                sm($chatID, "Ho mutato $userbanned [$userID] per invio di link non consentito", $menu);
            } elseif ($imp['link']['pena'] == 'Kick') {
                $id = $userID;
                if (!isOk(ban($chatID, $id))||!isOk(unban($chatID, $id))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                sm($chatID, "Ho espulso $userbanned [$id] dal gruppo per invio di link non consentito.", $menu);
            }
        }
    }
    if ($mention && ($imp['channels']['pena'] != 'no' or $imp['channels']['eliminazione'])) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($username)) {
                $userbanned = '@' . $username;
            } else {
                $userbanned = '<a href="tg://user?id=' . $userID . '">' . htmlspecialchars($nome) . '</a>';
            }
            if ($imp['channels']['eliminazione']) {
                dm($chatID,$msgid);
                if ($imp['channels']['pena'] == 'no') {
                    sm($chatID,$userbanned ." Non è consentito l'invio di username pubblici");
                }
            }
            if ($imp['channels']['pena'] == 'Warn') {
                $maxw = $imp['maxwarn'];
                $pena = $imp['pena'];
                $warns = warn($chatID, $userID);
                $menu[] = [['text' => "✔Rimuovi warn", "callback_data" => "/unwarn $userID"]];
                sm($chatID, "$userbanned [$userID] ha ricevuto un'ammonizione <b>$warns/$maxw</b> " . ' per invio di username pubblici', $menu);
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

            } elseif ($imp['channels']['pena'] == 'Ban') {
                $id = $userID;
                if (!isOk(ban($chatID, $id))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $id"]];
                sm($chatID, "Ho bannato $userbanned [$id] dal gruppo per invio di username pubblici non consentito.", $menu);
            } elseif ($imp['channels']['pena'] == 'Mute') {
                if (!isOK(limita($chatID, $userID))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $userID"]];
                sm($chatID, "Ho mutato $userbanned [$userID] per invio di username pubblici non consentito", $menu);
            } elseif ($imp['channels']['pena'] == 'Kick') {
                $id = $userID;
                if (!isOk(ban($chatID, $id))||!isOk(unban($chatID, $id))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                sm($chatID, "Ho espulso $userbanned [$id] dal gruppo per invio di username pubblici non consentito.", $menu);
            }
        }
    }
    if (isset($update['message']["forward_from_chat"]['username']) && ($imp['forwarded']['pena'] != 'no' or $imp['forwarded']['eliminazione'])) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($username)) {
                $userbanned = '@' . $username;
            } else {
                $userbanned = '<a href="tg://user?id=' . $userID . '">' . htmlspecialchars($nome) . '</a>';
            }
            if ($imp['forwarded']['eliminazione']) {
                dm($chatID,$msgid);
                if ($imp['forwarded']['pena'] == 'no') {
                    sm($chatID,$userbanned ." Non è consentito l'inoltro da canali pubblici");
                }
            }
            if ($imp['forwarded']['pena'] == 'Warn') {
                $maxw = $imp['maxwarn'];
                $pena = $imp['pena'];
                $warns = warn($chatID, $userID);
                $menu[] = [['text' => "✔Rimuovi warn", "callback_data" => "/unwarn $userID"]];
                sm($chatID, "$userbanned [$userID] ha ricevuto un'ammonizione <b>$warns/$maxw</b> " . ' per inoltro da un canale pubblico', $menu);
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

            } elseif ($imp['forwarded']['pena'] == 'Ban') {
                $id = $userID;
                if (!isOk(ban($chatID, $id))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                $menu[] = [['text' => "✔Unban", "callback_data" => "/unban $id"]];
                sm($chatID, "Ho bannato $userbanned [$id] dal gruppo per inoltro da un canale pubblico.", $menu);
            } elseif ($imp['forwarded']['pena'] == 'Mute') {
                if (!isOK(limita($chatID, $userID))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $userID"]];
                sm($chatID, "Ho mutato $userbanned [$userID] per inoltro da un canale pubblico", $menu);
            } elseif ($imp['forwarded']['pena'] == 'Kick') {
                $id = $userID;
                if (!isOk(ban($chatID, $id))||!isOk(unban($chatID, $id))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                sm($chatID, "Ho espulso $userbanned [$id] dal gruppo per inoltro da un canale pubblico.", $menu);
            }
        }
    }
    //Forwarded messages


}