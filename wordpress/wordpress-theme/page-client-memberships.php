<?php
require_once 'functions.php';
require_login();

$membershipPurchaseError = null;


$dashboardResponse = api_get('/dashboard', auth: true);
$dashboardData = $dashboardResponse['result'] ?? [];
$user = $dashboardData['user'] ?? [];
$userId = $user['id'] ?? 0;

$response = api_get('/membership-plans', auth: false);
$result = $response['result'] ?? [];
$plans = isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);

// Handle Purchase POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['purchase_plan_id'])) {
    $planId = (int)$_POST['purchase_plan_id'];
    
    // Determine User Situation
    $isNewUser = empty($user['membership_plan_id']);
    $isReturningUser = !empty($user['membership_plan_id']) && ($user['membership_status'] ?? '') !== 'active';
    $isActiveUser = ($user['membership_status'] ?? '') === 'active';

    $update = api_post('/users/' . $userId, [
        '_method' => 'PUT',
        'membership_plan_id' => $planId,
        'membership_status' => 'active'
    ], auth: true);

    if (!empty($update['result']) && $update['result'] !== false) {
        $statusParam = 'success';
        if ($isNewUser) {
            $statusParam = 'pending_verification';
        } elseif ($isActiveUser) {
            $statusParam = 'plan_changed';
        }
        
        wp_redirect(home_url('/?pagename=client-dashboard&purchase=' . $statusParam));
        exit;
    } else {
        $membershipPurchaseError = api_message($update) ?: 'Could not process purchase.';
    }
}

// Handle Cancellation POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_membership') {
    $update = api_post('/users/' . $userId, [
        '_method' => 'PUT',
        'membership_plan_id' => null,
        'membership_status' => 'expired'
    ], auth: true);

    if (!empty($update['result']) && $update['result'] !== false) {
        wp_redirect(home_url('/?pagename=client-dashboard&cancelled=1'));
        exit;
    } else {
        $membershipPurchaseError = api_message($update) ?: 'Could not cancel membership.';
    }
}

wp_app_page_start('Choose Your Plan');
?>

<?php if ($membershipPurchaseError): ?>
    <div class="mb-8 bg-error/10 border border-error/20 p-6 rounded-[2rem] text-error flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
        <div class="w-12 h-12 rounded-2xl bg-error/20 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined">warning</span>
        </div>
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest opacity-50 mb-1">Purchase Error</p>
            <p class="text-sm font-bold"><?= h($membershipPurchaseError) ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if (($response['result'] ?? null) === false): ?>
    <div class="bg-error-container/20 border border-error/20 p-10 rounded-[3rem] text-error text-center mb-12">
        <span class="material-symbols-outlined text-6xl mb-4 opacity-50">cloud_off</span>
        <h3 class="text-2xl font-headline font-black uppercase tracking-tight mb-2">Service Unavailable</h3>
        <p class="font-bold opacity-70"><?= api_message($response) ?: 'We could not connect to the membership service.' ?></p>
    </div>
<?php elseif (!$plans): ?>
    <div class="text-center py-20 bg-surface-container rounded-3xl border border-outline-variant/10">
        <span class="material-symbols-outlined text-6xl text-zinc-600 mb-4 opacity-20">inventory_2</span>
        <h3 class="text-2xl font-headline font-bold text-zinc-500 uppercase tracking-tight">No Plans Available</h3>
        <p class="text-zinc-400 mt-2">Check back soon for new membership options.</p>
    </div>
