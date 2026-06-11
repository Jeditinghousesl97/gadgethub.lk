<?php
$_isAjax = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($_isAjax) {
    require_once dirname(__DIR__) . '/includes/functions.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!isAdminLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Session expired.']); exit; }
} else {
    $pageTitle  = 'Settings';
    $activePage = 'settings';
    require_once __DIR__ . '/includes/layout.php';
    require_once dirname(__DIR__) . '/includes/functions.php';
}

$db = getDB();

// Handle saves 
if (isPost()) {
    $section = post('section');

    // Store info
    if ($section === 'store') {
        $fields = ['store_name','store_tagline','store_phone','store_whatsapp','store_email','store_address','store_hours','store_map_embed','store_map_link'];
        foreach ($fields as $f) setSetting($f, post($f));
        if (isAjax()) jsonOk('Store information saved.');
        flash('success', 'Store information saved.');
        redirect(BASE_URL . 'admin/settings.php#store');
    }

    // Social media
    if ($section === 'social') {
        $fields = ['facebook_url','instagram_url','youtube_url','tiktok_url'];
        foreach ($fields as $f) setSetting($f, post($f));
        if (isAjax()) jsonOk('Social media links saved.');
        flash('success', 'Social media links saved.');
        redirect(BASE_URL . 'admin/settings.php#social');
    }

    // SEO
    if ($section === 'seo') {
        $fields = [
            'seo_site_title',
            'seo_meta_description',
            'seo_google_verification',
            'seo_twitter_handle',
            'seo_robots_custom',
        ];
        foreach ($fields as $f) setSetting($f, post($f));

        if (!empty($_FILES['seo_default_image']['name'])) {
            $uploaded = uploadImage($_FILES['seo_default_image'], 'site');
            if (!$uploaded) {
                if (isAjax()) jsonErr('SEO image upload failed. Use JPG, PNG or WebP under 5MB.');
                flash('error', 'SEO image upload failed. Use JPG, PNG or WebP under 5MB.');
                redirect(BASE_URL . 'admin/settings.php#seo');
            }
            deleteUploadedFile(getSetting('seo_default_image', ''));
            setSetting('seo_default_image', $uploaded);
        }

        if (!empty($_FILES['site_favicon']['name'])) {
            $uploaded = uploadImage($_FILES['site_favicon'], 'site');
            if (!$uploaded) {
                if (isAjax()) jsonErr('Favicon upload failed. Use JPG, PNG or WebP under 5MB.');
                flash('error', 'Favicon upload failed. Use JPG, PNG or WebP under 5MB.');
                redirect(BASE_URL . 'admin/settings.php#seo');
            }
            deleteUploadedFile(getSetting('site_favicon', ''));
            setSetting('site_favicon', $uploaded);
        }

        if (isAjax()) jsonOk('SEO settings saved.');
        flash('success', 'SEO settings saved.');
        redirect(BASE_URL . 'admin/settings.php#seo');
    }

    // Delivery
    if ($section === 'delivery') {
        setSetting('free_delivery_min', post('free_delivery_min'));
        setSetting('enable_free_delivery_min', isset($_POST['enable_free_delivery_min']) ? '1' : '0');
        setSetting('currency_symbol',   post('currency_symbol'));
        setSetting('currency_code',     post('currency_code'));
        if (isAjax()) jsonOk('Delivery settings saved.');
        flash('success', 'Delivery settings saved.');
        redirect(BASE_URL . 'admin/settings.php#delivery');
    }

    // Payment methods
    if ($section === 'payments') {
        setSetting('pm_cod_enabled', isset($_POST['pm_cod_enabled']) ? '1' : '0');
        setSetting('pm_cod_desc', post('pm_cod_desc'));

        setSetting('pm_bank_enabled', isset($_POST['pm_bank_enabled']) ? '1' : '0');
        setSetting('pm_bank_desc', post('pm_bank_desc'));
        setSetting('pm_bank_name', post('pm_bank_name'));
        setSetting('pm_bank_account_name', post('pm_bank_account_name'));
        setSetting('pm_bank_account_number', post('pm_bank_account_number'));
        setSetting('pm_bank_branch', post('pm_bank_branch'));
        setSetting('pm_bank_instructions', post('pm_bank_instructions'));

        setSetting('pm_whatsapp_enabled', isset($_POST['pm_whatsapp_enabled']) ? '1' : '0');
        setSetting('pm_whatsapp_desc', post('pm_whatsapp_desc'));

        setSetting('pm_payhere_enabled', isset($_POST['pm_payhere_enabled']) ? '1' : '0');
        setSetting('pm_payhere_desc', post('pm_payhere_desc'));
        setSetting('pm_payhere_merchant_id', post('pm_payhere_merchant_id'));
        setSetting('pm_payhere_merchant_secret', post('pm_payhere_merchant_secret'));
        setSetting('pm_payhere_handling_fee_percent', post('pm_payhere_handling_fee_percent'));
        setSetting('pm_payhere_sandbox', isset($_POST['pm_payhere_sandbox']) ? '1' : '0');
        setSetting('pm_payhere_notes', post('pm_payhere_notes'));

        setSetting('pm_koko_enabled', isset($_POST['pm_koko_enabled']) ? '1' : '0');
        setSetting('pm_koko_desc', post('pm_koko_desc'));
        setSetting('pm_koko_merchant_id', post('pm_koko_merchant_id'));
        setSetting('pm_koko_api_key', post('pm_koko_api_key'));
        setSetting('pm_koko_plugin_name', post('pm_koko_plugin_name'));
        setSetting('pm_koko_plugin_version', post('pm_koko_plugin_version'));
        setSetting('pm_koko_public_key', post('pm_koko_public_key'));
        setSetting('pm_koko_private_key', post('pm_koko_private_key'));
        setSetting('pm_koko_handling_fee_percent', post('pm_koko_handling_fee_percent'));
        setSetting('pm_koko_sandbox', isset($_POST['pm_koko_sandbox']) ? '1' : '0');
        setSetting('pm_koko_notes', post('pm_koko_notes'));

        if (isAjax()) jsonOk('Payment methods saved.');
        flash('success', 'Payment methods saved.');
        redirect(BASE_URL . 'admin/settings.php#payments');
    }

    // Theme colors
    if ($section === 'theme') {
        $fields = [
            'theme_primary','theme_primary_lt','theme_accent','theme_green','theme_wa',
            'theme_bg','theme_bg2','theme_bg3','theme_bg4',
            'theme_card','theme_card_hover','theme_border','theme_border_lt',
            'theme_text','theme_text2','theme_text_muted','theme_text_dim',
        ];
        foreach ($fields as $f) {
            $v = strtolower(trim(post($f)));
            if (preg_match('/^#[0-9a-f]{6}$/', $v)) {
                setSetting($f, $v);
            }
        }
        if (isAjax()) jsonOk('Theme colors saved.');
        flash('success', 'Theme colors saved.');
        redirect(BASE_URL . 'admin/settings.php#theme');
    }

    // Header announcement bar
    if ($section === 'header_ann') {
        for ($i = 1; $i <= 6; $i++) {
            setSetting('ann_icon_' . $i, post('ann_icon_' . $i));
            setSetting('ann_text_' . $i, post('ann_text_' . $i));
            setSetting('ann_link_' . $i, post('ann_link_' . $i));
        }
        if (isAjax()) jsonOk('Announcement bar settings saved.');
        flash('success', 'Announcement bar settings saved.');
        redirect(BASE_URL . 'admin/settings.php#header');
    }

    // Email / SMTP
    if ($section === 'email') {
        $fields = ['smtp_host','smtp_port','smtp_encryption','smtp_username',
                   'smtp_from_name','smtp_from_email','admin_notify_email'];
        foreach ($fields as $f) setSetting($f, post($f));
        if (post('smtp_password')) setSetting('smtp_password', post('smtp_password'));
        if (isAjax()) jsonOk('Email settings saved.');
        flash('success', 'Email settings saved.');
        redirect(BASE_URL . 'admin/settings.php#email');
    }

    // Test email
    if ($section === 'test_email') {
        require_once dirname(__DIR__) . '/includes/mailer.php';
        $testTo = getSetting('admin_notify_email') ?: getSetting('store_email');
        if ($testTo) {
            $ok = sendMail($testTo, 'Admin', 'Test Email - GADGET HUB',
                '<h2 style="color:#d4920a">✅ Email is working!</h2><p>Your SMTP settings are configured correctly on GADGET HUB Admin.</p>');
            if (isAjax()) {
                if ($ok) jsonOk('Test email sent to ' . $testTo . '.');
                else     jsonErr('Failed to send. Check your SMTP settings.');
            }
            flash($ok ? 'success' : 'error',
                  $ok ? 'Test email sent to ' . $testTo . '.' : 'Failed to send. Check your SMTP settings.');
        } else {
            if (isAjax()) jsonErr('Set an Admin Notification Email first.');
            flash('error', 'Set an Admin Notification Email first.');
        }
        redirect(BASE_URL . 'admin/settings.php#email');
    }

    // Change username
    if ($section === 'username') {
        $newUsername = post('username');
        $password    = post('current_password');

        if (!$newUsername || !$password) {
            if (isAjax()) jsonErr('All fields are required.');
            flash('error', 'All fields are required.');
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $newUsername)) {
            if (isAjax()) jsonErr('Username must be 3–30 characters (letters, numbers, underscore).');
            flash('error', 'Username must be 3–30 characters (letters, numbers, underscore).');
        } else {
            $stmt = $db->prepare('SELECT password FROM admin_users WHERE id = ?');
            $stmt->execute([adminId()]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($password, $hash)) {
                if (isAjax()) jsonErr('Current password is incorrect.');
                flash('error', 'Current password is incorrect.');
            } else {
                $taken = $db->prepare('SELECT id FROM admin_users WHERE username = ? AND id != ?');
                $taken->execute([$newUsername, adminId()]);
                if ($taken->fetch()) {
                    if (isAjax()) jsonErr('That username is already taken.');
                    flash('error', 'That username is already taken.');
                } else {
                    $db->prepare('UPDATE admin_users SET username = ? WHERE id = ?')->execute([$newUsername, adminId()]);
                    $_SESSION['admin_username'] = $newUsername;
                    if (isAjax()) jsonOk('Username updated successfully.');
                    flash('success', 'Username updated successfully.');
                }
            }
        }
        redirect(BASE_URL . 'admin/settings.php#account');
    }

    // Change password
    if ($section === 'password') {
        $current = post('current_password');
        $new     = post('new_password');
        $confirm = post('confirm_password');

        if (!$current || !$new || !$confirm) {
            if (isAjax()) jsonErr('All fields are required.');
            flash('error', 'All fields are required.');
        } elseif (strlen($new) < 8) {
            if (isAjax()) jsonErr('New password must be at least 8 characters.');
            flash('error', 'New password must be at least 8 characters.');
        } elseif ($new !== $confirm) {
            if (isAjax()) jsonErr('New passwords do not match.');
            flash('error', 'New passwords do not match.');
        } else {
            $stmt = $db->prepare('SELECT password FROM admin_users WHERE id = ?');
            $stmt->execute([adminId()]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($current, $hash)) {
                if (isAjax()) jsonErr('Current password is incorrect.');
                flash('error', 'Current password is incorrect.');
            } else {
                $db->prepare('UPDATE admin_users SET password = ? WHERE id = ?')
                   ->execute([password_hash($new, PASSWORD_DEFAULT), adminId()]);
                if (isAjax()) jsonOk('Password changed successfully.');
                flash('success', 'Password changed successfully.');
            }
        }
        redirect(BASE_URL . 'admin/settings.php#account');
    }
}

