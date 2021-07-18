
	<!--<pre>
	{$data|print_r}
	</pre>-->

<section id="wrapper" style="background-color: white !important;">
	<div class="container">
		<div style="clear:both;"></div>
			<div class="row">
				<div id="center_column" class="center_column col-xs-12 col-sm-12">
					{capture name=path}{l s='pagopar' mod='vpos'}{/capture}
					<h1 class="page-heading">
					    {l s='Paga con tu tarjeta a través de Bancard' mod='vpos'}
					</h1>
					
					{if $data.payment_error ne ""}
						<p class="alert alert-warning">{l s='Los detalles del pago no son válidos, póngase en contacto con el administrador.' mod='vpos'}</p>
					{/if}
					{if $data.nbProducts <= 0}
						<p class="alert alert-warning">{l s='Su cesta está vacía.' mod='vpos'}</p>
					{else}
					<div id="errors"></div>
					<!-- AQUI VA EL CUADRO DE INFORMACION DE LO QUE SE DEBE PAGAR -->
				</div>
			</div>
			{block name='payment_execution_form'}
				{include file='modules/vpos/views/templates/front/payment_execution_form.tpl' data=$data}
			{/block}
			{/if}
			<!--INICIA Boton para volver atras-->	   	
			<p id="cart_navigation" class="cart_navigation clearfix">
			    <a href="{$link->getPageLink('order')|escape:'htmlall':'UTF-8'}" class="btn btn-primary pull-xs-left">
					<i class="icon-chevron-left"></i>
					{l s='otros métodos de pago' mod='vpos'}
				</a>		
			</p>
			<!--TERNINA Boton para volver atras-->
			<img src="{$data.this_path_ssl}bancard.jpg" style="width: 100%; margin: 20 px;">
	</div>
</section>



