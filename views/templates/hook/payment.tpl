
<style>
      p.payment_module.payl8r a 
	{ldelim}
		padding-left:17px;
	{rdelim}

   p.payment_module.payl8r a:after
	{ldelim}
      display: block;
      content: "\f054";
      position: absolute;
      right: 15px;
      margin-top: -11px;
      top: 50%;
      font-family: "FontAwesome";
      font-size: 25px;
      height: 22px;
      width: 14px;
      color: #777; 
	{rdelim}
</style>

<p class="payment_module payl8r">
	<a href="{$link->getModuleLink('payl8r', 'payment', [], true)|escape:'html'}" title="{l s='Pay by Payl8r.' mod='payl8r'}">
		<img src="{$this_path_payl8r}views/img/payl8rlogo.png" alt="{l s='Pay by payl8r' mod='payl8r'}" width="92" height="31" />
		<span>{l s='Buy online and pay later when it suits you.' mod='payl8r'}</span>
	</a>
</p>
