<?php

namespace App\Actions\Admin;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use Laravel\Fortify\Actions\AttemptToAuthenticate as FortifyAttempt;

class AttemptToAuthenticate extends FortifyAttempt
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * The login rate limiter instance.
     *
     * @var \Laravel\Fortify\LoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @param  \Laravel\Fortify\LoginRateLimiter  $limiter
     * @return void
     */
    public function __construct(StatefulGuard $guard, LoginRateLimiter $limiter)
    {
        $this->guard = $guard;
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $this->guard->attempt(
            $request->only(Fortify::username(), 'password'),
            $request->boolean('remember')
        );

        $user = $this->guard->user();

        //管理者でない場合ログアウト
        if (!$user || !$user->is_admin) {
            $this->guard->logout();

            throw ValidationException::withMessages([
                Fortify::username() => __('管理者アカウントでログインしてください。'),
            ]);
        }

        return $next($request);
    }

    /**
     * Attempt to authenticate using a custom callback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    protected function handleUsingCustomCallback($request, $next)
    {
        $user = call_user_func(Fortify::$authenticateUsingCallback, $request);

        if (! $user) {
            $this->fireFailedEvent($request);

            return $this->throwFailedAuthenticationException($request);
        }

        $this->guard->login($user, $request->boolean('remember'));

        return $next($request);
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException($request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function fireFailedEvent($request)
    {
        event(new Failed(config('fortify.guard'), null, [
            Fortify::username() => $request->{Fortify::username()},
            'password' => $request->password,
        ]));
    }
}
