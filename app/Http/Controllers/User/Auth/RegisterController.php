<?php

namespace App\Http\Controllers\User\Auth;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\Intended;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Wallet;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{

    use RegistersUsers;

    public function __construct()
    {
        parent::__construct();
    }

    public function showRegistrationForm()
    {
        $pageTitle = "Register";

        if (gs('registration')) {
            Intended::identifyRoute();
            return view('Template::user.auth.register', compact('pageTitle'));
        } else {
            return view('Template::registration_disabled', compact('pageTitle'));
        }
    }


    protected function validator(array $data)
    {

        $passwordValidation = Password::min(6);

        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        // Validation rules - lastname not required for establishment
        $rules = [
            'firstname' => 'required',
            'email'     => 'required|string|email|unique:users',
            'password'  => ['required', 'confirmed', $passwordValidation],
            'captcha'   => 'sometimes|required',
            'agree'     => 'nullable'
        ];

        // Add lastname validation only for individual accounts
        if (!isset($data['form_type']) || $data['form_type'] != 'establishment') {
            $rules['lastname'] = 'required';
        }

        $validate = Validator::make($data, $rules, [
            'firstname.required'=>'The first name field is required',
            'lastname.required'=>'The last name field is required'
        ]);

        return $validate;
    }

    public function register(Request $request)
    {
        if (!gs('registration')) {
            $notify[] = ['error', 'Registration not allowed'];
            return back()->withNotify($notify);
        }
        $this->validator($request->all())->validate();

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        // Prepare data array - exclude file to avoid serialization issues
        $data = $request->except(['commercial_registration']);
        
        // Handle commercial registration file upload for establishment
        if (isset($data['form_type']) && $data['form_type'] == 'establishment' && $request->hasFile('commercial_registration')) {
            $file = $request->file('commercial_registration');
            // Save in public/users folder
            $path = public_path(getFilePath('users'));
            // Create directory if doesn't exist
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            // Generate unique filename
            $filename = uniqid() . time() . '.' . $file->getClientOriginalExtension();
            // Move file to public/users
            $file->move($path, $filename);
            // Store only filename (path is public/users)
            $data['commercial_registration_path'] = $filename;
        }

        event(new Registered($user = $this->create($data)));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }



    protected function create(array $data)
    {
        $referBy = session()->get('reference');
        if ($referBy) {
            $referUser = User::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }

        //User Create
        $user            = new User();
        $user->email     = strtolower($data['email']);
        $user->password  = Hash::make($data['password']);
        $user->ref_by    = $referUser ? $referUser->id : 0;
        
        // Determine user type from form data
        if (isset($data['form_type']) && $data['form_type'] == 'establishment') {
            $user->type = 'establishment';
            $user->user_type = 'establishment';
            $user->firstname = $data['firstname']; // اسم المنشأة
            $user->lastname = ''; // لا يوجد lastname للمنشأة
        } else {
            $user->type = 'individual';
            $user->user_type = 'individual';
            $user->firstname = $data['firstname'];
            $user->lastname = $data['lastname'];
        }
        
        // Save mobile and dial_code if provided
        if (isset($data['mobile']) && !empty($data['mobile'])) {
            $user->mobile = $data['mobile'];
        }
        if (isset($data['country_code']) && !empty($data['country_code'])) {
            $user->dial_code = $data['country_code'];
        }
        
        // Handle establishment-specific fields
        if ($user->type == 'establishment') {
            // Use firstname as establishment_name if provided
            if (isset($data['firstname'])) {
                $user->establishment_name = $data['firstname'];
            }
            if (isset($data['establishment_info'])) {
                $user->establishment_info = $data['establishment_info'];
            }
            // Handle commercial registration file upload
            if (isset($data['commercial_registration_path'])) {
                $user->commercial_registration = $data['commercial_registration_path'];
            }
        }
        
        $user->kv = gs('kv') ? Status::NO : Status::YES;
        $user->ev = gs('ev') ? Status::NO : Status::YES;
        $user->sv = gs('sv') ? Status::NO : Status::YES;
        $user->ts = Status::DISABLE;
        $user->tv = Status::ENABLE;
        $user->profile_complete = Status::YES; // Set profile as complete to skip user-data page
        
        // For establishment accounts, set status to DISABLE until admin approval
        if ($user->type == 'establishment') {
            $user->status = Status::USER_BAN; // Disable account until admin approval
        } else {
            $user->status = Status::USER_ACTIVE; // Individual accounts are active by default
        }
        
        $user->save();

        // Create empty wallet for the user
        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->balance = 0;
        // For establishment accounts, set wallet status to DISABLE until admin approval
        if ($user->type == 'establishment') {
            $wallet->status = Status::DISABLE;
        } else {
            $wallet->status = Status::ENABLE;
        }
        $wallet->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();


        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        if ($exist) {
            $userLogin->longitude    = $exist->longitude;
            $userLogin->latitude     = $exist->latitude;
            $userLogin->city         = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude    = @implode(',', $info['long']);
            $userLogin->latitude     = @implode(',', $info['lat']);
            $userLogin->city         = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country      = @implode(',', $info['country']);
        }

        $userAgent          = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os      = @$userAgent['os_platform'];
        $userLogin->save();

        $userType = $user->type == 'establishment' ? 'منشأة' : 'مستخدم';
        $this->audit('register', "تم تسجيل {$userType} جديد: " . ($user->establishment_name ?? $user->username ?? $user->email), $user);

        return $user;
    }

    public function checkUser(Request $request){
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->email) {
            $exist['data'] = User::where('email',$request->email)->exists();
            $exist['type'] = 'email';
            $exist['field'] = 'Email';
        }
        if ($request->mobile) {
            $exist['data'] = User::where('mobile',$request->mobile)->where('dial_code',$request->mobile_code)->exists();
            $exist['type'] = 'mobile';
            $exist['field'] = 'Mobile';
        }
        if ($request->username) {
            $exist['data'] = User::where('username',$request->username)->exists();
            $exist['type'] = 'username';
            $exist['field'] = 'Username';
        }
        return response($exist);
    }

    public function registered()
    {
        return to_route('user.home');
    }

}