// Load current settings
$s = fn($k, $d='') => getSetting($k, $d);
$kokoReadiness = getKokoReadiness();

// Load admin user
$adminUser = $db->prepare('SELECT name, username, email, role, last_login, created_at FROM admin_users WHERE id = ?');
$adminUser->execute([adminId()]);
$adminUser = $adminUser->fetch();
?>

<div class="page-header">
  <div>
    <h1>Settings</h1>
    <p>Manage your store configuration and admin account.</p>
  </div>
</div>

<!-- Tab nav -->
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:24px;border-bottom:1px solid var(--border);padding-bottom:0">
  <?php
  $tabs = [
    'store'    => ['fas fa-store',       'Store Info'],
    'social'   => ['fab fa-instagram',   'Social Media'],
    'seo'      => ['fas fa-magnifying-glass', 'SEO'],
    'delivery' => ['fas fa-truck',       'Delivery'],
    'payments' => ['fas fa-credit-card', 'Payments'],
    'header'   => ['fas fa-bullhorn',    'Header Bar'],
    'theme'    => ['fas fa-palette',     'Theme Colors'],
    'email'    => ['fas fa-envelope',    'Email / SMTP'],
    'account'  => ['fas fa-user-shield', 'Admin Account'],
  ];
  foreach ($tabs as $id => [$icon, $label]): ?>
    <a href="#<?= $id ?>" class="settings-tab" data-tab="<?= $id ?>">
      <i class="<?= $icon ?>"></i> <?= $label ?>
    </a>
  <?php endforeach ?>
