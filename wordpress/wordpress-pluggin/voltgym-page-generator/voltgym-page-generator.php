<?php
/**
 * Plugin Name: VoltGym Page Generator
 * Description: Creates and updates the required WordPress pages for the VoltGym theme.
 * Version: 1.0.0
 * Author: VoltGym
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Returns every WordPress page required by the VoltGym theme.
 *
 * Slugs are derived from the template file names by removing the "page-"
 * prefix and ".php" extension. Templates are assigned through
 * _wp_page_template and are expected to exist in the active theme.
 *
 * @return array<int, array{title:string, slug:string, template:string}>
 */
function voltgym_page_generator_pages(): array
{
    return [
        ['title' => 'Client Bookings', 'slug' => 'client-bookings', 'template' => 'page-client-bookings.php'],
        ['title' => 'Client Catalog', 'slug' => 'client-catalog', 'template' => 'page-client-catalog.php'],
        ['title' => 'Client Classes', 'slug' => 'client-classes', 'template' => 'page-client-classes.php'],
        ['title' => 'Client Dashboard', 'slug' => 'client-dashboard', 'template' => 'page-client-dashboard.php'],
        ['title' => 'Client Diet Plan', 'slug' => 'client-diet-plan', 'template' => 'page-client-diet-plan.php'],
        ['title' => 'Client Diet Plans', 'slug' => 'client-diet-plans', 'template' => 'page-client-diet-plans.php'],
        ['title' => 'Client Equipment', 'slug' => 'client-equipment', 'template' => 'page-client-equipment.php'],
        ['title' => 'Client Exercises', 'slug' => 'client-exercises', 'template' => 'page-client-exercises.php'],
        ['title' => 'Client Favorites', 'slug' => 'client-favorites', 'template' => 'page-client-favorites.php'],
        ['title' => 'Client Friends', 'slug' => 'client-friends', 'template' => 'page-client-friends.php'],
        ['title' => 'Client Meal Schedule', 'slug' => 'client-meal-schedule', 'template' => 'page-client-meal-schedule.php'],
        ['title' => 'Client Memberships', 'slug' => 'client-memberships', 'template' => 'page-client-memberships.php'],
        ['title' => 'Client Metrics', 'slug' => 'client-metrics', 'template' => 'page-client-metrics.php'],
        ['title' => 'Client Notifications', 'slug' => 'client-notifications', 'template' => 'page-client-notifications.php'],
        ['title' => 'Client Recipe', 'slug' => 'client-recipe', 'template' => 'page-client-recipe.php'],
        ['title' => 'Client Recipes', 'slug' => 'client-recipes', 'template' => 'page-client-recipes.php'],
        ['title' => 'Client Routine', 'slug' => 'client-routine', 'template' => 'page-client-routine.php'],
        ['title' => 'Client Routines', 'slug' => 'client-routines', 'template' => 'page-client-routines.php'],
        ['title' => 'Client Settings', 'slug' => 'client-settings', 'template' => 'page-client-settings.php'],
        ['title' => 'Client User Profile', 'slug' => 'client-user-profile', 'template' => 'page-client-user-profile.php'],
        ['title' => 'Forgot Password', 'slug' => 'forgot-password', 'template' => 'page-forgot-password.php'],
        ['title' => 'Logout', 'slug' => 'logout', 'template' => 'page-logout.php'],
        ['title' => 'Register', 'slug' => 'register', 'template' => 'page-register.php'],
        ['title' => 'Staff Admin User Create', 'slug' => 'staff-admin-user-create', 'template' => 'page-staff-admin-user-create.php'],
        ['title' => 'Staff Admin User Edit', 'slug' => 'staff-admin-user-edit', 'template' => 'page-staff-admin-user-edit.php'],
        ['title' => 'Staff Admin Users', 'slug' => 'staff-admin-users', 'template' => 'page-staff-admin-users.php'],
        ['title' => 'Staff Attendance', 'slug' => 'staff-attendance', 'template' => 'page-staff-attendance.php'],
        ['title' => 'Staff Cancel Class', 'slug' => 'staff-cancel-class', 'template' => 'page-staff-cancel-class.php'],
        ['title' => 'Staff Class Bookings', 'slug' => 'staff-class-bookings', 'template' => 'page-staff-class-bookings.php'],
        ['title' => 'Staff Create Class', 'slug' => 'staff-create-class', 'template' => 'page-staff-create-class.php'],
        ['title' => 'Staff Create Diet Plan', 'slug' => 'staff-create-diet-plan', 'template' => 'page-staff-create-diet-plan.php'],
        ['title' => 'Staff Create Equipment', 'slug' => 'staff-create-equipment', 'template' => 'page-staff-create-equipment.php'],
        ['title' => 'Staff Create Exercise', 'slug' => 'staff-create-exercise', 'template' => 'page-staff-create-exercise.php'],
        ['title' => 'Staff Create Gym', 'slug' => 'staff-create-gym', 'template' => 'page-staff-create-gym.php'],
        ['title' => 'Staff Create Gym Inventory', 'slug' => 'staff-create-gym-inventory', 'template' => 'page-staff-create-gym-inventory.php'],
        ['title' => 'Staff Create Notification', 'slug' => 'staff-create-notification', 'template' => 'page-staff-create-notification.php'],
        ['title' => 'Staff Create Recipe', 'slug' => 'staff-create-recipe', 'template' => 'page-staff-create-recipe.php'],
        ['title' => 'Staff Create Room', 'slug' => 'staff-create-room', 'template' => 'page-staff-create-room.php'],
        ['title' => 'Staff Create Routine', 'slug' => 'staff-create-routine', 'template' => 'page-staff-create-routine.php'],
        ['title' => 'Staff Dashboard', 'slug' => 'staff-dashboard', 'template' => 'page-staff-dashboard.php'],
        ['title' => 'Staff Edit Class', 'slug' => 'staff-edit-class', 'template' => 'page-staff-edit-class.php'],
        ['title' => 'Staff Edit Diet Plan', 'slug' => 'staff-edit-diet-plan', 'template' => 'page-staff-edit-diet-plan.php'],
        ['title' => 'Staff Edit Equipment', 'slug' => 'staff-edit-equipment', 'template' => 'page-staff-edit-equipment.php'],
        ['title' => 'Staff Edit Exercise', 'slug' => 'staff-edit-exercise', 'template' => 'page-staff-edit-exercise.php'],
        ['title' => 'Staff Edit Gym', 'slug' => 'staff-edit-gym', 'template' => 'page-staff-edit-gym.php'],
        ['title' => 'Staff Edit Gym Inventory', 'slug' => 'staff-edit-gym-inventory', 'template' => 'page-staff-edit-gym-inventory.php'],
        ['title' => 'Staff Edit Recipe', 'slug' => 'staff-edit-recipe', 'template' => 'page-staff-edit-recipe.php'],
        ['title' => 'Staff Edit Room', 'slug' => 'staff-edit-room', 'template' => 'page-staff-edit-room.php'],
        ['title' => 'Staff Edit Routine', 'slug' => 'staff-edit-routine', 'template' => 'page-staff-edit-routine.php'],
        ['title' => 'Staff Manage Classes', 'slug' => 'staff-manage-classes', 'template' => 'page-staff-manage-classes.php'],
        ['title' => 'Staff Manage Diet Plans', 'slug' => 'staff-manage-diet-plans', 'template' => 'page-staff-manage-diet-plans.php'],
        ['title' => 'Staff Manage Equipment', 'slug' => 'staff-manage-equipment', 'template' => 'page-staff-manage-equipment.php'],
        ['title' => 'Staff Manage Exercises', 'slug' => 'staff-manage-exercises', 'template' => 'page-staff-manage-exercises.php'],
        ['title' => 'Staff Manage Gym Inventory', 'slug' => 'staff-manage-gym-inventory', 'template' => 'page-staff-manage-gym-inventory.php'],
        ['title' => 'Staff Manage Gyms', 'slug' => 'staff-manage-gyms', 'template' => 'page-staff-manage-gyms.php'],
        ['title' => 'Staff Manage Recipes', 'slug' => 'staff-manage-recipes', 'template' => 'page-staff-manage-recipes.php'],
        ['title' => 'Staff Manage Routines', 'slug' => 'staff-manage-routines', 'template' => 'page-staff-manage-routines.php'],
        ['title' => 'Staff Notifications', 'slug' => 'staff-notifications', 'template' => 'page-staff-notifications.php'],
        ['title' => 'Staff Rooms', 'slug' => 'staff-rooms', 'template' => 'page-staff-rooms.php'],
        ['title' => 'Staff View Diet Plan', 'slug' => 'staff-view-diet-plan', 'template' => 'page-staff-view-diet-plan.php'],
        ['title' => 'Staff View Equipment', 'slug' => 'staff-view-equipment', 'template' => 'page-staff-view-equipment.php'],
        ['title' => 'Staff View Exercise', 'slug' => 'staff-view-exercise', 'template' => 'page-staff-view-exercise.php'],
        ['title' => 'Staff View Gym', 'slug' => 'staff-view-gym', 'template' => 'page-staff-view-gym.php'],
        ['title' => 'Staff View Gym Inventory', 'slug' => 'staff-view-gym-inventory', 'template' => 'page-staff-view-gym-inventory.php'],
        ['title' => 'Staff View Notification', 'slug' => 'staff-view-notification', 'template' => 'page-staff-view-notification.php'],
        ['title' => 'Staff View Recipe', 'slug' => 'staff-view-recipe', 'template' => 'page-staff-view-recipe.php'],
        ['title' => 'Staff View Room', 'slug' => 'staff-view-room', 'template' => 'page-staff-view-room.php'],
        ['title' => 'Staff View Routine', 'slug' => 'staff-view-routine', 'template' => 'page-staff-view-routine.php'],
    ];
}

