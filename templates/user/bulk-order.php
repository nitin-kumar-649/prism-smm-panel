<?php ob_start(); ?>

<div class="max-w-3xl mx-auto">
    <div class="glass-card rounded-xl border border-white/5 p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Bulk Orders</h3>
        <p class="text-sm text-gray-500 mb-6">Place multiple orders at once. One order per line.</p>

        <form id="bulk-form" onsubmit="return handleBulkOrder(event)">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Format: <code class="text-indigo-400">service_id|link|quantity</code></label>
                    <textarea name="orders" rows="10" required
                              class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white font-mono text-sm placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none resize-none"
                              placeholder="1|https://instagram.com/user1|1000&#10;2|https://instagram.com/p/abc123|500&#10;5|https://youtube.com/watch?v=xyz|2000"></textarea>
                </div>

                <div class="p-4 rounded-xl bg-indigo-500/5 border border-indigo-500/10 text-sm text-gray-400">
                    <i class="fas fa-info-circle text-indigo-400 mr-1"></i>
                    Each line = one order. Use pipe (|) to separate service ID, link, and quantity.
                </div>

                <div id="bulk-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>
                <div id="bulk-results" class="hidden space-y-2"></div>

                <button type="submit" id="bulk-btn"
                        class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 transition-all duration-300">
                    <i class="fas fa-layer-group mr-2"></i> Place Bulk Orders
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleBulkOrder(e) {
    e.preventDefault();
    const form = document.getElementById('bulk-form');
    const btn = document.getElementById('bulk-btn');
    const errorDiv = document.getElementById('bulk-error');
    const resultsDiv = document.getElementById('bulk-results');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    errorDiv.classList.add('hidden');
    resultsDiv.classList.add('hidden');

    fetch('/user/bulk-order', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            resultsDiv.innerHTML = data.results.map((r, i) =>
                `<div class="p-2 rounded-lg text-xs ${r.success ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400'}">
                    Order ${i+1}: ${r.success ? 'Success - ' + r.order_id : r.error}
                </div>`
            ).join('');
            resultsDiv.classList.remove('hidden');
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-layer-group mr-2"></i> Place Bulk Orders';
    });
    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
