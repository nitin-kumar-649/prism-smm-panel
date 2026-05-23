<?php ob_start(); ?>

<div class="min-h-screen py-20 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 text-sm mb-4">
                <i class="fas fa-arrow-left"></i> Back to home
            </a>
            <h1 class="text-3xl font-bold text-white"><?= e($page['title']) ?></h1>
        </div>
        <div class="glass-card rounded-2xl border border-white/10 p-8 prose prose-invert max-w-none">
            <?= $page['content'] ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/auth.php'; ?>
