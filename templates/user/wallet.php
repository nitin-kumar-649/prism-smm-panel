<?php ob_start(); ?>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Add Funds -->
    <div class="lg:col-span-1">
        <div class="glass-card rounded-xl border border-white/5 p-6">
            <h3 class="text-lg font-semibold text-white mb-1">Add Funds</h3>
            <p class="text-xs text-gray-500 mb-6">Choose amount and payment method</p>

            <!-- Balance Card -->
            <div class="p-4 rounded-xl bg-gradient-to-r from-indigo-600/20 to-purple-600/20 border border-indigo-500/20 mb-6">
                <p class="text-xs text-gray-400 mb-1">Current Balance</p>
                <p class="text-2xl font-bold text-white"><?= format_money((float) ($currentUser['balance'] ?? 0)) ?></p>
            </div>

            <form id="deposit-form" onsubmit="return handleDeposit(event)">
                <?= csrf_field() ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Amount (<?= env('CURRENCY_SYMBOL', '$') ?>)</label>
                        <input type="number" name="amount" step="0.01" min="1" max="10000" required
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                               placeholder="Enter amount">
                        <div class="flex gap-2 mt-2">
                            <?php foreach ([5, 10, 25, 50, 100] as $amt): ?>
                            <button type="button" onclick="this.closest('form').querySelector('[name=amount]').value=<?= $amt ?>"
                                    class="flex-1 py-1.5 text-xs rounded-lg bg-white/5 text-gray-400 hover:bg-white/10 hover:text-white transition-colors">
                                $<?= $amt ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Payment Method</label>
                        <div class="space-y-2">
                            <?php foreach ($gateways as $key => $gw): ?>
                            <label class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10 cursor-pointer hover:border-indigo-500/30 transition-colors has-[:checked]:border-indigo-500/50 has-[:checked]:bg-indigo-500/5">
                                <input type="radio" name="gateway" value="<?= $key ?>" <?= $key === 'manual' ? 'checked' : '' ?>
                                       class="w-4 h-4 text-indigo-600 bg-white/5 border-white/20 focus:ring-indigo-500">
                                <i class="<?= $gw['icon'] ?> text-lg text-gray-400 w-6"></i>
                                <span class="text-sm text-gray-300"><?= e($gw['label']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div id="deposit-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>

                    <button type="submit" id="deposit-btn"
                            class="w-full py-3 rounded-xl bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold shadow-lg shadow-green-500/20 hover:shadow-green-500/40 transition-all duration-300">
                        <i class="fas fa-plus-circle mr-2"></i> Add Funds
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions -->
    <div class="lg:col-span-2">
        <div class="glass-card rounded-xl border border-white/5 overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5">
                <h3 class="text-sm font-semibold text-white">Transaction History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-white/[0.03]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">ID</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Type</th>
                            <th class="px-4 py-3 text-right text-xs text-gray-500 font-medium">Amount</th>
                            <th class="px-4 py-3 text-right text-xs text-gray-500 font-medium hidden md:table-cell">Balance</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium hidden lg:table-cell">Description</th>
                            <th class="px-4 py-3 text-right text-xs text-gray-500 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($transactions['data'])): ?>
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">No transactions yet.</td></tr>
                        <?php else: foreach ($transactions['data'] as $txn): ?>
                        <tr class="hover:bg-white/[0.02]">
                            <td class="px-4 py-3 text-xs font-mono text-gray-500"><?= e(substr($txn['transaction_id'], 0, 12)) ?></td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full <?= $txn['amount'] > 0 ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $txn['type'])) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs font-medium <?= $txn['amount'] > 0 ? 'text-green-400' : 'text-red-400' ?>">
                                <?= $txn['amount'] > 0 ? '+' : '' ?><?= format_money((float)$txn['amount']) ?>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-gray-400 hidden md:table-cell"><?= format_money((float)$txn['balance_after']) ?></td>
                            <td class="px-4 py-3 text-xs text-gray-500 truncate max-w-[200px] hidden lg:table-cell"><?= e($txn['description'] ?? '') ?></td>
                            <td class="px-4 py-3 text-right text-xs text-gray-500"><?= time_ago($txn['created_at']) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (($transactions['total_pages'] ?? 0) > 1): ?>
        <div class="flex justify-center mt-4">
            <?= get_pagination_html($transactions['current_page'], $transactions['total_pages'], '/user/wallet') ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function handleDeposit(e) {
    e.preventDefault();
    const form = document.getElementById('deposit-form');
    const btn = document.getElementById('deposit-btn');
    const errorDiv = document.getElementById('deposit-error');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    errorDiv.classList.add('hidden');

    fetch('/user/wallet/deposit', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus-circle mr-2"></i> Add Funds';
    })
    .catch(() => {
        errorDiv.textContent = 'Network error.';
        errorDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus-circle mr-2"></i> Add Funds';
    });

    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
