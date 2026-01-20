{* 
* @Module Name: Leo Blog
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Content Management
*}

<ol class="level{$level|escape:'htmlall':'UTF-8'}">
    {foreach from=$data item=$menu}
        <li id="list_{$menu.id_leoblogcat|escape:'htmlall':'UTF-8'}">
            <input type="checkbox" value="{$menu.randkey|escape:'htmlall':'UTF-8'}" name="chk_cat[]" id="chk-{$menu.id_leoblogcat|escape:'htmlall':'UTF-8'}" {if $menu.id_leoblogcat|array_search:$select !== false}checked="checked"{/if}/>
            <label for="chk-{$menu.id_leoblogcat|escape:'htmlall':'UTF-8'}">{$menu.title|escape:'htmlall':'UTF-8'} (ID:{$menu.id_leoblogcat|escape:'htmlall':'UTF-8'})</label>
            {if $menu.id_leoblogcat != $parent}
                {$model_leoblogcat->genTreeForApPageBuilder($menu.id_leoblogcat, $level + 1, $select)|escape:'htmlall':'UTF-8'}
            {/if}
        </li>
    {/foreach}
</ol>
