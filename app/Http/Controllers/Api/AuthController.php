<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;
use App\Models\User;
use App\Http\Resources\GeneralError;
use App\Http\Resources\GeneralResponse;
use App\Models\PackageType;
use App\Models\ReferFriend;
use App\Models\Role;
use App\Notifications\SendResetPasswordMail;
use App\Notifications\ReferFriendMail;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @return [string] json data
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,NULL,id,deleted_at,NULL'],
            'contact_number' => ['required', 'unique:users,contact_number,NULL,id,deleted_at,NULL'],
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        DB::beginTransaction();
        try {

            $data = $request->all();
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }
            $referral_code = substr(sha1(rand()), 0, 6);
            $user = new User;
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->email = $data['email'];
            $user->password = isset($data['password']) ? $data['password'] : '123456';
            $user->contact_number = $data['contact_number'];
            $user->referral_code = $referral_code;
            $user->save();

            $user->attachRole(Role::getRoleByName('PersonalUser')->id);

            DB::commit();
            return new GeneralResponse(['status' => 1, 'data' => $user->toArray(), 'message' => 'success']);
        } catch (\Exception $e) {
            DB::rollback();
            return new GeneralError(['status' => 0, 'data' => [], 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create user
     *
     * @return [string] json data
     */
    public function registerSetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
            'password' => ['required', 'min:6'],
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        DB::beginTransaction();
        try {

            $data = $request->all();
            $user = User::where('email', $data['email'])->first();
            $user->password = bcrypt($data['password']);
            $user->is_password_set = 1;
            $user->save();

            DB::commit();
            return new GeneralResponse(['status' => 1, 'data' => $user->toArray(), 'message' => 'success']);
        } catch (\Exception $e) {
            DB::rollback();
            return new GeneralError(['status' => 0, 'data' => [], 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create user
     *
     * @return [string] json data
     */
    public function loginFirstPart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        $user = User::where('email', $request->email)->first();
        $data = ['is_password_set' => $user->is_password_set, 'is_payment_done' => $user->is_payment_done];

        return new GeneralResponse(['status' => 1, 'data' => $data, 'message' => 'success']);
    }

    /**
     * Create user
     *
     * @return [string] json data
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken($user->email . '-' . now());
            $data = ['token' => $token->accessToken, 'user_details' => $request->user()];
            return new GeneralResponse(['status' => 1, 'data' => $data, 'message' => 'success']);
        } else {
            return new GeneralError(['status' => 0, 'data' => ['password' => 'Invalid Credentials!'], 'message' => 'Invalid Credentials!']);
        }
    }

    /**
     * Create user
     *
     * @return [string] json data
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        $otp = rand(100000, 999999);
        $user = User::where('email', $request->email)->first();
        $user->notify(new SendResetPasswordMail($otp));
        $user->otp = $otp;
        $user->save();

        return new GeneralResponse(['status' => 1, 'data' => $user->toArray(), 'message' => 'Verification code has been sent to your email']);
    }

    /**
     * Create user
     *
     * @return [string] json data
     */
    public function resetPassword(Request $request)
    {
        // $requestData = decryptData(getPassphrase(), $request->all());
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required'],
            'confirm_password' => ['required', 'same:password']
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        $user = User::where('email', $requestData['email'])->first();
        if ($user->otp != $request->otp) {
            return new GeneralError(['status' => 0, 'message' => 'Invalid OTP']);
        }
        $user->password = bcrypt($requestData['password']);
        $user->is_password_set = 1;
        $user->save();

        return new GeneralResponse(['status' => 1, 'data' => $user->toArray(), 'message' => 'Password updated successfully!']);
    }

    /**
     * Create user
     *
     * @return [string] json data
     */
    public function getPackages(Request $request)
    {
        $data = PackageType::with(['packages', 'packages.package'])->where(['status' => 'Enabled', 'package_type' => 'main'])->get()->toArray();
        return new GeneralResponse(['status' => 1, 'data' => $data, 'message' => 'success']);
    }

    public function getMyProfile(Request $request)
    {
        return new GeneralResponse(['status' => 1, 'data' => $request->user(), 'message' => 'success']);
    }

    public function createReferFriends(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required'],
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $refer_friend = new ReferFriend;

            $refer_friend->user_id = $request->user()->id;
            $refer_friend->name = $data['name'];
            $refer_friend->message = $data['message'];
            $refer_friend->email = $data['email'];
            $refer_friend->save();

            if ($request->user()->referral_code == null) {
                $val = substr(sha1(rand()), 0, 6);
                $user = User::where('id', $request->user()->id)->first();
                $user->referral_code = $val;
                $user->save();
                $referral_code = $val;
            } else {
                $referral_code = $request->user()->referral_code;
            }
            $invitedUser = new User;
            $invitedUser->email = $data['email'];
            $invitedUser->notify(new ReferFriendMail($referral_code));


            DB::commit();
            return new GeneralResponse(['status' => 1, 'data' => $referral_code, 'message' => 'success']);
        } catch (\Exception $e) {
            DB::rollback();
            return new GeneralError(['status' => 0, 'data' => $e, 'message' => $e->getMessage()]);
        }
    }

    public function changePassword(Request $request)
    {
        // $requestData = decryptData(getPassphrase(), $request->all());
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'password' => ['required'],
            'confirm_password' => ['required', 'same:password']
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        $user = User::where('id', $request->user()->id)->first();
        $user->password = bcrypt($requestData['password']);
        $user->save();

        return new GeneralResponse(['status' => 1,  'message' => 'Password updated successfully!']);
    }

    /**
     * Create user
     *
     * @return [string] json data
     */
    public function checkIsLogin(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        if (Hash::check($request->password, $request->user()->password)) {
            return new GeneralResponse(['status' => 1, 'data' => $request->user(), 'message' => 'success']);
        } else {
            return new GeneralError(['status' => 0, 'data' => ['password' => 'Password is incorrect'], 'message' => 'Invalid Data']);
        }
    }

    public function sendSms(Request $request)
    {

        $user = User::where('id', $request->user()->id)->first();

        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_TOKEN');

        $client = new Client($sid, $token);
        $number = "+911234567890";
        $message = "Hello, This is Tesing SMS from test project";
        $client->messages->create(
            $number,
            [
                'from' => env('TWILIO_FROM'),
                'body' => $message,
            ]
        );

        return new GeneralResponse(['status' => 1,  'message' => 'Sms send successfully!']);
    }
}
