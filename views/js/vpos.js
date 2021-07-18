/*
*	PERMITE RECARGAR LA TABLA DE TARJETAS
*/

// let reload = 0;
// console.log('antes de cargar', reload);
// $(window).load(function() {
// 	reload = 1;
// 	console.log('despues de cargar', reload);
        
// });


//SELECT QUE PERMITE CARGAR LOS PRODUCTOS
$('#opcion-tarjetas').change(function () {
	let opcion = $('#opcion-tarjetas option:selected').val();
	let processId = $('#processid').val();
	//let processId = processIdval
	switch (opcion) {
		case '1':
			optionselect1(processId, styles);
		break;
		case '2':
			optionselect2();
		break;

		case '3':
			optionselect3();
		break;
	}
	//console.log('opcion seleccionada', opcion);
	//console.log('vposProcessid', {$data.vposProcessId});
	//console.log('processId', processId);
});

styles = {
		"form-background-color": "rgb(235, 235, 235)", 
		"button-background-color": "#4faed1", 
		"button-text-color": "#fcfcfc", 
		"button-border-color": "#dddddd", 
		"input-background-color": "#fcfcfc", 
		"input-text-color": "#111111", 
		"input-placeholder-color": "#111111"
	};

function optionselect1(processid, styles) {
	$('#divopcion2').hide();
	$('#divopcion3').hide();
	$('#divopcion1').show();
	//$('#divopcion1').append('<input type="hidden" name="selectedcard" id="selectedcard" value="1">');
	Bancard.Checkout.createForm('iframe-container1', processid, styles);
}

function optionselect2() {
	$('#divopcion1').hide();
	$('#divopcion3').hide();
	$('#divopcion2').show();
	loadCards();
}

function optionselect3() {
	$('#divopcion1').hide();
	$('#divopcion2').hide();
	$('#divopcion3').show();
	//$('#divopcion1').append('<input type="hidden" name="selectedcard" id="selectedcard" value="1">');
	
}

function paymentZimple() {

	let nrotel = $('#numerotelefono').val();
	let url = $('#urlpaymentzimple').val();

	$.ajax({
		url: url,
		type: 'POST',
		data: {
			nrotel: nrotel,
			tipopago: 'zimple'
		},
		success: function(res) {
			//console.log('ESTA ES LA RESPUESTA => ', res);
			Bancard.Zimple.createForm('iframe-container3', res, styles);
		},
		error: function(res) {
			alert_error_info();
		}
	});	
}

// ACCIONES REALIZADAS AL ABRIR EL MODAL
$('#modalAddCard').on('show.bs.modal', function () {

	let url = $('#urlpaymentcard').val();
	let userid = $('#userid').val();
	let useremail = $('#useremail').val();

	$.ajax({
		url: url,
		type: 'POST',
		data: {
			tipoaccion: 'addcard',
			userid: userid,
			useremail: useremail,
		},
		success: function(res) {
			Bancard.Cards.createForm('iframe-container2', res, styles);
		},
		error: function(res) {
			alert_error_info();
		}
	});
	
});

// obtiene token de la tarjeta para eliminar esa tarjeta
$('#list-cards').on('click','#deletecard', function() {
	//let currentRow = $(this).find('#aliastoken');
	let currentRow = $(this).closest('tr');
	let token = currentRow.find('#aliastoken').html();
	deleteCard(token);
	
});

// obtiene token de la tarjeta para pagar con esa tarjeta
$('#list-cards').on('click','#paywithcard', function() {
	
	let currentRow = $(this).closest('tr');
	let token = currentRow.find('#aliastoken').html();
	paymentCard(token);
});



function loadCards() {
	
	let url = $('#urlpaymentcard').val();
	let userid = $('#userid').val();
	
	$.ajax({
		url: url,
		type: 'POST',
		data: {
			tipoaccion: 'getcards',
			userid: userid,
		},
		success: function(res) {
			let jsoncards = $.parseJSON(res);
			let length = jsoncards.cards.length;
			listCards(jsoncards, length);
		},
		error: function(res) {
			alert_error_info();
		}
	});
	
}
	
function listCards(jsoncards, jsoncardslength) {
	let cards = 0;
	let buttons = "<button class='btn btn-success' id='paywithcard' name='paywithcard' style='text-transform: uppercase; font-weight: 600;'>Pagar</button> " +
			   	"<button class='btn btn-danger' id='deletecard' name='deletecard' style='text-transform: uppercase; font-weight: 600;'>Eliminar</button>"
	if(jsoncardslength > 0)
	{
		while(cards < jsoncardslength)
		{
			let card = 	"<tr role='row'>" +
						"<td>" + jsoncards.cards[cards].card_brand + "</td>" +
						"<td>" + jsoncards.cards[cards].card_masked_number + "</td>" +
						"<td>" + jsoncards.cards[cards].expiration_date + "</td>" +
						"<td style='display:none'; id='aliastoken'>" + jsoncards.cards[cards].alias_token + "</td>" +
						"<td>" + buttons + "</td>"
						"</tr>";

						cards = cards + 1;
			$('#list-cards').append(card);
		}
	} else {
		var card = "<tr><td colspan='3'>Usted no posee tarjetas agregadas, por favor registre una.</td</tr>";	
		$("#list-cards").append(card);
	}
}

function deleteCard(aliastoken) {
	
	let url = $('#urlpaymentcard').val();
	let userid = $('#userid').val();
	
	$.ajax({
		url: url,
		type: 'POST',
		data: {
			tipoaccion: 'deletecards',
			userid: userid,
			aliastoken: aliastoken,
		},
		/*beforeSend: function() {
			
		},*/ 
		success: function(res) {
			if(res == 'success'){
				alert_error();
				location.reload();
			} else {
				alert_error_info();
			}
		},
		error: function(res) {
			alert_error_info();
		}
	});
}

function paymentCard(aliastoken) {

	let url = $('#urlpaymentcard').val();
	let cartid = $('#cartid').val();

	$.ajax({
		url: url,
		type: 'POST',
		data: {
			tipoaccion: 'paymentcards',
			aliastoken: aliastoken,
			cartid: cartid,
		},
		/*beforeSend: function() {
			
		},*/ 
		success: function(res) {
			// console.log('ESTO SE RESPONDE => ', res);
			
			if(res == 'sucess')
			{
				let link = "index.php?fc=module&module=vpos&controller=validation&mode_status="+res+"&id_cart="+cartid;
				window.location.href = link;
			} else {
				alert_error_payment(res);
			}
		},
		error: function(res) {
			alert_error_info();
		}
	});
}








