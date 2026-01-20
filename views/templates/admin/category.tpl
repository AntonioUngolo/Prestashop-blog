{* 
* @Module Name: Leo Blog
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Content Management
*}
<script type="text/javascript">
    var leoblog_del_img_txt = "{$leoblog_del_img_txt|escape:'htmlall':'UTF-8'}";
    var leoblog_del_img_mess = "{$leoblog_del_img_mess|escape:'htmlall':'UTF-8'}";
    var action = "{$action|escape:'htmlall':'UTF-8'}";
    var addnew = "{$addnew|escape:'htmlall':'UTF-8'}";
</script>

<div class="" id="megamenu">
    <div class="col-md-4">
        <div class="panel panel-default">
            <h3 class="panel-title">{$text_title|escape:'htmlall':'UTF-8'}</h3>
            <div class="panel-content">
                {$text_content|escape:'htmlall':'UTF-8'}
                <hr>
                <p>
                    <input type="button" value="{$text_value|escape:'htmlall':'UTF-8'}" id="addcategory" data-loading-text="{$text_process|escape:'htmlall':'UTF-8'}" class="btn btn-danger" name="addcategory">
                </p>
                <hr>{$tree}{* HTML form , no escape necessary *}
                <a href="javascript:void(0);" class="btn btn-danger delete_many_menus">
                    <i class="icon-trash"></i>&nbsp;Delete selected
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-8">{$generate_form}{* HTML form , no escape necessary *}</div>
</div>
{literal}
<script type="text/javascript">
    $("#content").PavMegaMenuList({action:action, addnew:addnew});
</script>
{/literal}