</div>

<div style="max-width:760px">

  <!-- Store Info -->
  <div class="settings-panel" id="panel-store">
    <form method="POST">
      <input type="hidden" name="section" value="store">
      <div class="card">
        <div class="card-title"><i class="fas fa-store" style="color:var(--primary)"></i> Store Information</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">Store Name</label>
              <input type="text" name="store_name" class="form-input"
                value="<?= htmlspecialchars($s('store_name','Gadget Hub')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Tagline</label>
              <input type="text" name="store_tagline" class="form-input"
                value="<?= htmlspecialchars($s('store_tagline')) ?>" placeholder="Short store tagline">
            </div>
          </div>

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">Phone / Call</label>
              <div style="position:relative">
                <i class="fas fa-phone" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px"></i>
                <input type="text" name="store_phone" class="form-input" style="padding-left:36px"
                  value="<?= htmlspecialchars($s('store_phone','+94 77 723 7962')) ?>" placeholder="+94 77 000 0000">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">WhatsApp Number <span class="form-hint" style="display:inline">(digits only)</span></label>
              <div style="position:relative">
                <i class="fab fa-whatsapp" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#25d366;font-size:14px"></i>
                <input type="text" name="store_whatsapp" class="form-input" style="padding-left:36px"
                  value="<?= htmlspecialchars($s('store_whatsapp','94777237962')) ?>" placeholder="94777237962">
              </div>
              <span class="form-hint">Used for WhatsApp order links. No + or spaces.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address</label>
            <div style="position:relative">
              <i class="fas fa-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px"></i>
              <input type="email" name="store_email" class="form-input" style="padding-left:36px"
                value="<?= htmlspecialchars($s('store_email','genecoretech@gmail.com')) ?>" placeholder="store@email.com">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="store_address" class="form-textarea" rows="2"
              placeholder="Full store address"><?= htmlspecialchars($s('store_address')) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Opening Hours</label>
            <input type="text" name="store_hours" class="form-input"
              value="<?= htmlspecialchars($s('store_hours','Mon-Sat: 8:00 AM - 7:00 PM | Sunday: Closed')) ?>"
              placeholder="Mon-Sat: 8:00 AM - 7:00 PM | Sunday: Closed">
          </div>

          <!-- Google Maps -->
          <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:4px">
            <div style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:14px">
              <i class="fas fa-map-marked-alt" style="color:var(--primary);margin-right:6px"></i> Google Maps
            </div>

            <div class="form-group">
              <label class="form-label">Map Embed URL <span style="color:var(--text-dim);font-weight:400;font-size:11px;text-transform:none">(shown on Contact page)</span></label>
              <div style="position:relative">
                <i class="fas fa-map" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#4285f4;font-size:13px"></i>
                <input type="url" name="store_map_embed" class="form-input" style="padding-left:36px"
                  value="<?= htmlspecialchars($s('store_map_embed')) ?>"
                  placeholder="https://www.google.com/maps/embed?pb=...">
              </div>
              <span class="form-hint">Google Maps → Share → <strong>Embed a map</strong> → copy only the <code style="background:var(--bg-3);padding:1px 5px;border-radius:3px">src</code> value from the iframe code</span>
            </div>

            <div class="form-group">
              <label class="form-label">Directions Link <span style="color:var(--text-dim);font-weight:400;font-size:11px;text-transform:none">("Open in Maps" button)</span></label>
              <div style="position:relative">
                <i class="fas fa-directions" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#34a853;font-size:13px"></i>
                <input type="url" name="store_map_link" class="form-input" style="padding-left:36px"
                  value="<?= htmlspecialchars($s('store_map_link')) ?>"
                  placeholder="https://maps.google.com/?q=...">
              </div>
              <span class="form-hint">Google Maps → Share → <strong>Copy link</strong> - used for the "Get Directions" button</span>
            </div>

            <?php if ($s('store_map_embed')): ?>
            <div style="margin-top:8px;border-radius:var(--r);overflow:hidden;height:160px;border:1px solid var(--border)">
              <iframe src="<?= htmlspecialchars($s('store_map_embed')) ?>"
                width="100%" height="100%" style="border:0;display:block" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade" title="Map preview"></iframe>
            </div>
            <?php endif ?>
          </div>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Store Info</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Social Media -->
  <div class="settings-panel" id="panel-social">
    <form method="POST">
      <input type="hidden" name="section" value="social">
      <div class="card">
        <div class="card-title"><i class="fas fa-share-alt" style="color:var(--primary)"></i> Social Media Links</div>
        <div class="form-grid" style="gap:16px">

          <?php
          $socials = [
            'facebook_url'  => ['fab fa-facebook-f',  '#1877f2', 'Facebook URL',  'https://web.facebook.com/genecoretech'],
            'instagram_url' => ['fab fa-instagram',   '#e1306c', 'Instagram URL', 'https://instagram.com/yourpage'],
            'youtube_url'   => ['fab fa-youtube',     '#ff0000', 'YouTube URL',   'https://youtube.com/yourchannel'],
            'tiktok_url'    => ['fab fa-tiktok',      '#010101', 'TikTok URL',    'https://tiktok.com/@yourpage'],
          ];
          foreach ($socials as $key => [$icon, $color, $label, $placeholder]): ?>
          <div class="form-group">
            <label class="form-label"><?= $label ?></label>
            <div style="position:relative">
              <i class="<?= $icon ?>" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:<?= $color ?>;font-size:14px"></i>
              <input type="url" name="<?= $key ?>" class="form-input" style="padding-left:36px"
                value="<?= htmlspecialchars($s($key)) ?>" placeholder="<?= $placeholder ?>">
            </div>
          </div>
          <?php endforeach ?>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Social Links</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- SEO -->
  <div class="settings-panel" id="panel-seo">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="section" value="seo">
      <div class="card">
        <div class="card-title"><i class="fas fa-magnifying-glass" style="color:var(--primary)"></i> SEO & Search Console</div>
        <div class="form-grid" style="gap:16px">
          <div class="form-group">
            <label class="form-label">Website SEO Title</label>
            <input type="text" name="seo_site_title" class="form-input"
              value="<?= htmlspecialchars($s('seo_site_title', $s('store_name', 'Gadget Hub'))) ?>"
              placeholder="Gadget Hub | Computer Parts & Electronics">
            <span class="form-hint">Used as the default site title for search and social sharing.</span>
          </div>

          <div class="form-group">
            <label class="form-label">Default Meta Description</label>
            <textarea name="seo_meta_description" class="form-textarea" rows="3" placeholder="Short summary of your business for Google search results."><?= htmlspecialchars($s('seo_meta_description', 'Premium computer parts, electronics and accessories in Sri Lanka.')) ?></textarea>
            <span class="form-hint">Recommended: 140-160 characters.</span>
          </div>

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">Google Site Verification</label>
              <input type="text" name="seo_google_verification" class="form-input"
                value="<?= htmlspecialchars($s('seo_google_verification')) ?>"
                placeholder="Paste the verification content value from Google Search Console">
              <span class="form-hint">Adds the <code>&lt;meta name="google-site-verification"&gt;</code> tag automatically.</span>
            </div>
            <div class="form-group">
              <label class="form-label">Twitter Handle</label>
              <input type="text" name="seo_twitter_handle" class="form-input"
                value="<?= htmlspecialchars($s('seo_twitter_handle')) ?>"
                placeholder="@gadgethub">
              <span class="form-hint">Optional. Used for Twitter card metadata.</span>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group">
              <label class="form-label">Default SEO Image</label>
              <?php if ($s('seo_default_image')): ?><img src="<?= BASE_URL . htmlspecialchars($s('seo_default_image')) ?>" alt="SEO Image" style="width:100%;height:160px;object-fit:cover;border-radius:8px;border:1px solid var(--border);margin-bottom:8px"><?php endif; ?>
              <input type="file" name="seo_default_image" class="form-input" accept="image/png,image/jpeg,image/webp">
              <span class="form-hint">Used as the Open Graph / social sharing image across the site.</span>
            </div>

            <div class="form-group">
              <label class="form-label">Website Favicon</label>
              <?php if ($s('site_favicon')): ?><img src="<?= BASE_URL . htmlspecialchars($s('site_favicon')) ?>" alt="Favicon" style="width:72px;height:72px;object-fit:contain;border-radius:8px;border:1px solid var(--border);margin-bottom:8px;background:var(--bg-3);padding:8px"><?php endif; ?>
              <input type="file" name="site_favicon" class="form-input" accept="image/png,image/jpeg,image/webp">
              <span class="form-hint">Recommended square image, ideally 512x512 or larger.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Extra robots.txt Directives</label>
            <textarea name="seo_robots_custom" class="form-textarea" rows="5" placeholder="Disallow: /private/&#10;Crawl-delay: 10"><?= htmlspecialchars($s('seo_robots_custom')) ?></textarea>
            <span class="form-hint">Optional custom lines that will be appended to <code>robots.txt</code>.</span>
          </div>

          <div style="padding:14px 16px;border:1px solid var(--border);border-radius:var(--r);background:var(--bg-3)">
            <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Search Console Links</div>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
              <div><strong>Sitemap:</strong> <a href="<?= BASE_URL ?>sitemap.xml" target="_blank" style="color:var(--primary)"><?= BASE_URL ?>sitemap.xml</a></div>
              <div><strong>Robots:</strong> <a href="<?= BASE_URL ?>robots.txt" target="_blank" style="color:var(--primary)"><?= BASE_URL ?>robots.txt</a></div>
            </div>
          </div>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save SEO Settings</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Delivery -->
  <div class="settings-panel" id="panel-delivery">
    <form method="POST">
      <input type="hidden" name="section" value="delivery">
      <div class="card">
        <div class="card-title"><i class="fas fa-truck" style="color:var(--primary)"></i> Delivery &amp; Currency</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-group">
            <label class="form-label">Free Delivery Minimum (Rs.)</label>
            <div style="position:relative">
              <i class="fas fa-truck" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--green);font-size:13px"></i>
              <input type="number" name="free_delivery_min" class="form-input" style="padding-left:36px" min="0"
                value="<?= htmlspecialchars($s('free_delivery_min','50000')) ?>">
            </div>
            <span class="form-hint">Used only when "Enable Free Delivery Minimum" is ON.</span>
          </div>

          <div class="form-group">
            <label class="form-label">Enable Free Delivery Minimum</label>
            <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;padding:10px 12px;border:1px solid var(--border);border-radius:var(--r);background:var(--bg-3)">
              <input type="checkbox" name="enable_free_delivery_min" value="1" <?= $s('enable_free_delivery_min','0') === '1' ? 'checked' : '' ?>>
              <span style="font-size:13px;color:var(--text)">Apply free delivery when subtotal reaches minimum amount</span>
            </label>
          </div>

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">Currency Symbol</label>
              <input type="text" name="currency_symbol" class="form-input" maxlength="10"
                value="<?= htmlspecialchars($s('currency_symbol','Rs.')) ?>" placeholder="Rs.">
            </div>
            <div class="form-group">
              <label class="form-label">Currency Code</label>
              <input type="text" name="currency_code" class="form-input" maxlength="5"
                value="<?= htmlspecialchars($s('currency_code','LKR')) ?>" placeholder="LKR">
            </div>
          </div>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Delivery Settings</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Payments -->
  <div class="settings-panel" id="panel-payments">
    <form method="POST">
      <input type="hidden" name="section" value="payments">
      <div class="card">
        <div class="card-title"><i class="fas fa-credit-card" style="color:var(--primary)"></i> Payment Methods</div>
        <div class="form-grid" style="gap:18px">

          <div class="form-group">
            <label class="form-label">Cash on Delivery (COD)</label>
            <label style="display:inline-flex;align-items:center;gap:10px;margin-bottom:8px">
              <input type="checkbox" name="pm_cod_enabled" value="1" <?= $s('pm_cod_enabled','1')==='1'?'checked':'' ?>> Enable
            </label>
            <input type="text" name="pm_cod_desc" class="form-input" value="<?= htmlspecialchars($s('pm_cod_desc','Pay in cash when your order arrives.')) ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Bank Transfer</label>
            <label style="display:inline-flex;align-items:center;gap:10px;margin-bottom:8px">
              <input type="checkbox" name="pm_bank_enabled" value="1" <?= $s('pm_bank_enabled','0')==='1'?'checked':'' ?>> Enable
            </label>
            <input type="text" name="pm_bank_desc" class="form-input" placeholder="Description" value="<?= htmlspecialchars($s('pm_bank_desc','Transfer to our bank account and share payment reference.')) ?>" style="margin-bottom:8px">
            <div class="form-grid form-grid-2" style="gap:8px">
              <input type="text" name="pm_bank_name" class="form-input" placeholder="Bank Name" value="<?= htmlspecialchars($s('pm_bank_name')) ?>">
              <input type="text" name="pm_bank_branch" class="form-input" placeholder="Branch" value="<?= htmlspecialchars($s('pm_bank_branch')) ?>">
              <input type="text" name="pm_bank_account_name" class="form-input" placeholder="Account Name" value="<?= htmlspecialchars($s('pm_bank_account_name')) ?>">
              <input type="text" name="pm_bank_account_number" class="form-input" placeholder="Account Number" value="<?= htmlspecialchars($s('pm_bank_account_number')) ?>">
            </div>
            <textarea name="pm_bank_instructions" class="form-textarea" rows="2" placeholder="Additional instructions" style="margin-top:8px"><?= htmlspecialchars($s('pm_bank_instructions')) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">WhatsApp Order</label>
            <label style="display:inline-flex;align-items:center;gap:10px;margin-bottom:8px">
              <input type="checkbox" name="pm_whatsapp_enabled" value="1" <?= $s('pm_whatsapp_enabled','1')==='1'?'checked':'' ?>> Enable
            </label>
            <input type="text" name="pm_whatsapp_desc" class="form-input" value="<?= htmlspecialchars($s('pm_whatsapp_desc','Finalize your order details with our team via WhatsApp.')) ?>">
          </div>

          <div class="form-group">
            <label class="form-label">PayHere (Card)</label>
            <label style="display:inline-flex;align-items:center;gap:10px;margin-bottom:8px">
              <input type="checkbox" name="pm_payhere_enabled" value="1" <?= $s('pm_payhere_enabled','0')==='1'?'checked':'' ?>> Enable
            </label>
            <input type="text" name="pm_payhere_desc" class="form-input" placeholder="Description" value="<?= htmlspecialchars($s('pm_payhere_desc','Pay securely online using card payments.')) ?>" style="margin-bottom:8px">
            <div class="form-grid form-grid-2" style="gap:8px">
              <input type="text" name="pm_payhere_merchant_id" class="form-input" placeholder="Merchant ID" value="<?= htmlspecialchars($s('pm_payhere_merchant_id')) ?>">
              <input type="text" name="pm_payhere_merchant_secret" class="form-input" placeholder="Merchant Secret" value="<?= htmlspecialchars($s('pm_payhere_merchant_secret')) ?>">
              <input type="number" name="pm_payhere_handling_fee_percent" class="form-input" min="0" max="100" step="0.01" placeholder="Handling Fee %" value="<?= htmlspecialchars($s('pm_payhere_handling_fee_percent','0')) ?>">
              <label style="display:inline-flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--border);border-radius:var(--r);background:var(--bg-3)">
                <input type="checkbox" name="pm_payhere_sandbox" value="1" <?= $s('pm_payhere_sandbox','1')==='1'?'checked':'' ?>> Sandbox mode
              </label>
            </div>
            <textarea name="pm_payhere_notes" class="form-textarea" rows="2" placeholder="Gateway notes / future keys" style="margin-top:8px"><?= htmlspecialchars($s('pm_payhere_notes')) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">KOKO (BNPL)</label>
            <?php if (!$kokoReadiness['ready']): ?>
              <div style="margin-bottom:10px;padding:10px 12px;border:1px solid rgba(245,158,11,.35);border-radius:10px;background:rgba(245,158,11,.08);font-size:12px;color:#b45309;line-height:1.6">
                <strong>KOKO readiness check:</strong> Missing <?= htmlspecialchars(implode(', ', $kokoReadiness['missing'])) ?>.
              </div>
            <?php else: ?>
              <div style="margin-bottom:10px;padding:10px 12px;border:1px solid rgba(34,197,94,.3);border-radius:10px;background:rgba(34,197,94,.08);font-size:12px;color:#15803d;line-height:1.6">
                <strong>KOKO readiness check:</strong> Server requirements and required credentials look available.
              </div>
            <?php endif ?>
            <?php if (!empty($kokoReadiness['warnings'])): ?>
              <div style="margin-bottom:10px;padding:10px 12px;border:1px solid rgba(239,68,68,.28);border-radius:10px;background:rgba(239,68,68,.08);font-size:12px;color:#b91c1c;line-height:1.6">
                <strong>KOKO credential warning:</strong> <?= htmlspecialchars(implode(', ', $kokoReadiness['warnings'])) ?>. Remove spaces/new lines copied from PDF or email.
              </div>
            <?php endif ?>
            <label style="display:inline-flex;align-items:center;gap:10px;margin-bottom:8px">
              <input type="checkbox" name="pm_koko_enabled" value="1" <?= $s('pm_koko_enabled','0')==='1'?'checked':'' ?>> Enable
            </label>
            <input type="text" name="pm_koko_desc" class="form-input" placeholder="Description" value="<?= htmlspecialchars($s('pm_koko_desc','Pay in 3 interest free instalments with KOKO.')) ?>" style="margin-bottom:8px">
            <div style="margin-bottom:10px;padding:10px 12px;border:1px solid rgba(59,130,246,.25);border-radius:10px;background:rgba(59,130,246,.08);font-size:12px;color:#1d4ed8;line-height:1.6">
              <strong>KOKO environment:</strong>
              <?= $s('pm_koko_sandbox','1')==='1' ? 'Sandbox / QA endpoint is active.' : 'Production endpoint is active.' ?>
              <br>
              <?= $s('pm_koko_sandbox','1')==='1'
                ? 'Orders will be sent to https://qaapi.paykoko.com/api/merchants/orderCreate'
                : 'Orders will be sent to https://prodapi.paykoko.com/api/merchants/orderCreate' ?>
              <br>
              QA credentials from KOKO email must be used with <strong>Sandbox mode</strong> enabled.
            </div>
            <div class="form-grid form-grid-2" style="gap:8px">
              <input type="text" name="pm_koko_merchant_id" class="form-input" placeholder="Merchant ID" value="<?= htmlspecialchars($s('pm_koko_merchant_id')) ?>">
              <input type="text" name="pm_koko_api_key" class="form-input" placeholder="API Key" value="<?= htmlspecialchars($s('pm_koko_api_key')) ?>">
              <input type="text" name="pm_koko_plugin_name" class="form-input" placeholder="Plugin Name" value="<?= htmlspecialchars($s('pm_koko_plugin_name','customapi')) ?>">
              <input type="text" name="pm_koko_plugin_version" class="form-input" placeholder="Plugin Version" value="<?= htmlspecialchars($s('pm_koko_plugin_version','1.0.1')) ?>">
              <input type="number" name="pm_koko_handling_fee_percent" class="form-input" min="0" max="100" step="0.01" placeholder="Handling Fee %" value="<?= htmlspecialchars($s('pm_koko_handling_fee_percent','0')) ?>">
              <label style="display:inline-flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--border);border-radius:var(--r);background:var(--bg-3)">
                <input type="checkbox" name="pm_koko_sandbox" value="1" <?= $s('pm_koko_sandbox','1')==='1'?'checked':'' ?>> Sandbox mode
              </label>
            </div>
            <textarea name="pm_koko_public_key" class="form-textarea" rows="4" placeholder="KOKO Public Key" style="margin-top:8px"><?= htmlspecialchars($s('pm_koko_public_key')) ?></textarea>
            <textarea name="pm_koko_private_key" class="form-textarea" rows="6" placeholder="Merchant Private Key" style="margin-top:8px"><?= htmlspecialchars($s('pm_koko_private_key')) ?></textarea>
            <textarea name="pm_koko_notes" class="form-textarea" rows="2" placeholder="Gateway notes / QA login details" style="margin-top:8px"><?= htmlspecialchars($s('pm_koko_notes')) ?></textarea>
          </div>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Payment Methods</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Header Announcement Bar -->
  <div class="settings-panel" id="panel-header">
    <form method="POST">
      <input type="hidden" name="section" value="header_ann">
      <div class="card">
        <div class="card-title"><i class="fas fa-bullhorn" style="color:var(--primary)"></i> Header Announcement Bar</div>
        <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px">
          Set icon class, text, and optional link for each message. Icon examples: <code style="background:var(--bg-3);padding:1px 5px;border-radius:3px">fas fa-tag</code>, <code style="background:var(--bg-3);padding:1px 5px;border-radius:3px">fas fa-truck</code>, <code style="background:var(--bg-3);padding:1px 5px;border-radius:3px">fas fa-shield-alt</code>.
        </p>
        <div class="form-grid" style="gap:16px">
          <?php
          $annDefaults = [
            1 => ['fas fa-tag', 'Free delivery on orders over Rs. 10,000', ''],
            2 => ['fas fa-boxes', 'Wholesale prices available - Contact us today', 'wholesale.php'],
            3 => ['fas fa-shield-alt', '100% Genuine products with manufacturer warranty', ''],
            4 => ['fas fa-headset', '24/7 Customer support', 'contact.php'],
            5 => ['fas fa-truck', 'Fast island-wide delivery', 'shipping.php'],
            6 => ['fas fa-star', 'Best prices guaranteed - Retail & Wholesale', 'shop.php'],
          ];
          for ($i = 1; $i <= 6; $i++):
            [$defIcon, $defText, $defLink] = $annDefaults[$i];
          ?>
          <div style="border:1px solid var(--border);border-radius:var(--r);padding:14px;background:var(--bg-3)">
            <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">Message <?= $i ?></div>
            <div class="form-grid form-grid-2" style="gap:12px">
              <div class="form-group">
                <label class="form-label">Icon Class</label>
                <input type="text" name="ann_icon_<?= $i ?>" class="form-input"
                  value="<?= htmlspecialchars($s('ann_icon_' . $i, $defIcon)) ?>" placeholder="fas fa-tag">
              </div>
              <div class="form-group">
                <label class="form-label">Link (optional)</label>
                <input type="text" name="ann_link_<?= $i ?>" class="form-input"
                  value="<?= htmlspecialchars($s('ann_link_' . $i, $defLink)) ?>" placeholder="https://... or shop.php">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Text</label>
              <input type="text" name="ann_text_<?= $i ?>" class="form-input"
                value="<?= htmlspecialchars($s('ann_text_' . $i, $defText)) ?>" placeholder="Announcement text">
            </div>
          </div>
          <?php endfor ?>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Header Bar</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Theme Colors -->
  <div class="settings-panel" id="panel-theme">
    <form method="POST">
      <input type="hidden" name="section" value="theme">
      <div class="card">
        <div class="card-title"><i class="fas fa-palette" style="color:var(--primary)"></i> Theme Colors</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-grid form-grid-2" style="gap:16px">
            <?php
            $colorFields = [
              'theme_primary'     => ['Primary',      '#d4920a'],
              'theme_primary_lt'  => ['Primary Light','#f0a820'],
              'theme_accent'      => ['Accent',       '#ff6b00'],
              'theme_green'       => ['Green',        '#16a34a'],
              'theme_wa'          => ['WhatsApp',     '#25d366'],
              'theme_bg'          => ['Background',   '#141414'],
              'theme_bg2'         => ['Background 2', '#191919'],
              'theme_bg3'         => ['Background 3', '#1e1e1e'],
              'theme_bg4'         => ['Background 4', '#252525'],
              'theme_card'        => ['Card',         '#1d1d1d'],
              'theme_card_hover'  => ['Card Hover',   '#242424'],
              'theme_border'      => ['Border',       '#303030'],
              'theme_border_lt'   => ['Border Light', '#3d3d3d'],
              'theme_text'        => ['Text',         '#ffffff'],
              'theme_text2'       => ['Text 2',       '#e8e8e8'],
              'theme_text_muted'  => ['Text Muted',   '#999999'],
              'theme_text_dim'    => ['Text Dim',     '#505050'],
            ];
            foreach ($colorFields as $key => [$label, $def]):
              $val = $s($key, $def);
            ?>
            <div class="form-group">
              <label class="form-label"><?= $label ?></label>
              <div style="display:flex;align-items:center;gap:10px">
                <input type="color" value="<?= htmlspecialchars($val) ?>" data-target="<?= $key ?>" style="width:44px;height:38px;border:1px solid var(--border);border-radius:8px;background:transparent;padding:2px;cursor:pointer">
                <input type="text" name="<?= $key ?>" class="form-input" value="<?= htmlspecialchars($val) ?>" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
              </div>
            </div>
            <?php endforeach ?>
          </div>

          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Theme Colors</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Email / SMTP -->
  <div class="settings-panel" id="panel-email">
    <form method="POST">
      <input type="hidden" name="section" value="email">
      <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fas fa-server" style="color:var(--primary)"></i> SMTP Server</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">SMTP Host</label>
              <input type="text" name="smtp_host" class="form-input"
                value="<?= htmlspecialchars($s('smtp_host')) ?>"
                placeholder="e.g. mail.yourdomain.com or smtp.gmail.com">
              <span class="form-hint">Your mail server hostname (from cPanel or Gmail)</span>
            </div>
            <div class="form-group">
              <label class="form-label">Port</label>
              <select name="smtp_port" class="form-select">
                <?php foreach (['587'=>'587 - TLS (recommended)','465'=>'465 - SSL','25'=>'25 - Plain'] as $p=>$l): ?>
                  <option value="<?= $p ?>" <?= $s('smtp_port','587')===$p?'selected':'' ?>><?= $l ?></option>
                <?php endforeach ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Encryption</label>
            <select name="smtp_encryption" class="form-select">
              <option value="tls"  <?= $s('smtp_encryption','tls')==='tls' ?'selected':'' ?>>TLS (STARTTLS) - port 587</option>
              <option value="ssl"  <?= $s('smtp_encryption','tls')==='ssl' ?'selected':'' ?>>SSL - port 465</option>
              <option value="none" <?= $s('smtp_encryption','tls')==='none'?'selected':'' ?>>None - port 25</option>
            </select>
          </div>

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">SMTP Username</label>
              <input type="text" name="smtp_username" class="form-input" autocomplete="off"
                value="<?= htmlspecialchars($s('smtp_username')) ?>"
                placeholder="your@email.com">
            </div>
            <div class="form-group">
              <label class="form-label">SMTP Password</label>
              <input type="password" name="smtp_password" class="form-input" autocomplete="new-password"
                placeholder="Leave blank to keep current">
              <span class="form-hint">Leave blank to keep the saved password</span>
            </div>
          </div>

        </div>
      </div>

      <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fas fa-paper-plane" style="color:var(--primary)"></i> Sender &amp; Notifications</div>
        <div class="form-grid" style="gap:16px">

          <div class="form-grid form-grid-2" style="gap:16px">
            <div class="form-group">
              <label class="form-label">From Name</label>
              <input type="text" name="smtp_from_name" class="form-input"
                value="<?= htmlspecialchars($s('smtp_from_name', getSetting('store_name','GADGET HUB Store'))) ?>"
                placeholder="GADGET HUB Store">
              <span class="form-hint">Shown as the sender in email clients</span>
            </div>
            <div class="form-group">
              <label class="form-label">From Email Address</label>
              <input type="email" name="smtp_from_email" class="form-input"
                value="<?= htmlspecialchars($s('smtp_from_email')) ?>"
                placeholder="noreply@yourdomain.com">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Admin Notification Email</label>
            <div style="position:relative">
              <i class="fas fa-bell" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--primary);font-size:13px"></i>
              <input type="email" name="admin_notify_email" class="form-input" style="padding-left:36px"
                value="<?= htmlspecialchars($s('admin_notify_email', getSetting('store_email'))) ?>"
                placeholder="admin@yourdomain.com">
            </div>
            <span class="form-hint">New order alerts are sent to this address</span>
          </div>

          <div style="display:flex;gap:12px;flex-wrap:wrap">
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Email Settings</button>
          </div>
        </div>
      </div>
    </form>

    <!-- Test email -->
    <div class="card">
      <div class="card-title"><i class="fas fa-vial" style="color:var(--primary)"></i> Test Email</div>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">
        Send a test email to your admin notification address to verify the SMTP settings are working.
      </p>
      <form method="POST">
        <input type="hidden" name="section" value="test_email">
        <button type="submit" class="btn btn-ghost"><i class="fas fa-paper-plane"></i> Send Test Email</button>
      </form>

      <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
        <div style="font-size:12.5px;color:var(--text-muted);line-height:1.8">
          <strong style="color:var(--text)">cPanel setup tip:</strong><br>
          Host: <code style="background:var(--bg-3);padding:1px 6px;border-radius:4px">mail.yourdomain.com</code> &nbsp;|&nbsp;
          Port: <code style="background:var(--bg-3);padding:1px 6px;border-radius:4px">587</code> &nbsp;|&nbsp;
          Encryption: <code style="background:var(--bg-3);padding:1px 6px;border-radius:4px">TLS</code><br>
          Username and password are the same as your cPanel email account.
        </div>
      </div>
    </div>
  </div>

  <!-- Admin Account -->
  <div class="settings-panel" id="panel-account">

    <!-- Admin info card -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-title"><i class="fas fa-user-shield" style="color:var(--primary)"></i> Admin Account</div>
      <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
        <div style="width:60px;height:60px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;flex-shrink:0">
          <?= strtoupper(substr(adminName(), 0, 1)) ?>
        </div>
        <div>
          <div style="font-size:16px;font-weight:700;color:var(--text)"><?= htmlspecialchars($adminUser['name'] ?? '') ?></div>
          <div style="font-size:13px;color:var(--text-muted);margin-top:2px">@<?= htmlspecialchars($adminUser['username'] ?? '') ?></div>
          <div style="display:flex;gap:12px;margin-top:6px;flex-wrap:wrap">
            <span class="badge badge-purple"><?= ucfirst($adminUser['role'] ?? 'admin') ?></span>
            <?php if ($adminUser['last_login']):
              $ll     = strtotime($adminUser['last_login']);
              $diff   = time() - $ll;
              $rel    = $diff < 3600   ? 'Just now'
                      : ($diff < 86400  ? floor($diff/3600) . 'h ago'
                      : ($diff < 604800 ? floor($diff/86400) . 'd ago'
                      : ''));
              $full   = date('d M Y, h:i A', $ll);
            ?>
              <span style="font-size:12px;color:var(--text-muted)" title="<?= $full ?>">
                <i class="fas fa-clock"></i>
                Last login: <?= $full ?>
                <?= $rel ? "<span style='color:var(--text-dim)'> ($rel)</span>" : '' ?>
              </span>
            <?php endif ?>

          </div>
        </div>
      </div>
    </div>

    <!-- Change username -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-title"><i class="fas fa-user-edit" style="color:var(--primary)"></i> Change Username</div>
      <form method="POST" style="max-width:420px">
        <input type="hidden" name="section" value="username">
        <div class="form-grid" style="gap:14px">
          <div class="form-group">
            <label class="form-label">New Username</label>
            <input type="text" name="username" class="form-input"
              value="<?= htmlspecialchars(adminUsername()) ?>"
              placeholder="Enter new username" required>
            <span class="form-hint">3-30 characters. Letters, numbers and underscore only.</span>
          </div>
          <div class="form-group">
            <label class="form-label">Current Password <span style="color:var(--text-muted);font-weight:400;text-transform:none;font-size:11px">to confirm</span></label>
            <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
          </div>
          <div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update Username</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Change password -->
    <div class="card">
      <div class="card-title"><i class="fas fa-lock" style="color:var(--primary)"></i> Change Password</div>
      <form method="POST" style="max-width:420px" id="pwForm">
        <input type="hidden" name="section" value="password">
        <div class="form-grid" style="gap:14px">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" id="newPw" class="form-input" placeholder="Min. 8 characters" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirmPw" class="form-input" placeholder="Repeat new password" required>
            <span class="form-hint" id="pwMatchHint" style="display:none"></span>
          </div>
          <div>
            <button type="submit" class="btn btn-gold" id="pwSubmit"><i class="fas fa-key"></i> Change Password</button>
          </div>
        </div>
      </form>
    </div>

  </div>

