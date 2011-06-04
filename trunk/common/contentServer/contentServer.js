//added this to protect us from UserCash
var toploc = top.location.href;
if(toploc != self.location && toploc.indexOf('template.php')==-1)
{
	top.location = self.location;
}

function toggle_visibility(id)
{
   var e = document.getElementById(id);
   if( !e.style.display || e.style.display == 'block' )
	  e.style.display = 'none';
   else
	  e.style.display = 'block';
}

function changePageTitle(contentId)
{
  var newTitle = getPageTitleFromUser(document.title);
  if(newTitle!=null)
  {
    self.location = "edit.php?content_id=" + contentId + "&content_title=" + newTitle;
    return true;
  }
  else
  {
    return false;
  }
}

function getPageTitleFromUser()
{
  return getPageTitleFromUser(null);
}

function getPageTitleFromUser(oldTitle)
{
  var userInput = null;
	var defaultString = "type your page title here";
	
	if(oldTitle != null && oldTitle.length > 0)
	{
	 defaultString = oldTitle;
  	}

	 do
	 {
	   userInput = prompt("Enter a title\nIt can't be blank", defaultString);
    }
	while(userInput != null && userInput.length < 1)

    return userInput;
}

function getProductNameFromUser(oldTitle)
{
  var userInput = null;
	var defaultString = "type your product name here";

	if(oldTitle != null && oldTitle.length > 0)
	{
	 defaultString = oldTitle;
  	}

	 do
	 {
	   userInput = prompt("Enter a product name\nIt can't be blank", defaultString);
    }
	while(userInput != null && userInput.length < 1)

    return userInput;
}

function getElementsByStyleClass (className)
{
	var all = document.all ? document.all : document.getElementsByTagName('*');
	var elements = new Array();
	for (var e = 0; e < all.length; e++)
	{
		if (all[e].className == className)
		{
			elements[elements.length] = all[e];
		}
	}
	return elements;
}

var transSections = 0;
var transBlurred = false;

function translate_initialize()
{
	if(typeof(theLanguage) == "undefined" || theLanguage==null || theLanguage==undefined)
	{
		alert('Site Config Error: Missing translations.js');
		return;
	}
	
	//translations array is optional
	if(typeof(translations) == "undefined")
	{
		translations=new Array();
	}
	
	var sections = getElementsByStyleClass("translate_me");
	var done_functions = new Array();
	
	var i=0;
	for(i=0; i<sections.length; i++)
	{
		var original = sections[i].innerHTML;

		if(original!=null && original.length>0)
		{
			if(translations[original]==null)
			{
				transSections++;
				if(!transBlurred)
				{
					transBlurred = true;
					//alert("blur screen while we translate");
				}

				var functionString = "\
done_functions["+i+"] =function translation_done_"+sections[i].id+"(result)\
{\
if (!result.error)\
{\
var container = document.getElementById('"+sections[i].id+"');\
var missing = document.getElementById('missing_words');\
var missing_term = 'translations[\"'+container.innerHTML+'\" ]=\"'+result.translation+'\";<br />';\
if(missing!=null) missing.innerText += missing_term;\
translations[container.innerHTML] = result.translation;\
container.innerHTML = result.translation;\
}\
else\
{\
if(result.message!=null && result.message!=undefined){alert(result.message);}\
}\
transSections--;\
if(transSections<1){}\
};";
				eval(functionString);
				if(original.length>500)
				{
					alert("text is too long\n" + original);
				}
				else
				{
					google.language.translate(original, "en", theLanguage, done_functions[i]);
				}
			}
			else
			{
				sections[i].innerHTML = translations[original];
			}
		}
	}
	
	var addonSelectors = getElementsByStyleClass("addonSelect");
	var selector = null;
	var j=0;
	var newText = null;
	for(i=0; i<addonSelectors.length; i++)
	{
		if(addonSelectors[i].childNodes.length!=1)
		{
			//alert("add-on select html is malformed");
		}
		else
		{
			selector = addonSelectors[i].childNodes[0];
			if(selector!=null)
			{
				for(j=0; j < selector.options.length; j++)
				{
					newText = translations[selector.options[j].text];
					if(newText!=null)
					{
						selector.options[j].text = newText;
					}
				}
			}
		}
	}
}

if(typeof(google)!="undefined")
{
	google.load("language", "1");
	google.setOnLoadCallback(translate_initialize);
}