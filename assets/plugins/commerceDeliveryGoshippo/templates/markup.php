<div id="goshippo_markup">
    <? if(!empty($errors)): ?>
        <div class="goshippo_errors">
            <?php foreach ($errors as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?endif; ?>

    <input type="hidden" name="delivery_goshippo_country_rates_request_hash" value="<?= $ratesRequestHash ?>">

    <div class="delivery_goshippo_country_owner <?= count($countries) === 1?'goshippo-hide':'' ?>">
        <select name="delivery_goshippo_country" id="delivery_goshippo_country" class="delivery_goshippo_country <?= count($countries) === 1?'goshippo-hide':'' ?>">
            <option value="">[%select_country%]</option>
            <?php foreach ($countries as $iso => $country): ?>
                <option data-state="<?= $country['require_state'] ?>" <?= $selectedCountry['iso'] == $iso ? 'selected':'' ?> value="<?= $iso?>"><?= $country['title'] ?></option>
            <?php endforeach; ?>
        </select>
        [+delivery_goshippo_country.error+]
    </div>


    <div class="delivery_goshippo_state_owner <?= empty($states)?'goshippo-hide':'' ?>" id="delivery_goshippo_state_owner">
        <select name="delivery_goshippo_state" id="delivery_goshippo_state" class="delivery_goshippo_state">
            <option value="">[%select_state%]</option>
            <?php foreach ($states as $state): ?>
                <option  <?= $selectedState['iso'] == $state['iso'] ? 'selected':'' ?> value="<?= $state['iso']?>"><?= $state['title'] ?></option>
            <?php endforeach; ?>
        </select>
        [+delivery_goshippo_state.error+]
    </div>

    <div class="delivery_goshippo_zip_owner">
        <input type="text" name="delivery_goshippo_zip" id="delivery_goshippo_zip" class="delivery_goshippo_zip" placeholder="[%fill_zip%]"
               value="<?= isset($request['delivery_goshippo_zip'])?$request['delivery_goshippo_zip']:'' ?>"
        >
        [+delivery_goshippo_zip.error+]
    </div>
    <div class="delivery_goshippo_city_owner">
        <input type="text" name="delivery_goshippo_city" id="delivery_goshippo_city" class="delivery_goshippo_city" placeholder="[%fill_city%]"
               value="<?= isset($request['delivery_goshippo_city'])?$request['delivery_goshippo_city']:'' ?>"
        >
        [+delivery_goshippo_city.error+]
    </div>
    <div class="delivery_goshippo_street_owner">
    <input type="text" name="delivery_goshippo_street" id="delivery_goshippo_street" class="delivery_goshippo_street" placeholder="[%fill_street%]"
           value="<?= isset($request['delivery_goshippo_street'])?$request['delivery_goshippo_street']:'' ?>"
    >
        [+delivery_goshippo_street.error+]
    </div>
    <? if(!empty($rates)): ?>
    <div class="rates_owner">
        <select name="delivery_goshippo_rate" id="delivery_goshippo_rate">
            <option value="">[%select_rate%]</option>
            <?php foreach ($rates as $rate): ?>
                <option  <?= $rate['object_id'] == $request['delivery_goshippo_rate'] ? 'selected':'' ?> value="<?= $rate['object_id']?>"><?= $rate['title'] ?></option>
            <?php endforeach; ?>

        </select>
        [+delivery_goshippo_rate.error+]
    </div>
    <?endif; ?>
</div>