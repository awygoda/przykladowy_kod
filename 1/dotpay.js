function addPayments()
{
        $.ajax({
            url: 'ajax/dotpay.php',
            type: 'post',
            data: {
                    
                },
            success: function(data){
                var result_json = $.parseJSON(data); 
                dotpayForm(result_json);
            }
        });
}

function dotpayForm(data){
    var form = $(document.createElement('form'));
    $(form).attr("action", "https://ssl.dotpay.pl/");
    $(form).attr("method", "POST");
    $(form).css("display", "none");

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "id")
        .val("00000");
    $(form).append($(input));

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "amount")
        .val(data.cena);
    $(form).append($(input));

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "opis")
        .val(data.opis);
    $(form).append($(input));

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "jezyk")
        .val("pl" );
    $(form).append($(input));

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "waluta")
        .val("PLN" );
    $(form).append($(input));

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "control")
        .val(data.control);
    $(form).append($(input));

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "typ")
        .val("3");
    $(form).append($(input));       

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "URL")
        .val(data.link);
    $(form).append($(input));

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "URLC")
        .val("https://"+ window.location.host + "/dotpay/dotpay.php");
    $(form).append($(input));

    form.appendTo( document.body );
    $(form).submit();
}