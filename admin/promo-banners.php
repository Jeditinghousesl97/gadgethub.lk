<?php
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = 'Promo Banners';
    $activePage = 'promo-banners';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();
ensurePromoBannersTable();

if (isPost()) {
    $action  = post('action');
    $id      = (int)post('id');
    $linkUrl = post('link_url');
    $newTab  = post('open_in_new_tab', '0') === '1' ? 1 : 0;
    $sort    = (int)post('sort_order', '0');
    $active  = post('is_active', '1') === '1' ? 1 : 0;

    if ($action === 'add') {
        if (empty($_FILES['image']['name'])) {
            if (isAjax()) jsonErr('Banner image is required.');
            flash('error', 'Banner image is required.');
            redirect(BASE_URL . 'admin/promo-banners.php');
        }

        $image = uploadImage($_FILES['image'], 'promo-banners');
        if (!$image) {
            if (isAjax()) jsonErr('Image upload failed. Use JPG, PNG or WebP under 5MB.');
            flash('error', 'Image upload failed. Use JPG, PNG or WebP under 5MB.');
            redirect(BASE_URL . 'admin/promo-banners.php');
        }

        $db->prepare('INSERT INTO promo_banners (image_path,link_url,open_in_new_tab,sort_order,is_active) VALUES (?,?,?,?,?)')
           ->execute([$image, $linkUrl ?: null, $newTab, $sort, $active]);

        if (isAjax()) jsonOk('Promo banner added successfully.');
        flash('success', 'Promo banner added successfully.');
        redirect(BASE_URL . 'admin/promo-banners.php');
    }

    if ($action === 'edit' && $id > 0) {
        $s = $db->prepare('SELECT image_path FROM promo_banners WHERE id=?');
        $s->execute([$id]);
        $row = $s->fetch();
        if (!$row) {
            if (isAjax()) jsonErr('Banner not found.');
            flash('error', 'Banner not found.');
            redirect(BASE_URL . 'admin/promo-banners.php');
        }

        $image = $row['image_path'];
        if (!empty($_FILES['image']['name'])) {
            $newImage = uploadImage($_FILES['image'], 'promo-banners');
            if (!$newImage) {
                if (isAjax()) jsonErr('Banner image upload failed.');
                flash('error', 'Banner image upload failed.');
                redirect(BASE_URL . 'admin/promo-banners.php?edit=' . $id);
            }
            if ($image && file_exists(ROOT_PATH . $image)) @unlink(ROOT_PATH . $image);
            $image = $newImage;
        }

        $db->prepare('UPDATE promo_banners SET image_path=?, link_url=?, open_in_new_tab=?, sort_order=?, is_active=? WHERE id=?')
           ->execute([$image, $linkUrl ?: null, $newTab, $sort, $active, $id]);

        if (isAjax()) jsonOk('Promo banner updated successfully.');
        flash('success', 'Promo banner updated successfully.');
        redirect(BASE_URL . 'admin/promo-banners.php');
    }
}

$banners = $db->query('SELECT * FROM promo_banners ORDER BY sort_order ASC, id ASC')->fetchAll();

$editing = [];
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM promo_banners WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: [];
}
?>

<div class="page-header">
  <div>
    <h1>Promo Banners</h1>
    <p>Manage the homepage promo banner section with image and link only.</p>
  </div>
  <?php if ($editing): ?><div class="ph-actions"><a href="promo-banners.php" class="btn btn-ghost"><i class="fas fa-times"></i> Cancel Edit</a></div><?php endif ?>
</div>

