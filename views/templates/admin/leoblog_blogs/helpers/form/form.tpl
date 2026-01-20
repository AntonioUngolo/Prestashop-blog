{* 
* @Module Name: Leo Blog
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Content Management
*}

<!-- @file modules\appagebuilder\views\templates\admin\ap_page_builder_theme_configuration\helpers\form\form.tpl -->
{extends file="helpers/form/form.tpl"}
{block name="input"}
	{if $input.type == 'date_leoblog'}
		<div class="row">
			<div class="input-group col-lg-4">
				<input
					id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
					type="text"
					data-hex="true"
					{if isset($input.class)} class="{$input.class}"
					{else}class="datepicker"{/if}
					name="{$input.name|escape:'htmlall':'UTF-8'}"
					value="{if isset($input.default) && $fields_value[$input.name] == ''}{$input.default|escape:'htmlall':'UTF-8'}{else}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}" />
				<span class="input-group-addon">
					<i class="icon-calendar-empty"></i>
				</span>
			</div>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
	
{/block}