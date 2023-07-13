<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{

    public function follow(User $user)
    {
        // You cannot follow yourself
        if ($user->id === auth()->user()->id) {
            return back()->with('failure', 'No te puedes seguir a ti mismo');
        }

        // You cannot follow someone you already follow
        $existsCheck = Follow::where([
            ['user_id', '=', auth()->user()->id],
            ['followeduser', '=', $user->id]
        ], [])->count();
        if ($existsCheck > 0) {
            return back()->with('failure', 'Ya sigues a este usuario');
        }
        

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser = $user->id;
        $newFollow->save();

        return back()->with('success', 'Ahora sigues a ' . $user->username);
    }

    public function unfollow(User $user){
        Follow::where([
            ['user_id', '=', auth()->user()->id],
            ['followeduser', '=', $user->id]
        ], [])->delete();
        return back()->with('success', 'Ya no sigues a ' . $user->username);
    }




}