<div style="display:grid;grid-template-columns:1fr 420px;gap:20px;align-items:start">
  <div class="card" style="padding:0;overflow:hidden">
    <div style="padding:18px 24px 0;display:flex;align-items:center;justify-content:space-between">
      <div class="card-title" style="margin:0">All Banners <span class="badge badge-gray" style="margin-left:8px" id="bannerCountBadge"><?= count($banners) ?></span></div>
    </div>
    <div style="padding:12px 0 0">
      <?php if ($banners): ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th style="width:42px"></th><th>#</th><th>Preview</th><th>Link</th><th style="text-align:center">Sort</th><th style="text-align:center">Status</th><th style="text-align:right">Actions</th></tr></thead>
          <tbody id="bannersSortableBody">
            <?php foreach ($banners as $i => $banner): ?>
            <tr draggable="true" data-id="<?= (int)$banner['id'] ?>">
              <td style="color:var(--text-dim);font-size:12px;cursor:grab"><i class="fas fa-grip-vertical"></i></td>
              <td style="color:var(--text-dim);font-size:12px"><?= $i + 1 ?></td>
              <td>
                <img src="<?= BASE_URL . htmlspecialchars($banner['image_path']) ?>" alt="Banner" style="width:160px;height:72px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
              </td>
              <td style="max-width:260px">
                <code style="font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block"><?= htmlspecialchars($banner['link_url'] ?: '#') ?></code>
                <?php if ((int)$banner['open_in_new_tab'] === 1): ?>
                  <span class="badge badge-blue" style="margin-top:6px">Opens New Tab</span>
                <?php endif ?>
              </td>
              <td style="text-align:center;color:var(--text-muted)"><?= (int)$banner['sort_order'] ?></td>
              <td style="text-align:center">
                <button type="button" class="badge <?= $banner['is_active'] ? 'badge-green' : 'badge-red' ?> js-toggle-banner" style="border:none;cursor:pointer;padding:3px 10px" data-id="<?= (int)$banner['id'] ?>">
                  <?= $banner['is_active'] ? 'Active' : 'Inactive' ?>
                </button>
              </td>
              <td style="text-align:right">
                <div style="display:flex;gap:6px;justify-content:flex-end">
                  <a href="promo-banners.php?edit=<?= (int)$banner['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
                  <button type="button" class="btn btn-danger btn-icon btn-sm js-delete-banner" title="Delete" data-id="<?= (int)$banner['id'] ?>"><i class="fas fa-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="empty-state"><i class="fas fa-image"></i><h3>No promo banners yet</h3><p>Add your first banner using the form.</p></div>
      <?php endif ?>
    </div>
  </div>

  <div class="card" style="position:sticky;top:80px">
    <div class="card-title"><?= $editing ? '<i class="fas fa-pen" style="color:var(--primary)"></i> Edit Banner' : '<i class="fas fa-plus" style="color:var(--primary)"></i> Add Banner' ?></div>
    <form method="POST" enctype="multipart/form-data" id="promoBannerForm">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$editing['id'] ?>"><?php endif ?>

      <div class="form-grid" style="gap:14px">
        <div class="form-group">
          <label class="form-label">Banner Image <?= $editing ? '' : '<span style="color:var(--red)">*</span>' ?></label>
          <?php if (!empty($editing['image_path'])): ?><img src="<?= BASE_URL . htmlspecialchars($editing['image_path']) ?>" alt="Banner" style="width:100%;height:160px;object-fit:cover;border-radius:8px;border:1px solid var(--border);margin-bottom:8px"><?php endif ?>
          <div id="promoBannerGuide" style="width:100%;aspect-ratio:16/7;border:1px dashed var(--border);border-radius:8px;background:var(--bg-3);margin-bottom:8px;display:flex;align-items:center;justify-content:center;color:var(--text-dim);font-size:12px">Banner Preview Guide</div>
          <input type="file" name="image" id="promoBannerInput" class="form-input" accept="image/*" <?= $editing ? '' : 'required' ?>>
          <span class="form-hint">Recommended landscape image for best homepage fit.</span>
        </div>

        <div class="form-group">
          <label class="form-label">Banner Link</label>
          <input type="text" name="link_url" class="form-input" placeholder="https://example.com or /shop.php" value="<?= htmlspecialchars($editing['link_url'] ?? '') ?>">
          <span class="form-hint">If empty, banner will link to <code>#</code>.</span>
        </div>

        <div class="form-group">
          <label class="form-label">Link Target</label>
          <select name="open_in_new_tab" class="form-select">
            <option value="0" <?= ((int)($editing['open_in_new_tab'] ?? 0) === 0) ? 'selected' : '' ?>>Open in Same Tab</option>
            <option value="1" <?= ((int)($editing['open_in_new_tab'] ?? 0) === 1) ? 'selected' : '' ?>>Open in New Tab</option>
          </select>
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
          <button type="submit" class="btn btn-gold" style="flex:1"><i class="fas <?= $editing ? 'fa-save' : 'fa-plus' ?>"></i> <?= $editing ? 'Save Changes' : 'Add Banner' ?></button>
          <?php if ($editing): ?><a href="promo-banners.php" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif ?>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('promoBannerForm').addEventListener('submit', function (e) {
  e.preventDefault();
  var form = this;
  var btn = form.querySelector('[type="submit"]');
  var orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
  fetch(location.pathname + location.search, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: new FormData(form)
  }).then(r => r.json()).then(res => {
    btn.disabled = false;
    btn.innerHTML = orig;
    adminSnackbar(res.message, res.success ? 'success' : 'error');
    if (res.success) setTimeout(() => window.location.href = location.pathname, 800);
  }).catch(() => {
    btn.disabled = false;
    btn.innerHTML = orig;
    adminSnackbar('Network error. Try again.', 'error');
  });
});

