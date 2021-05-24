<select id="delivery_goshippo_country" data-delivery="goshippo" class="form-control" name="order[fields][delivery_goshippo_country]">
    <option value="">[%select_country%]</option>

    <?php foreach ($countries as $iso => $country): ?>
        <option data-state="<?= $country['require_state'] ?>" <?= $selectedCountry['iso'] == $iso ? 'selected':'' ?> value="<?= $iso?>"><?= $country['title'] ?></option>
    <?php endforeach; ?>

</select>