<?php else: ?>
    <?php 
    $isPendingActivation = !empty($user['membership_plan_id']) && empty($user['current_gym_id']);
    
    // Extract the three main plans
    $physicalPlan = null;
    $onlinePlan = null;
    $duoPlan = null;

    foreach ($plans as $plan) {
        $type = strtolower($plan['type'] ?? '');
        if ($type === 'physical' && !$physicalPlan) $physicalPlan = $plan;
        if ($type === 'online' && !$onlinePlan) $onlinePlan = $plan;
        if ($type === 'duo' && !$duoPlan) $duoPlan = $plan;
    }

    // Default features if not provided by API
    $getFeatures = function($type) {
        return [
            'online' => [
                ['icon' => 'edgesensor_low', 'text' => 'Unlimited Routine Library'],
                ['icon' => 'restaurant', 'text' => 'Personalized Recipe Vault'],
                ['icon' => 'video_library', 'text' => '100+ Exercise Tutorials'],
            ],
            'physical' => [
                ['icon' => 'schedule', 'text' => '24/7 Gym Access'],
                ['icon' => 'lock', 'text' => 'Personal Locker'],
                ['icon' => 'spa', 'text' => 'Recovery Zone'],
            ],
            'duo' => [
                ['icon' => 'group', 'text' => '2 Full Access Passes'],
                ['icon' => 'sync', 'text' => 'Buddy Workout Sync'],
                ['icon' => 'confirmation_number', 'text' => '10 Guest Passes/Mo'],
            ],
        ][$type] ?? [];
    };
    ?>

    <!-- Hero Section -->
    <section class="mb-16">
        <p class="font-body text-on-surface-variant max-w-md text-sm md:text-lg leading-relaxed mt-4 animate-in fade-in slide-in-from-left duration-700 delay-200">
            Unleash peak performance with tiered programs designed to optimize every metric of your physical output.
        </p>
    </section>

    <?php if ($isPendingActivation): ?>
        <div class="bg-primary/10 border border-primary/20 rounded-3xl p-6 mb-12 flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="w-12 h-12 rounded-2xl bg-primary/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary">info</span>
            </div>
            <p class="text-xs font-bold text-zinc-300">
                <span class="text-primary uppercase tracking-widest block mb-1">Activation Pending</span>
                You already have a plan. Please visit your local gym to finalize your registration and assign your center.
            </p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch mb-24">
        
        <!-- ONLINE PLAN -->
        <?php if ($onlinePlan): ?>
        <div class="relative bg-surface-container p-8 rounded-3xl flex flex-col justify-between border border-outline-variant/10 group hover:border-primary/30 transition-all duration-500 animate-in fade-in zoom-in-95 duration-700 delay-300">
            <div>
                <div class="flex justify-between items-start mb-8">
                    <span class="material-symbols-outlined text-primary text-5xl opacity-40 group-hover:opacity-100 transition-opacity" style='font-variation-settings: "FILL" 1;'>devices</span>
                    <span class="font-headline font-bold text-2xl text-primary/20">01</span>
                </div>
                <h3 class="font-headline text-3xl font-black tracking-tight uppercase mb-2">ONLINE</h3>
                <p class="text-on-surface-variant text-xs mb-8 font-medium leading-relaxed"><?= h($onlinePlan['description'] ?? 'Train anywhere with elite digital coaching and tracking.') ?></p>
                <ul class="space-y-4 mb-10">
                    <?php foreach ($getFeatures('online') as $f): ?>
                        <li class="flex items-center gap-3 text-[10px] font-black uppercase tracking-wider text-on-surface">
                            <span class="material-symbols-outlined text-primary text-lg" style="font-variation-settings: 'FILL' 1;"><?= $f['icon'] ?></span>
                            <?= $f['text'] ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <div class="mb-6">
                    <span class="font-headline text-4xl font-black text-primary"><?= (int)$onlinePlan['price'] ?>€</span>
                    <span class="text-on-surface-variant font-bold text-xs uppercase tracking-widest">/MO</span>
                </div>
                <?php 
                    $isCurrent = ($user['membership_plan_id'] ?? 0) == $onlinePlan['id'];
                    $isDisabled = $isCurrent || $isPendingActivation;
                ?>
                <button 
                    <?= !$isDisabled ? 'onclick="openCheckout(' . h(json_encode($onlinePlan)) . ')"' : '' ?>
                    class="w-full py-4 rounded-full font-black uppercase tracking-widest text-xs transition-all duration-300 <?= $isDisabled ? 'bg-zinc-800 text-zinc-500 cursor-not-allowed' : 'bg-gradient-to-r from-primary to-primary-fixed text-on-primary shadow-lg shadow-primary/10 active:scale-95' ?>"
                    <?= $isDisabled ? 'disabled' : '' ?>
                >
                    <?= $isCurrent ? 'Current Plan' : ($isPendingActivation ? 'Activation Pending' : 'Start Digital') ?>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- PHYSICAL PLAN (Centerpiece) -->
        <?php if ($physicalPlan): ?>
        <div class="relative bg-surface-container-high p-8 rounded-[2rem] flex flex-col justify-between border-4 border-primary shadow-[0_0_40px_rgba(215,255,0,0.2)] md:-translate-y-6 z-10 animate-in fade-in zoom-in-95 duration-700 delay-500">
            <div class="absolute top-0 right-0 bg-primary text-on-primary px-6 py-1.5 font-headline font-black text-[10px] tracking-widest uppercase rounded-bl-2xl">
                Most Popular
            </div>
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/20 blur-[80px] rounded-full"></div>
            
            <div>
                <div class="flex justify-between items-start mb-8">
                    <span class="material-symbols-outlined text-primary text-5xl" style='font-variation-settings: "FILL" 1;'>fitness_center</span>
                    <span class="font-headline font-bold text-2xl text-primary/40">02</span>
                </div>
                <h3 class="font-headline text-3xl font-black tracking-tight uppercase mb-2 drop-shadow-[0_0_10px_rgba(215,255,0,0.5)]">PHYSICAL</h3>
                <p class="text-zinc-400 text-xs mb-8 font-medium leading-relaxed"><?= h($physicalPlan['description'] ?? 'Unrestricted access to our high-performance power houses.') ?></p>
                <ul class="space-y-4 mb-10">
                    <?php foreach ($getFeatures('physical') as $f): ?>
                        <li class="flex items-center gap-3 text-[10px] font-black uppercase tracking-wider text-on-surface">
                            <span class="material-symbols-outlined text-primary text-lg" style="font-variation-settings: 'FILL' 1;"><?= $f['icon'] ?></span>
                            <?= $f['text'] ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <div class="mb-6">
                    <span class="font-headline text-5xl font-black text-primary"><?= (int)$physicalPlan['price'] ?>€</span>
                    <span class="text-on-surface-variant font-bold text-xs uppercase tracking-widest">/MO</span>
                </div>
                <?php 
                    $isCurrent = ($user['membership_plan_id'] ?? 0) == $physicalPlan['id'];
                    $isDisabled = $isCurrent || $isPendingActivation;
                ?>
                <button 
                    <?= !$isDisabled ? 'onclick="openCheckout(' . h(json_encode($physicalPlan)) . ')"' : '' ?>
                    class="w-full py-5 rounded-full font-black uppercase tracking-widest text-[10px] transition-all duration-300 <?= $isDisabled ? 'bg-zinc-800 text-zinc-500 cursor-not-allowed' : 'bg-primary text-on-primary shadow-xl shadow-primary/20 active:scale-95' ?>"
                    <?= $isDisabled ? 'disabled' : '' ?>
                >
                    <?= $isCurrent ? 'Current Plan' : ($isPendingActivation ? 'Activation Pending' : 'Join the Lab') ?>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- DUO PLAN -->
        <?php if ($duoPlan): ?>
        <div class="relative bg-surface-container p-8 rounded-3xl flex flex-col justify-between border border-outline-variant/10 group hover:border-primary/30 transition-all duration-500 animate-in fade-in zoom-in-95 duration-700 delay-700">
            <div>
                <div class="flex justify-between items-start mb-8">
                    <span class="material-symbols-outlined text-primary text-5xl opacity-40 group-hover:opacity-100 transition-opacity" style='font-variation-settings: "FILL" 1;'>groups</span>
                    <span class="font-headline font-bold text-2xl text-primary/20">03</span>
                </div>
                <h3 class="font-headline text-3xl font-black tracking-tight uppercase mb-2">DUO</h3>
                <p class="text-on-surface-variant text-xs mb-8 font-medium leading-relaxed"><?= h($duoPlan['description'] ?? 'Performance is better together. Shared gains, shared energy.') ?></p>
                <ul class="space-y-4 mb-10">
                    <?php foreach ($getFeatures('duo') as $f): ?>
                        <li class="flex items-center gap-3 text-[10px] font-black uppercase tracking-wider text-on-surface">
                            <span class="material-symbols-outlined text-primary text-lg" style="font-variation-settings: 'FILL' 1;"><?= $f['icon'] ?></span>
                            <?= $f['text'] ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <div class="mb-6">
                    <span class="font-headline text-4xl font-black text-primary"><?= (int)$duoPlan['price'] ?>€</span>
                    <span class="text-on-surface-variant font-bold text-xs uppercase tracking-widest">/MO</span>
                </div>
                <?php 
                    $isCurrent = ($user['membership_plan_id'] ?? 0) == $duoPlan['id'];
                    $isDisabled = $isCurrent || $isPendingActivation;
                ?>
                <button 
                    <?= !$isDisabled ? 'onclick="openCheckout(' . h(json_encode($duoPlan)) . ')"' : '' ?>
                    class="w-full py-4 rounded-full font-black uppercase tracking-widest text-xs transition-all duration-300 <?= $isDisabled ? 'bg-zinc-800 text-zinc-500 cursor-not-allowed' : 'bg-gradient-to-r from-primary to-primary-fixed text-on-primary shadow-lg shadow-primary/10 active:scale-95' ?>"
                    <?= $isDisabled ? 'disabled' : '' ?>
                >
                    <?= $isCurrent ? 'Current Plan' : ($isPendingActivation ? 'Activation Pending' : 'Upgrade Both') ?>
                </button>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <?php if (!empty($user['membership_plan_id']) && !empty($user['current_gym_id'])): ?>
        <div class="mt-24 pt-12 border-t border-outline-variant/10">
            <div class="bg-error/5 border border-error/10 rounded-[2.5rem] p-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="text-center md:text-left">
                    <h3 class="text-2xl font-headline font-black uppercase tracking-tight text-white mb-2 italic">Cancel Membership</h3>
                    <p class="text-zinc-500 text-sm font-medium max-w-md">
                        If you cancel now, you will lose access to all facilities and online programs at the end of your current period.
                    </p>
                </div>
                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel your membership? This action cannot be undone.')">
                    <input type="hidden" name="action" value="cancel_membership">
                    <button type="submit" class="px-8 py-4 bg-zinc-900 text-error border border-error/20 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-error hover:text-white transition-all duration-300">
                        Terminate Plan
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Checkout Modal -->
<div id="checkout-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 backdrop-blur-md p-4 transition-all duration-300">
    <div id="checkout-box" class="bg-surface-container rounded-[2.5rem] max-w-lg w-full border border-outline-variant/20 shadow-2xl overflow-hidden scale-95 opacity-0 transition-all duration-300">
        
        <div class="p-8 pb-0 flex items-center justify-between">
            <h3 class="text-2xl font-headline font-black uppercase tracking-tight">Complete Purchase</h3>
            <button onclick="closeCheckout()" class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-zinc-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" id="purchase-form" class="p-8">
            <input type="hidden" name="purchase_plan_id" id="modal-plan-id">
            
            <div class="bg-surface-container-low p-6 rounded-3xl mb-8 border border-outline-variant/10">
                <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mb-1">Plan Summary</p>
                <div class="flex items-center justify-between">
                    <h4 id="modal-plan-name" class="text-xl font-headline font-bold uppercase tracking-tight text-primary">--</h4>
                    <span id="modal-plan-price" class="text-2xl font-headline font-black tracking-tighter">--</span>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">Card Holder</label>
                    <input type="text" placeholder="John Doe" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:outline-none focus:border-primary/50 transition-colors">
                </div>
                <div>
                    <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">Card Number</label>
                    <div class="relative">
                        <input type="text" placeholder="XXXX XXXX XXXX XXXX" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:outline-none focus:border-primary/50 transition-colors">
                        <span class="material-symbols-outlined absolute right-5 top-1/2 -translate-y-1/2 text-zinc-600">credit_card</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">Expiry</label>
                        <input type="text" placeholder="MM/YY" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:outline-none focus:border-primary/50 transition-colors">
                    </div>
                    <div>
                        <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">CVV</label>
                        <input type="text" placeholder="XXX" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:outline-none focus:border-primary/50 transition-colors">
                    </div>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="w-full bg-primary text-on-primary font-black py-5 rounded-2xl text-sm uppercase tracking-widest mt-10 hover:scale-[1.02] transition-transform shadow-2xl shadow-primary/20 flex items-center justify-center gap-3">
                Confirm Purchase
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
            
            <p class="text-center text-[10px] text-zinc-500 font-bold uppercase tracking-widest mt-6 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-xs">lock</span>
                Secure 256-bit SSL encrypted payment
            </p>
        </form>

        <!-- Loading Overlay (hidden by default) -->
        <div id="loading-overlay" class="absolute inset-0 bg-surface-container/80 backdrop-blur-sm hidden items-center justify-center flex-col z-20">
            <div class="w-16 h-16 border-4 border-primary/20 border-t-primary rounded-full animate-spin mb-4"></div>
            <p class="font-headline font-bold uppercase tracking-widest text-sm">Processing Payment...</p>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('checkout-modal');
    const box = document.getElementById('checkout-box');
    const form = document.getElementById('purchase-form');
    const loading = document.getElementById('loading-overlay');

    function openCheckout(plan) {
        document.getElementById('modal-plan-id').value = plan.id;
        document.getElementById('modal-plan-name').innerText = plan.name;
        document.getElementById('modal-plan-price').innerText = plan.price + '€';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
        }, 50);
    }

    function closeCheckout() {
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    form.addEventListener('submit', (e) => {
        loading.classList.remove('hidden');
        loading.classList.add('flex');
        // Let the form submit naturally to PHP
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeCheckout();
    });
</script>

<?php
wp_app_page_end();
?>
