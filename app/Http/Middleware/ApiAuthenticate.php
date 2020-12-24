<?php


namespace App\Http\Middleware;


use App\UserToken;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Cache;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();
        $deviceId = $request->header('uuid');
        $token = Cache::remember('user_token_'.$deviceId.'_'.$token, 86400, function () use ($deviceId, $token) {
            return UserToken::where(['device_id' => $deviceId, 'token' => $token])
                ->where('expires_at', '>', Carbon::now())->first();
        });
        if($token) {
            $request->merge(['auth_user' => $token->user_id]);
            return $next($request);
        }
        $response = [
            'status' => 'error',
            'data' => [],
            'message' => 'Unauthenticated to perform this request'
        ];
        return response()->json($response, 403);
    }
}
