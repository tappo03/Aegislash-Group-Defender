<?php
echo "<br />Funzioni";


function sr($method, $args)
{
    global $token;
    $args    = http_build_query($args);
    $request = curl_init("https://api.telegram.org/$token/$method");
    curl_setopt_array($request, array(
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_USERAGENT => 'cURL request',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $args
    ));
    $result = curl_exec($request);
    curl_close($request);
    return $result;
}
function entitytohtml($msg,$entities) {
    $added = 0;
    $msg = htmlspecialchars($msg);
    foreach($entities as $entity) {
        if ($entity['type'] == "bold") {
            $msg = substr_replace($msg, '<b>', $added+$entity['offset'], 0);
            $msg = substr_replace($msg, '</b>', $added+$entity['offset']+3+$entity['length'], 0);
            $added += 7;
        }
        if ($entity['type'] == "italic") {
            $msg = substr_replace($msg, '<i>', $added+$entity['offset'], 0);
            $msg = substr_replace($msg, '</i>', $added+$entity['offset']+3+$entity['length'], 0);
            $added += 7;
        }
        if ($entity['type'] == "code" || $entity['type'] == "pre") {
            $msg = substr_replace($msg, '<code>', $added+$entity['offset'], 0);
            $msg = substr_replace($msg, '</code>', $added+$entity['offset']+6+$entity['length'], 0);
            $added += 13;
        }
        if ($entity['type'] == "text_link") {
            $ins = "<a href='$entity[url]'>";
            $msg = substr_replace($msg, $ins, $added+$entity['offset'], 0);
            $msg = substr_replace($msg, '</a>', $added+$entity['offset']+strlen($ins)+$entity['length'], 0);
            $added += strlen($ins)+4;
        }
    }

    return $msg;
}
function action($chatID, $action)
{
    $args = array(
        "chat_id" => $chatID,
        "action" => $action
    );
    return sr("sendChatAction", $args);
}
function sm($chatID, $msg, $menu = false, $keyboardtype = false, $parse_mode = false, $reply_to_message = false, $disablewebpreview = false) {
    global $config;
    if (!$keyboardtype && $menu) {
        $keyboardtype = $config['tastiera'];
    }
    if ($keyboardtype == "reply") {
        $rm = array(
            'keyboard' => $menu,
            'resize_keyboard' => true
        );
    } elseif ($keyboardtype == "inline") {
        $rm = array(
            'inline_keyboard' => $menu
        );
    } elseif ($keyboardtype == "nascondi") {
        $rm = array(
            'hide_keyboard' => true
        );
    }
    $rm = json_encode($rm);
    
    if (!$parse_mode) {
        $parse_mode = $config['parse_mode'];
    }
    if (!$disablewebpreview) {
        $disablewebpreview = $config['disabilitapreview'];
    }
    $args = array(
        "chat_id" => $chatID,
        "text" => $msg,
        "parse_mode" => $parse_mode,
        "reply_to_message_id" => $reply_to_message,
        "disable_web_page_preview" => $disablewebpreview,
    );
    if ($menu)
        $args['reply_markup'] = $rm;
    if ($config['action']) {
        action($chatID, "typing");
    }
    return sr("sendMessage", $args);
}
function sendMessage()
{
    return call_user_func_array("sm", func_get_args());
}
function em($chatID, $msg, $msgid, $menu = false, $keyboardtype = false, $parse_mode = false, $reply_to_message = false, $disablewebpreview = false)
{
    global $config;
    if (!$keyboardtype && $menu) {
        $keyboardtype = $config['tastiera'];
    }
    if ($keyboardtype == "reply") {
        $rm = array(
            'keyboard' => $menu,
            'resize_keyboard' => true
        );
    } elseif ($keyboardtype == "inline") {
        $rm = array(
            'inline_keyboard' => $menu
        );
    } elseif ($keyboardtype == "nascondi") {
        $rm = array(
            'hide_keyboard' => true
        );
    }
    $rm = json_encode($rm);
    
    if (!$parse_mode) {
        $parse_mode = $config['parse_mode'];
    }
    if (!$disablewebpreview) {
        $disablewebpreview = $config['disabilitapreview'];
    }
    $args = array(
        "chat_id" => $chatID,
        "text" => $msg,
        "parse_mode" => $parse_mode,
        "reply_to_message_id" => $reply_to_message,
        "disable_web_page_preview" => $disablewebpreview,
        "message_id" => $msgid
    );
    return sr("editMessageText", $args);
}
function cb_reply($id, $text, $alert = false, $cbmid = false, $ntext = false, $nmenu = false, $npm = "pred")
{
    global $chatID;
    global $config;
    if ($npm == 'pred')
        $npm = $config['parse_mode'];
    if ($cbmid) {
        if ($nmenu) {
            $rm = array(
                'inline_keyboard' => $nmenu
            );
            $rm = json_encode($rm);
        }
        $args = array(
            'chat_id' => $chatID,
            'message_id' => $cbmid,
            'text' => $ntext,
            'parse_mode' => $npm
        );
        if ($nmenu)
            $args["reply_markup"] = $rm;
        $r = sr("editMessageText", $args);
    }
    $args = array(
        'callback_query_id' => $id,
        'text' => $text,
        'show_alert' => $alert
    );
    $r    = sr("answerCallbackQuery", $args);
    return $r;
}
function editMessageText()
{
    return call_user_func_array("em", func_get_args());
}
//ForwardMessage
function fw($chatID, $from, $msgid) {
    $args = array(
        "chat_id" => $chatID,
        "from_chat_id" => $from,
        "message_id" => $msgid
    );
    return sr("forwardMessage", $args);
}
function forwardMessage()
{
    return call_user_func_array("fw", func_get_args());
}
//sendPhoto
function si($chatID, $image, $caption = "", $menu = false, $keyboardtype = false, $parse_mode = false, $reply_to_message = false)
{
    global $config;
    if (!$keyboardtype && $menu) {
        $keyboardtype = $config['tastiera'];
    }
    if ($keyboardtype == "reply") {
        $rm = array(
            'keyboard' => $menu,
            'resize_keyboard' => true
        );
    } elseif ($keyboardtype == "inline") {
        $rm = array(
            'inline_keyboard' => $menu
        );
    } elseif ($keyboardtype == "nascondi") {
        $rm = array(
            'hide_keyboard' => true
        );
    }
    $rm = json_encode($rm);
    if (!$parse_mode) {
        $parse_mode = $config['parse_mode'];
    }
    $args = array(
        "chat_id" => $chatID,
        "photo" => $image,
        "parse_mode" => $parse_mode,
        "reply_to_message_id" => $reply_to_message,
        "caption" => $caption
    );
    if ($menu)
        $args['reply_markup'] = $rm;
    if ($config['action']) {
        action($chatID, "upload_photo");
    }
    return sr("sendPhoto", $args);
}
function sendPhoto()
{
    return call_user_func_array("si", func_get_args());
}
//deleteMessage
function dm($chatID, $msgid)
{
    global $token;
    $args = array(
        "chat_id" => $chatID,
        "message_id" => $msgid
    );
    return sr("deleteMessage", $args);
}
function deleteMessage()
{
    return call_user_func_array("dm", func_get_args());
}

