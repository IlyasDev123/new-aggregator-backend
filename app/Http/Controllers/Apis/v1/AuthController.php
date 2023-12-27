<?php

namespace App\Http\Controllers\Apis\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Contracts\AuthServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UserPreferenceRequest;

class AuthController extends Controller
{
    public function __construct(protected AuthServiceInterface $authService)
    {
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authService->login($request->validated());
            return responeSuccess($user, "User logged in successfully");
        } catch (\Exception $e) {
            return sendErrorResponse($e->getMessage());
        }
    }

    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authService->register($request->validated());
            return responeSuccess($user, "User registered successfully");
        } catch (\Exception $e) {
            return sendErrorResponse("Something went wrong" . $e->getMessage());
        }
    }

    public function logout()
    {
        $this->authService->logout();
        return responeSuccess(null, "User logged out successfully");
    }

    public function setUserPreferences(UserPreferenceRequest $request)
    {
        try {
            $user = $this->authService->setUserPreferences($request->validated());
            return responeSuccess($user, "User prefrences updated successfully");
        } catch (\Exception $e) {
            return sendErrorResponse("Something went wrong" . $e->getMessage());
        }
    }

    public function passwordReset(UpdatePasswordRequest $request)
    {
        $user = auth()->user();
        if (!Hash::check($request->old_password, $user->password)) {
            return sendErrorResponse('Old password is incorrect');
        }
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return responeSuccess(null, 'Reset password successfully');
    }
}
