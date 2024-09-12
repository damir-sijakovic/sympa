
function sympa_message(message, type="warning"){
	
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


function sympa_isIntegerString(str) {
    if (str !== "" && !isNaN(str) && Number.isInteger(parseFloat(str))) {
        //console.log(`${str} is an integer.`);
        return true;
    } else {
        //console.log(`${str} is not an integer.`);
        return false;
    }
}
