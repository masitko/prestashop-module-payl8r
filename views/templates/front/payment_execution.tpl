
{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='payl8r'}">{l s='Checkout' mod='bankwire'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Payl8r Payment' mod='payl8r'}
{/capture}

{* include file="$tpl_dir./breadcrumb.tpl" *}

{* <h2>{l s='Order summary' mod='payl8r'}</h2> *}

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='payl8r'}</p>
{else}

{* <h3>{l s='Payl8r payment' mod='Payl8r'}</h3> *}
<form id="payl8rForm" target="payl8rFrame" action="{$action}" method="post">
  <input type="hidden" name="data" value="{$data}"/>
  <input type="hidden" name="rid" value="{$rid}">  
  <input type="submit" style="display:none">
</form>
<iframe width="100%" height="100%" src="" name="payl8rFrame" style="min-height:600px; border: none;margin:0"></iframe>
<script type="text/javascript">
  (function () {
    window.addEventListener("message", pl_iframe_heightUpdate, false);
     var prevHeight = jQuery('[name="payl8rFrame"]').height();
    function pl_iframe_heightUpdate(event) {
      var origin = event.origin || event.originalEvent.origin;
      if (origin !== "https://payl8r.com")
        return;
      if (prevHeight !== jQuery('[name="payl8rFrame"]').height())
        prevHeight = event.data;
        jQuery('[name="payl8rFrame"]').height(event.data+80);
    }
    document.getElementById("payl8rForm").submit();
  })();
</script>
{/if}
