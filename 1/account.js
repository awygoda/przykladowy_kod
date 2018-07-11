function savePersonalData() {
    var action = 'savePersonalData';
    var name = $("input[name='name']").val();
    var nip = $("input[name='nip']").val();
    var address = $("input[name='address']").val();
    var postcode = $("input[name='postcode']").val();
    var city = $("input[name='city']").val();
    var country = $("select[name='country']").val();
    var phone = $("input[name='phone']").val();
    var invoiceName = $("input[name='invoice-name']").val();
    var invoiceNip = $("input[name='invoice-nip']").val();
    var invoiceAddress = $("input[name='invoice-address']").val();
    var invoicePostcode = $("input[name='invoice-postcode']").val();
    var invoiceCity = $("input[name='invoice-city']").val();
    var invoiceCountry = $("select[name='invoice-country']").val();
    if($('#copy-data').prop('checked')){
        var checked = 1;
    } else {
        var checked = 0; 
    }
	$.ajax({
		url: '/ajax/account.php',
		type: 'post',
        data: { action, name, nip, address, postcode, city, country, phone, invoiceName, invoiceNip,
                invoiceAddress, invoicePostcode, invoiceCity, invoiceCountry, checked
            },
		success: function(data){
            var result_json = $.parseJSON(data);
            // alert(data);
            if(result_json.wynik == 1 ){
                $(".alert").remove();
                var newDiv = "<div class='alert alert-success text-center' id='errorsAlerts'><ul><li class='err'>";
                newDiv += "Dane zostały zaaktualizowane.";
                newDiv += "</li></ul></div)";
                $( "#errors" ).append( newDiv );
                $(document).ready(function(){
                    $('#errorsAlerts').delay(2000).fadeOut('slow');
                })          
            } else {
                $(".alert").remove();
                var newDiv = "<div class='alert alert-danger text-center' id='errorsAlerts'><ul><li class='err'>";
                newDiv += result_json;
                newDiv += "</li></ul></div)";
                $( "#errors" ).append( newDiv );
                $(document).ready(function(){
                    $('#errorsAlerts').delay(2000).fadeOut('slow');
                })
            }    
        }
	});
}

function changeEmailData() {
    var action = 'changeEmailData';
    var email = $("input[name='email']").val();
    var newEmail = $("input[name='new-email']").val();
	$.ajax({
		url: '/ajax/account.php',
		type: 'post',
        data: { action, email, newEmail
            },
		success: function(data){
            var result_json = $.parseJSON(data);
            // alert(data);
            if(result_json.wynik == 1 ){
                $(".alert").remove();
                var newDiv = "<div class='alert alert-success text-center' id='errorsAlerts'><ul><li class='err'>";
                newDiv += "Dane zostały zaaktualizowane.";
                newDiv += "</li></ul></div)";
                $( "#errors-email" ).append( newDiv );
                $(document).ready(function(){
                    $('#errorsAlerts').delay(2000).fadeOut('slow');
                })          
            } else {
                $(".alert").remove();
                var newDiv = "<div class='alert alert-danger text-center' id='errorsAlerts'><ul><li class='err'>";
                newDiv += result_json;
                newDiv += "</li></ul></div)";
                $( "#errors-email" ).append( newDiv );
                $(document).ready(function(){
                    $('#errorsAlerts').delay(2000).fadeOut('slow');
                })
            }    
        }
	});
}

function changePasswordData() {
    var action = 'changePasswordData';
    var oldPassword = $("input[name='old-password']").val();
    var newPassword = $("input[name='new-password']").val();
    var newRePassword = $("input[name='new-re-password']").val();
	$.ajax({
		url: '/ajax/account.php',
		type: 'post',
        data: { action, oldPassword, newPassword, newRePassword,
            },
		success: function(data){
            var result_json = $.parseJSON(data);
            if(result_json.wynik == 1 ){
                $(".alert").remove();
                var newDiv = "<div class='alert alert-success text-center' id='errorsAlerts'><ul><li class='err'>";
                newDiv += "Dane zostały zaaktualizowane.";
                newDiv += "</li></ul></div)";
                $( "#errors-password" ).append( newDiv );
                $(document).ready(function(){
                    $('#errorsAlerts').delay(2000).fadeOut('slow');
                })          
            } else {
                $(".alert").remove();
                var newDiv = "<div class='alert alert-danger text-center' id='errorsAlerts'><ul><li class='err'>";
                newDiv += result_json;
                newDiv += "</li></ul></div)";
                $( "#errors-password" ).append( newDiv );
                $(document).ready(function(){
                    $('#errorsAlerts').delay(2000).fadeOut('slow');
                })
            }    
        }
	});
}

function sendMessage()
{
    var email = $("input[name='email']").val();
    var title = $("input[name='title']").val();
    var description = $("textarea[name='description']").val();
    $.ajax({
		url: '/ajax/message.php',
		type: 'post',
        data: { email, title, description
            },
		success: function(data){
            var result_json = $.parseJSON(data);
            // alert(data);
            if(result_json.wynik == 1 ){
                window.location = 'wiadomosc-wyslana'
            } else {
                $(".alert").remove();
                var newDiv = "<div class='alert alert-danger text-center' id='errorsAlerts'><ul><li class='err'>";
                newDiv += result_json;
                newDiv += "</li></ul></div)";
                $( "#errors" ).append( newDiv );
                $(document).ready(function(){
                    $('#errorsAlerts').delay(2000).fadeOut('slow');
                })
            }    
        }
	});
}