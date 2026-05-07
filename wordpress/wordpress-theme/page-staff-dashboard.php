<?php
/*
Template Name: Staff Dashboard
*/
require_once 'functions.php';
require_advanced();

$response = api_get('/staff/dashboard', auth: true);
$data = $response['result'] ?? [];

$user = $data['user'] ?? [];
$todayClasses = $data['today_classes'] ?? [];
$scheduleClasses = $data['upcoming_classes'] ?? $todayClasses;
$scheduleClasses = is_array($scheduleClasses) ? array_slice($scheduleClasses, 0, 4) : [];

$notif = isset($data['pending_notifications']) && is_array($data['pending_notifications'])
    ? count($data['pending_notifications'])
    : (int)($data['unread_notifications_count'] ?? 0);

$totalUsers = (int)($data['active_members_count'] ?? $data['stats']['total_users'] ?? 0);
$activeMemberships = (int)($data['active_members_count'] ?? $data['stats']['active_memberships'] ?? 0);
$totalGyms = isset($data['gyms']) && is_array($data['gyms'])
    ? count($data['gyms'])
    : (int)($data['stats']['total_gyms'] ?? 0);

$staff_name = $user['full_name'] ?? $user['username'] ?? 'Team Member';
$staff_role = $user['role'] ?? 'Staff';
$staff_intro_bits = array_filter([h($staff_name), h($staff_role)], static function ($value) {
    return $value !== '';
});

function staff_dashboard_date_label($value): string
{
    $timestamp = strtotime((string)$value);

    if (!$timestamp) {
        return '';
    }

    $date_key = date('Y-m-d', $timestamp);
    $today_key = date('Y-m-d');

    return $date_key === $today_key ? 'Today' : date('M j', $timestamp);
}

function staff_dashboard_time_label($value): string
{
    $timestamp = strtotime((string)$value);

    return $timestamp ? date('H:i', $timestamp) : '';
}

function staff_dashboard_class_title(array $class): string
{
    $activity = $class['activity']['name'] ?? '';
    $name = $class['name'] ?? '';

    return h($activity ?: $name ?: 'Class');
}

$page_title = 'Staff Portal';
$GLOBALS['hide_global_header'] = true;
$GLOBALS['hide_global_footer'] = true;

voltgym_get_header();
?>

<header class="fixed top-0 left-0 z-50 flex h-16 w-full items-center justify-between border-b border-outline-variant/10 bg-[#0d0f08]/95 px-6 backdrop-blur-xl">
    <div class="flex items-center gap-4">
        <span class="material-symbols-outlined text-primary-container">bolt</span>
        <h1 class="font-headline text-2xl font-black italic tracking-tighter text-primary-container uppercase">
            VOLT
        </h1>
    </div>

    <div class="flex items-center gap-4">
        <span class="hidden text-xs font-black uppercase tracking-[0.2em] text-on-surface-variant md:block">
            Staff Portal
        </span>

        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-primary-container bg-surface-container-high font-headline font-black text-primary-container">
            <?= strtoupper(substr(h($staff_name), 0, 1)) ?>
        </div>

        <a href="<?= esc_url(home_url('/?pagename=logout')) ?>" class="text-on-surface-variant transition hover:text-error">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </div>
</header>

