<?php

use Utils\Database\XDb;
//prepare the templates and include all neccessary
require_once('./lib/common.inc.php');

$message = false;
$allOk = true;
$from_username = "";
$from_email = "";
$to_username = "";
$to_email = "";

//Preprocessing
if ($error == false) {
    ///user logged in?
    if ($usr == false) {
        $target = urlencode(tpl_get_current_page());
        tpl_redirect('login.php?target=' . $target);
    } else {
        $tplname = 'mailto';
        require_once($stylepath . '/mailto.inc.php');

        $userid = isset($_REQUEST['userid']) ? $_REQUEST['userid'] : 0;
        if (!$userid)
            $message = $message_user_not_found;

        $send_emailaddress = isset($_REQUEST['send_emailaddress']) ? $_REQUEST['send_emailaddress'] : 0;

        // get sender data
        $resp = XDb::xSql(
            "SELECT `username`, `email` FROM `user` WHERE `user_id`= ? LIMIT 1", $usr['userid']);

        if ($row = XDb::xFetchArray($resp)) {
            $from_username = $row['username'];
            $from_email = $row['email'];
        } else {
            $message = $message_title_internal;
        }

        // get reciver data
        if (!$message) {
            $resp = XDb::xSql(
                "SELECT `username`, `email` FROM `user` WHERE `user_id`= ? ", $userid);

            if ($row = XDb::xFetchArray($resp)) {
                $to_username = $row['username'];
                $to_email = $row['email'];
            } else
                $message = $message_title_internal;
        }

        // got messsage via url to display?
        $message = isset($_REQUEST['message']) ? $_REQUEST['message'] : '';
        $subject = "";
        $text = "";

        if (!$message) {
            if (isset($_POST['submit'])) {
                // store
                $subject = isset($_REQUEST['subject']) ? stripslashes($_REQUEST['subject']) : '';
                $text = isset($_REQUEST['text']) ? stripslashes($_REQUEST['text']) : '';

                $allOk = true;
                if ($subject <= "") {
                    $message_errnosubject = $errnosubject;
                    $allOk = false;
                }
                if ($text <= "") {
                    $message_errnotext = $errnotext;
                    $allOk = false;
                }

                if ($allOk) {
                    $subject = mb_ereg_replace('{subject}', $subject, $mailsubject);
                    $subject = mb_ereg_replace('{from_username}', $from_username, $subject);

                    $text = mb_ereg_replace('{{text}}', $text, $send_emailaddress == 1 ? $mailtext_email : $mailtext_anonymous);
                    $text = mb_ereg_replace('{from_userid}', $usr["userid"], $text);
                    $text = mb_ereg_replace('{from_email}', $from_email, $text);
                    $text = mb_ereg_replace('{from_username}', $from_username, $text);
                    $text = mb_ereg_replace('{to_email}', $to_email, $text);
                    $text = mb_ereg_replace('{to_username}', $to_username, $text);

                    XDb::xSql("INSERT INTO `email_user`
                              SET `ipaddress`=? , `date_generated`=NOW(), `date_sent`='0',
                                  `from_user_id`= ? , `from_email`=?, `to_user_id`=?,
                                  `to_email`=?, `mail_subject`=?, `mail_text`=?, `send_emailaddress`=?",
                              $_SERVER["REMOTE_ADDR"], $usr['userid'], $from_email, $userid,
                              $to_email, $subject, $text, $send_emailaddress);

                    tpl_redirect('mailto.php?userid=' . urlencode($userid) . '&message=' . urlencode($message_sent));
                }
            }
        }

        // display
        tpl_set_var('userid', htmlspecialchars($userid, ENT_COMPAT, 'UTF-8'));
        tpl_set_var('to_username', htmlspecialchars($to_username, ENT_COMPAT, 'UTF-8'));

        if ($message) {
            tpl_set_var('message_start', '');
            tpl_set_var('message_end', '');
            tpl_set_var('message', strip_tags($message));
            tpl_set_var('formular_start', '<!--');
            tpl_set_var('formular_end', '-->');
        } else {
            tpl_set_var('message_start', '<!--');
            tpl_set_var('message_end', '-->');
            tpl_set_var('message', strip_tags($message));
            tpl_set_var('formular_start', '');
            tpl_set_var('formular_end', '');
            tpl_set_var('subject', htmlspecialchars($subject, ENT_COMPAT, 'UTF-8'));
            tpl_set_var('errnosubject', isset($message_errnosubject) ? $message_errnosubject : '');
            tpl_set_var('errnotext', isset($$message_errnotext) ? $message_errnotext : '');
            tpl_set_var('text', htmlspecialchars($text, ENT_COMPAT, 'UTF-8'));
            tpl_set_var('send_emailaddress_sel', $send_emailaddress == 1 ? "checked" : "");
        }
    }
}

//make the template and send it out
tpl_BuildTemplate();
