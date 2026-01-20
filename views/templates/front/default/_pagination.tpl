{**
 * Compralosubito24 Blog - Pagination Template
 *}

{if isset($pages_nb) && $pages_nb > 1}
    <div class="pagination-wrapper">
        <nav aria-label="Comment pagination">
            <ul class="pagination">
                {if $p > 1}
                    <li class="page-item">
                        <a class="page-link" href="{$requestPage|escape:'html':'UTF-8'}?p=1" aria-label="{l s='First' mod='cs24blog'}">
                            <span aria-hidden="true">&laquo;&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="{$requestPage|escape:'html':'UTF-8'}?p={$p-1}" aria-label="{l s='Previous' mod='cs24blog'}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                {/if}

                {section name=pagination start=$start loop=$stop+1 step=1}
                    {if $p == $smarty.section.pagination.index}
                        <li class="page-item active">
                            <span class="page-link">{$smarty.section.pagination.index}</span>
                        </li>
                    {else}
                        <li class="page-item">
                            <a class="page-link" href="{$requestPage|escape:'html':'UTF-8'}?p={$smarty.section.pagination.index}">
                                {$smarty.section.pagination.index}
                            </a>
                        </li>
                    {/if}
                {/section}

                {if $p < $pages_nb}
                    <li class="page-item">
                        <a class="page-link" href="{$requestPage|escape:'html':'UTF-8'}?p={$p+1}" aria-label="{l s='Next' mod='cs24blog'}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="{$requestPage|escape:'html':'UTF-8'}?p={$pages_nb}" aria-label="{l s='Last' mod='cs24blog'}">
                            <span aria-hidden="true">&raquo;&raquo;</span>
                        </a>
                    </li>
                {/if}
            </ul>
        </nav>
    </div>
{/if}
