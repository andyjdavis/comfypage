<?php
require_once('common/utils/Globals.php');
require_once('common/contentServer/wizard/wizard_common.php');

Globals::dont_cache();

if(Login::logged_in(true))
{
}

$error = $page = null;

if($_GET)
{
    $page = Globals::get_param('page', $_GET);
    $saveData = Globals::get_param('saveData', $_GET);

    if(!empty($saveData))
    {
	$your_name = Globals::get_param('your_name', $_GET);

	$headerHtml = '<div align=center><h1>'.$your_name.'</h1></div>';
	$header = Load::page(HEADER);
	$header->set(RAW_CONTENT, $headerHtml);

	$footerHtml = '';
	$footer = Load::page(FOOTER);
	$footer->set(RAW_CONTENT, $footerHtml);

	if(empty($error) == true)
	{
		Globals::redirect('wizard.php?page=' . ($page+1));
	}
    }
    else
    {
	//theyve just come to this page
	if(empty($page)) {
		$page = 1;
	}
    }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>Wizard page <?php echo($page); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
        <?php
                echo(Message::get_language_JS_block());
        ?>
        <script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
    </head>

    <body>
        <table class="admin_table" cellspacing="20px;">
            <tr><th colspan="2" class="admin_section"><span id=t class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(STEP_COUNT); ?></span></th></tr>
            <tr>
                <td align="center">
                    <?php
                    echo(Message::get_error_display($error));
                    ?>
                    <form method=get>
                        <input type=hidden name=page id=page value=<?php echo($page); ?>>
                        <input type=hidden name=saveData id=saveData value=1>

                        <p><span id="n" class=translate_me>What would you like your site's title to be?</span>&nbsp;&nbsp;<input type=text size=40 name=your_name></p>
                        <p>This will be in your site's header.</p>
                        <p>To change it later go to the Site Manager and click on Borders.</p>
                        <p><span id="s" class=translate_me><input type=submit value=' Next '></span></p>
                    </form>
                </td>
            </tr>
        </table>

        <?php echo(Globals::get_affinity_footer(false)); ?>
    </body>
</html>
