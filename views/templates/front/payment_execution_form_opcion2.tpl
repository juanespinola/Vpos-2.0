<div class="box cheque-box" id="divopcion2" style="display:none;">
    <h3 class="page-subheading">{l s='Pagar con Vpos de Bancard' mod='vpos'}</h3>
    <div style="width: 100%; margin: 20 px;">
    	<!-- APERTURA TABLA TARJETAS -->
		{block name='payment_execution_form'}
			{include file='modules/vpos/views/templates/front/payment_execution_form_opcion2_tablecard.tpl'}
		{/block}
		<!-- CIERRE TABLA TARJETAS -->
    </div>
	    <!-- APERTURA MODAL -->
		{block name='payment_execution_form'}
			{include file='modules/vpos/views/templates/front/payment_execution_form_opcion2_modal.tpl'}
		{/block}
		<!-- CIERRE MODAL -->
    <div>
    	<button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAddCard">
    		{l s=' + Agregar Tarjeta ' mod='vpos'}
    	</button>
    </div>
</div>
<br>