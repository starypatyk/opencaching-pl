<?php

require_once("./lib/common.inc.php");

if (isset($_SESSION['user_id'])) {

    if (isSet($_GET['id']) && !empty($_GET['id']) && preg_match("/^\d+$/", $_GET['id'])) {

        db_connect();

        $id = mysql_real_escape_string($_GET['id']);

        $query = "select user_id,deleted,cache_id,type from cache_logs where id = '" . $id . "'";
        $wynik = db_query($query);
        $wiersz = mysql_fetch_assoc($wynik);

        $user_id2 = $wiersz['user_id'];

        if (empty($user_id2))
            $tpl->assign("error", "1");
        elseif ($user_id2 != $_SESSION['user_id'])
            $tpl->assign("error", "2");
        elseif ($wiersz['deleted'] == '1')
            $tpl->assign("error", "1");
        elseif (isSet($_POST['confirm']) && $_POST['confirm'] == "true") {

            $cahce_id = $wiersz['cache_id'];
            $user_id = $wiersz['user_id'];
            $type = $wiersz['type'];

            $query = "update cache_logs set deleted=1 where id=" . $id;
            db_query($query);

            switch ($type) {
                case 1:
                    $query = "update user set founds_count=founds_count-1 where user_id = " . $_SESSION['user_id'];
                    db_query($query);
                    $query = "update caches set founds=founds-1 where cache_id = " . $cahce_id;
                    db_query($query);

                    $query = "SELECT 1 FROM `cache_rating` where user_id=" . $_SESSION['user_id'] . " and cache_id=" . $cahce_id;
                    $wynik = db_query($query);
                    $czy_toprating = mysql_fetch_row($wynik);

                    if ($czy_toprating[0] == 1) {

                        $query = "delete from `cache_rating` where user_id=" . $_SESSION['user_id'] . " and cache_id=" . $cahce_id;
                        db_query($query);

                        // Notify OKAPI's replicate module of the change.
                        // Details: https://github.com/opencaching/okapi/issues/265
                        require_once($rootpath . 'okapi/facade.php');
                        \okapi\Facade::schedule_user_entries_check($cahce_id, $_SESSION['user_id']);
                        \okapi\Facade::disable_error_handling();

                        // don't use this query! This update is generated by trigger "cacheRatingAfterDelete" on MySQL
                        // ==== Limak 10.02.2012 ===
                        //$query="update caches set topratings=topratings-1 where cache_id=".$cahce_id;
                        //db_query($query);
                    }

                    $query = "SELECT 1 FROM `scores` where user_id=" . $_SESSION['user_id'] . " and cache_id=" . $cahce_id;
                    $wynik = db_query($query);
                    $czy_scores = mysql_fetch_row($wynik);

                    if ($czy_scores[0] == 1) {

                        $query = "delete from `scores` where user_id=" . $_SESSION['user_id'] . " and cache_id=" . $cahce_id;
                        db_query($query);
                        $query = "update caches set votes=votes-1 ,score=(SELECT round( avg( score ) , 1 ) FROM scores WHERE cache_id = " . $cahce_id . ") where cache_id = " . $cahce_id;

                        db_query($query);
                    }

                    break;
                case 2:
                    $query = "update user set notfounds_count=notfounds_count-1 where user_id = " . $_SESSION['user_id'];
                    $query = "update caches set notfounds=notfounds-1 where cache_id = " . $cahce_id;
                    db_query($query);
                    break;
                case 3:
                    $query = "update user set log_notes_count=log_notes_count-1 where user_id = " . $_SESSION['user_id'];
                    $query = "update caches set notes=notes-1 where cache_id = " . $cahce_id;
                    db_query($query);
                    break;
            }

            $query = "select wp_oc from caches where cache_id=" . $cahce_id;
            $wynik = db_query($query);
            $wiersz = mysql_fetch_assoc($wynik);
            $wp_oc = $wiersz['wp_oc'];

            header('Location: ./viewcache.php?wp=' . $wp_oc);
            exit;
        }
    }
} else
    header('Location: ./index.php');

$tpl->display('tpl/removelog.tpl');
?>