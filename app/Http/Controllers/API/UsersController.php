<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\QuizRequest;
use App\Http\Requests\StoreUserRequest;
use App\Services\UsersRecommendationsService;
use App\User;
use App\UserLike;
use App\UserQuiz;
use App\UserToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Intervention\Zodiac\Calculator;

class UsersController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();
        $user = new User();
        $user->fill($data);
        $user->password = Hash::make($data['password']);
        $calc = new Calculator();
        $sign = $calc->make($data['birthday']);

        $user->sign = $user->getSignId($sign);
        $user->save();
        $token = $user->createTokenFromRequest($request);
        if (isset($data['images']) && !empty($data['images'])) {
            $user->storeProfileImages($data['images']);
        }
        $quiz = new UserQuiz();
        $quiz->fill($data);
        $quiz->user_id = $user->id;
        if ($quiz->save()) {
            $user->agrees_count = $quiz->answer_1 + $quiz->answer_2 + $quiz->answer_3 + $quiz->answer_4;
            $user->save();
        }
        if ($user && $token) {
            return $this->sendApiResponse('success', ['user' => $user, 'token' => $token, 'quiz' => $quiz], 'Registration complete successfully');
        } else {
            return $this->sendApiResponse('error', ['request_reflection' => $request->all()], 'Something went wrong during user creation', 401);
        }
    }

    public function login(Request $request)
    {
        $data = $request->all();
        $user = User::where(['email' => $data['email']])->first();
        if (!isset($data['uuid']) || empty($data['uuid']))
            return $this->sendApiResponse('error', ['request_reflection' => $request->all()], 'No device id provided', 403);
        if(!$user) {
            return $this->sendApiResponse('error', ['request_reflection' => $request->all()], 'No user found', 404);
        }
        if ($user && Hash::check($data['password'], $user->password)) {
            $token = UserToken::where(['user_id' => $user->id, 'device_id' => $data['uuid']])->where('expires_at', '>', Carbon::now())->first();
            if ($token) {
                $token->expires_at = Carbon::now()->addDays(30);
                $token->save();
                return $this->sendApiResponse('success', ['user' => $user, 'token' => $token], 'Token extended');
            } else {
                return $this->sendApiResponse('success', ['user' => $user, 'token' => $user->createTokenFromRequest($request)], 'New token is successfully generated');
            }
        } else {
            return $this->sendApiResponse('error', ['request_reflection' => $request->all()], 'Wrong password', 403);
        }
    }

    public function updateProfile(ProfileRequest $request)
    {
        $data = $request->all();
        $user = User::find($request->auth_user);
        $user->fill($data);
        if(!empty($request->get('password', ''))) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return $this->sendApiResponse('success', ['user' => $user], 'Profile is updated successfully');

    }

    public function list(Request $request)
    {

        $user = User::find($request->auth_user);

        if (!$user) return $this->sendApiResponse('error', ['request_reflection' => $request->all()], 'No user found', 404);
        $searchService = new UsersRecommendationsService();

        $results = $searchService->getUserRecommendations($user);

        return $this->sendApiResponse('success', ['users' => $results], 'Recommendation successfully formed');
    }

    public function storeLike(Request $request)
    {
        $like = new UserLike();
        $like->user_id = $request->auth_user;
        $like->like_id = $request->like_id;
        $isMatch = UserLike::where(['like_id' => $request->auth_user, 'user_id' => $request->like_id])->count();
        try {
            $like->save();
            return $this->sendApiResponse('success', ['like' => $like, 'is_match' => $isMatch], 'Like was saved successfully');
        } catch (\Exception $e) {
            return $this->sendApiResponse('error', ['request_reflection' => $request->all()], 'Error while trying to save like data', 422);
        }
    }

    public function getLikesData(Request $request)
    {
        $likesIds = UserLike::where('like_id', $request->auth_user)->distinct('user_id')->pluck('user_id');
        $matchesIds = UserLike::whereIn('like_id', $likesIds)->where('user_id', $request->auth_user)->pluck('like_id');
        $matches = User::whereIn('id', $matchesIds)->get();
        $likes = User::whereIn('id', $likesIds)->whereNotIn('id', $matchesIds)->get();
        return $this->sendApiResponse('success', ['matches' => $matches, 'likes' => $likes]);
    }
}
