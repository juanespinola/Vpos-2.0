<br/>
<fieldset>
    <div class="panel">
        <legend>Respuestas de Pago - Detalles de Pago</legend>
        <span style="font-size: 14px;">
            {if $vpos_response gt 0}
                {foreach from=$vpos_response  item="message"}
                    Response Descripción: <b>{$message.response_description|escape:'htmlall':'UTF-8'}</b>
                    <br>
                    Nro. Orden: <b>{$orderId|escape:'htmlall':'UTF-8'}</b>
                    <br>
                    Nro. De Autorización: <b>{$message.authorization_number|escape:'htmlall':'UTF-8'}</b>
                    <br>
                {/foreach}
            {/if}
        </span>
        <br/>
        <div style="text-align:center">
          <button class="btn button btn-default" type="button" onClick="AdminConfirmBuy({$orderId|escape:'htmlall':'UTF-8'})">
                 Confirmar Pago.
          </button>
          <!-- <img src="../img/loading.gif" style="display:none" id="loading"> -->
        </div>
    </div>
    
</fieldset>

<script>
function AdminConfirmBuy(orderid)
{
  //console.log('orderid', orderid);
  $.post(
    "{$urlConfirmBuy|escape:'htmlall':'UTF-8'|replace:'&amp;':'&'}&ajax",        
    {ldelim}
      orderid:orderid,
      action:"AdminConfirmBuy",
    {rdelim},
    function(data){            
      // console.log(data);
      if(data == 'success')
      {
        location.reload();    
      } else {
        location.reload();
      }
    }); 
}
</script>

				
										
										
                                        
