<?php ob_start(); ?>

<div class="max-w-3xl mx-auto">
    <div class="glass-card rounded-xl border border-white/5 p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Place New Order</h3>

        <form id="order-form" onsubmit="return handleOrder(event)">
            <?= csrf_field() ?>
            <div class="space-y-5">
                <!-- Category & Service -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Category</label>
                    <select id="category-select" onchange="filterServices(this.value)"
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Service</label>
                    <select id="service-select" name="service_id" required onchange="loadServiceInfo(this.value)"
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none">
                        <option value="">Select a service</option>
                        <?php foreach ($services as $catName => $catServices): ?>
                            <optgroup label="<?= e($catName) ?>" data-category="<?= $catServices[0]['category_id'] ?? '' ?>">
                                <?php foreach ($catServices as $s): ?>
                                    <option value="<?= $s['id'] ?>"
                                            data-price="<?= $s['price_per_1000'] ?>"
                                            data-min="<?= $s['min_quantity'] ?>"
                                            data-max="<?= $s['max_quantity'] ?>"
                                            data-drip="<?= $s['drip_feed'] ?>"
                                            data-category="<?= $s['category_id'] ?>">
                                        <?= e($s['name']) ?> - <?= format_money((float) $s['price_per_1000']) ?>/1K
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Service Info -->
                <div id="service-info" class="hidden p-4 rounded-xl bg-indigo-500/5 border border-indigo-500/10">
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 block">Price/1K</span>
                            <span id="info-price" class="font-semibold text-indigo-400">-</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Min</span>
                            <span id="info-min" class="font-semibold text-white">-</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Max</span>
                            <span id="info-max" class="font-semibold text-white">-</span>
                        </div>
                    </div>
                    <p id="info-desc" class="text-xs text-gray-500 mt-2"></p>
                </div>

                <!-- Link -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Link</label>
                    <input type="url" name="link" required
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                           placeholder="https://instagram.com/p/example">
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Quantity</label>
                    <input type="number" name="quantity" id="quantity-input" required min="1"
                           oninput="calculateCharge()"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                           placeholder="Enter quantity">
                </div>

                <!-- Drip Feed -->
                <div id="drip-feed-section" class="hidden">
                    <label class="flex items-center gap-2 cursor-pointer mb-3">
                        <input type="checkbox" id="drip-feed-toggle" onchange="document.getElementById('drip-feed-fields').classList.toggle('hidden')"
                               class="w-4 h-4 rounded border-white/20 bg-white/5 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-400">Enable Drip-Feed</span>
                    </label>
                    <div id="drip-feed-fields" class="hidden grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Runs</label>
                            <input type="number" name="drip_feed_runs" min="1" value="1"
                                   class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm focus:border-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Interval (minutes)</label>
                            <input type="number" name="drip_feed_interval" min="0" value="60"
                                   class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- Total Charge -->
                <div class="p-4 rounded-xl bg-gradient-to-r from-indigo-500/10 to-purple-500/10 border border-indigo-500/20">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Total Charge</span>
                        <span id="total-charge" class="text-xl font-bold text-indigo-400">$0.00</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Balance: <?= format_money((float) ($currentUser['balance'] ?? 0)) ?></div>
                </div>

                <div id="order-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>

                <button type="submit" id="order-btn"
                        class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Place Order</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentService = null;

function filterServices(categoryId) {
    const select = document.getElementById('service-select');
    const groups = select.querySelectorAll('optgroup');
    groups.forEach(g => {
        const opts = g.querySelectorAll('option');
        let hasVisible = false;
        opts.forEach(o => {
            if (!categoryId || o.dataset.category === categoryId) {
                o.style.display = '';
                hasVisible = true;
            } else {
                o.style.display = 'none';
            }
        });
        g.style.display = (!categoryId || hasVisible) ? '' : 'none';
    });
    select.value = '';
    document.getElementById('service-info').classList.add('hidden');
}

function loadServiceInfo(serviceId) {
    if (!serviceId) return;
    const option = document.querySelector(`#service-select option[value="${serviceId}"]`);
    if (!option) return;

    currentService = {
        price: parseFloat(option.dataset.price),
        min: parseInt(option.dataset.min),
        max: parseInt(option.dataset.max),
        drip: option.dataset.drip === '1'
    };

    document.getElementById('info-price').textContent = '$' + currentService.price.toFixed(4);
    document.getElementById('info-min').textContent = currentService.min.toLocaleString();
    document.getElementById('info-max').textContent = currentService.max.toLocaleString();
    document.getElementById('service-info').classList.remove('hidden');
    document.getElementById('quantity-input').min = currentService.min;
    document.getElementById('quantity-input').max = currentService.max;
    document.getElementById('quantity-input').placeholder = `${currentService.min} - ${currentService.max}`;

    document.getElementById('drip-feed-section').classList.toggle('hidden', !currentService.drip);
    calculateCharge();

    fetch('/user/service-info?id=' + serviceId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (data.description) {
            document.getElementById('info-desc').textContent = data.description;
        }
    });
}

function calculateCharge() {
    if (!currentService) return;
    const qty = parseInt(document.getElementById('quantity-input').value) || 0;
    const charge = (qty / 1000) * currentService.price;
    document.getElementById('total-charge').textContent = '$' + charge.toFixed(2);
}

function handleOrder(e) {
    e.preventDefault();
    const form = document.getElementById('order-form');
    const btn = document.getElementById('order-btn');
    const errorDiv = document.getElementById('order-error');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    errorDiv.classList.add('hidden');

    fetch('/user/new-order', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Order placed! ID: ' + data.order_id, 'success');
            form.reset();
            document.getElementById('service-info').classList.add('hidden');
            document.getElementById('total-charge').textContent = '$0.00';
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-shopping-cart"></i><span>Place Order</span>';
    })
    .catch(() => {
        errorDiv.textContent = 'Network error.';
        errorDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-shopping-cart"></i><span>Place Order</span>';
    });

    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
