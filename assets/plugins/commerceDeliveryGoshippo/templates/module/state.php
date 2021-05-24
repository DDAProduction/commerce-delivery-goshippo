<select data-delivery="goshippo" id="delivery_goshippo_state" class="form-control" name="order[fields][delivery_goshippo_state]">
    <option value="">[%state%]</option>

    <?php foreach ($states as $state): ?>
        <option  <?= $selectedState['iso'] == $state['iso'] ? 'selected':'' ?> value="<?= $state['iso']?>"><?= $state['title'] ?></option>
    <?php endforeach; ?>


</select>