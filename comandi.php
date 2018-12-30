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
    if ($cbdata == "list") {
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/start"),
        );
        cb_reply($cbid, "Ok", false, $cbmid,"Funzioni del bot attuali: /ban,/unban,/mute,/unmute" , $menu);
    }
}
if ($chatID < 0) {
    if (isset($update['message']['new_chat_member'])) {
        if ($update['message']['new_chat_member']['username'] == $userbot) {
            sm($chatID, "Grazie per avermi aggiunto! Fammi admin per permettermi di funzionare correttamente!");
        }

    }
    // Menu
    if (stripos($msg, "/settings") === 0) {
        if (!isAdmin($chatID, $userID)) {
            sm($chatID, "Solo gli admin possono eseguire questo comando!", false, false, false, $msgid);
            exit;
        }
        $menu[] = array(
            array("text" => "⛔Limitazioni utente",
                "callback_data" => "/menulim"),
        );
        if (isset($cbdata)) {
            cb_reply($cbid, "Ok", false, $cbmid, 'Ecco la lista delle impostazioni', $menu);
        } else {
            sm($chatID, 'Ecco la lista delle impostazioni', $menu);
        }
    }
    if ($cbdata == "/menulim") {
        $menu[] = array(
            array("text" => "🔙Torna indietro",
                "callback_data" => "/settings"),
        );
        cb_reply($cbid, "Ok", false, $cbmid, '🚫 /ban' . PHP_EOL . 'Bandisci un utente dal gruppo' . PHP_EOL . "Formati:" . PHP_EOL . "<code>/ban @username tempo motivazione</code>\n<code>/ban @username motivazione</code>\n<code>/ban tempo motivazione</code>\nRispondendo all'utente da bannare:\n<code>/ban tempo moticazione</code>\n<code>/ban tempo</code>\n<code>/ban motivazione</code>. \nIl tempo deve essere in formato GG/MM/YYYY, al posto dell'username si può utilizzare anche l'id dell'utente.\n✔/unban\nPer sbannare un utente puoi usare:\n<code>/unabn</code> rispondendo a un suo messaggio\n<code>/unban @username</code>\n<code>/unban userID</code>\n💬/mute\nPer evitare che un utente possa inviare messaggi\n<code>/mute @username tempo motivazione</code>\n<code>/mute @username motivazione</code>\n<code>/mute tempo motivazione</code>\nRispondendo all'utente da bannare:\n<code>/mute tempo moticazione</code>\n<code>/mute tempo</code>\n<code>/mute motivazione</code>. \nIl tempo deve essere in formato GG/MM/YYYY, al posto dell'username si può utilizzare anche l'id dell'utente.\n✔/unmute\nPer sbannare un utente puoi usare:\n<code>/unmute</code> rispondendo a un suo messaggio\n<code>/unmute @username</code>\n<code>/unmute userID</code>", $menu);
    }

    //code
    if (isset($msg) || isset($cbdata)) {
        include('plugins/ban.php');
    }
}
if ($config['show_update']) {
    sm($chatID, json_encode(json_decode($content), JSON_PRETTY_PRINT));
}
