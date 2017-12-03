
{* <link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/pl-calculator.css"> *}
<link href="https://fonts.googleapis.com/css?family=Lato:400,300,700" rel="stylesheet" type="text/css">
<div style="max-width:1200px;margin-top:30px;margin-left:auto;margin-right:auto">
<div id="payl8r_template_full">
	<div id="payl8r_template_header">
		<img src="{$this_path_payl8r}views/img/payl8r-logo.jpg" alt="" />
	</div>
	<div id="payl8r_template_leftcolumn">
		<div class="payl8r_template_content_wrapper">
			<div class="payl8r_template_content">
				<div class="payl8r_template_content_text">
					<h1>Spread the cost of your online basket!</h1>
					<p>
						Payl8r is a simple payment option that enables you to purchase goods and services online and spread the cost over time period to suit.
					</p>
				</div>
			</div>
			<div class="payl8r_template_content">
				<div class="payl8r_template_content_text">
					<h1>Repayments</h1>
					<p>
						You can repay in full within 30 days at 0% interest, or choose an instalment plan to repay on a monthly basis. You can change your instalment plan at any time.
						<span>Interest rates from 0% - 24% per year</span>
						Click the Payl8r button at checkout on participating stores and they will approve you within minutes
					</p>
				</div>
			</div>
			<div class="payl8r_template_content">
				<div class="payl8r_template_content_text">
					<h1>Will I qualify?</h1>
					<p>
						If you have a UK bank account or debit card, you can use Payl8r! 
						<span>student</span>
						<span>retired</span>
						<span>employed</span> 
						<span>self-employed</span>
						<span>benefits</span>
						Finance is subject to your ability to repay the loan
					</p>
				</div>
			</div>
		</div>
    </div>
    <div id="payl8r_template_rightcolumn">
        <div class="payl8r_template_content_wrapper">
	        <div class="payl8r_template_content">
		        <div class="payl8r_template_content_text">
		        	<h1>How It Works</h1>
		        	<div class="flow-chart-container">
				        <ul class="flow-chart-item">
				            <li>Add items to basket and checkout</li>
				            <li>Click Payl8r</li>
				            <li>Choose your payment plan</li>
				        </ul>
				    </div>
				    
				    <div class="payl8r-price-checker-wrapper">
					    <div>
						    <label class="example_amount_label" for="example_amount">£</label>
						    <input id="example_amount" type="number" min="50" value="50" />
						    
						    <!-- <label class="example_amount_label label_up_arrow" for="example_amount">&#8679;</label>
						    <label class="example_amount_label label_down_arrow" for="example_amount">&#8681;</label> -->
					    </div>
						<div id="example_frame">
							
						</div>
						<div>
						    <input id="example_value" type="hidden" value="1" />
					    </div>
					</div>
					<p>
						(COMPANY NAME HERE) is a registered Trading name, Credit Introducer and Appointed Representative of Social Money Ltd t/a Payl8r, a company registered in England under company number 08054296 and is authorised and regulated by the Financial Conduct Authority and is entered on the Financial Services Register under reference number: 675283. registered with the Office of the Information Commissioner reference number 08054296.
					</p>
		        </div>
	        </div>
        </div>
    </div>
</div>
</div>

{* <script src="assets/js/jquery.min.js" charset="utf-8"></script>
<script src="assets/js/pl-calculator.js" charset="utf-8"></script> *}
<script>
	// Example of draw to frame
	$(document).ready(function(){
		plcalc.draw_to_frame($("#example_amount").val(), $('#example_value'), $('#example_frame'));
		
		$(document).on("change keyup", "#example_amount", function(){
			plcalc.draw_to_frame_html($(this).val(), $("#example_frame"));
		});
	});
</script>