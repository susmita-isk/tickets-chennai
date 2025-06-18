<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function login(Request $request)
    {
       
        $authenticated = false;

        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('LOGIN_ID',$request->username)->first();
        $inactiveUser = User::where(['LOGIN_ID' => $request->username, 'ACTIVE_FLAG' => 'N'])->first();

        if($inactiveUser){
            return back()->withErrors([
                'email' => 'Inactive User !!',
            ]);
        }

        if($user)
        {
           $authenticated = Hash::check($request->password, $user->PASSWORD);
        }   
 
        if ($authenticated) {

            Auth::login($user);
 
            return redirect()->intended('home');
        }
 
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}