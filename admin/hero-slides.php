<?php
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = 'Hero Slides';
    $activePage = 'hero-slides';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();
ensureHeroSlidesTable();

if (isPost()) {
    $action  = post('action');
    $id      = (int)post('id');
    $linkUrl = post('link_url');
    $newTab  = post('open_in_new_tab', '0') === '1' ? 1 : 0;
    $sort    = (int)post('sort_order', '0');
    $active  = post('is_active', '1') === '1' ? 1 : 0;

    if ($action === 'add') {
        if (empty($_FILES['desktop_image']['name']) || empty($_FILES['mobile_image']['name'])) {
            if (isAjax()) jsonErr('Desktop and mobile images are required.');
            flash('error', 'Desktop and mobile images are required.');
            redirect(BASE_URL . 'admin/hero-slides.php');
        }

        $desktop = uploadImage($_FILES['desktop_image'], 'hero');
        $mobile  = uploadImage($_FILES['mobile_image'], 'hero');
        if (!$desktop || !$mobile) {
            if ($desktop && file_exists(ROOT_PATH . $desktop)) @unlink(ROOT_PATH . $desktop);
            if ($mobile && file_exists(ROOT_PATH . $mobile)) @unlink(ROOT_PATH . $mobile);
            if (isAjax()) jsonErr('Image upload failed. Use JPG, PNG or WebP under 5MB.');
            flash('error', 'Image upload failed. Use JPG, PNG or WebP under 5MB.');
            redirect(BASE_URL . 'admin/hero-slides.php');
        }

        $db->prepare('INSERT INTO hero_slides (desktop_image,mobile_image,link_url,open_in_new_tab,sort_order,is_active) VALUES (?,?,?,?,?,?)')
           ->execute([$desktop, $mobile, $linkUrl ?: null, $newTab, $sort, $active]);

        if (isAjax()) jsonOk('Hero slide added successfully.');
        flash('success', 'Hero slide added successfully.');
        redirect(BASE_URL . 'admin/hero-slides.php');
    }

    if ($action === 'edit' && $id > 0) {
        $s = $db->prepare('SELECT desktop_image,mobile_image FROM hero_slides WHERE id=?');
        $s->execute([$id]);
        $row = $s->fetch();
        if (!$row) {
            if (isAjax()) jsonErr('Slide not found.');
            flash('error', 'Slide not found.');
            redirect(BASE_URL . 'admin/hero-slides.php');
        }

        $desktop = $row['desktop_image'];
        $mobile  = $row['mobile_image'];

        if (!empty($_FILES['desktop_image']['name'])) {
            $newDesktop = uploadImage($_FILES['desktop_image'], 'hero');
            if (!$newDesktop) {
                if (isAjax()) jsonErr('Desktop image upload failed.');
                flash('error', 'Desktop image upload failed.');
                redirect(BASE_URL . 'admin/hero-slides.php?edit=' . $id);
            }
            if ($desktop && file_exists(ROOT_PATH . $desktop)) @unlink(ROOT_PATH . $desktop);
            $desktop = $newDesktop;
        }

        if (!empty($_FILES['mobile_image']['name'])) {
            $newMobile = uploadImage($_FILES['mobile_image'], 'hero');
            if (!$newMobile) {
                if (isAjax()) jsonErr('Mobile image upload failed.');
                flash('error', 'Mobile image upload failed.');
                redirect(BASE_URL . 'admin/hero-slides.php?edit=' . $id);
            }
            if ($mobile && file_exists(ROOT_PATH . $mobile)) @unlink(ROOT_PATH . $mobile);
            $mobile = $newMobile;
        }

        $db->prepare('UPDATE hero_slides SET desktop_image=?, mobile_image=?, link_url=?, open_in_new_tab=?, sort_order=?, is_active=? WHERE id=?')
           ->execute([$desktop, $mobile, $linkUrl ?: null, $newTab, $sort, $active, $id]);

        if (isAjax()) jsonOk('Hero slide updated successfully.');
        flash('success', 'Hero slide updated successfully.');
        redirect(BASE_URL . 'admin/hero-slides.php');
    }
}

$slides = $db->query('SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC')->fetchAll();