/**
 * Creates or updates all configured pages.
 *
 * @return array{created:int, existing:int, updated:int, errors:int, rows:array<int, array<string, string>>}
 */
function voltgym_page_generator_run(): array
{
    $result = [
        'created' => 0,
        'existing' => 0,
        'updated' => 0,
        'errors' => 0,
        'rows' => [],
    ];

    foreach (voltgym_page_generator_pages() as $page) {
        $slug = sanitize_title($page['slug']);
        $title = sanitize_text_field($page['title']);
        $template = sanitize_file_name($page['template']);
        $existing = get_page_by_path($slug, OBJECT, 'page');
        $template_exists = locate_template([$template], false, false) !== '';
        $status = 'updated';

        if ($existing instanceof WP_Post) {
            $result['existing']++;
            $page_id = (int) $existing->ID;
        } else {
            $page_id = wp_insert_post([
                'post_type' => 'page',
                'post_title' => $title,
                'post_name' => $slug,
                'post_status' => 'publish',
                'post_content' => '',
            ], true);

            if (is_wp_error($page_id)) {
                $result['errors']++;
                $result['rows'][] = [
                    'title' => $title,
                    'slug' => $slug,
                    'template' => $template,
                    'status' => 'error: ' . $page_id->get_error_message(),
                ];
                continue;
            }

            $result['created']++;
            $status = 'created';
        }

        $current_template = (string) get_post_meta((int) $page_id, '_wp_page_template', true);

        if ($current_template !== $template) {
            update_post_meta((int) $page_id, '_wp_page_template', $template);
            $result['updated']++;
        }

        if (!$template_exists) {
            $status .= ' (template file not found in active theme)';
        }

        $result['rows'][] = [
            'title' => $title,
            'slug' => $slug,
            'template' => $template,
            'status' => $status,
        ];
    }

    flush_rewrite_rules(false);

    return $result;
}

