<div class="box cheque-box" id="divopcion3" style="display:none;">
    <h3 class="page-subheading">{l s='Pago Zimple' mod='vpos'}</h3>
    <!-- APERTURA OPCION 1 -->
		{block name='payment_execution_form_opcion3_zimple'}
			{include file='modules/vpos/views/templates/front/payment_execution_form_opcion3_zimple.tpl' data=$data}
		{/block}
	<!-- CIERRE OPCION 1 -->
    <div style="height: 500px; width: 100%; margin: 20 px;" id="iframe-container3"></div>

</div>