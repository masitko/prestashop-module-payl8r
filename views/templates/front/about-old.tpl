<div class="collapsible">
  <p
    class="block-title text-center"
    onClick="payl8rSlide()"
  >
    <img
      src="https://payl8r.com/frontend/img/payl8r-logo.png"
      alt="Payl8r logo"
      id="payl8r_logo"
    >
    Buy Now Pay Later Finance
  </p>
  <!-- Payl8r Integration -->
  <div class="payl8r-content feature-wrapper block-content">
    <p style="text-align:center;font-size:11px;">
      Select Payl8r for Payment on the checkout and complete your application.
      <br>
      <br>
      * Note: when using Payl8r we cannot guarantee next day delivery due to the authorisation  process.
    </p>
    <div id="example_frame"></div>

    <script
      src="{$this_path_payl8r}views/js/pl-calculator.js"
      charset="utf-8"
    ></script>
    <script>
								jQuery(document).ready(function(){
                  jQuery('.payl8r-content').slideToggle();
                  var price = jQuery('span#our_price_display').attr('content');
									plcalc.draw_to_frame(price, jQuery('#quantity_wanted'), jQuery('#example_frame'));
								});
                function payl8rSlide() {
                  jQuery('.payl8r-content').slideToggle( 500 );
                }
    </script>
  </div>
</div>
