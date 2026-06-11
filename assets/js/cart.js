(function (window) {
  const KEY = 'gadgethub_cart';
  const LEGACY_KEY = 'genex_cart';

  const CAT_ICONS = {
    processors: 'fas fa-microchip', ram: 'fas fa-memory',
    storage: 'fas fa-hdd', graphics: 'fas fa-desktop',
    motherboards: 'fas fa-server', monitors: 'fas fa-tv',
    keyboards: 'fas fa-keyboard', mouse: 'fas fa-mouse',
    cables: 'fas fa-plug', 'power-banks': 'fas fa-battery-full',
    chargers: 'fas fa-charging-station', earphones: 'fas fa-headphones',
    'mobile-displays': 'fas fa-mobile-alt', audio: 'fas fa-headphones',
    gaming: 'fas fa-gamepad'
  };

  function load() {
    try {
      const current = JSON.parse(localStorage.getItem(KEY) || 'null');
      if (Array.isArray(current)) {
        // Normalize older cart entries so shipping calculations remain reliable.
        const normalized = current.map(i => ({
          ...i,
          qty: Math.min(Math.max(parseInt(i.qty || 1, 10), 1), 10),
          productId: i.productId ?? i.product_id ?? null,
          weight_kg: Math.max(0, parseFloat(i.weight_kg ?? i.weight ?? 0) || 0),
          free_delivery: !!(i.free_delivery ?? i.freeDelivery ?? false),
        }));
        if (JSON.stringify(normalized) !== JSON.stringify(current)) {
          localStorage.setItem(KEY, JSON.stringify(normalized));
        }
        return normalized;
      }

      const legacy = JSON.parse(localStorage.getItem(LEGACY_KEY) || 'null');
      if (Array.isArray(legacy)) {
        localStorage.setItem(KEY, JSON.stringify(legacy));
        return legacy;
      }

      return [];
    } catch {
      return [];
    }
  }

  function save(items)  { localStorage.setItem(KEY, JSON.stringify(items)); }
  function slugify(s)   { return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); }

  function iconFor(category) { return CAT_ICONS[category] || 'fas fa-box'; }

  const Cart = {
    getItems()  { return load(); },
    getCount()  { return load().reduce((s, i) => s + i.qty, 0); },
    getTotal()  { return load().reduce((s, i) => s + i.price * i.qty, 0); },
    getTotalWeight() {
      return load().reduce((s, i) => {
        if (i.free_delivery) return s;
        return s + ((parseFloat(i.weight_kg || 0) || 0) * i.qty);
      }, 0);
    },

    addItem(product) {
      const items = load();
      const id    = slugify(product.name);
      const exist = items.find(i => i.id === id);
      if (exist) {
        exist.qty = Math.min(exist.qty + 1, 10);
        if (!exist.productId && (product.productId || product.product_id)) {
          exist.productId = product.productId || product.product_id;
        }
        if ((!exist.weight_kg || parseFloat(exist.weight_kg) <= 0) && (product.weight_kg || product.weight)) {
          exist.weight_kg = Math.max(0, parseFloat(product.weight_kg ?? product.weight ?? 0) || 0);
        }
        if (product.free_delivery !== undefined) {
          exist.free_delivery = !!product.free_delivery;
        }
      } else {
        items.push({
          id,
          productId: product.productId || null,
          name:     product.name,
          category: product.category || '',
          price:    product.price,
          oldPrice: product.oldPrice || null,
          icon:     product.icon || iconFor(product.category),
          weight_kg: Math.max(0, parseFloat(product.weight_kg ?? product.weight ?? 0) || 0),
          free_delivery: !!product.free_delivery,
          qty:      1
        });
      }
      save(items);
      this.updateBadge();
      return items;
    },

    removeItem(id) {
      const items = load().filter(i => i.id !== id);
      save(items); this.updateBadge(); return items;
    },

    updateQty(id, qty) {
      let items = load();
      if (qty < 1) { items = items.filter(i => i.id !== id); }
      else { const it = items.find(i => i.id === id); if (it) it.qty = Math.min(qty, 10); }
      save(items); this.updateBadge(); return items;
    },

    clear() { save([]); this.updateBadge(); },

    updateBadge() {
      const n = this.getCount();
      document.querySelectorAll('#cartCount, #mbCartCount').forEach(b => {
        b.textContent = n;
        if (b.id === 'mbCartCount') b.style.display = n > 0 ? 'flex' : 'none';
        b.style.transform = 'scale(1.5)';
        setTimeout(() => b.style.transform = '', 200);
      });
    },

    fmt(n) { return 'Rs. ' + n.toLocaleString(); },

    buildWaText(number) {
      const items = this.getItems();
      if (!items.length) return '';
      const waNum = number || window.GADGET_HUB_WA || '94777237962';
      let msg = 'Hello Gadget Hub, I would like to order:\n\n';
      items.forEach(i => { msg += `- ${i.name}  x${i.qty}  -  ${this.fmt(i.price * i.qty)}\n`; });
      msg += `\nTotal: ${this.fmt(this.getTotal())}`;
      msg += '\n\nPlease confirm availability and delivery details. Thank you!';
      return 'https://wa.me/' + waNum + '?text=' + encodeURIComponent(msg);
    }
  };

  window.GadgetHubCart = Cart;
  window.GenexCart = Cart;
})(window);
