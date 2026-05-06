<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BodyMetricController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DietPlanController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\GymClassController;
use App\Http\Controllers\GymController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFavoriteController;
use App\Http\Controllers\UserMealScheduleController;
use Illuminate\Support\Facades\Route;

/**
 * GymApp API routes — v1.
 *
 * SRP: Solely responsible for mapping HTTP endpoints to controller actions.
 *
 * Guard: auth:sanctum
 * Middleware aliases:
 *   admin    → AdminMiddleware (role = admin)
 *   advanced → AdvancedMiddleware (role ∈ {admin, manager, staff})
 *   staff_portal → StaffPortalMiddleware (role ∈ {admin, manager, assistant, staff})
 *   user_management → UserManagementMiddleware (role ∈ {admin, manager, assistant})
 */

Route::prefix('v1')->group(function (): void {

    // ─── Auth ────────────────────────────────────────────────────────────────

    Route::prefix('auth')->group(function (): void {

        Route::post('/register',        [AuthController::class, 'register']);
        Route::post('/login',           [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout',                  [AuthController::class, 'logout']);
            Route::get('/me',                       [AuthController::class, 'me']);
            Route::post('/email/resend',            [AuthController::class, 'resendVerification']);
            Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
                ->name('verification.verify')
                ->middleware('signed');
        });
    });

    // ─── Public endpoints (no auth required) ────────────────────────────────

    Route::get('/membership-plans',        [MembershipPlanController::class, 'index']);
    Route::get('/membership-plans/{id}',   [MembershipPlanController::class, 'show']);

    Route::get('/activities',              [ActivityController::class, 'index']);
    Route::get('/activities/{id}',         [ActivityController::class, 'show']);

    Route::get('/equipment',               [EquipmentController::class, 'index']);
    Route::get('/equipment/{id}',          [EquipmentController::class, 'show']);

    Route::get('/exercises',               [ExerciseController::class, 'index']);
    Route::get('/exercises/{id}',          [ExerciseController::class, 'show']);

    Route::get('/recipes',                 [RecipeController::class, 'index']);
    Route::get('/recipes/{id}',            [RecipeController::class, 'show']);

    Route::get('/gyms',                    [GymController::class, 'index']);
    Route::get('/gyms/{id}',               [GymController::class, 'show']);

    Route::get('/routines',                [RoutineController::class, 'index']);
    Route::get('/routines/{id}',           [RoutineController::class, 'show']);

    Route::get('/classes',                 [GymClassController::class, 'index']);
    Route::get('/classes/{id}',            [GymClassController::class, 'show']);

    Route::get('/rooms',                   [RoomController::class, 'index']);
    Route::get('/rooms/{id}',              [RoomController::class, 'show']);

    // ─── Public image streaming (no auth required) ──────────────────────────

    Route::get('/users/{id}/photo',            [UserController::class,         'showPhoto'])->name('users.photo');
    Route::get('/exercises/{id}/image',        [ExerciseController::class,     'showImage'])->name('exercises.image');
    Route::get('/equipment/{id}/image',        [EquipmentController::class,    'showImage'])->name('equipment.image');
    Route::get('/recipes/{id}/image',          [RecipeController::class,       'showImage'])->name('recipes.image');
    Route::get('/routines/{id}/image',         [RoutineController::class,      'showImage'])->name('routines.image');
    Route::get('/diet-plans/{id}/image',       [DietPlanController::class,     'showImage'])->name('diet-plans.image');
    Route::get('/rooms/{id}/image',            [RoomController::class,         'showImage'])->name('rooms.image');
    Route::get('/activities/{id}/image',       [ActivityController::class,     'showImage'])->name('activities.image');
    Route::get('/membership-plans/{id}/image', [MembershipPlanController::class,'showImage'])->name('membership-plans.image');
    Route::get('/gyms/{id}/logo',              [GymController::class,          'showLogo'])->name('gyms.logo');

    // ─── Authenticated endpoints ─────────────────────────────────────────────

    Route::middleware('auth:sanctum')->group(function (): void {

        // Aggregate Dashboards
        Route::get('/dashboard',        [DashboardController::class,      'index']);
        Route::put('/me/update',        [UserController::class,           'updateMe']);
        Route::post('/me/photo',        [UserController::class,           'uploadMyPhoto']);
        Route::middleware('staff_portal')->get('/staff/dashboard', [StaffDashboardController::class, 'index']);

        // Friendships
        Route::get('/friends/search',       [FriendshipController::class, 'search']);
        Route::get('/friends',              [FriendshipController::class, 'index']);
        Route::post('/friends/{id}/toggle', [FriendshipController::class, 'toggle']);
        Route::get('/friends/{id}/profile', [FriendshipController::class, 'showProfile']);

        // Bookings
        Route::get('/bookings',             [BookingController::class, 'index']);
        Route::get('/bookings/{id}',        [BookingController::class, 'show']);
        Route::post('/bookings',            [BookingController::class, 'store']);
        Route::post('/bookings/{id}/cancel',[BookingController::class, 'cancel']);
        Route::delete('/bookings/{id}',     [BookingController::class, 'destroy'])->middleware('admin');

        // Body Metrics
        Route::get('/body-metrics',          [BodyMetricController::class, 'index']);
        Route::get('/body-metrics/{id}',     [BodyMetricController::class, 'show']);
        Route::post('/body-metrics',         [BodyMetricController::class, 'store']);
        Route::delete('/body-metrics/{id}',  [BodyMetricController::class, 'destroy'])->middleware('admin');

        // Meal Schedule
        Route::get('/meal-schedule',              [UserMealScheduleController::class, 'index']);
        Route::get('/meal-schedule/{id}',         [UserMealScheduleController::class, 'show']);
        Route::post('/meal-schedule',             [UserMealScheduleController::class, 'store']);
        Route::put('/meal-schedule/{id}',         [UserMealScheduleController::class, 'update']);
        Route::delete('/meal-schedule/{id}',      [UserMealScheduleController::class, 'destroy']);

        // Settings
        Route::get('/settings',    [SettingController::class, 'show']);
        Route::put('/settings',    [SettingController::class, 'update']);

        // Favorites
        Route::get('/favorites',           [UserFavoriteController::class, 'index']);
        Route::post('/favorites',          [UserFavoriteController::class, 'store']);
        Route::delete('/favorites/{id}',   [UserFavoriteController::class, 'destroy']);

        // Routine management & activation
        Route::post('/routines/{id}/activate',  [RoutineController::class, 'activate']);
        Route::post('/routines/{id}/favorite',  [RoutineController::class, 'favorite']);

        // DietPlan management
        Route::post('/diet-plans/{id}/favorite', [DietPlanController::class, 'favorite']);

        // Staff Attendance (self service)
        Route::post('/attendance/clock-in',     [StaffAttendanceController::class, 'clockIn']);
        Route::post('/attendance/{id}/clock-out',[StaffAttendanceController::class, 'clockOut']);
        Route::get('/attendance',               [StaffAttendanceController::class, 'index']);
        Route::get('/attendance/{id}',          [StaffAttendanceController::class, 'show']);

        Route::middleware('user_management')->group(function (): void {
            Route::get('/users',                     [UserController::class, 'index']);
            Route::get('/users/{id}',                [UserController::class, 'show']);
            Route::post('/users',                    [UserController::class, 'store']);
            Route::put('/users/{id}',                [UserController::class, 'update']);
        });

        // ─── Advanced (admin | manager | staff) ──────────────────────────────

        Route::middleware('advanced')->group(function (): void {

            // DietPlan
            Route::get('/diet-plans',             [DietPlanController::class, 'index']);
            Route::get('/diet-plans/{id}',        [DietPlanController::class, 'show']);
            Route::post('/diet-plans/{id}/recipes', [DietPlanController::class, 'addRecipe']);
            Route::delete('/diet-plans/{id}/recipes', [DietPlanController::class, 'removeRecipe']);

            // Exercises (write)
            Route::post('/exercises',         [ExerciseController::class, 'store']);
            Route::put('/exercises/{id}',     [ExerciseController::class, 'update']);

            // Recipes (write)
            Route::post('/recipes',           [RecipeController::class, 'store']);
            Route::put('/recipes/{id}',       [RecipeController::class, 'update']);

            // Routines (write)
            Route::post('/routines',                           [RoutineController::class, 'store']);
            Route::put('/routines/{id}',                       [RoutineController::class, 'update']);
            Route::post('/routines/{id}/exercises',            [RoutineController::class, 'addExercise']);
            Route::delete('/routines/{routineId}/exercises/{exerciseId}', [RoutineController::class, 'removeExercise']);
            Route::post('/routines/{id}/reorder',              [RoutineController::class, 'reorder']);
            Route::post('/routines/{id}/duplicate',            [RoutineController::class, 'duplicate']);

            // User photo upload (user_online may also use this for their own photo)
            Route::post('/users/{id}/photo',           [UserController::class, 'uploadPhoto']);

            // GymClass (write + mark attendance)
            Route::post('/classes',                       [GymClassController::class, 'store']);
            Route::put('/classes/{id}',                   [GymClassController::class, 'update']);
            Route::post('/classes/{id}/mark-attendance',  [GymClassController::class, 'markAttendance']);

            // Rooms (write)
            Route::post('/rooms',              [RoomController::class, 'store']);
            Route::put('/rooms/{id}',          [RoomController::class, 'update']);

            // Image uploads (advanced and above)
            Route::post('/exercises/{id}/image',         [ExerciseController::class,  'uploadImage']);
            Route::post('/recipes/{id}/image',           [RecipeController::class,    'uploadImage']);
            Route::post('/routines/{id}/image',          [RoutineController::class,   'uploadImage']);
            Route::post('/rooms/{id}/image',             [RoomController::class,      'uploadImage']);

            // Notifications
            Route::get('/notifications',       [NotificationController::class, 'index']);
            Route::get('/notifications/{id}',  [NotificationController::class, 'show']);
            Route::post('/notifications',      [NotificationController::class, 'store']);
        });

        // ─── Admin only ───────────────────────────────────────────────────────

        Route::middleware('admin')->group(function (): void {

            // MembershipPlan (write)
            Route::post('/membership-plans',           [MembershipPlanController::class, 'store']);
            Route::put('/membership-plans/{id}',       [MembershipPlanController::class, 'update']);
            Route::delete('/membership-plans/{id}',    [MembershipPlanController::class, 'destroy']);

            // Activity (write)
            Route::post('/activities',                 [ActivityController::class, 'store']);
            Route::put('/activities/{id}',             [ActivityController::class, 'update']);
            Route::delete('/activities/{id}',          [ActivityController::class, 'destroy']);

            // Equipment (write)
            Route::post('/equipment',                  [EquipmentController::class, 'store']);
            Route::put('/equipment/{id}',              [EquipmentController::class, 'update']);
            Route::delete('/equipment/{id}',           [EquipmentController::class, 'destroy']);

            // Exercise (delete)
            Route::delete('/exercises/{id}',           [ExerciseController::class, 'destroy']);

            // Recipe (delete)
            Route::delete('/recipes/{id}',             [RecipeController::class, 'destroy']);

            // DietPlan (write)
            Route::post('/diet-plans',                 [DietPlanController::class, 'store']);
            Route::put('/diet-plans/{id}',             [DietPlanController::class, 'update']);
            Route::delete('/diet-plans/{id}',          [DietPlanController::class, 'destroy']);

            // Gym
            Route::post('/gyms',                       [GymController::class, 'store']);
            Route::put('/gyms/{id}',                   [GymController::class, 'update']);
            Route::delete('/gyms/{id}',                [GymController::class, 'destroy']);
            Route::post('/gyms/{id}/assign-manager',   [GymController::class, 'assignManager']);

            // Users
            Route::delete('/users/{id}',               [UserController::class, 'destroy']);
            Route::post('/users/{id}/block',           [UserController::class, 'block']);
            Route::post('/users/{id}/unblock',         [UserController::class, 'unblock']);
            Route::post('/users/{id}/reset-strikes',   [UserController::class, 'resetStrikes']);

            // Room (delete)
            Route::delete('/rooms/{id}',               [RoomController::class, 'destroy']);

            // Routine (delete)
            Route::delete('/routines/{id}',            [RoutineController::class, 'destroy']);

            // GymClass
            Route::post('/classes/{id}/cancel',        [GymClassController::class, 'cancel']);
            Route::delete('/classes/{id}',             [GymClassController::class, 'destroy']);

            // StaffAttendance (delete)
            Route::delete('/attendance/{id}',          [StaffAttendanceController::class, 'destroy']);

            // Notifications (delete)
            Route::delete('/notifications/{id}',       [NotificationController::class, 'destroy']);

            // Image uploads (admin only)
            Route::post('/users/{id}/photo',            [UserController::class,          'uploadPhoto']);
            Route::post('/equipment/{id}/image',        [EquipmentController::class,     'uploadImage']);
            Route::post('/activities/{id}/image',       [ActivityController::class,      'uploadImage']);
            Route::post('/membership-plans/{id}/image', [MembershipPlanController::class,'uploadImage']);
            Route::post('/diet-plans/{id}/image',       [DietPlanController::class,      'uploadImage']);
            Route::post('/gyms/{id}/logo',              [GymController::class,           'uploadLogo']);

            // Image deletes (admin only)
            Route::delete('/users/{id}/photo',            [UserController::class,          'deletePhoto']);
            Route::delete('/exercises/{id}/image',        [ExerciseController::class,      'deleteImage']);
            Route::delete('/equipment/{id}/image',        [EquipmentController::class,     'deleteImage']);
            Route::delete('/recipes/{id}/image',          [RecipeController::class,        'deleteImage']);
            Route::delete('/routines/{id}/image',         [RoutineController::class,       'deleteImage']);
            Route::delete('/diet-plans/{id}/image',       [DietPlanController::class,      'deleteImage']);
            Route::delete('/rooms/{id}/image',            [RoomController::class,          'deleteImage']);
            Route::delete('/activities/{id}/image',       [ActivityController::class,      'deleteImage']);
            Route::delete('/membership-plans/{id}/image', [MembershipPlanController::class,'deleteImage']);
            Route::delete('/gyms/{id}/logo',              [GymController::class,           'deleteLogo']);
        });
    });
});