add_action('admin_menu', static function (): void {
    add_management_page(
        'VoltGym Page Generator',
        'VoltGym Page Generator',
        'manage_options',
        'voltgym-page-generator',
        'voltgym_page_generator_render_admin_page'
    );
});

function voltgym_page_generator_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'voltgym-page-generator'));
    }

    $run_result = null;

    if (isset($_POST['voltgym_page_generator_run'])) {
        check_admin_referer('voltgym_page_generator_run', 'voltgym_page_generator_nonce');
        $run_result = voltgym_page_generator_run();
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html('VoltGym Page Generator'); ?></h1>

        <p>
            <?php echo esc_html('This tool creates or updates the WordPress pages required by the VoltGym theme and assigns each page template. It does not modify theme files.'); ?>
        </p>

        <?php if (is_array($run_result)) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    echo esc_html(sprintf(
                        'Created: %d. Existing: %d. Template updates: %d. Errors: %d.',
                        $run_result['created'],
                        $run_result['existing'],
                        $run_result['updated'],
                        $run_result['errors']
                    ));
                    ?>
                </p>
            </div>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html('Title'); ?></th>
                        <th><?php echo esc_html('Slug'); ?></th>
                        <th><?php echo esc_html('Template'); ?></th>
                        <th><?php echo esc_html('Status'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($run_result['rows'] as $row) : ?>
                        <tr>
                            <td><?php echo esc_html($row['title']); ?></td>
                            <td><?php echo esc_html($row['slug']); ?></td>
                            <td><?php echo esc_html($row['template']); ?></td>
                            <td><?php echo esc_html($row['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('tools.php?page=voltgym-page-generator')); ?>">
            <?php wp_nonce_field('voltgym_page_generator_run', 'voltgym_page_generator_nonce'); ?>
            <p>
                <button type="submit" name="voltgym_page_generator_run" value="1" class="button button-primary">
                    <?php echo esc_html('Crear/actualizar paginas'); ?>
                </button>
            </p>
        </form>
    </div>
    <?php
}
