{* 
* @Module Name: Leo Blog
* @Website: leotheme.com.com - prestashop template provider
* @author Leotheme <leotheme@gmail.com>
* @copyright  Leotheme
* @description: Content Management
*}
{if isset($errors) && count($errors) && current($errors) != ''}

	<div class="bootstrap">
		<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		{if count($errors) == 1}
			{reset($errors|escape:'htmlall':'UTF-8')}
		{else }
			{l s='%d errors' sprintf=[$errors|count] mod='leoblog'}
			<br/>
			<ol>
				{foreach $errors as $error}
					<li>{$error|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ol>
		{/if}
		</div>
	</div>
{/if}