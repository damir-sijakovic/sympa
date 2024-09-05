
function ds_message(message, type="warning"){
	
	let element = document.getElementById("ds-top-message");
	
	element.innerHTML = `
		<div id="ds-top-message" class="ds-animate-top"> 
			<div class="alert alert-${type} alert-dismissible fade show m-0" role="alert">
			  ${message}
			  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		</div>
	`;		
}

