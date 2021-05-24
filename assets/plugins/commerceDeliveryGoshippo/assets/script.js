(function () {


    $(document).on('change','#delivery_goshippo_rate',function () {

        Commerce.updateOrderData(document.querySelector('[data-commerce-order]'))

    });
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

    });


    let fullNameFieldName = goshippoConfig.fullNameField;

    let goshippoFieldChangeTimer;
    function goshippoFieldChange() {

        if(!canRequestRates()){
            console.log('can not requestRates');
            return;
        }


        clearTimeout(goshippoFieldChangeTimer);
        goshippoFieldChangeTimer = setTimeout(function () {

            $('#goshippo_markup input, #goshippo_markup select').prop('readonly',true)
            $('#goshippo_markup').addClass('goshippo-reload');

            Commerce.updateOrderData(document.querySelector('[data-commerce-order]'))
            console.log('Goshippo requestRates')
        },1000)
    }
    $(document)
        .on('keyup','[data-commerce-order] ['+fullNameFieldName+']',goshippoFieldChange)
        .on('change','#delivery_goshippo_country',goshippoFieldChange)
        .on('change','#delivery_goshippo_state',goshippoFieldChange)

        .on('keyup','#delivery_goshippo_zip',goshippoFieldChange)
        .on('keyup','#delivery_goshippo_city',goshippoFieldChange)
        .on('keyup','#delivery_goshippo_street',goshippoFieldChange)
    ;




    function canRequestRates() {
        let fullNameFieldName = goshippoConfig.fullNameField;
        let deliveryMethod = $('[data-commerce-order] [name="delivery_method"]').val();

        let $countryOption = $('#delivery_goshippo_country option:selected');

        let name = $('[data-commerce-order] [name="'+fullNameFieldName+'"]').val();

        let country =  $countryOption.val();
        let state =  $('#delivery_goshippo_state').val();
        let requireState =  parseInt($countryOption.data('state'));

        let zip =  $('#delivery_goshippo_zip').val();
        let city =  $('#delivery_goshippo_city').val();
        let street =  $('#delivery_goshippo_street').val();


        return deliveryMethod === goshippoConfig.deliveryMethodKey && name && country && (requireState === 0 || state) && zip && city && street
    }

})()