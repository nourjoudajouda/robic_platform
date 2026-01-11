<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Lib\Intended;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Status;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    use AuthenticatesUsers;


    protected $username;


    public function __construct()
    {
        parent::__construct();
    }

    public function showLoginForm()
    {
        $pageTitle = "Login";
        Intended::identifyRoute();
        return view('Template::user.auth.login', compact('pageTitle'));
    }

    public function login(Request $request)
    {
        // Determine username field type before validation
        $this->username = $this->findUsername();

        $this->validateLogin($request);

        if(!verifyCaptcha()){
            $notify[] = ['error','Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        Intended::reAssignSession();

        return $this->sendFailedLoginResponse($request);
    }

    public function findUsername()
    {
        $login = request()->input('username');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $login]);
        return $fieldType;
    }

    public function username()
    {
        // If username is not set yet, determine it from request
        if (!$this->username) {
            $this->username = $this->findUsername();
        }
        return $this->username;
    }

    protected function validateLogin($request)
    {

        $validator = Validator::make($request->all(), [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            Intended::reAssignSession();
            $validator->validate();
        }

    }

    public function logout()
    {
        $user = auth()->user();
        $this->guard()->logout();
        request()->session()->invalidate();

        if ($user) {
            $this->audit('logout', 'تسجيل خروج المستخدم: ' . $user->username, $user);
        }

        $notify[] = ['success', 'You have been logged out.'];
        return to_route('user.login')->withNotify($notify);
    }


    public function authenticated(Request $request, $user)
    {
        // Don't toggle verification status - this was causing issues
        // $user->tv = $user->ts == Status::VERIFIED ? Status::UNVERIFIED : Status::VERIFIED;
        // $user->save();
        
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip',$ip)->first();
        $userLogin = new UserLogin();
        
        try {
            if ($exist) {
                $userLogin->longitude =  $exist->longitude;
                $userLogin->latitude =  $exist->latitude;
                $userLogin->city =  $exist->city;
                $userLogin->country_code = $exist->country_code;
                $userLogin->country =  $exist->country;
            }else{
                $info = @json_decode(@json_encode(getIpInfo()), true);
                $userLogin->longitude =  @implode(',', $info['long'] ?? []);
                $userLogin->latitude =  @implode(',', $info['lat'] ?? []);
                $userLogin->city =  @implode(',', $info['city'] ?? []);
                $userLogin->country_code = @implode(',', $info['code'] ?? []);
                $userLogin->country =  @implode(',', $info['country'] ?? []);
            }
        } catch (\Exception $e) {
            // If IP info fails, continue with empty values
            \Log::warning('Failed to get IP info during login: ' . $e->getMessage());
        }

        try {
            $userAgent = osBrowser();
            $userLogin->browser = @$userAgent['browser'] ?? 'Unknown';
            $userLogin->os = @$userAgent['os_platform'] ?? 'Unknown';
        } catch (\Exception $e) {
            // If browser info fails, use defaults
            \Log::warning('Failed to get browser info during login: ' . $e->getMessage());
            $userLogin->browser = 'Unknown';
            $userLogin->os = 'Unknown';
        }
        
        $userLogin->user_id = $user->id;
        $userLogin->user_ip =  $ip;
        $userLogin->save();

        $this->audit('login', 'تسجيل دخول المستخدم: ' . $user->username, $user);

        notify($user, 'LOGIN_NOTIFICATION', [
            'time' => showDateTime(now()),
            'ip' => getRealIP(),
            'browser' => @$userAgent['browser'],
            'os' => @$userAgent['os_platform'],
        ]);

        $redirection = Intended::getRedirection();
        return $redirection ? $redirection : to_route('user.home');
    }


}
