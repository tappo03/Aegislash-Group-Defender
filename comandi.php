<?php
if (isset($msg)) {
    if (stripos($msg, "/start") === 0) {
        $menu[] = array(
            array("text" => "📃Lista funzioni",
                "callback_data" => "list"),
        );
        $menu[] = array(
            array("text" => "☕Github",
                "url" => "https://github.com/SilverOS/Aegislash-Group-Defender"),
        );
        $menu[] = array(
            array("text" => "👥Aggiungimi a un gruppo",
                "url" => "http://t.me/aegislashbot?startgroup=true"),
        );
        if (isset($cbdata)) {
            cb_reply($cbid, "Ok", false, $cbmid, 'Benvenuto su Aegislashbot!'. PHP_EOL. 'Questo è un bot open-source per la gestione dei gruppi sviluppato da @SilverOSp. Aggiungimi in un gruppo e fammi admin per permettermi di moderarlo al meglio!', $menu);
        } else {
            sm($chatID, 'Benvenuto su Aegislashbot!'.PHP_EOL .'Questo è un bot open-source per la gestione dei gruppi sviluppato da @SilverOSp. Aggiungimi in un gruppo e fammi admin per permettermi di moderarlo al meglio!', $menu);
        }
    }
    if (isset($cbdata)&& $cbdata == "list") {
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/start"),
        );
        cb_reply($cbid, "Ok", false, $cbmid,"Funzioni del bot attuali: \n⛔️/ban,/unban,/mute,/unmute, /kick\n❗️Warn\n☔️Antiflood\n💬Benvenuto e regole\n🌑Modalità notte\n📩Antispam" , $menu);
    }
}
if ($chatID < 0) {

    // Night Mode
    $q = $db->prepare('SELECT settings FROM groups WHERE chat_id = ?');
    $q->execute([$chatID]);
    $e = $q->fetch(PDO::FETCH_ASSOC);
    $imp = json_decode($e['settings'],true);
    if ($imp['night']['enabled']) {
        if ($imp['night']['start'] < $imp['night']['end']) {
            if (date('H:i') > $imp['night']['start'] && date('H:i') < $imp['night']['end']) {
                $isnight = true;
            }
        } elseif ($imp['night']['start'] > $imp['night']['end']) {
            if (date('H:i') > $imp['night']['start'] || date('H:i') < $imp['night']['end']) {
                $isnight = true;
            }
        }
    }
    if ($isnight) {
        if (!$imp['night']['media']) {
            if (isset($audio) || isset($sticker) || isset($photo)|| isset($document)|| isset($video) || isset($animation) || isset($video_note) || isset($voice)) {
                if (!isAdmin($chatID, $userID)) {
                    dm($chatID, $msgid);
                }
            }
        }
        if (!$imp['night']['text']) {
            if (isset($msg) && !$cbdata) {
                if (!isAdmin($chatID, $userID)) {
                    dm($chatID, $msgid);
                }
            }
        }
    }



    if (isset($update['message']['new_chat_member'])) {
        if ($update['message']['new_chat_member']['username'] == $userbot) {
            sm($chatID, "Grazie per avermi aggiunto! Fammi admin per permettermi di funzionare correttamente!");
        } else {
            $q = $db->prepare('SELECT welcome,settings FROM groups WHERE chat_id = ?');
            $q->execute([$chatID]);
            $e = $q->fetch(PDO::FETCH_ASSOC);
            $message = json_decode($e['welcome'],true);
            if (json_decode($e['settings'],true)['welcome'] && strlen($message) > 0) {
                sm($chatID,str_replace(['{NAME}','{SURNAME}','{ID}','{USERNAME}'],[htmlspecialchars($nome),htmlspecialchars($cognome),$userID,htmlspecialchars($username)],$message));
            }
            if ($isnight && !$imp['night']['join']) {
                if ($username) {
                    $userbanned = '@' . $username;
                } else {
                    $userbanned = '<a href="tg://user?id=' . $id . '">' . htmlspecialchars($nome) . '</a>';
                }
                if (!isOk(limita($chatID, $userID))) {
                    sm($chatID, "Non ho i permessi sufficenti per eseguire questa azione,assicurati che io sia admin");
                    exit;
                }
                $menu[] = [['text' => "✔Unmuta", "callback_data" => "/unmute $userID"]];
                sm($chatID, "Ho mutato $userbanned [$userID] per essere entrato nel gruppo durante la modalità notte.", $menu);
            }
        }
    }
    // Menu
    if (isset($msg) && stripos($msg, "/settings") === 0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        if (!isAdmin($chatID, $userID)) {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            exit;
        }
        $menu[] = array(
            array("text" => "⛔Limitazioni utente",
                "callback_data" => "/menulim"),
            array("text" => "❗Warn",
                "callback_data" => "/menuwarn"),
        );
        $menu[] = array(
            array("text" => "✉️Antispam",
                "callback_data" => "/menuantispam"),
            array("text" => "☔️Antiflood",
                "callback_data" => "/menuantiflood"),
        );
        $menu[] = array(
            array("text" => "🆕 Messaggio Benvenuto",
                "callback_data" => "/welcome"),
            array("text" => "👮🏻 Regole",
                "callback_data" => "/menurules"),
        );
        $menu[] = array(
            array("text" => "🌑Night mode",
                "callback_data" => "/night"),
        );
        if (isset($cbdata)) {
            cb_reply($cbid, "Ok", false, $cbmid, 'Ecco la lista delle impostazioni', $menu);
        } else {
            sm($chatID, 'Ecco la lista delle impostazioni', $menu);
        }
    }
    if (isset($cbdata)&& stripos($cbdata, '/night')===0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id = ?');
        $q->execute([$chatID]);
        $e = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($e['settings'],true);
        if (explode(' ',$cbdata)[1] == 'toggle') {
            $imp['night']['enabled'] ? $imp['night']['enabled'] = false : $imp['night']['enabled'] = true;
            $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1')->execute([json_encode($imp)]);
        } elseif (explode(' ',$cbdata)[1] == 'media') {
        $imp['night']['media'] ? $imp['night']['media'] = false : $imp['night']['media'] = true;
        $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1')->execute([json_encode($imp)]);
        } elseif (explode(' ',$cbdata)[1] == 'text') {
            $imp['night']['text'] ? $imp['night']['text'] = false : $imp['night']['text'] = true;
            $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1')->execute([json_encode($imp)]);
        } elseif (explode(' ',$cbdata)[1] == 'join') {
            $imp['night']['join'] ? $imp['night']['join'] = false : $imp['night']['join'] = true;
            $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1')->execute([json_encode($imp)]);
        }
        $imp['night']['start'] ?  $start = $imp['night']['start'] : $start = 'non impostato';
        $imp['night']['end'] ? $end = $imp['night']['end'] : $end = 'non impostato';
        $imp['night']['enabled'] ? $act = '❌Disabilita' : $act = '✅Abilita';
        !$imp['night']['text'] ? $actt = '❌Messaggi' : $actt = '✅Messaggi';
        !$imp['night']['media'] ? $actm = '❌Media' : $actm = '✅Media';
        !$imp['night']['join'] ? $actj = '❌Entrate' : $actj = '✅Entrate';
        $menu[] = [['text' => $act,'callback_data' => '/night toggle']];
        $menu[] = array(
            array("text" => "🌑Imposta orario",
                "callback_data" => "/timenight"),
        );
        $menu[] = [['text' => $actt,'callback_data' => '/night text'],['text' => $actm,'callback_data' => '/night media']];
        $menu[] = [['text' => $actj,'callback_data' => '/night join']];
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/settings"),
        );
        cb_reply($cbid, "Ok", false, $cbmid, "🌑Qui potrai abilitare o disabilitare la modalità notte, che può limitare l'invio di messaggi o media in orari notturni. \n\n🌕Inizio: $start\n☀️Fine: $end",$menu);
    }
    if (isset($cbdata)&& stripos($cbdata, '/timenight')===0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id = ?');
        $q->execute([$chatID]);
        $e = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($e['settings'], true);
        if ($cbdata == '/timenight') {
            for ($i = 0;$i < 24; $i+=4) {
                $menu[] = [['text' => $i.':00', 'callback_data' => '/timenight '.$i.':00'],['text' => ($i+1).':00', 'callback_data' => '/timenight ' . ($i+1) . ':00'],['text' => ($i+2).':00', 'callback_data' => '/timenight ' . ($i+2) . ':00'],['text' => ($i+3).':00', 'callback_data' => '/timenight ' . ($i+3) . ':00']];
            }
            cb_reply($cbid, "Ok", false, $cbmid, "🌑Selezione ora di inizio", $menu);
        } elseif (count(explode(' ',$cbdata)) == 2) {
            $start  = explode(' ',$cbdata)[1];
            for ($i = 0;$i < 24; $i+=4) {
                $menu[] = [['text' => $i.':00', 'callback_data' => '/timenight ' . $start   .' ' .$i.':00'],['text' => ($i+1).':00', 'callback_data' => '/timenight ' . $start.  ' ' . ($i+1) . ':00'],['text' => ($i+2).':00', 'callback_data' => '/timenight ' . $start.  ' ' .($i+2) . ':00'],['text' => ($i+3).':00', 'callback_data' => '/timenight ' . $start.  ' ' .($i+3) . ':00']];
            }
            cb_reply($cbid, "Ok", false, $cbmid, "🌑Selezione ora di fine", $menu);
        } elseif (count(explode(' ',$cbdata)) == 3) {
            $start  = explode(' ',$cbdata)[1];
            $end  = explode(' ',$cbdata)[2];
            $imp['night']['start'] = $start;
            $imp['night']['end'] = $end;
            $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = ?')->execute([json_encode($imp),$chatID]);
            $menu[] = array(
                array("text" => "🔙Torna indietro",
                    "callback_data" => "/night"),
            );
            cb_reply($cbid, "Ok", false, $cbmid, "Impostata modalità notte $start - $end", $menu);
        }
    }
    if (isset($cbdata)&& stripos($cbdata, '/menurules')===0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id = ?');
        $q->execute([$chatID]);
        $e = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($e['settings'],true);
        if (explode(' ',$cbdata)[1] == 'toggle') {
            $imp['rules'] ? $imp['rules'] = false : $imp['rules'] = true;
            $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1')->execute([json_encode($imp)]);
        }
        $imp['rules'] ? $act = '❌Disabilita' : $act = '✅Abilita';
        $menu[] = [['text' => $act,'callback_data' => '/menurules toggle']];
        $menu[] = array(
            array("text" => "👀Vedi Messaggio attuale",
                "callback_data" => "/regole"),
        );
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/settings"),
        );
        cb_reply($cbid, "Ok", false, $cbmid, "👮🏻 Qui puoi impostare delle regole che serviranno a indicare agli utenti come comportarsi nel gruppo.\n\n👤Se l'opzione è abilitata gli utenti potranno accedere al ragolamento grazie al comando /regole.\n\n👷🏻Per impostare un regolamento scrivi il messaggio nella chat e poi usa il comando /setrules rispondendoci",$menu);
    }
    if ($msg == '/regole') {
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id = ?');
        $q->execute([$chatID]);
        $e = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($e['settings'],true);
        if ((!isAdmin($chatID, $userID)) && (!$imp['rules'] or $cbdata)) {
            exit;
        }
        $q = $db->prepare('SELECT rules FROM groups WHERE chat_id = ?');
        $q->execute([$chatID]);
        $e = $q->fetch(PDO::FETCH_ASSOC);
        $message = json_decode($e['rules'],true);
        if ($message == "") {
            sm($chatID,'Non sono ancora state impostate delle regole, fallo con /setrules');
            exit;
        }
        sm($chatID,$message);
    }
    if ($msg == '/setrules') {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        if (!isset( $update["message"]["reply_to_message"]["text"])) {
            sm($chatID,'Rispondi al messaggio che deve essere impostato come benvenuto.');
            exit;
        } else {
            if (strlen( $update["message"]["reply_to_message"]["text"]) > 4000) {
                sm($chatID,'Il messaggio non deve superare i 4000 caratteri oppure rischia di non essere inviato!');
                exit;
            }
            $message = $update["message"]["reply_to_message"]["text"];
            if (!isOK(sm($chatID,'Regole impostate:'.PHP_EOL .$message))) {
                sm($chatID,'Sintassi HTML errata');
                exit;
            }
            $db->prepare('UPDATE groups SET rules = ? WHERE chat_id = ?')->execute([json_encode($message),$chatID]);
        }
    }
    if (isset($cbdata)&& stripos($cbdata, '/welcome')===0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id = ?');
        $q->execute([$chatID]);
        $e = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($e['settings'],true);
        if (explode(' ',$cbdata)[1] == 'toggle') {
            $imp['welcome'] ? $imp['welcome'] = false : $imp['welcome'] = true;
            $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1')->execute([json_encode($imp)]);
        }
        $imp['welcome'] ? $act = '❌Disabilita' : $act = '✅Abilita';
        $menu[] = [['text' => $act,'callback_data' => '/welcome toggle']];
        $menu[] = array(
            array("text" => "👀Vedi Messaggio attuale",
                "callback_data" => "/seewelcome"),
        );
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/settings"),
        );
        cb_reply($cbid, "Ok", false, $cbmid, "👥In questa sezione puoi impostare un messaggio che verrà inviato a ogni utente alla sua entrata.\n\n#️⃣ Puoi utilizzare i tag {NAME} {SURNAME} {ID} {USERNAME} che verranno sostituiti con le informazioni dell'utente.\n\n✏️Per impostare il messaggio di bnevenuto scrivilo prima in chat con la formattazione che preferisci e poi invia il comando /setwelcome rispondendoci.",$menu);
    }
    if ($cbdata == '/seewelcome') {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $q = $db->prepare('SELECT welcome FROM groups WHERE chat_id = ?');
        $q->execute([$chatID]);
        $e = $q->fetch(PDO::FETCH_ASSOC);
        $message = json_decode($e['welcome'],true);
        if ($message == "") {
            sm($chatID,'Non è stato ancora impostato alcun messaggio di benvenuto, fallo con /setwelcome');
            exit;
        }
        sm($chatID,$message);
    }
    if ($msg == '/setwelcome') {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        if (!isset( $update["message"]["reply_to_message"]["text"])) {
            sm($chatID,'Rispondi al messaggio che deve essere impostato come benvenuto.');
            exit;
        } else {
            if (strlen( $update["message"]["reply_to_message"]["text"]) > 4000) {
                sm($chatID,'Il messaggio non deve superare i 4000 caratteri oppure rischia di non essere inviato!');
                exit;
            }
            $message = $update["message"]["reply_to_message"]["text"];
            if (!isOK(sm($chatID,'Benvenuto impostato:'.PHP_EOL .$message))) {
                sm($chatID,'Sintassi HTML errata');
                exit;
            }
            $db->prepare('UPDATE groups SET welcome = ? WHERE chat_id = ?')->execute([json_encode($message),$chatID]);
        }
    }
    if (isset($cbdata)&& stripos($cbdata, '/setap')===0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $ex = explode(' ',$msg);
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
        $q->execute([$chatID]);
        $res = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($res['settings'],true);
        $pena2 = $imp['antiflood']['pena'];
        $pena = $ex[1];
        if ($pena == $pena2) {
            cb_reply($cbid,'Questa pena è già impostata.',true);
        } elseif ($pena == 'ban' || $pena == 'kick' || $pena == 'mute' || $pena == 'warn') {
            $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
            $q->execute([$chatID]);
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $imp = json_decode($res['settings'],true);
            $imp['antiflood']['pena'] = $pena;
            $q2 = $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1');
            $q2->execute([json_encode($imp)]);
            cb_reply($cbid,'Nuova pena impostata a ' . $pena,true);
            $cbdata = '/penantiflood';
        }
    }
    if (isset($cbdata) && $cbdata == "/penantiflood") {
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
        $imp = json_decode($res['settings'],true);
        $pena = $imp['antiflood']['pena'];
        $menu[] = array(
            array("text" => "⛔️Ban",
                "callback_data" => "/setap ban"),
            array("text" => "👞Kick",
                "callback_data" => "/setap kick"),
            array("text" => "🔇Mute",
                "callback_data" => "/setap mute"),
            array("text" => "❗️Warn",
                "callback_data" => "/setap warn"),
        );
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/menuantiflood"),
        );
        cb_reply($cbid, "Ok", false, $cbmid,"Ora imposta cosa succederà quando un utente attiverà l'antiflood.\nPena attuale: $pena",$menu);
    }
    if (isset($cbdata) && stripos($cbdata, "/menuantiflood") === 0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
        $q->execute([$chatID]);
        $res = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($res['settings'],true);
        if (explode(' ',$msg)[1] == 'on') {
            $imp['antiflood']['actived'] = 'on';
            $q2 = $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1');
            $q2->execute([json_encode($imp)]);
        } elseif (explode(' ',$msg)[1] == 'off') {
            $imp['antiflood']['actived'] = 'no';
            $q2 = $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1');
            $q2->execute([json_encode($imp)]);
        }
        $imp['antiflood']['actived'] == 'on' ? $act = '✅' : $act = '❌';
        $menu[] = [['text' => '✅Abilita','callback_data' => '/menuantiflood on'],['text' => '❌Disbilita','callback_data' => '/menuantiflood off']];
        $menu[] = array(
            array("text" => "📛Imposta pena",
                "callback_data" => "/penantiflood"),
        );
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/settings"),
        );
        cb_reply($cbid, "Ok", false, $cbmid, "L'antiflood permette di punire gli utenti che mandano troppi messaggi in un lasso di tempo\n\n🛃Stato: $act\n✉️Numero messaggi: ".$imp['antiflood']['messages']."\n🕐 Lasso di tempo: " .$imp['antiflood']['time']."s\n📛Pena: ".$imp['antiflood']['pena']."\n\nPer impostare il lasso di tempo utilizza <code>/settime secondi</code>, per impostare i messaggi massimi: <code>/setmaxmsg numero</code>",$menu);
    }
    if (isset($cbdata) && stripos($cbdata,"/setantispam")===0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        if (explode(' ',$cbdata)[1] == "link") {
            $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
            $q->execute([$chatID]);
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $imp = json_decode($res['settings'],true);
            if (explode(' ',$cbdata)[2] == "Elimina") {
                $imp['link']['eliminazione'] = ($imp['link']['eliminazione'] == true) ? false : true;
            } else {
                $imp['link']['pena'] = explode(' ',$cbdata)[2];
            }
            $q2 = $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1');
            $q2->execute([json_encode($imp)]);
        } elseif (explode(' ',$cbdata)[1] == "channel") {
            $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
            $q->execute([$chatID]);
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $imp = json_decode($res['settings'],true);
            if (explode(' ',$cbdata)[2] == "Elimina") {
                $imp['channels']['eliminazione'] = ($imp['channels']['eliminazione'] == true) ? false : true;
            } else {
                $imp['channels']['pena'] = explode(' ',$cbdata)[2];
            }            $q2 = $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1');
            $q2->execute([json_encode($imp)]);
        } elseif (explode(' ',$cbdata)[1] == "forwarded") {
            $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
            $q->execute([$chatID]);
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $imp = json_decode($res['settings'],true);
            if (explode(' ',$cbdata)[2] == "Elimina") {
                $imp['forwarded']['eliminazione'] = ($imp['forwarded']['eliminazione'] == true) ? false : true;
            } else {
                $imp['forwarded']['pena'] = explode(' ',$cbdata)[2];
            }
            $q2 = $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1');
            $q2->execute([json_encode($imp)]);
        }
        $cbdata = '/menuantispam';
    }
    if (isset($cbdata) && $cbdata == "/menuantispam") {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
        $q->execute([$chatID]);
        $res = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($res['settings'],true);
        if ($imp['link']['eliminazione']) {
            $elink = '✅';
        } else {
            $elink = '❌';
        }
        if ($imp['channels']['eliminazione']) {
            $echan = '✅';
        } else {
            $echan = '❌';
        }
        if ($imp['forwarded']['eliminazione']) {
            $efw = '✅';
        } else {
            $efw = '❌';
        }
        $menu [] = [["text" => "🔗","callback_data" => "/link"],["text" => "✅","callback_data" => "/setantispam link no"],["text" => "❗️","callback_data" => "/setantispam link Warn"],["text" => "⛔","callback_data" => "/setantispam link Ban"],["text" => "🔇","callback_data" => "/setantispam link Mute"],["text" => "👞","callback_data" => "/setantispam link Kick"],["text" => "🗑","callback_data" => "/setantispam link Elimina"]];
        $menu [] = [["text" => "📢","callback_data" => "/channel"],["text" => "✅","callback_data" => "/setantispam channel no"],["text" => "❗️","callback_data" => "/setantispam channel Warn"],["text" => "⛔","callback_data" => "/setantispam channel Ban"],["text" => "🔇","callback_data" => "/setantispam channel Mute"],["text" => "👞","callback_data" => "/setantispam channel Kick"],["text" => "🗑","callback_data" => "/setantispam channel Elimina"]];
        $menu [] = [["text" => "➡️","callback_data" => "/forwarded"],["text" => "✅","callback_data" => "/setantispam forwarded no"],["text" => "❗️","callback_data" => "/setantispam forwarded Warn"],["text" => "⛔","callback_data" => "/setantispam forwarded Ban"],["text" => "🔇","callback_data" => "/setantispam forwarded Mute"],["text" => "👞","callback_data" => "/setantispam forwarded Kick"],["text" => "🗑","callback_data" => "/setantispam forwarded Elimina"]];
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/settings"),
        );
        cb_reply($cbid, "Ok", false, $cbmid, "✉️L'antispam serve a evitare messaggi pubblicitari indesiderati da parte degli utenti.\n\n🔗Pena invio link: ".$imp['link']['pena']." 🗑: $elink\n📢Pena invio username pubblici: ".$imp['channels']['pena']."  🗑: $echan\n➡Pena inoltri da canali pubblici: ".$imp['forwarded']['pena']."  🗑: $efw\n\n ❗️= Warn,🗑 = Eliminazione,✅ = nulla,⛔ = Ban,👞 = Kick,🔇 = Muta \n\n<i>Nella tastiera sottostante puoi modificare le pene</i>",$menu);
    }
    if (isset($cbdata) && $cbdata == "/menulim") {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/settings"),
        );
        // So che i messaggi così non andrebbero messi, ma non ci capivo un cazzo e venivano brutti,ora ve li beccate così.
        cb_reply($cbid, "Ok", false, $cbmid, "🚫Ban\nPer bannare qualcuno dal tuo gruppo\n
Uso: <code>/ban utente tempo motivazione</code>
\nUtente è l'id o l'username dell'utente da bannare, <b>necessario</b> se non si risponde a un suo messaggio.\nTempo è la durata del ban,<b>opzionale,default per sempre</b>, in formato unix,oppure in formato DD/MM/YYYY\nMotivazione <b>opzionale</b>: Causa del ban.\n
Rispondendo a un messaggio dell'utente da bannare:
<code>/ban tempo motivazione</code>
\nTempo è la durata del ban,<b>opzionale,default per sempre</b>, in formato unix,oppure in formato DD/MM/YYYY\nMotivazione <b>opzionale</b>: Causa del ban.\n
✔Unban\nPer sbannare qualcuno dal gruppo\n
Uso: <code>/unban utente</code>
\n<code>/unban</code> rispondendo a un messaggio dell'utente da sbannare.\n
👟Kick\nPer cacciare qualcuno dal gruppo\n
Uso: <code>/kick utente</code>\n\n<code>/kick</code> rispondendo a un messaggio dell'utente da cacciare.\n
🔕Mute\nPer non permettere a un utente di inviare messaggi.\n
Uso: <code>/mute utente tempo motivazione</code>
\nUtente è l'id o l'username dell'utente da bannare, <b>necessario</b> se non si risponde a un suo messaggio.\nTempo è la durata del mute,<b>opzionale,default per sempre</b>, in formato unix,oppure in formato DD/MM/YYYY\nMotivazione <b>opzionale</b>: Causa del mute.\n
Rispondendo a un messaggio dell'utente da mutare:
<code>/mute tempo motivazione</code>
\nTempo è la durata del mute,<b>opzionale,default per sempre</b>, in formato unix,oppure in formato DD/MM/YYYY\nMotivazione <b>opzionale</b>: Causa del mute.\n
✔Unmute\nPer smutare qualcuno\n
Uso: <code>/unmute utente</code>
\n<code>/unmute</code> rispondendo a un messaggio dell'utente da smutare.",$menu);
    }
    if (isset($cbdata) && $cbdata == "/menuwarn") {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $menu[] = array(
            array("text" => "📛Imposta pena",
                "callback_data" => "/maxw"),
        );
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/settings"),
        );
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=?');
        $q->execute([$chatID]);
        $res = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($res['settings'],true);
        $maxw = $imp['maxwarn'];
        $pena = $imp['pena'];
        cb_reply($cbid, "Ok", false, $cbmid,"❗Warn\n\nI warn servono ad ammonire un utente in caso compia qualche azione contro le regole.
⚙Impostazioni:\n❗️Warn massimi: $maxw\n⛔️Pena: $pena
/warn per aggiungere un warn
Uso: <code>/warn utente motivazione</code>
\nUtente è l'id o l'username dell'utente da warnare, <b>necessario</b> se non si risponde a un suo messaggio.\nMotivazione <b>opzionale</b>: Causa del warn.\n
Rispondendo a un messaggio dell'utente da warnare:
<code>/warn motivazione</code>\n
✔/unwarn\nPer togliere un warn\n 
Uso: <code>/unwarn utente</code>
\n<code>/unwarn</code> rispondendo a un messaggio dell'utente da cui togliere il warn.\n
Per impostare il numero di warn massimi usa <code>/setmaxwarn n</code>", $menu);
    }

    if (isset($cbdata)&& stripos($cbdata, '/setwp')===0) {
        if (!isAdmin($chatID, $userID)) {
            if (isset($cbdata)) {
                cb_reply($cbid,"Solo gli admin possono eseguire questo comando!",true);
            } else {
                sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            }
            exit;
        }
        $ex = explode(' ',$msg);
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
        $q->execute([$chatID]);
        $res = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($res['settings'],true);
        $pena2 = $imp['pena'];
        $pena = $ex[1];
        if ($pena == $pena2) {
            cb_reply($cbid,'Questa pena è già impostata.',true);
        } elseif ($pena == 'ban' || $pena == 'kick' || $pena == 'mute') {
            $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
            $q->execute([$chatID]);
            $res = $q->fetch(PDO::FETCH_ASSOC);
            $imp = json_decode($res['settings'],true);
            $imp['pena'] = $pena;
            $q2 = $db->prepare('UPDATE groups SET settings = ? WHERE chat_id = '. $chatID . ' LIMIT 1');
            $q2->execute([json_encode($imp)]);
            cb_reply($cbid,'Nuova pena impostata a ' . $pena,true);
            $cbdata = '/maxw';
        }
    }
    if (isset($cbdata) && $cbdata == "/maxw") {
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
        $imp = json_decode($res['settings'],true);
        $pena = $imp['pena'];
        $menu[] = array(
            array("text" => "⛔️Ban",
                "callback_data" => "/setwp ban"),
            array("text" => "👞Kick",
                "callback_data" => "/setwp kick"),
            array("text" => "🔇Mute",
                "callback_data" => "/setwp mute"),
        );
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/menuwarn"),
        );
        cb_reply($cbid, "Ok", false, $cbmid,"Ora imposta cosa succederà quando un utente raggiungerà il numero di warn massimi.\nPena attuale: $pena",$menu);
    }
    //code
    if (isset($update['message']) || isset($cbdata)) {
        include('plugins/ban.php');
        include('plugins/warn.php');
        $q = $db->prepare('SELECT settings FROM groups WHERE chat_id=? LIMIT 1');
        $q->execute([$chatID]);
        $res = $q->fetch(PDO::FETCH_ASSOC);
        $imp = json_decode($res['settings'],true);
        if ($imp['channels']['pena'] != "no" || $imp['link']['pena'] != "no" || $imp['channels']['eliminazione'] || $imp['link']['eliminazione'] || $imp['forwarded']['pena'] != "no" || $imp['forwarded']['pena'] != "no") {
            include('plugins/antispam.php');
        }
            include('plugins/antiflood.php');
    }
}
if ($config['show_update']) {
    sm($chatID, json_encode(json_decode($content), JSON_PRETTY_PRINT));
}