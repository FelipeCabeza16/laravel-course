<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function storeAvatar(Request $request) {
        $request->validate([
            'avatar' => 'required|image|max:4096'
        ]);

        $user = auth()->user();

        $filename = $user->id . '-' . uniqid() . '.jpg';

        $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/' . $filename, $imgData);

        $oldAvatar = $user->avatar;

        // Update database 
        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != '/fallback-avatar.jpg'){
            Storage::delete(str_replace('/storage/', '/public/', $oldAvatar));
        }

        return back()->with('success', 'Cambiaste la foto de perfil');

    }
    public function viewAvatarForm() {
        return view('avatar-view');
    }

    public function logout(){
        
        event(new OurExampleEvent(
            ['username' => auth()->user()->username,
            'action' => 'logout']));
            
        
        auth()->logout();
        return redirect('/')->with('success', 'Cerraste Sesión');;
    }

    public function showCorrectHomePage() {
        if (auth()->check()) {
            return view('homepage-feed', ['posts' => auth()->user()->feedPosts()->latest()->paginate(4)]);
        } else {
            // if (Cache::has('postCount')){
            //     $postCount = Cache::get('postCount');
            // }else {
            //     sleep(5);
            //     $postCount = Post::count();
            //     // Third argument seconds
            //     Cache::put('postCount', $postCount, 60 );
                
            // }
            // Key, Seconds, Function if not exists
            $postCount = Cache::remember('postCount', 100, function(){
                sleep(1);
                return Post::count();
            });
            return view('home', ['postCount' => $postCount]);
        }
    }


    public function loginApi(Request $req) {
        $incomingFields = $req->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (auth()->attempt($incomingFields)) {
            $user = User::where('username', $incomingFields['username'])->first();
            $token = $user->createToken('ourapptoken')->plainTextToken;
            return $token;
        } else {
            return response()->json(['error' => 'Wrong credentials'], 401);
        }


    }


    private function getSharedData($user) {
        $currentlyFollowing = 0;

        if (auth()->check()) {
            $currentlyFollowing = Follow::where([
                ['user_id', '=', auth()->user()->id],
                ['followeduser', '=', $user->id]
            ], [])->count();

        }

        View::share('sharedData', 
        ['username' => $user->username,
        'currentlyFollowing' => $currentlyFollowing,
        'avatar' => $user->avatar, 
        'postsCount' => $user->posts()->count(),
        'followerCount' => $user->followers()->count(),
        'followingCount' => $user->following()->count(),

        ]);
    }

    public function profile(User $user) {
        $this->getSharedData($user);
        return view('profile-posts', ['posts' => $user->posts()->latest()->get()]);
    }


    public function profileRaw(User $user) {
        return response()->json([
            'theHTML' => view('profile-posts-only', ['posts' => $user->posts()->latest()->get()])->render(),
            'docTitle' => $user->username . ' - Perfil ',
        ]);
    }


    public function followers(User $user) {

        $this->getSharedData($user);
        return view('profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }


    public function followersRaw(User $user) {

        return response()->json([
            'theHTML' => view('profile-followers-only', ['followers' => $user->followers()->latest()->get()])->render(),
            'docTitle' => $user->username . ' - Seguidores ',
        ]);
    }

    public function following(User $user) {

        $this->getSharedData($user);
        return view('profile-following', ['following' => $user->following()->latest()->get()]);
    }

    public function followingRaw(User $user) {
        return response()->json([
            'theHTML' => view('profile-following-only', ['following' => $user->following()->latest()->get()])->render(),
            'docTitle' => $user->username . ' - Seguidos ',
        ]);
    }


    public function login(Request $request){


        $incomingFields = $request->validate([
        'loginusername' => 'required',
        'loginpassword' => 'required',
        ]);

        if (auth()->attempt(
            ['username' => $incomingFields['loginusername'],
            'password' => $incomingFields['loginpassword']],

        )) {

            $request->session()->regenerate();
            
            event(new OurExampleEvent(
                ['username' => auth()->user()->username,
                'action' => 'login']));

            
            return redirect('/')->with('success', 'Iniciaste Sesión');
        } else {
            return redirect('/')->with('failure', 'Datos inválidos');


        }
    }

    public function register(Request $request) {
        $incomingFields = $request->validate([
            'username' => [
                'required',
                'min:3',
                'max:100',
                Rule::unique('users', 'username')
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
            ],
            'password' => [
                'required',
                'min:6',
                'confirmed'
            ],
        ]);


        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);

        auth()->login($user);
    

        return redirect('/')->with('success', 'Te registraste en la plataforma');

    }
}
