<?php ob_start(); ?>

<!-- Hero Section -->
<div class="min-h-screen">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 glass-navbar border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <span class="text-sm font-black text-white">P</span>
                </div>
                <span class="text-lg font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Prism SMM</span>
            </div>
            <div class="flex items-center gap-3">
                <?php if (is_logged_in()): ?>
                    <a href="/<?= current_user_role() === 'admin' ? 'admin' : 'user' ?>" class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all">
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="/login" class="px-4 py-2 rounded-xl text-gray-400 hover:text-white text-sm font-medium transition-colors">Sign In</a>
                    <a href="/register" class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all">
                        Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="relative py-20 lg:py-32 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-sm mb-6">
                <i class="fas fa-bolt"></i>
                <span>#1 SMM Panel for Social Media Growth</span>
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
                Grow Your <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">Social Media</span> Presence
            </h1>
            <p class="text-lg text-gray-400 max-w-2xl mx-auto mb-10">
                Premium social media marketing services at the best prices. Instagram, YouTube, TikTok, Twitter, and more. Fast delivery, 24/7 support, and a powerful API.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/register" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-xl shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-300 flex items-center gap-2">
                    <span>Start Now</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#services" class="px-8 py-3.5 rounded-xl bg-white/5 border border-white/10 text-gray-300 font-semibold hover:bg-white/10 transition-all duration-300">
                    View Services
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="max-w-4xl mx-auto mt-20 grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $heroStats = [
                ['icon' => 'fas fa-users', 'value' => '50K+', 'label' => 'Happy Clients'],
                ['icon' => 'fas fa-shopping-cart', 'value' => '1M+', 'label' => 'Orders Completed'],
                ['icon' => 'fas fa-cubes', 'value' => count($categories ?? []) . '+', 'label' => 'Platforms'],
                ['icon' => 'fas fa-headset', 'value' => '24/7', 'label' => 'Support'],
            ];
            foreach ($heroStats as $stat):
            ?>
            <div class="glass-card rounded-xl border border-white/5 p-5 text-center">
                <i class="<?= $stat['icon'] ?> text-indigo-400 text-xl mb-2"></i>
                <div class="text-2xl font-bold text-white"><?= $stat['value'] ?></div>
                <div class="text-xs text-gray-500"><?= $stat['label'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Features -->
    <section class="py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-white text-center mb-12">Why Choose <span class="text-indigo-400">Prism</span>?</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <?php
                $features = [
                    ['icon' => 'fas fa-bolt', 'title' => 'Instant Delivery', 'desc' => 'Most orders start within minutes. Fast, reliable, and consistent.'],
                    ['icon' => 'fas fa-shield-alt', 'title' => 'Safe & Secure', 'desc' => 'CSRF, XSS, SQL injection protection. Your data is always safe.'],
                    ['icon' => 'fas fa-code', 'title' => 'Powerful API', 'desc' => 'Full REST API for automation. Integrate with your own applications.'],
                    ['icon' => 'fas fa-sync', 'title' => 'Auto Refill', 'desc' => 'Automatic refill guarantee on supported services.'],
                    ['icon' => 'fas fa-layer-group', 'title' => 'Drip-Feed', 'desc' => 'Gradual delivery for natural-looking growth.'],
                    ['icon' => 'fas fa-headset', 'title' => '24/7 Support', 'desc' => 'Ticket support system with fast response times.'],
                ];
                foreach ($features as $feature):
                ?>
                <div class="glass-card rounded-xl border border-white/5 p-6 hover:border-indigo-500/30 transition-all duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center mb-4 group-hover:bg-indigo-500/20 transition-colors">
                        <i class="<?= $feature['icon'] ?> text-indigo-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2"><?= $feature['title'] ?></h3>
                    <p class="text-sm text-gray-400"><?= $feature['desc'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Services Preview -->
    <section id="services" class="py-20 px-4 bg-white/[0.02]">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-white text-center mb-4">Our Services</h2>
            <p class="text-gray-400 text-center mb-12">Premium quality services for all major social media platforms</p>

            <?php if (!empty($services)): ?>
            <div class="space-y-8">
                <?php foreach ($services as $categoryName => $categoryServices): ?>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-hashtag text-indigo-400"></i>
                        <?= e($categoryName) ?>
                    </h3>
                    <div class="glass-card rounded-xl border border-white/5 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-gray-400 font-medium">ID</th>
                                        <th class="px-4 py-3 text-left text-gray-400 font-medium">Service</th>
                                        <th class="px-4 py-3 text-center text-gray-400 font-medium">Rate/1K</th>
                                        <th class="px-4 py-3 text-center text-gray-400 font-medium">Min</th>
                                        <th class="px-4 py-3 text-center text-gray-400 font-medium">Max</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php foreach ($categoryServices as $service): ?>
                                    <tr class="hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 text-gray-500"><?= $service['id'] ?></td>
                                        <td class="px-4 py-3 text-gray-300">
                                            <?= e($service['name']) ?>
                                            <?php if ($service['refill']): ?><span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-green-500/10 text-green-400">Refill</span><?php endif; ?>
                                            <?php if ($service['drip_feed']): ?><span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400">Drip-feed</span><?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-center font-medium text-indigo-400"><?= format_money((float) $service['price_per_1000']) ?></td>
                                        <td class="px-4 py-3 text-center text-gray-400"><?= number_format($service['min_quantity']) ?></td>
                                        <td class="px-4 py-3 text-center text-gray-400"><?= number_format($service['max_quantity']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="text-center mt-10">
                <a href="/register" class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all">
                    <span>Sign Up & Order Now</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="text-xs font-black text-white">P</span>
                    </div>
                    <span class="text-sm text-gray-500">&copy; <?= date('Y') ?> Prism SMM Panel. All rights reserved.</span>
                </div>
                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <a href="/page/terms" class="hover:text-gray-300 transition-colors">Terms</a>
                    <a href="/page/privacy" class="hover:text-gray-300 transition-colors">Privacy</a>
                    <a href="/page/faq" class="hover:text-gray-300 transition-colors">FAQ</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/auth.php'; ?>
