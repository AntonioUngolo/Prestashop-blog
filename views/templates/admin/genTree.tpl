{* 
* @Module Name: Leo Blog
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Content Management
*}

<ol class="level{$level|escape:'htmlall':'UTF-8'}{$t|escape:'htmlall':'UTF-8'}">
    {foreach from=$data item=$menu}
        <li id="list_{$menu.id_leoblogcat|escape:'htmlall':'UTF-8'}" class="{if $param_id_leoblogcat == $menu.id_leoblogcat}selected{/if}">
        <div>
            <span class="disclose"><span></span></span>
            {$menu.title|escape:'htmlall':'UTF-8'} (ID:{$menu.id_leoblogcat|escape:'htmlall':'UTF-8'})
            <input type="checkbox" name="menubox[]" value="{$menu['id_leoblogcat']|escape:'htmlall':'UTF-8'}" class="quickselect" title="Select to delete">
            <span class="quickedit" rel="id_{$menu.id_leoblogcat|escape:'htmlall':'UTF-8'}">E</span>
            <span class="quickdel" rel="id_{$menu.id_leoblogcat|escape:'htmlall':'UTF-8'}">D</span>
        </div>
        {if $menu.id_leoblogcat != $parent}
            {$model_leoblogcat->genTree($menu.id_leoblogcat, $level + 1)|escape:'htmlall':'UTF-8'}
        {/if}
        </li>
    {/foreach}
</ol>