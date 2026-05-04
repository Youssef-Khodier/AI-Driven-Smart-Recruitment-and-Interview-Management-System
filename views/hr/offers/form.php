<h1><?= isset($replacesOfferId) && $replacesOfferId ? 'Create Replacement Offer' : 'Create Offer' ?></h1>
<form method="POST" action="<?= e(url('hr.offers.store', [$applicationId])) ?>">
    <?= csrf_field() ?>
    
    <div>
        <label>Offer Type:</label>
        <select name="offer_type" required>
            <?php foreach ($offerTypes as $type): ?>
                <option value="<?= e($type) ?>"><?= e($type) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div>
        <label>CTC (Annual):</label>
        <input type="number" step="0.01" min="0" name="ctc" required>
    </div>
    
    <div>
        <label>Bonus:</label>
        <input type="number" step="0.01" min="0" name="bonus" value="0">
    </div>
    
    <div>
        <label>Stock Options:</label>
        <input type="number" step="0.01" min="0" name="stock_options" value="0">
    </div>
    
    <div>
        <label>Expiry Date & Time:</label>
        <!-- Use Y-m-d\TH:i formatting for HTML5 datetime-local inputs if possible -->
        <input type="datetime-local" name="expiry_date" required>
    </div>
    
    <div>
        <button type="submit">Create Draft Offer</button>
    </div>
</form>
