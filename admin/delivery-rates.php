<?php
$pageTitle = 'Delivery Fees';
$activePage = 'delivery-rates';
require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$db = getDB();
ensureDeliveryRatesTable();

$districts = getSriLankaDistricts();

// Seed missing districts once with zero values
$seedStmt = $db->prepare(
    "INSERT INTO delivery_rates (district, first_kg_fee, additional_kg_fee, is_active)
     VALUES (?, 0, 0, 1)
     ON DUPLICATE KEY UPDATE district = VALUES(district)"
);
foreach ($districts as $d) $seedStmt->execute([$d]);

if (isPost()) {
    $first = $_POST['first_kg_fee'] ?? [];
    $add   = $_POST['additional_kg_fee'] ?? [];
    $act   = $_POST['is_active'] ?? [];

    $up = $db->prepare(
        "UPDATE delivery_rates
         SET first_kg_fee = ?, additional_kg_fee = ?, is_active = ?
         WHERE district = ?"
    );

    foreach ($districts as $d) {
        $f = max(0, (float)($first[$d] ?? 0));
        $a = max(0, (float)($add[$d] ?? 0));
        $on = isset($act[$d]) ? 1 : 0;
        $up->execute([$f, $a, $on, $d]);
    }

    flash('success', 'Delivery fee matrix updated successfully.');
    redirect(BASE_URL . 'admin/delivery-rates.php');
}

$rows = $db->query("SELECT district, first_kg_fee, additional_kg_fee, is_active FROM delivery_rates ORDER BY district")->fetchAll();
$rateMap = [];
foreach ($rows as $r) $rateMap[$r['district']] = $r;
?>

<div class="page-header">
  <div>
    <h1>District Delivery Fees</h1>
    <p>Set delivery fee per district by first 1kg and each additional 1kg.</p>
  </div>
</div>

<form method="POST" class="card" style="padding:0;overflow:hidden">
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>District</th>
          <th>First 1kg (Rs.)</th>
          <th>Additional 1kg (Rs.)</th>
          <th>Active</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($districts as $d):
        $r = $rateMap[$d] ?? ['first_kg_fee' => 0, 'additional_kg_fee' => 0, 'is_active' => 1];
      ?>
        <tr>
          <td style="font-weight:600;color:var(--text)"><?= htmlspecialchars($d) ?></td>
          <td>
            <input type="number" class="form-input" name="first_kg_fee[<?= htmlspecialchars($d) ?>]" min="0" step="0.01"
              value="<?= htmlspecialchars((string)$r['first_kg_fee']) ?>" style="max-width:180px">
          </td>
          <td>
            <input type="number" class="form-input" name="additional_kg_fee[<?= htmlspecialchars($d) ?>]" min="0" step="0.01"
              value="<?= htmlspecialchars((string)$r['additional_kg_fee']) ?>" style="max-width:180px">
          </td>
          <td>
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" name="is_active[<?= htmlspecialchars($d) ?>]" value="1" <?= ((int)$r['is_active'] === 1) ? 'checked' : '' ?>>
              <span style="font-size:12px;color:var(--text-muted)">Enabled</span>
            </label>
          </td>
        </tr>
      <?php endforeach ?>
      </tbody>
    </table>
  </div>

  <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px">
    <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Delivery Fees</button>
  </div>
</form>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
