{* 
* @Module Name: Leo Blog
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Content Management
*}

<div class="panel form-horizontal">
    <div class="form-group">
        <div class="col-lg-1">
            <a class="megamenu-correct-module btn btn-success" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=leoblog&success=correct&correctmodule=1">
                <i class="icon-AdminParentPreferences"></i>{l s='Correct module' mod='leoblog'}
            </a>
        </div>
        <div class="col-lg-5">* {l s='Please backup the database before run correct module to safe' mod='leoblog'}</div>
    </div>
</div>