<main class="mx-auto min-h-screen max-w-7xl px-6 pb-32 pt-24">

    <section class="mb-10">
        <p class="mb-2 text-xs font-black uppercase tracking-[0.3em] text-primary-container">
            Management Area
        </p>

        <h2 class="font-headline text-5xl font-black uppercase leading-none tracking-tighter md:text-7xl">
            Staff <span class="italic text-primary-container">Portal</span>
        </h2>

        <p class="mt-3 text-sm font-medium text-on-surface-variant">
            Welcome back<?= $staff_intro_bits ? ', ' . implode(' | ', $staff_intro_bits) : '' ?>
        </p>
    </section>

    <?php if (($response['result'] ?? null) === false): ?>
        <?php show_error(api_message($response)); ?>
    <?php endif; ?>

    <section class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-12">

        <article class="relative overflow-hidden rounded-3xl border border-primary-container/30 bg-surface-container p-7 md:col-span-4">
            <div class="absolute -right-6 -top-6 opacity-10">
                <span class="material-symbols-outlined text-[140px]">groups</span>
            </div>

            <p class="mb-4 text-xs font-black uppercase tracking-[0.25em] text-on-surface-variant">
                Active Members
            </p>

            <p class="font-headline text-8xl font-black leading-none tracking-tighter text-[#f4f4e8]">
                <?= $totalUsers ?>
            </p>

            <div class="mt-8 grid grid-cols-2 gap-4 border-t border-outline-variant/20 pt-5">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Active Plans</p>
                    <p class="mt-1 text-xl font-black"><?= $activeMemberships ?></p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Total Gyms</p>
                    <p class="mt-1 text-xl font-black"><?= $totalGyms ?></p>
                </div>
            </div>
        </article>

        <a href="<?= esc_url(home_url('/?pagename=staff-attendance')) ?>" class="group rounded-3xl bg-primary-container p-7 text-on-primary-container shadow-[0_0_30px_rgba(212,251,0,0.18)] transition hover:scale-[1.01] md:col-span-4">
            <div class="flex h-full min-h-[190px] flex-col items-center justify-center gap-4">
                <span class="material-symbols-outlined text-5xl" style="font-variation-settings:'FILL' 1;">fingerprint</span>
                <p class="text-center font-headline text-sm font-black uppercase tracking-[0.2em]">
                    Clock In/Out
                </p>
            </div>
        </a>

        <article class="rounded-3xl border border-outline-variant/10 bg-surface-container-high p-7 md:col-span-4">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container">notifications_active</span>
                    <p class="text-xs font-black uppercase tracking-[0.2em]">System Alerts</p>
                </div>

                <?php if ($notif > 0): ?>
                    <span class="rounded-full bg-primary-container px-3 py-1 text-[10px] font-black uppercase text-on-primary-container">
                        <?= $notif ?> new
                    </span>
                <?php endif; ?>
            </div>

            <p class="text-sm leading-relaxed text-on-surface-variant">
                Revisa avisos, mensajes del sistema y actividad reciente del portal.
            </p>

            <a href="<?= esc_url(home_url('/?pagename=staff-notifications')) ?>" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-surface-bright px-4 py-3 text-[10px] font-black uppercase tracking-widest transition hover:text-primary-container">
                View Notifications
            </a>
        </article>

    </section>

    <section class="mb-5 rounded-3xl border border-outline-variant/10 bg-surface-container p-6">
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-primary-container">
                    Staff Tools
                </p>
                <h3 class="mt-1 font-headline text-3xl font-black uppercase tracking-tight">
                    Manage Content
                </h3>
            </div>
            <p class="text-sm text-on-surface-variant">
                Accesos rápidos a las áreas que puede editar o revisar el staff.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

            <?php
            $tools = [
                ['Classes', 'staff-manage-classes', 'event'],
                ['Routines', 'staff-manage-routines', 'fitness_center'],
                ['Exercises', 'staff-manage-exercises', 'exercise'],
                ['Diet Plans', 'staff-manage-diet-plans', 'restaurant'],
                ['Recipes', 'staff-manage-recipes', 'menu_book'],
                ['Users', 'staff-admin-users', 'groups'],
                ['Rooms', 'staff-rooms', 'meeting_room'],
                ['Gyms', 'staff-manage-gyms', 'location_city'],
                ['Equipment', 'staff-manage-equipment', 'construction'],
                ['Gym Equipment Inventory', 'staff-manage-gym-inventory', 'inventory_2'],
            ];
            ?>

            <?php foreach ($tools as $tool): ?>
                <a href="<?= esc_url(home_url('/?pagename=' . $tool[1])) ?>"
                   class="group rounded-2xl border border-outline-variant/10 bg-surface-container-high p-5 transition hover:-translate-y-0.5 hover:border-primary-container/60 hover:bg-surface-bright">
                    <span class="material-symbols-outlined mb-4 text-3xl text-primary-container transition group-hover:scale-110">
                        <?= h($tool[2]) ?>
                    </span>
                    <p class="font-headline text-sm font-black uppercase tracking-widest">
                        <?= h($tool[0]) ?>
                    </p>
                </a>
            <?php endforeach; ?>

        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-12">

        <article class="rounded-3xl border border-outline-variant/10 bg-surface-container p-7 md:col-span-8">
            <div class="mb-7 flex items-end justify-between gap-4">
                <div>
                    <p class="mb-1 text-xs font-black uppercase tracking-[0.25em] text-on-surface-variant">
                        Today's Briefing
                    </p>
                    <h3 class="font-headline text-3xl font-black uppercase tracking-tight">
                        Shift Schedule
                    </h3>
                </div>

                <a href="<?= esc_url(home_url('/?pagename=staff-manage-classes')) ?>" class="hidden text-xs font-black uppercase tracking-widest text-primary-container hover:underline sm:inline-flex">
                    View Full Schedule →
                </a>
            </div>

            <?php if ($scheduleClasses): ?>
                <div class="space-y-3">
                    <?php foreach ($scheduleClasses as $c): ?>
                        <?php
                        $class_meta_bits = [];
                        $date_label = staff_dashboard_date_label($c['start_time'] ?? '');
                        $time_label = staff_dashboard_time_label($c['start_time'] ?? '');
                        $room_name = h($c['room']['name'] ?? '');
                        $capacity = (int)($c['capacity'] ?? $c['capacity_limit'] ?? 0);

                        if ($date_label !== '' || $time_label !== '') {
                            $class_meta_bits[] = trim($date_label . ($time_label !== '' ? ' | ' . $time_label : ''));
                        }

                        if ($room_name !== '') {
                            $class_meta_bits[] = $room_name;
                        }

                        if ($capacity > 0) {
                            $class_meta_bits[] = $capacity . ' Cap';
                        }
                        ?>
                        <div class="flex flex-col gap-3 rounded-2xl border border-outline-variant/10 bg-surface-container-high p-3 transition hover:bg-surface-bright sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-container/10 text-primary-container">
                                    <span class="material-symbols-outlined">event</span>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate font-headline text-sm font-black uppercase tracking-wider">
                                        <?= staff_dashboard_class_title($c) ?>
                                    </p>
                                    <?php if ($class_meta_bits): ?>
                                        <p class="truncate text-xs text-on-surface-variant">
                                            <?= h(implode(' | ', $class_meta_bits)) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <a
                                href="<?= esc_url(home_url('/?pagename=staff-class-bookings&id=' . (int)($c['id'] ?? 0))) ?>"
                                class="inline-flex items-center justify-center rounded-full border border-outline-variant/30 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-on-surface transition hover:border-primary-container hover:text-primary-container"
                            >
                                View
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="rounded-2xl border border-outline-variant/10 bg-surface-container-high p-5">
                    <p class="text-sm italic text-on-surface-variant">
                        No upcoming classes scheduled.
                    </p>
                </div>
            <?php endif; ?>
        </article>

        <article class="rounded-3xl border border-outline-variant/10 bg-surface-container-high p-7 md:col-span-4">
            <p class="mb-1 text-xs font-black uppercase tracking-[0.25em] text-primary-container">
                Today's Quick Actions
            </p>

            <h3 class="mb-4 font-headline text-2xl font-black uppercase">
                Staff Shortcuts
            </h3>

            <div class="space-y-2 text-sm">
                <a class="flex items-center justify-between rounded-xl bg-surface-container px-4 py-3 transition hover:text-primary-container" href="<?= esc_url(home_url('/?pagename=staff-admin-user-create')) ?>">
                    <span class="font-bold">Create User</span>
                    <span class="material-symbols-outlined text-lg">person_add</span>
                </a>
                <a class="flex items-center justify-between rounded-xl bg-surface-container px-4 py-3 transition hover:text-primary-container" href="<?= esc_url(home_url('/?pagename=staff-create-class')) ?>">
                    <span class="font-bold">Create Class</span>
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                </a>
                <a class="flex items-center justify-between rounded-xl bg-surface-container px-4 py-3 transition hover:text-primary-container" href="<?= esc_url(home_url('/?pagename=staff-create-routine')) ?>">
                    <span class="font-bold">Create Routine</span>
                    <span class="material-symbols-outlined text-lg">fitness_center</span>
                </a>
            </div>
        </article>

    </section>

</main>

<?php
voltgym_get_template_part('template-parts/nav', 'staff');
voltgym_get_footer();

unset($GLOBALS['hide_global_header']);
unset($GLOBALS['hide_global_footer']);
?>
