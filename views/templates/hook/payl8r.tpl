<div class="collapsible">
  <p
    class="block-title text-center payl8r-clickable"
    onClick="payl8rSlide()"
    style="cursor:pointer"
  >
    <img
      src="{$this_path_payl8r}views//img/payl8r-logo.png"
      alt="Payl8r logo"
      id="payl8r_logo"
    >
    Buy Now Pay Later Finance
<img
      src="{$this_path_payl8r}views/img/payl8r-down-c.png"
      alt="Payl8r togglr"
      id="payl8r_down"
    >    
  </p>
  <!-- Payl8r Integration -->
  <div class="payl8r-content feature-wrapper block-content">
    <p style="text-align:center;font-size:11px;">
      Select Payl8r for Payment on the checkout and complete your application.
      <br>
      <br>
      * Note: when using Payl8r we cannot guarantee next day delivery due to the authorisation  process.
    </p>
    <div id="payl8r_frame"></div>

    <script
      src="{$this_path_payl8r}views/js/pl-calculator.js"
      charset="utf-8"
    ></script>
    <script>
jQuery(document).ready(function(){
	jQuery('.payl8r-content').slideToggle();
	drawCalc();
	jQuery('input, select').change(function(){
	   setTimeout(drawCalc, 500);
	});
});
function drawCalc() {
       var price = jQuery('span#our_price_display').html();
	price = parseInt(price.replace(/[^0-9\.]/g, ''), 10);
        plcalc.draw_to_frame(price, jQuery('#quantity_wanted'), jQuery('#payl8r_frame'));
}
function payl8rSlide() {
	jQuery('.payl8r-content').slideToggle( 500 );
}
    </script>
  </div>
</div>