//GRUPPI
function deleteChatPhoto($chatID)
{
    global $token;
    $args = array(
        "chat_id" => $chatID
    );
    return sr("deleteChatPhoto", $args);
}
function setChatPhoto($chatID, $photo)
{
    global $token;
    $args = array(
        "chat_id" => $chatID,
        "photo" => $photo
    );
    return sr("setChatPhoto", $args);
}
function ban($chatID, $userID, $time = 0)
{
    global $api;
    $args = array(
        'chat_id' => $chatID,
        'user_id' => $userID,
        'until_date' => $time
    );
    return sr("kickChatMember", $args);
}
function isOk ($up) {
    $j = json_decode($up,true);
    return $j[ok];
}
function kickChatMember()
{
    return call_user_func_array("ban", func_get_args());
}
function unban($chatID, $userID)
{
    global $api;
    $args = array(
        'chat_id' => $chatID,
        'user_id' => $userID
    );
    return sr("unbanChatMember", $args);
}
function unbanChatMember()
{
    return call_user_func_array("unban", func_get_args());
}
//fissa
function fissa($chatID, $msgid)
{
    global $api;
    $args = array(
        'chat_id' => $chatID,
        'message_id' => $msgid
    );
    return sr("pinChatMessage", $args);
}
function pinChatMessage()
{
    return call_user_func_array("fissa", func_get_args());
}
function unpinChatMessage($chatID)
{
    global $token;
    $args = array(
        "chat_id" => $chatID
    );
    return sr("unpinChatMessage", $args);
}
function limita($chatID, $userID, $dateRelase=0, $sendMsg=false, $sendMedia=false, $sendOther=false, $WPPreview=false)
{
    global $token;
    $args = array(
        "chat_id" => $chatID,
        "user_id" => $userID,
        "until_date" => $dateRelase,
        "can_send_messages" => $sendMsg,
        "can_send_media_messages" => $sendMedia,
        "can_send_other_messages" => $sendOther,
        "can_add_web_page_previews" => $WPPreview
    );
    return sr("restrictChatMember", $args);
}
function restrictChatMember()
{
    return call_user_func_array("limita", func_get_args());
}
function promoteChatMember($chatID, $userID, $changeInfo, $postMsg, $modifyMsg, $deleteMsg, $inviteUsers, $restrictUsers, $pinMsg, $promoteUsers)
{
    global $token;
    $args = array(
        "chat_id" => $chatID,
        "user_id" => $userID,
        "can_change_info" => $changeInfo,
        "can_post_messages" => $postMsg,
        "can_edit_messages" => $modifyMsg,
        "can_delete_messages" => $deleteMsg,
        "can_invite_users" => $inviteUsers,
        "can_restrict_members" => $restrictUsers,
        "can_pin_messages" => $pinMsg,
        "can_promote_members" => $promoteUsers
    );
    return sr("promoteChatMember", $args);
}
function warn ($chatID,$userID) {
    global $db;
    $warns = $db->prepare('SELECT warns FROM groups WHERE chat_id = ? LIMIT 1');
    $warns->execute([$chatID]);
    $res = $warns->fetch(PDO::FETCH_ASSOC);
    $warnlist = json_decode($res['warns'],true);
    if (!isset($warnlist[$userID])) {
        $warnlist[$userID] = 0;
    }
    $warnlist[$userID]++;
    $warnj = json_encode($warnlist);
    $q = $db->prepare('UPDATE groups SET warns = ? WHERE chat_id = '.$chatID);
    $q ->execute([$warnj]);
    return $warnlist[$userID];
}
function setwarn ($chatID,$userID,$nw) {
    global $db;
    $warns = $db->prepare('SELECT warns FROM groups WHERE chat_id = ? LIMIT 1');
    $warns->execute([$chatID]);
    $res = $warns->fetch(PDO::FETCH_ASSOC);
    $warnlist = json_decode($res['warns'],true);
    $warnlist[$userID] = $nw;
    $warnj = json_encode($warnlist);
    $q = $db->prepare('UPDATE groups SET warns = ? WHERE chat_id = '.$chatID);
    $q ->execute([$warnj]);
    return $warnlist[$userID];
}
function unwarn ($chatID,$userID) {
    global $db;
    $warns = $db->prepare('SELECT warns FROM groups WHERE chat_id = ? LIMIT 1');
    $warns->execute([$chatID]);
    $res = $warns->fetch(PDO::FETCH_ASSOC);
    $warnlist = json_decode($res['warns'],true);
    if (!isset($warnlist[$userID])) {
        $warnlist[$userID] = 0;
    }
    if ($warnlist[$userID] <= 0) {
        return false;
    }
        $warnlist[$userID]--;
    $warnj = json_encode($warnlist);
    $q = $db->prepare('UPDATE groups SET warns = ? WHERE chat_id = '.$chatID);
    $q ->execute([$warnj]);
    return $warnlist[$userID];
}
function getlink($chatID)
{
    global $token;
    $args = array(
        "chat_id" => $chatID
    );
    $j    = json_decode(sr("exportChatInviteLink", $args), true);
    return $j["result"];
}
function exportChatInviteLink()
{
    return call_user_func_array("getlink", func_get_args());
}
function getChatMembersCount($chatID)
{
    global $token;
    $args = array(
        "chat_id" => $chatID
    );
    $j    = json_decode(sr("getChatMembersCount", $args), true);
    return $j["result"];
}
function setChatTitle($chatID, $title)
{
    $args = array(
        "chat_id" => $chatID,
        "title" => $title
    );
    return sr("setChatTitle", $args);
}
function setChatDescription($chatID, $description)
{
    $args = array(
        "chat_id" => $chatID,
        "title" => $description
    );
    return sr("setChatDescription", $args);
}
function leaveChat($chatID)
{
    $args = array(
        "chat_id" => $chatID
    );
    return sr("leaveChat", $args);
}
function getChatMember($chatID, $userID)
{
    $args = array(
        "chat_id" => $chatID,
        "user_id" => $userID
    );
    return sr("getChatMember", $args);
}
function isAdmin($chatID, $userID)
{
    $args = array(
        'chat_id' => $chatID
    );
    $add = sr("getChatAdministrators", $args);
    $admins = json_decode($add, true);
    foreach ($admins['result'] as $adminsa) {
        if ($adminsa['user']['id'] == $userID)
            return true;
    }
}
    if ($config['db']) {
        function id($username)
        {
            global $userbot;
            global $db;
            $username = str_replace("@", "", $username);
            $q        = $db->prepare("select * from `users` WHERE username LIKE ?");
            $q->execute([$username]);
            $u        = $q->fetch(PDO::FETCH_ASSOC);
            return $u['chat_id'];
        }
        function username($id)
        {
            global $db;
            $q = $db->prepare("select * from `users` where chat_id = ?");
            $q->execute([$id]);
            $u = $q->fetch(PDO::FETCH_ASSOC);
            return $u['username'];
        }
    }