$editing = [];
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM hero_slides WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: [];
}
?>

<div class="page-header">
  <div>
    <h1>Hero Slides</h1>
    <p>Manage full-screen hero slider images for Home page.</p>
  </div>
  <?php if ($editing): ?><div class="ph-actions"><a href="hero-slides.php" class="btn btn-ghost"><i class="fas fa-times"></i> Cancel Edit</a></div><?php endif ?>
</div>

<div style="display:grid;grid-template-columns:1fr 420px;gap:20px;align-items:start">
  <div class="card" style="padding:0;overflow:hidden">
    <div style="padding:18px 24px 0;display:flex;align-items:center;justify-content:space-between">
      <div class="card-title" style="margin:0">All Slides <span class="badge badge-gray" style="margin-left:8px" id="slideCountBadge"><?= count($slides) ?></span></div>
    </div>
    <div style="padding:12px 0 0">
      <?php if ($slides): ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th style="width:42px"></th><th>#</th><th>Preview</th><th>Link</th><th style="text-align:center">Sort</th><th style="text-align:center">Status</th><th style="text-align:right">Actions</th></tr></thead>
          <tbody id="slidesSortableBody">
            <?php foreach ($slides as $i => $s): ?>
            <tr draggable="true" data-id="<?= (int)$s['id'] ?>">
              <td style="color:var(--text-dim);font-size:12px;cursor:grab"><i class="fas fa-grip-vertical"></i></td>
              <td style="color:var(--text-dim);font-size:12px"><?= $i + 1 ?></td>
              <td>
                <div style="display:flex;gap:10px;align-items:center">
                  <img src="<?= BASE_URL . htmlspecialchars($s['desktop_image']) ?>" alt="Desktop" style="width:110px;height:52px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
                  <img src="<?= BASE_URL . htmlspecialchars($s['mobile_image']) ?>" alt="Mobile" style="width:38px;height:52px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
                </div>
              </td>
              <td style="max-width:260px">
                <code style="font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block"><?= htmlspecialchars($s['link_url'] ?: '#') ?></code>
                <?php if ((int)$s['open_in_new_tab'] === 1): ?>
                  <span class="badge badge-blue" style="margin-top:6px">Opens New Tab</span>
                <?php endif ?>
              </td>
              <td style="text-align:center;color:var(--text-muted)"><?= (int)$s['sort_order'] ?></td>
              <td style="text-align:center">
                <button type="button" class="badge <?= $s['is_active'] ? 'badge-green' : 'badge-red' ?> js-toggle-slide" style="border:none;cursor:pointer;padding:3px 10px" data-id="<?= (int)$s['id'] ?>">
                  <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                </button>
              </td>
              <td style="text-align:right">
                <div style="display:flex;gap:6px;justify-content:flex-end">
                  <a href="hero-slides.php?edit=<?= (int)$s['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
                  <button type="button" class="btn btn-danger btn-icon btn-sm js-delete-slide" title="Delete" data-id="<?= (int)$s['id'] ?>"><i class="fas fa-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="empty-state"><i class="fas fa-images"></i><h3>No slides yet</h3><p>Add your first hero slide using the form.</p></div>
      <?php endif ?>
    </div>
  </div>

  <div class="card" style="position:sticky;top:80px">
    <div class="card-title"><?= $editing ? '<i class="fas fa-pen" style="color:var(--primary)"></i> Edit Slide' : '<i class="fas fa-plus" style="color:var(--primary)"></i> Add Slide' ?></div>
    <form method="POST" enctype="multipart/form-data" id="heroForm">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$editing['id'] ?>"><?php endif ?>
      <div class="form-grid" style="gap:14px">

        <div class="form-group">
          <label class="form-label">Desktop Image <?= $editing ? '' : '<span style="color:var(--red)">*</span>' ?></label>
          <?php if (!empty($editing['desktop_image'])): ?><img src="<?= BASE_URL . htmlspecialchars($editing['desktop_image']) ?>" alt="Desktop" style="width:100%;height:110px;object-fit:cover;border-radius:8px;border:1px solid var(--border);margin-bottom:8px"><?php endif ?>
          <div id="desktopGuide" style="width:100%;aspect-ratio:16/6;border:1px dashed var(--border);border-radius:8px;background:var(--bg-3);margin-bottom:8px;display:flex;align-items:center;justify-content:center;color:var(--text-dim);font-size:12px">Desktop Ratio Guide (16:6)</div>
          <input type="file" name="desktop_image" id="desktopInput" class="form-input" accept="image/*" <?= $editing ? '' : 'required' ?>>
        </div>

        <div class="form-group">
          <label class="form-label">Mobile Image <?= $editing ? '' : '<span style="color:var(--red)">*</span>' ?></label>
          <?php if (!empty($editing['mobile_image'])): ?><img src="<?= BASE_URL . htmlspecialchars($editing['mobile_image']) ?>" alt="Mobile" style="width:130px;height:160px;object-fit:cover;border-radius:8px;border:1px solid var(--border);margin-bottom:8px"><?php endif ?>
          <div id="mobileGuide" style="width:130px;aspect-ratio:9/16;border:1px dashed var(--border);border-radius:8px;background:var(--bg-3);margin-bottom:8px;display:flex;align-items:center;justify-content:center;color:var(--text-dim);font-size:12px">9:16</div>
          <input type="file" name="mobile_image" id="mobileInput" class="form-input" accept="image/*" <?= $editing ? '' : 'required' ?>>
        </div>

        <div class="form-group">
          <label class="form-label">Slide Link</label>
          <input type="text" name="link_url" class="form-input" placeholder="https://example.com or /shop.php" value="<?= htmlspecialchars($editing['link_url'] ?? '') ?>">
          <span class="form-hint">If empty, slide will link to <code>#</code>.</span>
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
          <button type="submit" class="btn btn-gold" style="flex:1"><i class="fas <?= $editing ? 'fa-save' : 'fa-plus' ?>"></i> <?= $editing ? 'Save Changes' : 'Add Slide' ?></button>
          <?php if ($editing): ?><a href="hero-slides.php" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif ?>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('heroForm').addEventListener('submit', function (e) {
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

document.querySelectorAll('.js-delete-slide').forEach(btn => {
  btn.addEventListener('click', () => {
    adminDeleteRow(btn, '<?= BASE_URL ?>admin/api/delete-hero-slide.php', () => {
      var badge = document.getElementById('slideCountBadge');
      if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent, 10) - 1);
    });
  });
});

