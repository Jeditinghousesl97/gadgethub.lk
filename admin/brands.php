<?php
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = 'Brands';
    $activePage = 'brands';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();
ensureBrandsTable();

// One-time seed: if admin brands are empty, import currently visible brands logic
// from active product brand values so /admin/brands.php matches /brands.php content.
$brandTableCount = (int)$db->query('SELECT COUNT(*) FROM brands')->fetchColumn();
if ($brandTableCount === 0) {
    $seedRows = $db->query("
        SELECT brand
        FROM products
        WHERE brand IS NOT NULL AND brand != '' AND is_active = 1
        GROUP BY LOWER(brand), brand
        ORDER BY brand ASC
    ")->fetchAll();

    if ($seedRows) {
        $ins = $db->prepare('
            INSERT INTO brands (name, slug, description, filter_tags, logo_path, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ');
        $i = 0;
        foreach ($seedRows as $r) {
            $name = trim((string)$r['brand']);
            if ($name === '') continue;
            $baseSlug = slugify($name);
            $slug = $baseSlug;
            $n = 1;
            $chk = $db->prepare('SELECT id FROM brands WHERE slug = ? LIMIT 1');
            while (true) {
                $chk->execute([$slug]);
                if (!$chk->fetch()) break;
                $slug = $baseSlug . '-' . $n++;
            }

            $legacyLogo = 'images/brands/' . $slug . '.png';
            $logoPath   = file_exists(ROOT_PATH . $legacyLogo) ? $legacyLogo : null;
            $desc       = 'Genuine ' . $name . ' products available in store.';

            $ins->execute([$name, $slug, $desc, 'other', $logoPath, $i++]);
        }
    }
}

if (isPost()) {
    $action = post('action');

    if ($action === 'add' || $action === 'edit') {
        $id       = (int)post('id');
        $name     = post('name');
        $slug     = post('slug') ?: slugify($name);
        $desc     = post('description');
        $filters  = post('filter_tags', 'other');
        $sort     = (int)post('sort_order', '0');
        $active   = post('is_active', '1') === '1' ? 1 : 0;

        if (!$name) {
            if (isAjax()) jsonErr('Brand name is required.');
            flash('error', 'Brand name is required.');
            redirect(BASE_URL . 'admin/brands.php');
        }

        $logoPath = null;
        if ($action === 'edit' && $id > 0) {
            $s = $db->prepare('SELECT logo_path FROM brands WHERE id=?');
            $s->execute([$id]);
            $logoPath = $s->fetchColumn() ?: null;
        }

        if (!empty($_FILES['logo']['name'])) {
            $uploaded = uploadImage($_FILES['logo'], 'brands');
            if ($uploaded) {
                if ($logoPath && str_starts_with($logoPath, 'uploads/') && file_exists(ROOT_PATH . $logoPath)) {
                    @unlink(ROOT_PATH . $logoPath);
                }
                $logoPath = $uploaded;
            } else {
                if (isAjax()) jsonErr('Logo upload failed. Use JPG, PNG or WebP under 5MB.');
                flash('error', 'Logo upload failed. Use JPG, PNG or WebP under 5MB.');
                redirect(BASE_URL . 'admin/brands.php' . ($id ? ('?edit=' . $id) : ''));
            }
        }

        try {
            if ($action === 'add') {
                $base = $slug; $i = 1;
                $chk = $db->prepare('SELECT id FROM brands WHERE slug=? LIMIT 1');
                while (true) {
                    $chk->execute([$slug]);
                    if (!$chk->fetch()) break;
                    $slug = $base . '-' . $i++;
                }

                $db->prepare('INSERT INTO brands (name,slug,description,filter_tags,logo_path,sort_order,is_active) VALUES (?,?,?,?,?,?,?)')
                   ->execute([$name, $slug, $desc ?: null, $filters ?: 'other', $logoPath, $sort, $active]);

                if (isAjax()) jsonOk("Brand \"$name\" added successfully.");
                flash('success', "Brand \"$name\" added successfully.");
            } else {
                $db->prepare('UPDATE brands SET name=?,slug=?,description=?,filter_tags=?,logo_path=?,sort_order=?,is_active=? WHERE id=?')
                   ->execute([$name, $slug, $desc ?: null, $filters ?: 'other', $logoPath, $sort, $active, $id]);

                if (isAjax()) jsonOk('Brand updated successfully.');
                flash('success', 'Brand updated successfully.');
            }
        } catch (PDOException $e) {
            if (isAjax()) jsonErr('Slug already in use by another brand.');
            flash('error', 'Slug already in use by another brand.');
        }

        redirect(BASE_URL . 'admin/brands.php');
    }
}

$brands = $db->query("
    SELECT b.*,
           (
               SELECT COUNT(*)
               FROM products p
               WHERE LOWER(p.brand) COLLATE utf8mb4_unicode_ci = LOWER(b.name) COLLATE utf8mb4_unicode_ci
                 AND p.is_active = 1
           ) AS product_count
    FROM brands b
    ORDER BY b.sort_order ASC, b.name ASC
")->fetchAll();

$editing = [];
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM brands WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: [];
}
?>

<div class="page-header">
  <div>
    <h1>Brands</h1>
    <p>Manage brand cards shown on the public Brands page.</p>
  </div>
  <?php if ($editing): ?>
  <div class="ph-actions">
    <a href="brands.php" class="btn btn-ghost"><i class="fas fa-times"></i> Cancel Edit</a>
  </div>
  <?php endif ?>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start">

  <div class="card" style="padding:0;overflow:hidden">
    <div style="padding:18px 24px 0;display:flex;align-items:center;justify-content:space-between">
      <div class="card-title" style="margin:0">All Brands <span class="badge badge-gray" style="margin-left:8px" id="brandCountBadge"><?= count($brands) ?></span></div>
    </div>
    <div style="padding:12px 0 0">
    <?php if ($brands): ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:40px">#</th>
              <th>Brand</th>
              <th>Filter Tags</th>
              <th style="text-align:center">Products</th>
              <th style="text-align:center">Sort</th>
              <th style="text-align:center">Status</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($brands as $i => $b): ?>
            <tr>
              <td style="color:var(--text-dim);font-size:12px"><?= $i + 1 ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:12px">
                  <div style="width:40px;height:40px;border-radius:8px;background:var(--bg-3);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;border:1px solid var(--border)">
                    <?php if (!empty($b['logo_path']) && file_exists(ROOT_PATH . $b['logo_path'])): ?>
                      <img src="<?= BASE_URL . htmlspecialchars($b['logo_path']) ?>" alt="<?= htmlspecialchars($b['name']) ?>" style="max-width:32px;max-height:32px;object-fit:contain">
                    <?php else: ?>
                      <i class="fas fa-tag" style="color:var(--primary)"></i>
                    <?php endif ?>
                  </div>
                  <div>
                    <div style="font-weight:600;color:var(--text);font-size:13.5px"><?= htmlspecialchars($b['name']) ?></div>
                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px"><code style="font-size:11px"><?= htmlspecialchars($b['slug']) ?></code></div>
                  </div>
                </div>
              </td>
              <td><code style="font-size:12px;color:var(--text-muted);background:var(--bg-3);padding:2px 8px;border-radius:4px"><?= htmlspecialchars($b['filter_tags']) ?></code></td>
              <td style="text-align:center"><span class="badge <?= $b['product_count'] > 0 ? 'badge-blue' : 'badge-gray' ?>"><?= (int)$b['product_count'] ?></span></td>
              <td style="text-align:center;color:var(--text-muted);font-size:13px"><?= (int)$b['sort_order'] ?></td>
              <td style="text-align:center">
                <button type="button"
                  class="badge <?= $b['is_active'] ? 'badge-green' : 'badge-red' ?> js-toggle-brand"
                  style="border:none;cursor:pointer;padding:3px 10px"
                  data-id="<?= (int)$b['id'] ?>"
                  data-active="<?= (int)$b['is_active'] ?>">
                  <?= $b['is_active'] ? 'Active' : 'Inactive' ?>
                </button>
              </td>
              <td style="text-align:right">
                <div style="display:flex;gap:6px;justify-content:flex-end">
                  <a href="brands.php?edit=<?= (int)$b['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
                  <button type="button" class="btn btn-danger btn-icon btn-sm js-delete-brand" title="Delete" data-id="<?= (int)$b['id'] ?>"><i class="fas fa-trash"></i></button>
                </div>
              </td>
            </tr>
          <?php endforeach ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-tags"></i>
        <h3>No brands yet</h3>
        <p>Add your first brand using the form.</p>
      </div>
    <?php endif ?>
    </div>
  </div>

  <div class="card" style="position:sticky;top:80px">
    <div class="card-title"><?= $editing ? '<i class="fas fa-pen" style="color:var(--primary)"></i> Edit Brand' : '<i class="fas fa-plus" style="color:var(--primary)"></i> Add Brand' ?></div>

    <form method="POST" enctype="multipart/form-data" id="brandForm">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$editing['id'] ?>"><?php endif ?>

      <div class="form-grid" style="gap:14px">
        <div class="form-group">
          <label class="form-label">Brand Name <span style="color:var(--red)">*</span></label>
          <input type="text" name="name" class="form-input" placeholder="e.g. Intel" required value="<?= htmlspecialchars($editing['name'] ?? '') ?>" id="brandName">
        </div>

        <div class="form-group">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-input" placeholder="auto-generated" value="<?= htmlspecialchars($editing['slug'] ?? '') ?>" id="brandSlug">
          <span class="form-hint">Used for fallback image lookup in <code>images/brands/{slug}.png</code></span>
        </div>

        <div class="form-group">
          <label class="form-label">Filter Tags</label>
          <input type="text" name="filter_tags" class="form-input" placeholder="components storage peripherals" value="<?= htmlspecialchars($editing['filter_tags'] ?? 'other') ?>">
          <span class="form-hint">Space-separated tags used by filter tabs.</span>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-textarea" rows="4" placeholder="Short brand description..."><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Brand Logo</label>
          <?php if (!empty($editing['logo_path']) && file_exists(ROOT_PATH . $editing['logo_path'])): ?>
            <div style="margin-bottom:10px;padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--bg-3)">
              <img src="<?= BASE_URL . htmlspecialchars($editing['logo_path']) ?>" alt="Logo" style="max-height:40px;max-width:100%;object-fit:contain">
            </div>
          <?php endif ?>
          <input type="file" name="logo" class="form-input" accept="image/*">
          <span class="form-hint">Optional. Upload JPG/PNG/WebP under 5MB.</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input" min="0" value="<?= (int)($editing['sort_order'] ?? 0) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
              <option value="1" <?= ((int)($editing['is_active'] ?? 1) === 1) ? 'selected' : '' ?>>Active</option>
              <option value="0" <?= ((int)($editing['is_active'] ?? 1) === 0) ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:4px">
          <button type="submit" class="btn btn-gold" style="flex:1"><i class="fas <?= $editing ? 'fa-save' : 'fa-plus' ?>"></i> <?= $editing ? 'Save Changes' : 'Add Brand' ?></button>
          <?php if ($editing): ?><a href="brands.php" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif ?>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
const brandName = document.getElementById('brandName');
const brandSlug = document.getElementById('brandSlug');
<?php if (!$editing): ?>
brandName.addEventListener('input', () => {
  brandSlug.value = brandName.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/[\s]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
});
<?php endif ?>

document.getElementById('brandForm').addEventListener('submit', function (e) {
  e.preventDefault();
  var form = this;
  var btn  = form.querySelector('[type="submit"]');
  var orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
  fetch(location.pathname + location.search, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: new FormData(form)
  })
  .then(function (r) { return r.json(); })
  .then(function (res) {
    btn.disabled = false;
    btn.innerHTML = orig;
    adminSnackbar(res.message, res.success ? 'success' : 'error');
    if (res.success) setTimeout(function () { window.location.href = location.pathname; }, 800);
  })
  .catch(function () {
    btn.disabled = false;
    btn.innerHTML = orig;
    adminSnackbar('Network error. Try again.', 'error');
  });
});

document.querySelectorAll('.js-delete-brand').forEach(function (btn) {
  btn.addEventListener('click', function () {
    adminDeleteRow(btn, '<?= BASE_URL ?>admin/api/delete-brand.php', function () {
      var badge = document.getElementById('brandCountBadge');
      if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent, 10) - 1);
    });
  });
});

document.querySelectorAll('.js-toggle-brand').forEach(function (btn) {
  btn.addEventListener('click', function () {
    fetch('<?= BASE_URL ?>admin/api/toggle-brand.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: parseInt(btn.dataset.id, 10) })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (!res.success) { adminSnackbar('Update failed.', 'error'); return; }
      var active = res.is_active === 1;
      btn.className = 'badge ' + (active ? 'badge-green' : 'badge-red') + ' js-toggle-brand';
      btn.textContent = active ? 'Active' : 'Inactive';
      btn.dataset.active = res.is_active;
      adminSnackbar('Brand ' + (active ? 'activated' : 'deactivated') + '.', 'success');
    })
    .catch(function () { adminSnackbar('Network error. Try again.', 'error'); });
  });
});
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
