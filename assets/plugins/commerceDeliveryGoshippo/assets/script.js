
$(document).on('change','#delivery_goshippo_country',function () {
    var $option = $('#delivery_goshippo_country option:selected');

    var country = $(this).val();



    if(parseInt($option.data('state')) === 1){
        $("#delivery_goshippo_state_owner").show();

        $.post('/ajax/commerce/delivery/goshippo/states',{
            country:country
        },function (response) {

            for(var i = 0;i < response.length;i++){
                var item = response[i];
                $('#delivery_goshippo_state').append($('<option></option>').val(item['iso']).text(item['title']))
            }

        })
    }
    else{
        $('#delivery_goshippo_state option:gt(0)').remove();
        $("#delivery_goshippo_state_owner").hide();
    }


})

