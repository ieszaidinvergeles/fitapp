<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\logs\AuthLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles all authentication endpoints for the GymApp API.
 *
 * SRP: Solely responsible for coordinating authentication flows.
 * DIP: Depends on FormRequest abstractions and the Password facade,
 *      not on concrete implementations of token or mail delivery.
 */
class AuthController extends Controller
{
    /**
     * Registers a new client user, dispatches the email verification event,
     * and creates the default settings record.
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Registration failed. Please try again.'];

        try {
            DB::beginTransaction();

            $user = User::create([
                'username'                => $request->username,
                'email'                   => $request->email,
                'password_hash'           => $request->password,
                'role'                    => 'client',
                'full_name'               => $request->full_name,
                'dni'                     => $request->dni,
                'birth_date'              => $request->birth_date,
                'membership_status'       => 'expired',
                'cancellation_strikes'    => 0,
                'is_blocked_from_booking' => false,
            ]);

            Setting::create(['user_id' => $user->id]);

            DB::commit();

            event(new Registered($user));

            $this->writeAuthLog($request, $user->id, 'register', true);

            $result       = true;
            $messageArray = ['general' => 'Registration successful. Please verify your email.'];
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->writeAuthLog($request, null, 'register_failed', false);
        }

        $status = $result ? Response::HTTP_CREATED : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json(['message' => $messageArray['general']], $status);
    }

    /**
     * Authenticates an existing user and returns a Sanctum API token.
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Invalid credentials.'];
        $data         = [];

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password_hash)) {
                $this->writeAuthLog($request, $user?->id, 'login_failed', false);

                return response()->json(['message' => $messageArray['general']], Response::HTTP_UNAUTHORIZED);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            $this->writeAuthLog($request, $user->id, 'login', true);

            $result       = true;
            $messageArray = ['general' => 'Login successful.'];
            $data         = [
                'token' => $token,
                'user'  => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                ],
            ];
        } catch (\Throwable $e) {
            $this->writeAuthLog($request, null, 'login_failed', false);
        }

        $status = $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json(
            $result ? array_merge(['message' => $messageArray['general']], $data) : ['message' => $messageArray['general']],
            $status
        );
    }

    /**
     * Revokes the current user's API token and logs the event.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Logout failed.'];

        try {
            $request->user()->currentAccessToken()->delete();

            $this->writeAuthLog($request, $request->user()->id, 'logout', true);

            $result       = true;
            $messageArray = ['general' => 'Logged out successfully.'];
        } catch (\Throwable $e) {
            // token deletion failure — still attempt to log
            $this->writeAuthLog($request, $request->user()?->id, 'logout_failed', false);
        }

        $status = $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json(['message' => $messageArray['general']], $status);
    }

    /**
     * Sends a password reset link to the provided email address.
     *
     * @param  ForgotPasswordRequest  $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Unable to send reset link.'];

        try {
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                $result       = true;
                $messageArray = ['general' => 'Password reset link sent to your email.'];
            } else {
                $messageArray = ['general' => __($status)];
            }
        } catch (\Throwable $e) {
            // no changes to AuthLog here — no user context available
        }

        $status = $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return response()->json(['message' => $messageArray['general']], $status);
    }

    /**
     * Resets the user's password using the provided token.
     *
     * @param  ResetPasswordRequest  $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Password reset failed.'];

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password): void {
                    $user->forceFill(['password_hash' => $password])->save();
                    $user->tokens()->delete();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                $result       = true;
                $messageArray = ['general' => 'Password reset successfully.'];
            } else {
                $messageArray = ['general' => __($status)];
            }
        } catch (\Throwable $e) {
            // no changes to AuthLog here — token context is not a session
        }

        $httpStatus = $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return response()->json(['message' => $messageArray['general']], $httpStatus);
    }

    /**
     * Marks the authenticated user's email address as verified.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Email verification failed.'];

        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified.'], Response::HTTP_OK);
            }

            if (!hash_equals(
                (string) $request->route('hash'),
                sha1($user->getEmailForVerification())
            )) {
                return response()->json(['message' => 'Invalid verification link.'], Response::HTTP_FORBIDDEN);
            }

            $user->markEmailAsVerified();

            $this->writeAuthLog($request, $user->id, 'email_verified', true);

            $result       = true;
            $messageArray = ['general' => 'Email verified successfully.'];
        } catch (\Throwable $e) {
            // verification failure does not require an AuthLog entry
        }

        $status = $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json(['message' => $messageArray['general']], $status);
    }

    /**
     * Resends the email verification notification to the authenticated user.
     *
     * @param  ResendVerificationRequest  $request
     * @return JsonResponse
     */
    public function resendVerification(ResendVerificationRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Failed to resend verification email.'];

        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email is already verified.'], Response::HTTP_OK);
            }

            $user->sendEmailVerificationNotification();

            $result       = true;
            $messageArray = ['general' => 'Verification email resent.'];
        } catch (\Throwable $e) {
            // log failure silently — no AuthLog event for resend
        }

        $status = $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json(['message' => $messageArray['general']], $status);
    }

    /**
     * Returns the authenticated user's profile data.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()], Response::HTTP_OK);
    }

    /**
     * Writes an entry to the auth_logs table.
     *
     * SRP: Isolates log persistence so it does not interrupt the main auth flow.
     *
     * @param  Request     $request
     * @param  int|null    $userId
     * @param  string      $eventType
     * @param  bool        $success
     * @return void
     */
    private function writeAuthLog(Request $request, ?int $userId, string $eventType, bool $success): void
    {
        try {
            AuthLog::create([
                'user_id'    => $userId,
                'event_type' => $eventType,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'success'    => $success,
            ]);
        } catch (\Throwable $e) {
            // Auth log failures must never interrupt the main auth response.
        }
    }
}
