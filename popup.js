
var background = chrome.extension.getBackgroundPage();
function delay(time) {
  return new Promise(resolve => setTimeout(resolve, time));
}

delay(1000).then(() => load());
var v = 0.1;

const load = () => {


		chrome.storage.local.get(['token'], function(result) {

					 if (typeof result.token === 'undefined') {
						    // if already set it then nothing to do 
								$('#loader').hide();
								$('#form').show();
								$('#check').hide();
						  } else {
						 	 
						 		$('#loader').show();
						 		$('#check').show(); 
						 		var apiCall = 'http://api.zwlsoftware.com/v1/?key=' + result.token +'&page=version&product=zalandobot_sneakers'

						 		$.ajax({
									url: apiCall,
									type: 'GET',
									dataType: 'json',
									data: {res: 'get', token: result.token},
								})
								.done(function(data) {
									 
							 
									if(v < data['version'])
									{
										 
										console.log('new update available: ' + data['version']  );
								 
										setTimeout(function (){
								 			$('#updateinfo').text('update required: v' + data['version'] + ' download here');
										}, 1000); 
									}
									else{
									 	checkstatus();
										console.log('version: ' + v); 
										$('#loader').hide();	
										$('#updateinfo').hide();
										$('#settings').show();
										
										console.log(background.statuscode);
									}


								})
								.fail(function() {
									console.log("error");
								})
								.always(function() {
									console.log("complete");
								});








						  }



					 
					});
}

function checkstatus(){

									
    if(background.statuscode == 0 ){
			$('#status').css({
			color: 'red'
		});
			$('#statusBtn').text('Activate');
		 		background.status = 'not running';
	}
	else if(background.statuscode == 1){

		$('#status').css({
			color: 'green'
			});
			$('#statusBtn').text('Deactivate');
		 		background.status = 'running';
	}
	else if(background.statuscode == 2)
	{
		$('#status').css({
					color: 'green'
					});
					$('#statusBtn').text('click to deactivate');
				 		background.status = 'running';
	}
	$('#status').text(background.status);
}


function validateEmail(email) {
  const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

 

function validate() {
  const $result = $("#result");
  const email = $("#email").val();
  $result.text("");

  if (validateEmail(email)) {
    //$result.text(email + " is valid :)");
  	// $result.css("color", "green");
      return true;
  } else {
    $result.text(email + " is not a valid email ");
    $result.css("color", "red");
     return false;
  }
     return false;
}

 


$(document).ready(function(){
	


	$('#statusBtn').on('click', function(){
		 	console.log(background.statuscode);
		  

		 	if(background.statuscode < 2)
		 	{
		 		background.statuscode++;
		 	}
		 	else if (background.statuscode == 2)
		 	{



		 		background.statuscode = 0;
		 	}
		 	checkstatus();
		});
		
	$('#loginbtn').on('click', function(){
		 
		if(validate()){
			const email = $("#email").val();
			rawpassword = $('#password').val();
			const apiCall = 'http://api.zwlsoftware.com/v1/?email='+email+'&password='+rawpassword+'&page=login&product=autoxize';
	 		
			$.ajax({
				url: apiCall,
				type: 'GET',
				dataType: 'json',
				data: {res: 'get', email: email, password: rawpassword, page: 'login', product: 'autoxize'},
			})
			.done(function(data) {
				console.log("success ");
				const jsondata = data;
				
				var access = jsondata['Access'];
			 	if(access == 1)
			 	{
			 		var apikey = jsondata['token'];
			 		console.log('chrome storage saved'); 
			 		chrome.storage.local.set({token: apikey}, function(){
					  console.log('Value is set to ' + apikey );
					});
					const $result = $("#result");

			 		$result.text('');

			 		$('#form').hide();
			 		delay(500).then(() => load());
			 	}
			 	else{
			 		//wrong login 
			 		const $result = $("#result");
			 		$result.text('Check your login creditials');
			 		$result.css("color", "red");
			 	}
			})
			.fail(function() {
				console.log("error");
			})
			.always(function() {
				console.log("complete");
			});
			
		}

		//console.log(res.text);

	});
   $('body').on('click', 'a', function(){
     chrome.tabs.create({url: $(this).attr('href')});
     return false;
   });
});
