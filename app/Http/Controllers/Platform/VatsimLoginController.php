<?php

namespace App\Http\Controllers\Platform;

use App\Models\User;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Vatsim\OAuth\SSOException;
use VatsimSso;

class VatsimLoginController extends Controller
{
    use RedirectsUsers, ThrottlesLogins;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * The redirect path.
     *
     * @return string
     */
    protected function redirectTo()
    {
        return route('platform.dashboard');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'id';
    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $token = VatsimSso::requestToken(route('platform.login.vatsim.callback'));

        $request->session()->put('auth.vatsim', [
            'key' => (string) $token->token->oauth_token,
            'secret' => (string) $token->token->oauth_token_secret,
        ]);

        $url = config('vatsim-sso.base').'auth/pre_login/?oauth_token='.$token->token->oauth_token;

        return redirect()->to($url);
    }

    /**
     * Handle a login callback request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function callback(Request $request)
    {
        $session = $request->session()->pull('auth.vatsim');

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        try {
            $ssoRequest = VatsimSso::checkLogin(
                $session['key'],
                $session['secret'],
                $request->input('oauth_verifier')
            );

            $user = $ssoRequest->user;

            return $this->processLogin($request, $user);
        } catch (SSOException $e) {
            $this->incrementLoginAttempts($request);

            return $this->sendFailedVatsimLoginResponse($e);
        }
    }

    /**
     * Update and log in the user.
     *
     * @param \Illuminate\Http\Request $request
     * @param $ssoUser
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function processLogin($request, $ssoUser)
    {
        /* @var User $user */
        User::updateOrCreate([
            'id' => $ssoUser->id,
        ], [
            'first_name' => $ssoUser->name_first ?? '',
            'last_name' => $ssoUser->name_last ?? '',
            'vatsim_sso_data' => $ssoUser,
        ]);

        if (in_array($ssoUser->id, config('auth.banned_users'))) {
            return redirect()->route('platform.login')
                ->with('error', 'SSO login failed: You are not authorized to use this service.');
        }

        $guardName = config('auth.defaults.guard');
        $remember = config("auth.guards.{$guardName}.remember", false);
        auth()->loginUsingId($ssoUser->id, $remember);

        return $this->sendLoginResponse($request);
    }

    /**
     * Get the failed login response instance.
     *
     * @param \Vatsim\OAuth\SSOException $e
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedVatsimLoginResponse(SSOException $e)
    {
        return redirect()->route('platform.login')
            ->with('error', 'SSO login failed: "'.$e->getMessage().'"');
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse($request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        return redirect()->route('platform.login')
            ->with('error', trans('auth.throttle', ['seconds' => $seconds]));
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse($request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return redirect()->intended($this->redirectPath());
    }
}
