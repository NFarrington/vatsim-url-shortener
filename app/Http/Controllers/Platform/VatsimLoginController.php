<?php

namespace App\Http\Controllers\Platform;

use App\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Vatsim\OAuth\SSOException;
use VatsimSso;

class VatsimLoginController extends Controller
{
    use RedirectsUsers, ThrottlesLogins;

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->middleware('guest');
        $this->em = $em;
    }

    protected function redirectTo()
    {
        return route('platform.dashboard');
    }

    public function username()
    {
        return 'id';
    }

    protected function throttleKey(Request $request)
    {
        return $request->ip();
    }

    public function maxAttempts()
    {
        return 20;
    }

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

    public function callback(Request $request)
    {
        $session = $request->session()->pull('auth.vatsim', [
            'key' => null,
            'secret' => null,
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if (!$session['key'] || !$session['secret']) {
            return redirect()->route('platform.login')
                ->with('error', 'SSO login failed: missing key or secret.');
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

    protected function processLogin(Request $request, $ssoUser)
    {
        /* @var User $user */
        $user = $this->em->getRepository(User::class)->find($ssoUser->id) ?? new User();
        $user->setId($ssoUser->id);
        $user->setFirstName($ssoUser->name_first ?? '');
        $user->setLastName($ssoUser->name_last ?? '');
        $user->setVatsimSsoData($ssoUser);
        $this->em->persist($user);
        $this->em->flush();

        if (in_array($ssoUser->id, config('auth.banned_users'))) {
            return redirect()->route('platform.login')
                ->with('error', 'SSO login failed: You are not authorized to use this service.');
        }

        $guardName = config('auth.defaults.guard');
        $remember = config("auth.guards.{$guardName}.remember", false);
        auth()->loginUsingId($ssoUser->id, $remember);

        return $this->sendLoginResponse($request);
    }

    protected function sendFailedVatsimLoginResponse(SSOException $e)
    {
        return redirect()->route('platform.login')
            ->with('error', 'SSO login failed: "'.$e->getMessage().'"');
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        return redirect()->route('platform.login')
            ->with('error', trans('auth.throttle', ['seconds' => $seconds]));
    }

    protected function sendLoginResponse($request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return redirect()->intended($this->redirectPath());
    }
}
