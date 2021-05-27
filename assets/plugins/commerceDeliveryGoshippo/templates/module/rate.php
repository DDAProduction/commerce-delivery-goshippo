<input type="hidden" name="order[fields][goshippo][rate]" id="delivery_goshippo_rate_full" value="<?= htmlentities(json_encode($selectedRate))?>">



<div class="row">
    <div class="col-10">
        <select data-delivery="goshippo" name="order[fields][delivery_goshippo_rate]" id="delivery_goshippo_rate">
            <option value="">[%select_rate%]</option>
            <?php foreach ($rates as $rate): ?>
                <option   value="<?= $rate['object_id']?>" <?= $rate['object_id'] == $selectedRate['object_id']?'selected':'' ?> ><?= $rate['title'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-2">
        <button class="btn btn-primary" id="update-rates" type="button">[%update%]</button>
    </div>
</div>