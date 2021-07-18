<!doctype html>
<html lang="{$language.iso_code|escape:'htmlall':'UTF-8'}">
<head>
	{block name='head'}
	  {include file='_partials/head.tpl'}
	{/block}
</head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<body id="{$page.page_name}" class="{$page.body_classes|classnames}">

{hook h='displayAfterBodyOpeningTag'}

<main>
	{block name='product_activation'}
		{include file='catalog/_partials/product-activation.tpl'}
	{/block}
	<header id="header">
		{block name='header'}
			{include file='_partials/header.tpl'}
		{/block}
	</header>
		{block name='notifications'}
			{include file='_partials/notifications.tpl'}
		{/block}
		<!-- AQUI EMPIEZA EL BLOQUE DE PAGO -->
		{block name='payment_execution_section'}
			{include file='modules/vpos/views/templates/front/payment_execution_section.tpl' data=$datos}
		{/block}
		<pre>
		{*$datos|print_r*}
		</pre>
		<!-- AQUI CIERRA EL BLOQUE DE PAGO -->

	<footer id="footer">
		{block name="footer"}
	  		{include file="_partials/footer.tpl"}
		{/block}
	</footer>
</main>

{block name='javascript_bottom'}
  {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
{/block}

{hook h='displayBeforeBodyClosingTag'}

{if $datos.mode == 'si'}
	<script src="https://vpos.infonet.com.py:8888/checkout/javascript/dist/bancard-checkout-3.0.0.js"></script>
{else}
	<script src="https://vpos.infonet.com.py/checkout/javascript/dist/bancard-checkout-3.0.0.js"></script>
{/if}
</body>
</html>

