<?php

namespace App\Http\Controllers\Platform;

use App\Entities\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Log;

class VatsimConnectLoginController extends Controller
{
    use RedirectsUsers, ThrottlesLogins;

    const VATSIM_CONNECT_SESSION_STATE_KEY = 'vatsim-connect-auth-state';

    private EntityManagerInterface $em;
    private GenericProvider $provider;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->middleware('guest');
        $this->em = $em;
        $this->userRepository = $userRepository;

        $this->middleware(function (Request $request, Closure $next) {
            $this->provider = new GenericProvider(
                [
                    'clientId' => config('vatsim-connect.client_id'),
                    'clientSecret' => config('vatsim-connect.client_secret'),
                    'redirectUri' => route('platform.login.vatsim-connect'),
                    'urlAuthorize' => config('vatsim-connect.base_url').'/oauth/authorize',
                    'urlAccessToken' => config('vatsim-connect.base_url').'/oauth/token',
                    'urlResourceOwnerDetails' => config('vatsim-connect.base_url').'/api/user',
                    'scopes' => config('vatsim-connect.scopes'),
                    'scopeSeparator' => ' ',
                ]
            );

            return $next($request);
        });
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
        if ($request->has('error')) {
            $request->session()->forget(self::VATSIM_CONNECT_SESSION_STATE_KEY);

            return redirect()->route('platform.login')
                ->with('error', "VATSIM Connect login failed: {$request->get('error')}. Please try again.");
        }

        if (!$request->has('code') || !$request->has('state')) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();

            $request->session()->put(self::VATSIM_CONNECT_SESSION_STATE_KEY, $this->provider->getState());

            return redirect()->to($authorizationUrl);
        }

        if ($request->input('state') !== $request->session()->pull(self::VATSIM_CONNECT_SESSION_STATE_KEY)) {
            Log::info('Login failure: invalid state.');

            return redirect()
                ->route('platform.login')
                ->with('error', 'VATSIM Connect login failed: invalid state. Please try again.');
        }

        return $this->verifyLogin($request);
    }

    private function verifyLogin(Request $request)
    {
        try {
            $accessToken = $this->provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $request->input('code'),
                ]
            );
        } catch (IdentityProviderException $e) {
            Log::info("Login failure: IdentityProviderException. {$e->getMessage()}");

            return redirect()
                ->route('platform.login')
                ->with('error', 'VATSIM Connect login failed: IdentityProviderException. Please try again.');
        }

        $resourceOwner = json_decode(
            json_encode(
                $this->provider->getResourceOwner($accessToken)->toArray()
            )
        );

        if (!isset($resourceOwner->data) || !isset($resourceOwner->data->cid)) {
            Log::info('Login failure: missing details.', ['resourceOwner' => $resourceOwner]);

            return redirect()
                ->route('platform.login')
                ->with('error', 'VATSIM Connect login failed: required details missing. Please try again.');
        }

        $user = $this->userRepository->find($resourceOwner->data->cid);
        if ($user === null) {
            $user = new User();
            $user->setId((int) $resourceOwner->data->cid);
        }
        if ($resourceOwner->data->oauth->token_valid === 'true') {
            $user->setVatsimConnectAccessToken($accessToken->getToken());
            $user->setVatsimConnectRefreshToken($accessToken->getRefreshToken());
            $tokenExpiry = $accessToken->getExpires();
            $user->setVatsimConnectTokenExpiry(
                $tokenExpiry === null ? null : Carbon::createFromTimestampUTC($tokenExpiry)
            );
        }
        $user->setFirstName($resourceOwner->data->personal->name_first ?? '');
        $user->setLastName($resourceOwner->data->personal->name_last ?? '');
        if (!empty($resourceOwner->data->personal->email ?? '')) {
            $user->setEmail($resourceOwner->data->personal->email);
            $user->setEmailVerified(true);
        }

        $this->em->persist($user);
        $this->em->flush();

        $atcRating = $resourceOwner->data->vatsim->rating->id ?? null;
        $pilotRating = $resourceOwner->data->vatsim->pilotrating->id ?? null;
        if (is_null($atcRating) && is_null($pilotRating)) {
            Log::info('Login failure: no rating.', ['resourceOwner' => $resourceOwner]);

            return redirect()->route('platform.login')
                ->with('error', 'VATSIM Connect login failed. You must have an ATC or pilot rating to log in.');
        }

        if (in_array($user->getId(), config('auth.banned_users'))) {
            Log::info('Login failure: banned user.', ['resourceOwner' => $resourceOwner]);

            return redirect()->route('platform.login')
                ->with('error', 'VATSIM Connect login failed. You are not authorized to use this service.');
        }

        $guardName = config('auth.defaults.guard');
        $remember = config("auth.guards.{$guardName}.remember", false);
        auth()->loginUsingId($user->getId(), $remember);
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        return redirect()->intended($this->redirectPath());
    }
}