document.querySelectorAll('.js-delete-banner').forEach(btn => {
  btn.addEventListener('click', () => {
    adminDeleteRow(btn, '<?= BASE_URL ?>admin/api/delete-promo-banner.php', () => {
      var badge = document.getElementById('bannerCountBadge');
      if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent, 10) - 1);
    });
  });
});

document.querySelectorAll('.js-toggle-banner').forEach(btn => {
  btn.addEventListener('click', () => {
    fetch('<?= BASE_URL ?>admin/api/toggle-promo-banner.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: parseInt(btn.dataset.id, 10) })
    }).then(r => r.json()).then(res => {
      if (!res.success) { adminSnackbar('Update failed.', 'error'); return; }
      const active = res.is_active === 1;
      btn.className = 'badge ' + (active ? 'badge-green' : 'badge-red') + ' js-toggle-banner';
      btn.textContent = active ? 'Active' : 'Inactive';
      adminSnackbar('Banner ' + (active ? 'activated' : 'deactivated') + '.', 'success');
    }).catch(() => adminSnackbar('Network error. Try again.', 'error'));
  });
});

var promoBannerInput = document.getElementById('promoBannerInput');
var promoBannerGuide = document.getElementById('promoBannerGuide');
if (promoBannerInput && promoBannerGuide) {
  promoBannerInput.addEventListener('change', function () {
    var file = this.files && this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      promoBannerGuide.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:8px">';
    };
    reader.readAsDataURL(file);
  });
}

(function initBannerSort() {
  var tbody = document.getElementById('bannersSortableBody');
  if (!tbody) return;
  var dragEl = null;

  tbody.querySelectorAll('tr[draggable="true"]').forEach(function (row) {
    row.addEventListener('dragstart', function () {
      dragEl = row;
      row.style.opacity = '.5';
    });
    row.addEventListener('dragend', function () {
      row.style.opacity = '';
      dragEl = null;
    });
    row.addEventListener('dragover', function (e) {
      e.preventDefault();
      if (!dragEl || dragEl === row) return;
      var rect = row.getBoundingClientRect();
      var after = (e.clientY - rect.top) > (rect.height / 2);
      if (after) row.after(dragEl); else row.before(dragEl);
    });
  });

  tbody.addEventListener('drop', function () {
    var ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map(function (tr) { return parseInt(tr.dataset.id, 10); });
    fetch('<?= BASE_URL ?>admin/api/reorder-promo-banners.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ids: ids })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (!res.success) { adminSnackbar('Sort update failed.', 'error'); return; }
      adminSnackbar('Banner order updated.', 'success');
      Array.from(tbody.querySelectorAll('tr')).forEach(function (tr, i) {
        var numCell = tr.children[1];
        if (numCell) numCell.textContent = i + 1;
      });
    })
    .catch(function () { adminSnackbar('Network error while sorting.', 'error'); });
  });
})();
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
