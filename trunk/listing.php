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
 * Page or product listing. Used by fckeditor to create links
 *

 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
?>
<html>
<head>
<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
function confirmDeletion(msg)
{
    return confirm(msg);
}
function SelectFile( fileUrl )
{
    // window.opener.SetUrl( url, width, height, alt);
    window.opener.SetUrl( fileUrl ) ;
    //if opened by the "create link" dialog then the protocol combo box exists
    if(window.opener.GetE('cmbLinkProtocol') != null)
    {
        //as we are creating a relative link
        //set the protocol to nothing
        window.opener.GetE('cmbLinkProtocol').value = '';
    }
    window.close() ;
}
</script>
</head>
<body>
<?php
    define('LISTING_TYPE','type');

    define('LISTING_PAGES','pages');
    define('LISTING_PRODUCTS','products');

    require_once('common/utils/Globals.php');

    $type = Globals::get_param(LISTING_TYPE, $_GET);

    $table = <<<END
<table cellpadding=5 cellspacing=3 align=center>
END;

    if( $type === LISTING_PAGES )
    {
        $ps = Load::page_store();
        $user_pages = $ps->load_users_pages();
        $index_page = $ps->load_index_page();

        $index_id = $index_page->id;
        $index_title = $index_page->get(CONTENT_TITLE);

        $table .= <<<END
<tr>
<td>
    <b><a href="javascript:SelectFile('index.php');">$index_title</a></b>
</td>
</tr>
END;

        foreach($user_pages as $content)
        {
            $content_id = $content->id;
            $content_title = $content->get(CONTENT_TITLE);

            $table .= <<<END
<tr>
<td>
    <a href="javascript:SelectFile('index.php?content_id=$content_id');">$content_title</a>
</td>
</tr>
END;
        }
    } else if( $type === LISTING_PRODUCTS ) {
        $prs = Load::product_store();
        $pgs = Load::payment_general_settings();
        $products = $prs->load_all_products();
        $product_id_url_param = PRODUCT_ID_URL_PARAM;
        $currency_code = $pgs->get(PAYMENT_CURRENCY);
        $currency_name = $pgs->get_name_of_currency_in_use();

        foreach($products as $product)
        {
	    $product_id = $product->id;
	    $product_title = $product->get(PRODUCT_TITLE);
	    $price = Format::price($product->get(PRODUCT_PRICE), $currency_code, true, false);

            $table .= <<<END
<tr>
<td>
    <a href="javascript:SelectFile('product.php?content_id=$product_id');">$product_title</a>
</td>
<td>$price</td>
</tr>
END;
        }

        $table .= <<<END
<tr>
<td colspan=2>Prices are in <b>$currency_name</b></td>
</tr>
</table>
END;
    }

    $table .= '</table>';

    echo($table);
?>
</body>
</html>