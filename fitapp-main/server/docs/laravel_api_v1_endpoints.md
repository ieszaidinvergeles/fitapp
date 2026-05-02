# Laravel API v1 Endpoint Catalog

This document lists every API call currently declared in `server/laravel/routes/api.php` for version `v1`.

## Conventions

- Base URL: `/api/v1`
- Auth:
  - `public`: no token required
  - `sanctum`: requires `Authorization: Bearer <token>`
- Middleware aliases:
  - `advanced`: `admin`, `manager`, `staff`
  - `admin`: `admin`
  - `staff_portal`: `admin`, `manager`, `assistant`, `staff`
  - `user_management`: `admin`, `manager`, `assistant`

## Endpoint Table

| Method | Endpoint | Auth | Middleware | Controller Action |
| --- | --- | --- | --- | --- |
| POST | `/auth/register` | public | `api` | `AuthController@register` |
| POST | `/auth/login` | public | `api` | `AuthController@login` |
| POST | `/auth/forgot-password` | public | `api` | `AuthController@forgotPassword` |
| POST | `/auth/reset-password` | public | `api` | `AuthController@resetPassword` |
| POST | `/auth/logout` | sanctum | `api, auth:sanctum` | `AuthController@logout` |
| GET | `/auth/me` | sanctum | `api, auth:sanctum` | `AuthController@me` |
| POST | `/auth/email/resend` | sanctum | `api, auth:sanctum` | `AuthController@resendVerification` |
| GET | `/auth/email/verify/{id}/{hash}` | sanctum | `api, auth:sanctum, signed` | `AuthController@verifyEmail` |
| GET | `/membership-plans` | public | `api` | `MembershipPlanController@index` |
| GET | `/membership-plans/{id}` | public | `api` | `MembershipPlanController@show` |
| GET | `/activities` | public | `api` | `ActivityController@index` |
| GET | `/activities/{id}` | public | `api` | `ActivityController@show` |
| GET | `/equipment` | public | `api` | `EquipmentController@index` |
| GET | `/equipment/{id}` | public | `api` | `EquipmentController@show` |
| GET | `/exercises` | public | `api` | `ExerciseController@index` |
| GET | `/exercises/{id}` | public | `api` | `ExerciseController@show` |
| GET | `/recipes` | public | `api` | `RecipeController@index` |
| GET | `/recipes/{id}` | public | `api` | `RecipeController@show` |
| GET | `/gyms` | public | `api` | `GymController@index` |
| GET | `/gyms/{id}` | public | `api` | `GymController@show` |
| GET | `/routines` | public | `api` | `RoutineController@index` |
| GET | `/routines/{id}` | public | `api` | `RoutineController@show` |
| GET | `/classes` | public | `api` | `GymClassController@index` |
| GET | `/classes/{id}` | public | `api` | `GymClassController@show` |
| GET | `/rooms` | public | `api` | `RoomController@index` |
| GET | `/rooms/{id}` | public | `api` | `RoomController@show` |
| GET | `/users/{id}/photo` | sanctum | `api, auth:sanctum` | `UserController@showPhoto` |
| GET | `/exercises/{id}/image` | sanctum | `api, auth:sanctum` | `ExerciseController@showImage` |
| GET | `/equipment/{id}/image` | sanctum | `api, auth:sanctum` | `EquipmentController@showImage` |
| GET | `/recipes/{id}/image` | sanctum | `api, auth:sanctum` | `RecipeController@showImage` |
| GET | `/routines/{id}/image` | sanctum | `api, auth:sanctum` | `RoutineController@showImage` |
| GET | `/diet-plans/{id}/image` | sanctum | `api, auth:sanctum` | `DietPlanController@showImage` |
| GET | `/rooms/{id}/image` | sanctum | `api, auth:sanctum` | `RoomController@showImage` |
| GET | `/activities/{id}/image` | sanctum | `api, auth:sanctum` | `ActivityController@showImage` |
| GET | `/membership-plans/{id}/image` | sanctum | `api, auth:sanctum` | `MembershipPlanController@showImage` |
| GET | `/gyms/{id}/logo` | sanctum | `api, auth:sanctum` | `GymController@showLogo` |
| GET | `/dashboard` | sanctum | `api, auth:sanctum` | `DashboardController@index` |
| GET | `/staff/dashboard` | sanctum | `api, auth:sanctum, staff_portal` | `StaffDashboardController@index` |
| GET | `/bookings` | sanctum | `api, auth:sanctum` | `BookingController@index` |
| GET | `/bookings/{id}` | sanctum | `api, auth:sanctum` | `BookingController@show` |
| POST | `/bookings` | sanctum | `api, auth:sanctum` | `BookingController@store` |
| POST | `/bookings/{id}/cancel` | sanctum | `api, auth:sanctum` | `BookingController@cancel` |
| DELETE | `/bookings/{id}` | sanctum | `api, auth:sanctum, admin` | `BookingController@destroy` |
| GET | `/body-metrics` | sanctum | `api, auth:sanctum` | `BodyMetricController@index` |
| GET | `/body-metrics/{id}` | sanctum | `api, auth:sanctum` | `BodyMetricController@show` |
| POST | `/body-metrics` | sanctum | `api, auth:sanctum` | `BodyMetricController@store` |
| DELETE | `/body-metrics/{id}` | sanctum | `api, auth:sanctum, admin` | `BodyMetricController@destroy` |
| GET | `/meal-schedule` | sanctum | `api, auth:sanctum` | `UserMealScheduleController@index` |
| GET | `/meal-schedule/{id}` | sanctum | `api, auth:sanctum` | `UserMealScheduleController@show` |
| POST | `/meal-schedule` | sanctum | `api, auth:sanctum` | `UserMealScheduleController@store` |
| PUT | `/meal-schedule/{id}` | sanctum | `api, auth:sanctum` | `UserMealScheduleController@update` |
| DELETE | `/meal-schedule/{id}` | sanctum | `api, auth:sanctum` | `UserMealScheduleController@destroy` |
| GET | `/settings` | sanctum | `api, auth:sanctum` | `SettingController@show` |
| PUT | `/settings` | sanctum | `api, auth:sanctum` | `SettingController@update` |
| GET | `/favorites` | sanctum | `api, auth:sanctum` | `UserFavoriteController@index` |
| POST | `/favorites` | sanctum | `api, auth:sanctum` | `UserFavoriteController@store` |
| DELETE | `/favorites/{id}` | sanctum | `api, auth:sanctum` | `UserFavoriteController@destroy` |
| POST | `/routines/{id}/activate` | sanctum | `api, auth:sanctum` | `RoutineController@activate` |
| POST | `/attendance/clock-in` | sanctum | `api, auth:sanctum` | `StaffAttendanceController@clockIn` |
| POST | `/attendance/{id}/clock-out` | sanctum | `api, auth:sanctum` | `StaffAttendanceController@clockOut` |
| GET | `/attendance` | sanctum | `api, auth:sanctum` | `StaffAttendanceController@index` |
| GET | `/attendance/{id}` | sanctum | `api, auth:sanctum` | `StaffAttendanceController@show` |
| GET | `/users` | sanctum | `api, auth:sanctum, user_management` | `UserController@index` |
| GET | `/users/{id}` | sanctum | `api, auth:sanctum, user_management` | `UserController@show` |
| POST | `/users` | sanctum | `api, auth:sanctum, user_management` | `UserController@store` |
| PUT | `/users/{id}` | sanctum | `api, auth:sanctum, user_management` | `UserController@update` |
| GET | `/diet-plans` | sanctum | `api, auth:sanctum, advanced` | `DietPlanController@index` |
| GET | `/diet-plans/{id}` | sanctum | `api, auth:sanctum, advanced` | `DietPlanController@show` |
| POST | `/exercises` | sanctum | `api, auth:sanctum, advanced` | `ExerciseController@store` |
| PUT | `/exercises/{id}` | sanctum | `api, auth:sanctum, advanced` | `ExerciseController@update` |
| POST | `/recipes` | sanctum | `api, auth:sanctum, advanced` | `RecipeController@store` |
| PUT | `/recipes/{id}` | sanctum | `api, auth:sanctum, advanced` | `RecipeController@update` |
| POST | `/routines` | sanctum | `api, auth:sanctum, advanced` | `RoutineController@store` |
| PUT | `/routines/{id}` | sanctum | `api, auth:sanctum, advanced` | `RoutineController@update` |
| POST | `/routines/{id}/exercises` | sanctum | `api, auth:sanctum, advanced` | `RoutineController@addExercise` |
| DELETE | `/routines/{routineId}/exercises/{exerciseId}` | sanctum | `api, auth:sanctum, advanced` | `RoutineController@removeExercise` |
| POST | `/routines/{id}/reorder` | sanctum | `api, auth:sanctum, advanced` | `RoutineController@reorder` |
| POST | `/routines/{id}/duplicate` | sanctum | `api, auth:sanctum, advanced` | `RoutineController@duplicate` |
| POST | `/users/{id}/photo` | sanctum | `api, auth:sanctum, advanced` | `UserController@uploadPhoto` |
| POST | `/classes` | sanctum | `api, auth:sanctum, advanced` | `GymClassController@store` |
| PUT | `/classes/{id}` | sanctum | `api, auth:sanctum, advanced` | `GymClassController@update` |
| POST | `/classes/{id}/mark-attendance` | sanctum | `api, auth:sanctum, advanced` | `GymClassController@markAttendance` |
| POST | `/rooms` | sanctum | `api, auth:sanctum, advanced` | `RoomController@store` |
| PUT | `/rooms/{id}` | sanctum | `api, auth:sanctum, advanced` | `RoomController@update` |
| POST | `/exercises/{id}/image` | sanctum | `api, auth:sanctum, advanced` | `ExerciseController@uploadImage` |
| POST | `/recipes/{id}/image` | sanctum | `api, auth:sanctum, advanced` | `RecipeController@uploadImage` |
| POST | `/routines/{id}/image` | sanctum | `api, auth:sanctum, advanced` | `RoutineController@uploadImage` |
| POST | `/rooms/{id}/image` | sanctum | `api, auth:sanctum, advanced` | `RoomController@uploadImage` |
| GET | `/notifications` | sanctum | `api, auth:sanctum, advanced` | `NotificationController@index` |
| GET | `/notifications/{id}` | sanctum | `api, auth:sanctum, advanced` | `NotificationController@show` |
| POST | `/notifications` | sanctum | `api, auth:sanctum, advanced` | `NotificationController@store` |
| POST | `/membership-plans` | sanctum | `api, auth:sanctum, admin` | `MembershipPlanController@store` |
| PUT | `/membership-plans/{id}` | sanctum | `api, auth:sanctum, admin` | `MembershipPlanController@update` |
| DELETE | `/membership-plans/{id}` | sanctum | `api, auth:sanctum, admin` | `MembershipPlanController@destroy` |
| POST | `/activities` | sanctum | `api, auth:sanctum, admin` | `ActivityController@store` |
| PUT | `/activities/{id}` | sanctum | `api, auth:sanctum, admin` | `ActivityController@update` |
| DELETE | `/activities/{id}` | sanctum | `api, auth:sanctum, admin` | `ActivityController@destroy` |
| POST | `/equipment` | sanctum | `api, auth:sanctum, admin` | `EquipmentController@store` |
| PUT | `/equipment/{id}` | sanctum | `api, auth:sanctum, admin` | `EquipmentController@update` |
| DELETE | `/equipment/{id}` | sanctum | `api, auth:sanctum, admin` | `EquipmentController@destroy` |
| DELETE | `/exercises/{id}` | sanctum | `api, auth:sanctum, admin` | `ExerciseController@destroy` |
| DELETE | `/recipes/{id}` | sanctum | `api, auth:sanctum, admin` | `RecipeController@destroy` |
| POST | `/diet-plans` | sanctum | `api, auth:sanctum, admin` | `DietPlanController@store` |
| PUT | `/diet-plans/{id}` | sanctum | `api, auth:sanctum, admin` | `DietPlanController@update` |
| DELETE | `/diet-plans/{id}` | sanctum | `api, auth:sanctum, admin` | `DietPlanController@destroy` |
| POST | `/gyms` | sanctum | `api, auth:sanctum, admin` | `GymController@store` |
| PUT | `/gyms/{id}` | sanctum | `api, auth:sanctum, admin` | `GymController@update` |
| DELETE | `/gyms/{id}` | sanctum | `api, auth:sanctum, admin` | `GymController@destroy` |
| POST | `/gyms/{id}/assign-manager` | sanctum | `api, auth:sanctum, admin` | `GymController@assignManager` |
| DELETE | `/users/{id}` | sanctum | `api, auth:sanctum, admin` | `UserController@destroy` |
| POST | `/users/{id}/block` | sanctum | `api, auth:sanctum, admin` | `UserController@block` |
| POST | `/users/{id}/unblock` | sanctum | `api, auth:sanctum, admin` | `UserController@unblock` |
| POST | `/users/{id}/reset-strikes` | sanctum | `api, auth:sanctum, admin` | `UserController@resetStrikes` |
| DELETE | `/rooms/{id}` | sanctum | `api, auth:sanctum, admin` | `RoomController@destroy` |
| DELETE | `/routines/{id}` | sanctum | `api, auth:sanctum, admin` | `RoutineController@destroy` |
| POST | `/classes/{id}/cancel` | sanctum | `api, auth:sanctum, admin` | `GymClassController@cancel` |
| DELETE | `/classes/{id}` | sanctum | `api, auth:sanctum, admin` | `GymClassController@destroy` |
| DELETE | `/attendance/{id}` | sanctum | `api, auth:sanctum, admin` | `StaffAttendanceController@destroy` |
| DELETE | `/notifications/{id}` | sanctum | `api, auth:sanctum, admin` | `NotificationController@destroy` |
| POST | `/users/{id}/photo` | sanctum | `api, auth:sanctum, admin` | `UserController@uploadPhoto` |
| POST | `/equipment/{id}/image` | sanctum | `api, auth:sanctum, admin` | `EquipmentController@uploadImage` |
| POST | `/activities/{id}/image` | sanctum | `api, auth:sanctum, admin` | `ActivityController@uploadImage` |
| POST | `/membership-plans/{id}/image` | sanctum | `api, auth:sanctum, admin` | `MembershipPlanController@uploadImage` |
| POST | `/diet-plans/{id}/image` | sanctum | `api, auth:sanctum, admin` | `DietPlanController@uploadImage` |
| POST | `/gyms/{id}/logo` | sanctum | `api, auth:sanctum, admin` | `GymController@uploadLogo` |
| DELETE | `/users/{id}/photo` | sanctum | `api, auth:sanctum, admin` | `UserController@deletePhoto` |
| DELETE | `/exercises/{id}/image` | sanctum | `api, auth:sanctum, admin` | `ExerciseController@deleteImage` |
| DELETE | `/equipment/{id}/image` | sanctum | `api, auth:sanctum, admin` | `EquipmentController@deleteImage` |
| DELETE | `/recipes/{id}/image` | sanctum | `api, auth:sanctum, admin` | `RecipeController@deleteImage` |
| DELETE | `/routines/{id}/image` | sanctum | `api, auth:sanctum, admin` | `RoutineController@deleteImage` |
| DELETE | `/diet-plans/{id}/image` | sanctum | `api, auth:sanctum, admin` | `DietPlanController@deleteImage` |
| DELETE | `/rooms/{id}/image` | sanctum | `api, auth:sanctum, admin` | `RoomController@deleteImage` |
| DELETE | `/activities/{id}/image` | sanctum | `api, auth:sanctum, admin` | `ActivityController@deleteImage` |
| DELETE | `/membership-plans/{id}/image` | sanctum | `api, auth:sanctum, admin` | `MembershipPlanController@deleteImage` |
| DELETE | `/gyms/{id}/logo` | sanctum | `api, auth:sanctum, admin` | `GymController@deleteLogo` |

## Notes

- The same method/path can appear more than once in the declaration file with different middleware scopes.
- The effective runtime behavior for duplicate declarations follows Laravel's route registration order.
