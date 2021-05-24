(function ($) {
    let goshippoFieldChangeTimer;

    let fullNameFieldName = goshippoConfig.fullNameField;

    var rates = [];


    $(document).on('change','#delivery_goshippo_rate',function () {

        var rateId =  $(this).val();

        for(var i = 0;i<rates.length;i++){

            if(rateId == rates[i]['object_id']){
                $('#delivery_goshippo_rate_full').val(JSON.stringify(rates[i]))
            }

        }



    });


    function canRequestRates() {
        let fullNameFieldName = goshippoConfig.fullNameField;
        let deliveryMethod = $('#order_form [name="order[fields][delivery_method]"]').val();

        let $countryOption = $('#delivery_goshippo_country option:selected');

        let name = $('#order_form [name="'+fullNameFieldName+'"]').val();

        let country =  $countryOption.val();
        let state =  $('#delivery_goshippo_state').val();
        let requireState =  parseInt($countryOption.data('state'));

        let zip =  $('#delivery_goshippo_zip').val();
        let city =  $('#delivery_goshippo_city').val();
        let street =  $('#delivery_goshippo_street').val();

        debugger;

        return deliveryMethod === goshippoConfig.deliveryMethodKey && name && country && (requireState === 0 || state) && zip && city && street
    }



    function goshippoUpdateRates() {

         if(!canRequestRates()){
            console.log('can not requestRates');
            return;
        }

        clearTimeout(goshippoFieldChangeTimer);
        goshippoFieldChangeTimer = setTimeout(function () {

            $('[data-delivery="goshippo"]').prop('readonly',true)
            $('#delivery_goshippo_rate option:gt(0)').remove();

            var cart = [];


            $('#products tr').each(function (ind,elem) {

                var iteration = ind + 1;


                cart.push({
                    id:$('[name="order[cart]['+iteration+'][id]"]').val(),
                    count:$('[name="order[cart]['+iteration+'][count]"]').val(),
                })

            })

            rates = [];


            $.post('/ajax/commerce/delivery/goshippo/rates-calculate',{
                name:$('#order_form [name="'+fullNameFieldName+'"]').val(),
                delivery_goshippo_country: $('#delivery_goshippo_country').val(),
                delivery_goshippo_state: $('#delivery_goshippo_state').val(),
                delivery_goshippo_zip: $('#delivery_goshippo_zip').val(),
                delivery_goshippo_city: $('#delivery_goshippo_city').val(),
                delivery_goshippo_street: $('#delivery_goshippo_street').val(),
                cart:cart

            },function (response) {


                if(response.status){

                    rates = response.rates;

                    for(var i=0;i<rates.length;i++){
                        var rate = response.rates[i];
                        $('#delivery_goshippo_rate').append($('<option></option>').val(rate.object_id).text(rate.title))
                    }
                }
                else{
                    alert(response.message)
                }

                $('[data-delivery="goshippo"]').prop('readonly',false);

            })


            //Commerce.updateOrderData(document.querySelector('[data-commerce-order]'))
        },1000)
    }

    $(document)
        .on('keyup','#order_form [name="'+fullNameFieldName+'"]',goshippoUpdateRates)
        .on('change','#delivery_goshippo_country',goshippoUpdateRates)
        .on('change','#delivery_goshippo_state',goshippoUpdateRates)

        .on('keyup','#delivery_goshippo_zip',goshippoUpdateRates)
        .on('keyup','#delivery_goshippo_city',goshippoUpdateRates)
        .on('keyup','#delivery_goshippo_street',goshippoUpdateRates)
    ;




    $(document).on('change','#delivery_goshippo_country',function () {
        var $option = $('#delivery_goshippo_country option:selected');

        var country = $(this).val();



        $('#delivery_goshippo_state option:gt(0)').remove();

        if(parseInt($option.data('state')) === 1){

            $.post('/ajax/commerce/delivery/goshippo/states',{
                country:country
            },function (response) {

                for(var i = 0;i < response.length;i++){
                    var item = response[i];
                    $('#delivery_goshippo_state').append($('<option></option>').val(item['iso']).text(item['title']))
                }

            })
        }


    });



})(jQuery)