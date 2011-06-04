<?php
// This file is part of ComfyPage - http://comfypage.com
//
// ComfyPage is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ComfyPage is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ComfyPage.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/contentServer/content_page.php');

$errorMsg = Globals::self_install_checks();
if($errorMsg)
{
    echo('<html><body style="text-align:center;">'.Message::get_error_display($errorMsg).'<p><a href="http://comfypage.com">Click here to return to ComfyPage.com</a></p></body></html>');
    exit();
}
else
{
    if( !Load::general_settings(DONE_WIZARD) ) {
        Globals::redirect('wizard.php');
    }
    if(Login::logged_in(false))
    {
        //so site owner can see their latest changes
        Globals::dont_cache();
    }
    $counter = Load::counter_settings();
    $counter->page_viewed();
    $page = 'No content was identified';
    $msg = null;
    //check for form submission
    $content_id = Globals::get_param(CONTENT_ID_URL_PARAM, $_POST);
    if($content_id)
    {
        require_once('common/lib/form_spam_blocker/fsbb.php');
        if(check_hidden_tags($_POST))
        {
            $site_id = Load::general_settings(NEW_SITE_ID);
            $subject = 'form submitted on '.$site_id;
            $message = 'A form was submitted at '.$site_id.'/index.php?'.CONTENT_ID_URL_PARAM.'='.$content_id."\r\n\r\n";

            $data = $_POST;
            unset($data['cpt']);
            unset($data[CONTENT_ID_URL_PARAM]);

            //remove the spam blocker fields so they're not sent to the site owner
            $blocker = new formSpamBotBlocker();
            unset($data[$blocker->keyName]);//63.9.7.23.44.22.353.10.31.25.329.7

            $blocker->getCodeID($_POST[$blocker->keyName]);

            $blocker->checkUserID($_POST);
            unset($data[$blocker->userIDName]);//712d8a0eaed7758a5be0038

            $blocker->checkDynID($_POST);
            unset($data[$blocker->dynIDName]);//1d37bf8

            $got_value = false;
            foreach ($data as $var => $value)
            {
                if($value!=null)
                {
                    $got_value = true;
                }
                $message .= "$var:   $value\r\n\r\n";
            }

            if( !$got_value )
            {
                $errorMsg = 'Form empty';
            }
            else if( empty($errorMsg) )
            {
                Globals::send_email(Load::general_settings(ADMIN_EMAIL), 'noreply@comfypage.com', $subject, $message);
                $msg = 'Your message has been sent';
            }
        }
    }

    $content_id = Globals::get_param(CONTENT_ID_URL_PARAM, $_GET, INDEX);
    if($content_id != null)
    {
        $hide_menu = Globals::get_param('hide_menu', $_GET, false);

        $m = Load::member_settings();
        $m->members_only_check_for_pages($content_id);
        $store = Load::page_store();
        if($store->store_item_exists($content_id) == false)
        {
            $content_id = ERROR_CONTENT;
            //track_user('Requested non existent page');
        }
        $page = get_content_page($content_id, null, $hide_menu, $msg.$errorMsg);
        echo($page);
        exit();
    }
}
?>
