{if $alertsave}
<div class="bootstrap">
	<div class="module_confirmation conf confirm alert alert-success">
			<button type="button" class="close" data-dismiss="alert" >x</button>
			{l s='Datos Guardados Correctamente!.' mod='vpos'}	
	</div>
</div>
{/if}


<div class="panel panel-default">
  
	  <form action="" method="post">
	    
	    <div class="form-group">
	      <label for="publickey">{l s='Clave Pública:' mod='vpos'}</label>
	      <input type="text" value="{$publickey}" class="form-control" id="publickey" name="publickey" placeholder="{l s='Ingrese clave pública' mod='vpos'}" required="required">
	    </div>
	    
	    <div class="form-group">
	      <label for="privatekey">{l s='Clave Privada:' mod='vpos'}</label>
	      <input type="text" class="form-control" value="{$privatekey}" id="privatekey" name="privatekey" placeholder="{l s='Ingrese Clave Privada' mod='vpos'}" required="required">
	    </div>

	    <div class="form-group">
	      <label for="urlresponse">{l s='URL de Notificación de Pagos(Copie esta url y pegue en el portal de comercios):' mod='vpos'}</label>
	      <input type="text" class="form-control" id="urlresponse" value="{$urlresponse}" name="urlresponse" required="required">
	    </div>

	    <div class="form-group">
	    	<label for="mode">{l s='Ambiente de Desarrollo?' mod='vpos'}</label>
		    <select class="form-control"  id="mode" name="mode">
		        <option value="si" {if 'si' == $mode} selected='selected' {/if}>Si</option>
		        <option value="no" {if 'no' == $mode} selected='selected' {/if}>No</option>
		    </select>
	    </div>
	    
      	<button type="submit" class="btn btn-primary btn-md" name="save">
      		<i class="process-icon-save"></i>
	      	{l s=' Guardar ' mod='vpos'}
	  	</button>

	  </form>
</div>