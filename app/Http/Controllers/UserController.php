<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

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