</div>

<style>
.settings-tab {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 18px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  border-radius: var(--r) var(--r) 0 0;
  border: 1px solid transparent;
  border-bottom: none;
  transition: var(--t);
  margin-bottom: -1px;
  cursor: pointer;
}
.settings-tab:hover { color: var(--text); background: var(--bg-3); }
.settings-tab.active { background: var(--bg-2); border-color: var(--border); color: var(--primary); }
.settings-panel { display: none; }
.settings-panel.active { display: block; }
</style>

<script>
const tabs   = document.querySelectorAll('.settings-tab');
const panels = document.querySelectorAll('.settings-panel');

function showTab(id) {
  tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === id));
  panels.forEach(p => p.classList.toggle('active', p.id === 'panel-' + id));
  history.replaceState(null, '', '#' + id);
}

tabs.forEach(t => t.addEventListener('click', e => { e.preventDefault(); showTab(t.dataset.tab); }));

// Show tab from URL hash
const hash = location.hash.replace('#','') || 'store';
showTab(['store','social','seo','delivery','payments','header','theme','email','account'].includes(hash) ? hash : 'store');

// Password match validation
const newPw     = document.getElementById('newPw');
const confirmPw = document.getElementById('confirmPw');
const hint      = document.getElementById('pwMatchHint');
const pwSubmit  = document.getElementById('pwSubmit');

