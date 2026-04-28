<?php
/*
Template Name: Staff Manage Routines
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page'] ?? 1));

$response = api_get('/routines?page=' . $page, auth: true);

function extract_list_from_response(array $response): array
{
    if (!empty($response['result']['data'])) {
        return $response['result']['data'];
    }

    if (!empty($response['result'])) {
        return $response['result'];
    }

    return [];
}

$routines = extract_list_from_response($response);

wp_app_page_start('Manage Routines', true);
?>

<?php if (($response['result'] ?? null) === false): ?>
    <?php show_error(api_message($response)); ?>
<?php endif; ?>

<div class="space-y-6">

    <!-- HEADER -->
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Routine List</h2>
            <p class="text-sm text-on-surface-variant">
                Gestiona rutinas, edítalas o elimínalas fácilmente.
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-routine')) ?>"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] hover:scale-[1.01] transition"
        >
            <span>+</span>
            <span>Create routine</span>
        </a>
    </section>

    <!-- LIST -->
    <section class="space-y-3">

        <?php foreach ($routines as $index => $r): ?>

            <?php
            $routine_id = (int)($r['id'] ?? 0);

            // Imagen placeholder (puedes cambiarlo luego por campo real)
            $images = [
                'https://images.unsplash.com/photo-1558611848-73f7eb4001a1',
                'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b',
                'https://images.unsplash.com/photo-1599058917765-a780eda07a3e',
                'https://images.unsplash.com/photo-1605296867424-35fc25c9212a'
            ];

            $img = $images[$index % count($images)];

            $name = $r['name'] ?? 'Routine';
            $difficulty = $r['difficulty_level'] ?? '-';
            $goal = $r['goal'] ?? '-';
            ?>

            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">

                <div class="flex gap-4 items-center justify-between">

                    <!-- IZQUIERDA: imagen + texto -->
                    <div class="flex gap-4 items-center min-w-0">

                        <!-- Imagen -->
                        <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="<?= esc_url($img) ?>"
                                 class="w-full h-full object-cover">
                        </div>

                        <!-- Info -->
                        <div class="min-w-0">
                            <p class="font-bold text-lg">
                                <?= h($name) ?>
                            </p>

                            <p class="text-sm text-on-surface-variant">
                                Difficulty: <?= h($difficulty) ?>
                                • Goal: <?= h($goal) ?>
                            </p>
                        </div>

                    </div>

                    <!-- DERECHA: botones -->
                    <div class="flex flex-wrap gap-2">

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-routine&id=' . $routine_id)) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-outline-variant/30 text-sm hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-routine&id=' . $routine_id)) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-outline-variant/30 text-sm hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('¿Eliminar esta rutina?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="routine_id" value="<?= $routine_id ?>">

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-error/40 text-sm text-error hover:bg-error/10"
                            >
                                Delete
                            </button>
                        </form>

                    </div>

                </div>

            </article>

        <?php endforeach; ?>

        <?php if (!$routines): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No routines found.</p>
            </div>
        <?php endif; ?>

    </section>

</div>

<?php
wp_app_page_end(true);
?>