<!-- <pre>
{$data.customer->email|print_r}
</pre> -->

<form action="{$link->getModuleLink('vpos', 'validation', [], true)|escape:'htmlall':'UTF-8'}" method="post" name="vpos_form" id="vpos_form">
	<fieldset>
		<input type="hidden" name="processid" id="processid" value="{$data.Processid}">
		<input type="hidden" name="userid" id="userid" value="{$data.customer->id}">
		<input type="hidden" name="useremail" id="useremail" value="{$data.customer->email}">
		<input type="hidden" name="cartid" id="cartid" value="{$data.cartid}">
		<input type="hidden" name="urlpaymentzimple" id="urlpaymentzimple" value="{$link->getModuleLink('vpos', 'paymentzimple', array())}">
		<input type="hidden" name="urlpaymentcard" id="urlpaymentcard" value="{$link->getModuleLink('vpos', 'paymentcard', array())}">
		
		<div style="height: 50px;width: 100%;">
			<select name="opcion-tarjetas" id="opcion-tarjetas" style="width: 50%;">
				<option value="">Seleccione una tarjeta</option>
				<option value="1">Pagar con Tarjeta</option>
				<option value="2">Guardar y Pagar con Tarjeta</option>
				<option value="3">Pago Zimple</option>
			</select>
		</div>
		<div style="height: 50px;">
			
		</div>

		<!-- APERTURA OPCION 1 -->
		{block name='payment_execution_form'}
			{include file='modules/vpos/views/templates/front/payment_execution_form_opcion1.tpl' data=$data}
		{/block}
		<!-- CIERRE OPCION 1 -->
		
		<!-- APERTURA OPCION 2 -->
		{block name='payment_execution_form'}
			{include file='modules/vpos/views/templates/front/payment_execution_form_opcion2.tpl' data=$data}
		{/block}
		<!-- CIERRE OPCION 2 -->

		<!-- APERTURA OPCION 3 -->
		{block name='payment_execution_form'}
			{include file='modules/vpos/views/templates/front/payment_execution_form_opcion3.tpl' data=$data}
		{/block}
		<!-- CIERRE OPCION 3 -->
	</fieldset>
</form>
<!-- ESTO DEBEMOS SACAR DE ACA COMO SEA -->
<style type="text/css">
	iframe {	height: 350px !important;	}
</style>

