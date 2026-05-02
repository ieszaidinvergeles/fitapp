<?php
require_once 'functions.php';
require_login();
$error = null;
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $consumed = (int)($_POST['is_consumed'] ?? 0);
        $res = api_put('/meal-schedule/' . $id, ['is_consumed' => !$consumed], auth: true);
    } else {
        $body = ['date' => $_POST['date'] ?? '', 'meal_type' => $_POST['meal_type'] ?? ''];
        if (!empty($_POST['recipe_id'])) $body['recipe_id'] = (int)$_POST['recipe_id'];
        $res = api_post('/meal-schedule', $body, auth: true);
    }
    if (!empty($res['result']) && $res['result'] !== false) $success = api_message($res) ?? 'Saved.';
    else $error = api_message($res) ?? 'Failed.';
}

$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/meal-schedule?page=' . $page, auth: true);
$rows = $response['result']['data'] ?? [];
$recipes = api_get('/recipes', auth: true)['result']['data'] ?? [];
wp_app_page_start('Meal Schedule');
?>
    <?php show_error($error); show_success($success); ?>
    <form method="POST" class="bg-surface-container rounded-2xl p-6 border border-outline-variant/20 grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <input type="hidden" name="action" value="create"/>
        <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3"/>
        <select name="meal_type" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3">
            <option value="breakfast">Breakfast</option><option value="lunch">Lunch</option><option value="dinner">Dinner</option><option value="snack">Snack</option><option value="pre_workout">Pre workout</option><option value="post_workout">Post workout</option>
        </select>
        <select name="recipe_id" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3">
            <option value="">No recipe</option>
            <?php foreach ($recipes as $r): ?><option value="<?= (int)($r['id'] ?? 0) ?>"><?= h($r['name'] ?? '') ?></option><?php endforeach; ?>
        </select>
        <button class="kinetic-gradient text-on-primary-container px-6 py-3 rounded-full font-black uppercase tracking-widest text-xs w-max">Add Meal</button>
    </form>
    <div class="space-y-3">
        <?php foreach ($rows as $m): ?>
            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20 flex items-center justify-between gap-3">
                <div>
                    <p class="font-bold"><?= h($m['date'] ?? '') ?> • <?= h($m['meal_type'] ?? '') ?></p>
                    <p class="text-xs text-on-surface-variant"><?= h($m['recipe']['name'] ?? 'No recipe') ?></p>
                    <p class="text-xs text-on-surface-variant">Calories: <?= h($m['recipe']['calories'] ?? '-') ?> • Protein: <?= h($m['recipe']['macros']['protein'] ?? '-') ?>g • Carbs: <?= h($m['recipe']['macros']['carbs'] ?? '-') ?>g • Fat: <?= h($m['recipe']['macros']['fat'] ?? '-') ?>g</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="toggle"/><input type="hidden" name="id" value="<?= (int)($m['id'] ?? 0) ?>"/>
                    <input type="hidden" name="is_consumed" value="<?= (int)($m['is_consumed'] ?? 0) ?>"/>
                    <button class="px-4 py-2 rounded-full border border-outline-variant/30 text-xs font-bold uppercase tracking-wider"><?= !empty($m['is_consumed']) ? 'Mark Uneaten' : 'Mark Eaten' ?></button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
<?php
wp_app_page_end(false);

