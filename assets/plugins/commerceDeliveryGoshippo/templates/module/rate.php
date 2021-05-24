<input type="text" name="order[fields][goshippo_rate]" id="delivery_goshippo_rate_full" value="<?= htmlentities(json_encode($selectedRate))?>">

<select data-delivery="goshippo" name="order[fields][delivery_goshippo_rate]" id="delivery_goshippo_rate">
    <option value="">[%select_rate%]</option>
    <?php foreach ($rates as $rate): ?>
        <option   value="<?= $rate['object_id']?>"><?= $rate['title'] ?></option>
    <?php endforeach; ?>

</select>