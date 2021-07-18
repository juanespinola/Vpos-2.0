function alert_success() {

	Swal.fire({
	  icon: 'success',
	  title: 'Su tarjeta fue agregada correctamente!',
	  showConfirmButton: false,
	  timer: 1500
	});
}

function alert_error() {

	Swal.fire({
	  icon: 'success',
	  title: 'Su tarjeta fue eliminada correctamente!',
	  showConfirmButton: false,
	  timer: 1500
	});
}

function alert_error_info() {

	Swal.fire({
	  icon: 'error',
	  title: 'Ocurrió un error, por favor contacte con soporte!',
	  showConfirmButton: false,
	  timer: 1500
	});
}

function alert_error_payment(message) {

	Swal.fire({
	  icon: 'error',
	  title: message,
	  showConfirmButton: false,
	  timer: 1500
	});
}
