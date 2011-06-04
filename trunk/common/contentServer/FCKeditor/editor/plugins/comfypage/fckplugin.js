//FCKCommands.RegisterCommand( 'ComfyPagePoll' , new FCKDialogCommand( FCKLang['DlgMyFindTitle'] , FCKLang['DlgMyFindTitle'] , FCKConfig.PluginsPath + 'comfypage/poll.html' , 340, 170 ) ) ;
var title = "Poll";
FCKCommands.RegisterCommand( 'ComfyPagePoll' , 
new FCKDialogCommand( title , title , FCKConfig.PluginsPath + 'comfypage/fck_poll.html' , 600, 400 ) ) ;

var oPollItem = new FCKToolbarButton( 'ComfyPagePoll', FCKLang['DlgMyFindTitle'] ) ;
oPollItem.IconPath = FCKConfig.PluginsPath + 'comfypage/poll_button.gif' ;

FCKToolbarItems.RegisterItem( 'ComfyPagePoll', oPollItem ) ; // 'ComfyPagePoll' is the name used in the Toolbar config.
