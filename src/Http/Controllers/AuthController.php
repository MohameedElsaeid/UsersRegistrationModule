<?php

namespace App\Http\Controllers;

use App\Device;
use App\ForgotPasswordCode;
use App\Mail\ResetPassword;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function signUp(Request $request,User $user)
    {
        validation($request->all(),$user->createValidation());
        $createUser = $this->createNewUser($request->all());
        $token      = $user->makeApiToken();
        
        $data['user']   = $createUser;
        $data['token']  = $token;
        myResponse(1,$data);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function creatse()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    
    //----------------------START LOG OUT
    public function logout(Request $request , Device $devices)
    {
        //**************START VALIDATION**************//
        $validation = $this->validation($request->all(), [
            'device_id' => 'required|string'
        ]);
        if ($validation != "true") {return $validation;}
        //**************END VALIDATION****************//
        
        $devices = $devices->newQuery();
        
        $query = $devices->where('device_id',$request->device_id)->first();
        
        
        if (!$query) {
            $response['isSuccess'] = false;
            $response['message'] = "Device id is not found";
            return response()->json($response, 200);
        }
        
        
        $query->online = 0;
        $query->save();
        
        $request->user()->token()->revoke();
        
        $response['isSuccess'] = true;
        $response['message'] = "Successfully logged out";
        return response()->json($response,200,[], JSON_NUMERIC_CHECK);
        
    }
    //----------------------END LOG OUT
    
    protected function createNewUser(array $data)
    {
        return User::create($data);
    }
    
    
    //-----------------START GENERATE NEW CODE---------------------//
    public function newCodeGenerate(Request $request)
    {
        
        //******************VALIDATION******************//
        $validation = $this->validation($request->all(),[
            'email'  => 'required|string|email',
        ]);
        if ($validation != "true"){return $validation;}
        //******************VALIDATION******************//
        
        $user = User::where([['email',$request->email]])->first();
        
        
        if (!$user){
            
            $response['isSuccess'] = false;
            $response['message']   = "Email Not Found";
            
            return response()->json($response,200);
        }
        
        
        $code  =  mt_rand(1000,9999);
        
        try{
            
            $newCodeInsert = new ForgotPasswordCode;
            $newCodeInsert->id   = $user->id;
            $newCodeInsert->code = $code;
            $newCodeInsert->save();
            
        } catch (\Illuminate\Database\QueryException $e) {
            
            $delCode = ForgotPasswordCode::find($user->id);
            $delCode->delete();
            
            
            $response['isSuccess'] = true;
            $response['message']   = "Mail has sent     Click Re send to resend mail again";
            return response()->json($response,200);
        } catch (PDOException $e) {
            
            $response['isSuccess'] = false;
            $response['message']   = "You already requested code if you not received mail wait 20 min and re request again";
            
            return response()->json($response,200);
        }
        
        Mail::send(new ResetPassword($user));
        
        $response['isSuccess'] = true;
        $response['message']   = "Mail Has Sent";
        
        return response()->json($response,200);
        
    }
    //-----------------START GENERATE NEW CODE---------------------//
    
    
    //-----------------START CHECKING CODE ---------------------//
    public function CheckCode(Request $request)
    {
        //******************VALIDATION******************//
        $validation = $this->validation($request->all(),[
            'code'      => 'required|numeric',
        ]);
        if ($validation != "true"){return $validation;}
        //******************VALIDATION******************//
        
        $user = ForgotPasswordCode::where('code',$request->code)->first();
        
        
        if (!$user){
            
            $response['isSuccess'] = false;
            $response['message']   = "Code not correct";
            return response()->json($response,200);
            
        }else{
            $response['isSuccess'] = true;
            $response['message']   = "Code is correct";
            return response()->json($response,200);
        }
        
        
    }
    //-----------------START CHECKING CODE ---------------------//
    
    //-----------------START CHANGE PASSWORD WITH CODE ---------------------//
    public function ChangePasswordWithCode(Request $request)
    {
        //******************VALIDATION******************//
        $validation = $this->validation($request->all(),[
            'code'           => 'required|numeric',
            'password'       => 'required|string|regex:/^\S*$/u',
            'device_id'      => 'required|string|max:191',
            'device_model'   => 'required|string|max:191',
            'firebase_token' => 'nullable|string|max:191',
        ]);
        if ($validation != "true"){return $validation;}
        //******************VALIDATION******************//
        
        $checkUser = DB::table('users')
            ->leftJoin('password_reset_codes','users.id','=','password_reset_codes.id')
            ->where('password_reset_codes.code','=',$request->code)
            ->first();

        
        
        if (!$checkUser){
            
            $response['isSuccess'] = false;
            $response['message']   = "Code not correct";
            return response()->json($response,200);
        }
    
        $userId = $checkUser->id;
        
        $user = User::where([['id',$userId]]);
//    dd($user);
        if (!$user->first()){
            
            $response['isSuccess'] = false;
            $response['message']   = "Mobile number not registered";
            
            return response()->json($response,200);
        }
        
        $update = $user->update(['password' => Hash::make($request->password)]);
        $user   = $user->first();
        
        //GENERATE TOKEN
        $token = null;
        if (!$token = Auth::guard('api')->fromUser($user))
        {
            $response['status']  = 1;
            $response['message'] = 'Invalid mobile or password';
            return response()->json($response,401);
        }
        
        
        //UPDATE OR CREATE DEVICE
        if ($token){
            $device = Device::updateOrCreate([
                'device_id' => $request->device_id
            ],[
                'user_id'        => $user->id,
                'device_id'      => $request->device_id,
                'device_model'   => $request->device_model,
                'version'        => $request->header('version'),
                'platform'       => $request->header('platform'),
                'firebase_token' => $request->firebase_token,
                'online'         => 1,
            ]);
        }
        
        
        if($update){
            $response['isSuccess']  = true;
            $response['message']    = "password Updated";
            $response['user']       = $user;
            $response['token']      = $token;
        }else{
            $response['isSuccess']  = false;
            $response['message']    = "password not Updated";
        }
        
        
        
        $delCode = ForgotPasswordCode::find($user->id);
        $delCode->delete();
        
        
        
        
        
        return response()->json($response,200);
        
        
    }
    //-----------------START CHANGE PASSWORD WITH CODE ---------------------//
}
