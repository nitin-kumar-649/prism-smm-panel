<?php ob_start(); ?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- API Key Card -->
    <div class="glass-card rounded-xl border border-white/5 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Your API Key</h3>
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input type="text" id="api-key-input" readonly value="<?= e($currentUser['api_key'] ?? 'No API key generated') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-gray-300 font-mono text-sm outline-none pr-12">
                <button onclick="copyApiKey()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-indigo-400 transition-colors" title="Copy">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            <button onclick="regenerateApiKey()" class="px-5 py-3 rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500/20 text-sm font-medium transition-colors whitespace-nowrap">
                <i class="fas fa-sync-alt mr-2"></i> Regenerate
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-2"><i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i> Keep your API key secret. Regenerating will invalidate the old key.</p>
    </div>

    <!-- API Endpoint -->
    <div class="glass-card rounded-xl border border-white/5 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">API Endpoint</h3>
        <div class="p-4 rounded-lg bg-white/5 border border-white/10">
            <code class="text-sm text-indigo-400 font-mono"><?= url('api/v2') ?></code>
        </div>
        <p class="text-xs text-gray-500 mt-2">All requests must include the <code class="text-indigo-400">key</code> parameter.</p>
    </div>

    <!-- API Methods -->
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-white">Available Actions</h3>

        <?php
        $apiMethods = [
            [
                'title' => 'Get Services',
                'params' => ['action' => 'services'],
                'response' => '[{"service": 1, "name": "Instagram Followers", "rate": "2.50", "min": 100, "max": 50000}]'
            ],
            [
                'title' => 'Add Order',
                'params' => ['action' => 'add', 'service' => '1', 'link' => 'https://instagram.com/user', 'quantity' => '1000'],
                'response' => '{"order": 12345}'
            ],
            [
                'title' => 'Order Status',
                'params' => ['action' => 'status', 'order' => '12345'],
                'response' => '{"charge": "2.50", "start_count": "1000", "status": "Completed", "remains": "0"}'
            ],
            [
                'title' => 'Multi Order Status',
                'params' => ['action' => 'status', 'orders' => '1,2,3'],
                'response' => '{"1": {"status": "Completed"}, "2": {"status": "Processing"}}'
            ],
            [
                'title' => 'Refill Order',
                'params' => ['action' => 'refill', 'order' => '12345'],
                'response' => '{"refill": 12345}'
            ],
            [
                'title' => 'Cancel Order',
                'params' => ['action' => 'cancel', 'order' => '12345'],
                'response' => '{"cancel": 12345}'
            ],
            [
                'title' => 'Check Balance',
                'params' => ['action' => 'balance'],
                'response' => '{"balance": "150.00", "currency": "USD"}'
            ],
        ];
        foreach ($apiMethods as $method):
        ?>
        <div class="glass-card rounded-xl border border-white/5 p-5">
            <h4 class="text-sm font-semibold text-white mb-3"><?= $method['title'] ?></h4>
            <div class="mb-3">
                <span class="text-xs text-gray-500">Parameters:</span>
                <div class="mt-1 p-3 rounded-lg bg-white/5 overflow-x-auto">
                    <code class="text-xs text-gray-300 font-mono">
                        <?php foreach ($method['params'] as $k => $v): ?>
                            <span class="text-indigo-400"><?= $k ?></span>=<span class="text-green-400"><?= $v ?></span><?php if ($k !== array_key_last($method['params'])): ?> &amp; <?php endif; ?>
                        <?php endforeach; ?>
                    </code>
                </div>
            </div>
            <div>
                <span class="text-xs text-gray-500">Response:</span>
                <pre class="mt-1 p-3 rounded-lg bg-white/5 text-xs text-gray-300 font-mono overflow-x-auto"><?= e($method['response']) ?></pre>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function copyApiKey() {
    const input = document.getElementById('api-key-input');
    navigator.clipboard.writeText(input.value).then(() => showToast('API key copied!', 'success'));
}

function regenerateApiKey() {
    if (!confirm('Regenerate API key? The old key will stop working.')) return;
    ajaxPost('/user/api/regenerate', {}, (data) => {
        if (data.success) {
            document.getElementById('api-key-input').value = data.api_key;
            showToast('API key regenerated!', 'success');
        }
    });
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
