<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        auth()->logout();
        return redirect('/')->with('success', 'Cerraste Sesión');;
    }

    public function showCorrectHomePage() {
        if (auth()->check()) {
            return view('homepage-feed');
        } else {
            return view('home');
        }
    }

    public function profile(User $user) {
        $posts = $user->posts()->get();
        return view('profile-posts',
        ['username' => $user->username,
        'posts' => $posts,
        'avatar' => $user->avatar, 
        'postsCount' => $posts->count(),        
        ]
);
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
