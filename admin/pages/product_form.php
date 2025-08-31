<?php
// $product_id is set if editing, else null
$editing = !empty($product['product_id']);
$pid = $product['product_id'] ?? 0;

// load existing grouped specs for edit
$grouped_specs = [];
if ($editing) {
  $sql = "SELECT spec_group,spec_name,spec_value,display_order FROM product_specs WHERE product_id=$pid ORDER BY COALESCE(spec_group,'') , display_order";
  $res = mysqli_query($conn, $sql);
  while ($r = mysqli_fetch_assoc($res)) {
    $g = $r['spec_group'] ?: 'General';
    $grouped_specs[$g][] = $r;
  }
}
?>
<div class="card mt-3">
  <div class="card-header">Product Specifications</div>
  <div class="card-body">
    <div id="specGroupsContainer">
      <?php if (!empty($grouped_specs)): ?>
        <?php foreach ($grouped_specs as $group => $rows): ?>
          <div class="spec-group mb-3 border rounded p-2">
            <div class="d-flex mb-2">
              <input name="spec_group_name[]" class="form-control me-2" value="<?= htmlspecialchars($group) ?>">
              <button type="button" class="btn btn-outline-danger btn-sm ms-auto remove-group-btn">Remove Group</button>
            </div>

            <div class="spec-rows">
              <?php foreach ($rows as $row): ?>
                <div class="input-group mb-2 spec-row">
                  <input name="spec_name_<?= htmlspecialchars($group) ?>[]" class="form-control" placeholder="Spec name" value="<?= htmlspecialchars($row['spec_name']) ?>">
                  <input name="spec_value_<?= htmlspecialchars($group) ?>[]" class="form-control" placeholder="Spec value" value="<?= htmlspecialchars($row['spec_value']) ?>">
                  <input type="number" name="spec_order_<?= htmlspecialchars($group) ?>[]" class="form-control w-25" placeholder="Order" value="<?= (int)$row['display_order'] ?>">
                  <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
                </div>
              <?php endforeach; ?>
            </div>

            <div>
              <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- initial empty group -->
        <div class="spec-group mb-3 border rounded p-2">
          <div class="d-flex mb-2">
            <input name="spec_group_name[]" class="form-control me-2" value="General">
            <button type="button" class="btn btn-outline-danger btn-sm ms-auto remove-group-btn">Remove Group</button>
          </div>
          <div class="spec-rows">
            <div class="input-group mb-2 spec-row">
              <input name="spec_name_General[]" class="form-control" placeholder="Spec name">
              <input name="spec_value_General[]" class="form-control" placeholder="Spec value">
              <input type="number" name="spec_order_General[]" class="form-control w-25" placeholder="Order" value="10">
              <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
            </div>
          </div>
          <div>
            <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="mt-2">
      <button id="addGroupBtn" type="button" class="btn btn-sm btn-success">+ Add Group</button>
    </div>
    <small class="text-muted d-block mt-2">Groups help show related specs (e.g., Processor). Fields posted as arrays.</small>
  </div>
</div>

<script>
  (function() {
    const container = document.getElementById('specGroupsContainer');

    function makeGroupHtml(groupName = 'Group') {
      const safe = groupName.replace(/[^a-zA-Z0-9_-]/g, '_');
      return `
    <div class="spec-group mb-3 border rounded p-2">
      <div class="d-flex mb-2">
        <input name="spec_group_name[]" class="form-control me-2" value="${groupName}">
        <button type="button" class="btn btn-outline-danger btn-sm ms-auto remove-group-btn">Remove Group</button>
      </div>
      <div class="spec-rows">
        <div class="input-group mb-2 spec-row">
          <input name="spec_name_${safe}[]" class="form-control" placeholder="Spec name">
          <input name="spec_value_${safe}[]" class="form-control" placeholder="Spec value">
          <input type="number" name="spec_order_${safe}[]" class="form-control w-25" placeholder="Order" value="10">
          <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>
        </div>
      </div>
      <div>
        <button type="button" class="btn btn-sm btn-primary add-row-btn">+ Add spec</button>
      </div>
    </div>`;
    }

    // delegate clicks
    container.addEventListener('click', function(e) {
      if (e.target.matches('#addGroupBtn')) return;
      if (e.target.closest('#addGroupBtn')) return;
    });

    document.getElementById('addGroupBtn').addEventListener('click', function() {
      const groupName = prompt('Group name (e.g. Processor, Performance, General):', 'New Group') || 'Group';
      container.insertAdjacentHTML('beforeend', makeGroupHtml(groupName));
    });

    // delegated remove group / add row / remove row / add-row-btn
    container.addEventListener('click', function(e) {
      if (e.target.matches('.remove-group-btn')) {
        e.target.closest('.spec-group').remove();
      }
      if (e.target.matches('.add-row-btn')) {
        const groupEl = e.target.closest('.spec-group');
        const groupName = groupEl.querySelector('input[name="spec_group_name[]"]').value || 'Group';
        const safe = groupName.replace(/[^a-zA-Z0-9_-]/g, '_');
        const row = document.createElement('div');
        row.className = 'input-group mb-2 spec-row';
        row.innerHTML = `<input name="spec_name_${safe}[]" class="form-control" placeholder="Spec name">
                       <input name="spec_value_${safe}[]" class="form-control" placeholder="Spec value">
                       <input type="number" name="spec_order_${safe}[]" class="form-control w-25" placeholder="Order" value="10">
                       <button type="button" class="btn btn-outline-secondary remove-row-btn">−</button>`;
        groupEl.querySelector('.spec-rows').appendChild(row);
      }
      if (e.target.matches('.remove-row-btn')) {
        e.target.closest('.spec-row').remove();
      }
    });
  })();
</script>