<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\UserNew;

class UserController extends Controller
{

    private $user;


    public function __construct(User $user)
    {

        $this->user = $user;

    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(User $user, Request $request)
    {

        $response   = array();
        $cell       = \Input::get('cell');
        $password   = \Input::get('password');
        $email      = \Input::get('email');
        $firstName  = \Input::get('firstName');
        $name       = \Input::get('name');
        $ID         = \Input::get('ID');
        $result     = User::where('Cell1','=',$cell)->first();


        if (sizeof($result) > 0)
        {
            $response["error"]   = TRUE;
            $response["message"] = "Sorry, this user already exists";
        }
        else {

            $newUser             = new User();
            $newUser->Fname      = $firstname;
            $newUser->Sname      = $name;
            $newUser->Email      = $email;
            $newUser->Password   = $password;
            $newUser->IDnumber   = $ID;
            $newUser->Cell1      = $cell;
            $newUser->api_user   = 1;
            $newUser->star_user  = 1;
            $newUser->Status     = 'Active';
            $newUser->api_key    = uniqid();
            $newUser->save();
            $response["error"]   = FALSE;
            $response["message"] = "You are successfully registered";

        }

         return \Response::json($response);

    }

    public function login(User $user, Request $request)
    {


         $response = array();
         $cell     = \Input::get('cell');
         $password = \Input::get('password');
         $data     = UserNew::where('cellphone','=',$cell)->first();
         $device   = \Request::header('User-Agent');


         if (sizeof($data) > 0 ) {

            $key = $data->api_key;
         }
         else {

            $key = "no key";
         }


         if (sizeof($data) > 0 )
         {
            $response["error"]     = false;
            $response['name']      = $data->name;
            $response['cell_no']   = $data->cellphone;
            $response['apiKey']    = $data->api_key;
            $response['api_key']   = $key;
            $response['createdAt'] = $data->created_at;

            \Log::info("Login Device:".$device.", User Cell:".$cell.", User Names:".$data->name);

         }
         else {

            $response['error']   = true;
            $response['message'] = 'Login failed. Incorrect credentials';

         }

         return \Response::json($response);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function forgot(User $user)
    {

         $response = array();
         $cell     = \Input::get('cell');
         $password = \Input::get('password');
         \Log::info('Password Change: User '.$cell ."New Password".$password );
         $userNew  = UserNew::where('cellphone','=',$cell)->first();


         if (sizeof($userNew) > 0)
         {

            $userNew->password   = \Hash::make($password);
            $userNew->save();
            $response["error"]   = false;
            $response["message"] = "You have successfully changed your password";

         }
         else {
              $response["error"]   = true;
              $response["message"] = "Sorry, you have not registered yet";
         }

          return \Response::json($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