document.querySelectorAll('.js-toggle-slide').forEach(btn => {
  btn.addEventListener('click', () => {
    fetch('<?= BASE_URL ?>admin/api/toggle-hero-slide.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: parseInt(btn.dataset.id, 10) })
    }).then(r => r.json()).then(res => {
      if (!res.success) { adminSnackbar('Update failed.', 'error'); return; }
      const active = res.is_active === 1;
      btn.className = 'badge ' + (active ? 'badge-green' : 'badge-red') + ' js-toggle-slide';
      btn.textContent = active ? 'Active' : 'Inactive';
      adminSnackbar('Slide ' + (active ? 'activated' : 'deactivated') + '.', 'success');
    }).catch(() => adminSnackbar('Network error. Try again.', 'error'));
  });
});

// Live ratio guide previews
function bindImagePreview(inputId, guideId) {
  var input = document.getElementById(inputId);
  var guide = document.getElementById(guideId);
  if (!input || !guide) return;
  input.addEventListener('change', function () {
    var file = this.files && this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      guide.innerHTML = '<img src=\"' + e.target.result + '\" style=\"width:100%;height:100%;object-fit:cover;border-radius:8px\">';
    };
    reader.readAsDataURL(file);
  });
}
bindImagePreview('desktopInput', 'desktopGuide');
bindImagePreview('mobileInput', 'mobileGuide');

// Drag-and-drop sorting
(function initSlideSort() {
  var tbody = document.getElementById('slidesSortableBody');
  if (!tbody) return;
  var dragEl = null;

  tbody.querySelectorAll('tr[draggable=\"true\"]').forEach(function (row) {
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
    fetch('<?= BASE_URL ?>admin/api/reorder-hero-slides.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ids: ids })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (!res.success) { adminSnackbar('Sort update failed.', 'error'); return; }
      adminSnackbar('Slide order updated.', 'success');
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