function checkPw() {
  if (!confirmPw.value) { hint.style.display='none'; return; }
  const match = newPw.value === confirmPw.value;
  hint.style.display = 'block';
  hint.textContent   = match ? '✓ Passwords match' : '✗ Passwords do not match';
  hint.style.color   = match ? 'var(--green)' : 'var(--red)';
  pwSubmit.disabled  = !match;
}
newPw.addEventListener('input', checkPw);
confirmPw.addEventListener('input', checkPw);
</script>

<script>
// Generic AJAX save for all settings forms
document.querySelectorAll('.settings-panel form').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn  = form.querySelector('[type="submit"]');
    var orig = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…'; }
    fetch(location.pathname, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (btn) { btn.disabled = false; btn.innerHTML = orig; }
      adminSnackbar(res.message, res.success ? 'success' : 'error');
      var section = form.querySelector('[name="section"]');
      if (res.success && section && section.value === 'username') {
        setTimeout(function () { location.reload(); }, 1200);
      }
    })
    .catch(function () {
      if (btn) { btn.disabled = false; btn.innerHTML = orig; }
      adminSnackbar('Network error. Try again.', 'error');
    });
  });
});

// Color picker sync
document.querySelectorAll('input[type="color"][data-target]').forEach(function (picker) {
  picker.addEventListener('input', function () {
    var name = this.dataset.target;
    var input = document.querySelector('input[name="' + name + '"]');
    if (input) input.value = this.value.toLowerCase();
  });
});
</script>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
