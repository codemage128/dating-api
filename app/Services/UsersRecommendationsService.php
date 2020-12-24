<?php


namespace App\Services;


use App\User;
use Carbon\Carbon;

class UsersRecommendationsService
{

    public function getUserRecommendations (User $user) {
        $compatibilities = $user->getCompatibilities();
        switch ($user->looking) {
            case 'she/her':
                $lookingForArray = ['she/her'];
                break;
            case 'he/him':
                $lookingForArray = ['he/him'];
                break;
            case 'she/her they/them':
                $lookingForArray = ['she/her', 'she/her they/them'];
                break;
            case 'he/him they/them':
                $lookingForArray = ['he/him', 'he/him they/them'];
                break;
            default:
                $lookingForArray = ['he/him', 'she/her', 'she/her they/them', 'he/him they/them', 'she/her he/him they/them'];
                break;
        }

        $mostCompatibleSignsIds = [];
        foreach ($compatibilities['most'] as $compatibility) {
            $mostCompatibleSignsIds[] = $user->getSignId($compatibility);
        }
        $secondCompatibleSignsIds = [];
        foreach ($compatibilities['second'] as $compatibility) {
            $secondCompatibleSignsIds[] = $user->getSignId($compatibility);
        }
        $datesBetween = [];

        $userRange = explode('-', $user->age_range);

        $datesBetween[] = Carbon::now()->subYears($userRange[1])->toDateString();
        $datesBetween[] = Carbon::now()->subYears($userRange[0])->toDateString();
        $result = collect();
        $mostCompatibleUsers = User::where('id', '<>', $user->id)->whereIn('pronoun', $lookingForArray)
            ->whereIn('sign', $mostCompatibleSignsIds)->whereBetween('birthday', $datesBetween)->get();
        $secondCompatibleUsers = User::where('id', '<>', $user->id)->whereIn('pronoun', $lookingForArray)
            ->whereIn('sign', $secondCompatibleSignsIds)->whereBetween('birthday', $datesBetween)->get();
        $otherUsers = User::where('id', '<>', $user->id)->whereIn('pronoun', $lookingForArray)
            ->whereNotIn('sign', array_merge($mostCompatibleSignsIds, $secondCompatibleSignsIds))
            ->whereBetween('birthday', $datesBetween)->get();
        return $result->concat($mostCompatibleUsers->shuffle())->concat($secondCompatibleUsers->shuffle())->concat($otherUsers->shuffle());
    }
}
