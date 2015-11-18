<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Report;
use App\DepartmentCategory;
use App\Department;
use App\DepartmentSubCategory;
use App\DepartmentSubSubCategory;
use App\User;
use App\UserNew;
use App\CaseReport;
use App\CaseResponder;
use App\CaseOwner;

class ReportController extends Controller
{


    private $report;


    public function __construct(Report $report)
    {

        $this->report = $report;

    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $reports  = $this->report->get();

        return $reports;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */

// Function for resizing jpg, gif, or png image files
public function ak_img_resize($target, $newcopy, $w, $h, $ext) { list($w_orig, $h_orig) = getimagesize($target); $scale_ratio = $w_orig / $h_orig; if (($w / $h) > $scale_ratio) { $w = $h * $scale_ratio; } else { $h = $w / $scale_ratio; } $img = ""; $ext = strtolower($ext); if ($ext == "gif"){ $img = imagecreatefromgif($target); } else if($ext =="png"){ $img = imagecreatefrompng($target); } else { $img = imagecreatefromjpeg($target); } $tci = imagecreatetruecolor($w, $h); // imagecopyresampled(dst_img, src_img, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)
imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig); imagejpeg($tci, $newcopy, 80); }





    public function store(Report $report, Request $request)
    {


         \Log::info("Request ".$request);
         $category         = \Input::get('category');
         \Log::info('GET Category ' .$category);
         $sub_category     = \Input::get('sub_category');
         \Log::info('GET Sub Category ' .$sub_category);
         $sub_sub_category = \Input::get('sub_sub_category');
         \Log::info('GET Sub Sub Category ' .$sub_sub_category);
         $sub_sub_category = (empty($sub_sub_category))? " " : $sub_sub_category;
         $description      = \Input::get('description');
         \Log::info('Get Description :'.$description);
         $description      = (empty($description))? " " : $description;
         $gps_lat          = \Input::get('gps_lat');
         \Log::info('GPS Lat :' .$gps_lat);
         $gps_lng          = \Input::get('gps_lng');
         \Log::info('GPS Lng :' .$gps_lng);
         $user_email       = \Input::get('user_email');
         \Log::info('Email :' .$user_email);
         $priority         = \Input::get('priorities');
         $priority         = (empty($priority))? "Normal" : $priority;
         \Log::info('Priority :' .$priority);
         $headers          = apache_request_headers();
         $response         = array();

        \Log::info("Request ".$request);
        if (count($_FILES) > 0) {

            $files = $_FILES['img'];
            $name  = uniqid('img-'.date('Ymd').'-');
            $temp  = explode(".",$files['name']);
            $name  = $name . '.'.end($temp);


            if (file_exists("uploads/".$name))
            {
                echo $_FILES["img"]["name"]."already exists. ";
            }
            else
            {

                $img_url      = "uploads/".$name;
                $target_file  = "uploads/$name";
                $resized_file = "uploads/$name";
                $wmax         = 600;
                $hmax         = 480;
                $fileExt      = 'jpg';

                if(move_uploaded_file($_FILES["img"]["tmp_name"],$img_url))
                {

                     $this->ak_img_resize($target_file, $resized_file, $wmax, $hmax, $fileExt);

                }

            }
        }


       $img_url = isset($img_url)? $img_url : "uploads/noimage.png";


        if (isset($headers['api_key'])) {


             $userNew = UserNew::where('api_key','=',$headers['api_key_new'])->first();

             if(sizeof($userNew) > 0) {


                 $objCat                           = DepartmentCategory::where('name','=',$category)->first();
                 \Log::info('Category Object :'.$objCat);
                 $department                       = Department::where('id','=',$objCat->department)->first();
                 \Log::info('Department Object : '.$department);

                 $objSubCat                        = DepartmentSubCategory::where('name','=',$sub_category)->first();
                 $SubCatName                       = (sizeof($objSubCat) > 0)? $objSubCat->name : "";
                 if(strlen($sub_sub_category) > 1) {
                     $objSubSubCat = DepartmentSubSubCategory::where('name','=',$sub_sub_category)->first();
                     $objSubSub    = $objSubSubCat->id;

                 }
                 else {

                     $objSubSubCat = 0;
                     $objSubSub = 0;
                 }


                 $case                   = New CaseReport();
                 $case->description      = $description;
                 $case->user             = $userNew->id;
                 $case->reporter         = $userNew->id;
                 $case->department       = $department->id;
                 $case->category         = $objCat->id;
                 $case->sub_category     = $objSubCat->id;
                 $case->sub_sub_category = $objSubSub;
                 $case->priority         = $priority;
                 $case->status           = 'Pending';
                 $case->gps_lat          = $gps_lat;
                 $case->precinct         = 5;
                 $case->img_url          = $img_url;
                 $case->gps_lng          = $gps_lng;
                 $case->save();

                 $caseOwner              = new CaseOwner();
                 $caseOwner->user        = $userNew->id;
                 $caseOwner->caseId      = $case->id;
                 $caseOwner->type        = 0;
                 $caseOwner->active      = 1;
                 $caseOwner->save();

                 $response["message"]          = "Report created successfully";
                 $response['error']            = FALSE;

                $data = array(
                    'name'   =>$userNew->name,
                    'caseID' =>$case->id,
                    'caseDesc' => $case->description
                );

                \Mail::send('emails.sms',$data, function($message) use ($userNew) {
                    $message->from('info@siyaleader.net', 'Siyaleader');
                    $message->to($userNew->username)->subject("Siyaleader Notification - New Case Reported:");

                });


                if (is_object($objSubSubCat)) {

                    $firstRespondersObj  = CaseResponder::where("sub_sub_category",'=',$objSubSubCat->id)
                                                ->select('firstResponder')->first();

                    /*$secondRespondersObj = CaseResponder::where("sub_sub_category",'=',$objSubSubCat->id)
                                                ->select('secondResponder')->first();

                    $thirdRespondersObj  = CaseResponder::where("sub_sub_category",'=',$objSubSubCat->id)
                                                ->select('thirdResponder')->first();
                    */

                    if (sizeof($firstRespondersObj) > 0) {


                        $case->status      = "Referred";
                        $case->referred_at = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();
                        $case->save();

                        $firstResponders  = explode(",",$firstRespondersObj->firstResponder);

                        if($firstRespondersObj->firstResponder > 0) {

                            foreach ($firstResponders as $firstResponder) {


                                $firstResponderUser = UserNew::find($firstResponder);
                                $caseOwner          = new CaseOwner();
                                $caseOwner->user    = $firstResponder ;
                                $caseOwner->caseId  = $case->id;
                                $caseOwner->type    = 1;
                                $caseOwner->active  = 1;
                                $caseOwner->save();

                                 $data = array(
                                        'name'   =>$firstResponderUser->name,
                                        'caseID' =>$case->id,
                                        'caseDesc' => $case->description,
                                        'caseReporter' => $case->description,
                                    );

                                \Mail::send('emails.responder',$data, function($message) use ($firstResponderUser) {

                                    $message->from('info@siyaleader.net', 'Siyaleader');
                                    $message->to($firstResponderUser->username)->subject("Siyaleader Notification - New Case Reported:");

                                });

                                $cellphone = $firstResponderUser->email;

                                \Mail::send('emails.caseEscalatedSMS',$data, function($message) use ($cellphone)
                                {
                                    $message->from('info@siyaleader.net', 'Siyaleader');
                                    $message->to('cooluma@siyaleader.net')->subject("REFER: $cellphone" );

                                });



                            }



                        }
                    }


               /*     if (sizeof($secondRespondersObj) > 0) {

                        $secondResponders  = explode(",",$secondRespondersObj->secondResponder);

                        if($secondRespondersObj->secondResponder > 0) {

                            foreach ($secondResponders as $secondResponder) {


                                $secondResponderUser = UserNew::find($secondResponder);
                                $caseOwner          = new CaseOwner();
                                $caseOwner->user    = $secondResponder ;
                                $caseOwner->caseId  = $case->id;
                                $caseOwner->type    = 2;
                                $caseOwner->active  = 1;
                                $caseOwner->save();

                                 $data = array(
                                        'name'   =>$secondResponderUser->name,
                                        'caseID' =>$case->id,
                                        'caseDesc' => $case->description,
                                        'caseReporter' => $case->description,
                                    );

                                \Mail::send('emails.responder',$data, function($message) use ($secondResponderUser)
                                {
                                    $message->from('info@siyaleader.co.za', 'Siyaleader');
                                    $message->to($secondResponderUser->username)->subject("Siyaleader Notification - New Case Reported:");

                                });

                            }



                        }
                    }
*/
            /*        if (sizeof($thirdRespondersObj) > 0) {

                        $thirdResponders  = explode(",",$thirdRespondersObj->thirdResponder);

                        if($thirdRespondersObj->thirdResponder > 0) {

                            foreach ($thirdResponders as $thirdResponder) {


                                $thirdResponderUser = UserNew::find($thirdResponder);
                                $caseOwner          = new CaseOwner();
                                $caseOwner->user    = $thirdResponder ;
                                $caseOwner->caseId  = $case->id;
                                $caseOwner->type    = 3;
                                $caseOwner->active  = 1;
                                $caseOwner->save();

                                 $data = array(
                                        'name'   =>$thirdResponderUser->name,
                                        'caseID' =>$case->id,
                                        'caseDesc' => $case->description,
                                        'caseReporter' => $case->description,
                                    );

                                \Mail::send('emails.responder',$data, function($message) use ($thirdResponderUser)
                                {
                                    $message->from('info@siyaleader.co.za', 'Siyaleader');
                                    $message->to($thirdResponderUser->username)->subject("Siyaleader Notification - New Case Reported:");

                                });

                            }



                        }
                    }*/

                }




                if (sizeof($objSubCat) > 0 && $objSubSubCat == "") {


                    $firstRespondersObj  = CaseResponder::where("sub_category",'=',$objSubCat->id)
                                                ->select('firstResponder')->first();

                  /*  $secondRespondersObj = CaseResponder::where("sub_category",'=',$objSubCat->id)
                                                ->select('secondResponder')->first();

                    $thirdRespondersObj  = CaseResponder::where("sub_category",'=',$objSubCat->id)
                                                ->select('thirdResponder')->first();

                */
                    if (sizeof($firstRespondersObj) > 0) {

                        $case->status = "Referred";
                        $case->referred_at = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();
                        $case->save();

                        $firstResponders  = explode(",",$firstRespondersObj->firstResponder);

                        if($firstRespondersObj->firstResponder > 0) {

                            foreach ($firstResponders as $firstResponder) {


                                $firstResponderUser = UserNew::find($firstResponder);
                                $caseOwner          = new CaseOwner();
                                $caseOwner->user    = $firstResponder ;
                                $caseOwner->caseId  = $case->id;
                                $caseOwner->type    = 1;
                                $caseOwner->active  = 1;
                                $caseOwner->save();

                                 $data = array(
                                        'name'   =>$firstResponderUser->name,
                                        'caseID' =>$case->id,
                                        'caseDesc' => $case->description,
                                        'caseReporter' => $case->description,
                                    );

                                \Mail::send('emails.responder',$data, function($message) use ($firstResponderUser) {
                                    $message->from('info@siyaleader.net', 'Siyaleader');
                                    $message->to($firstResponderUser->username)->subject("Siyaleader Notification - New Case Reported:");

                                });

                                $cellphone = $firstResponderUser->cellphone;

                               \Mail::send('emails.caseEscalatedSMS',$data, function($message) use ($cellphone)
                                {
                                    $message->from('info@siyaleader.net', 'Siyaleader');
                                    $message->to('cooluma@siyaleader.net')->subject("REFER: $cellphone" );

                                });

                            }



                        }
                    }


               /*     if (sizeof($secondRespondersObj) > 0) {

                        $secondResponders  = explode(",",$secondRespondersObj->secondResponder);

                        if($secondRespondersObj->secondResponder > 0) {

                            foreach ($secondResponders as $secondResponder) {


                                $secondResponderUser = UserNew::find($secondResponder);
                                $caseOwner          = new CaseOwner();
                                $caseOwner->user    = $secondResponder ;
                                $caseOwner->caseId  = $case->id;
                                $caseOwner->type    = 2;
                                $caseOwner->active  = 1;
                                $caseOwner->save();

                                 $data = array(
                                        'name'   =>$secondResponderUser->name,
                                        'caseID' =>$case->id,
                                        'caseDesc' => $case->description,
                                        'caseReporter' => $case->description,
                                    );

                                \Mail::send('emails.responder',$data, function($message) use ($secondResponderUser)
                                {
                                    $message->from('info@siyaleader.co.za', 'Siyaleader');
                                    $message->to($secondResponderUser->username)->subject("Siyaleader Notification - New Case Reported:");

                                });

                            }



                        }
                    }*/

                    /*if (sizeof($thirdRespondersObj) > 0) {

                        $thirdResponders  = explode(",",$thirdRespondersObj->thirdResponder);

                        if($thirdRespondersObj->thirdResponder > 0) {

                            foreach ($thirdResponders as $thirdResponder) {


                                $thirdResponderUser = UserNew::find($thirdResponder);
                                $caseOwner          = new CaseOwner();
                                $caseOwner->user    = $thirdResponder ;
                                $caseOwner->caseId  = $case->id;
                                $caseOwner->type    = 3;
                                $caseOwner->active  = 1;
                                $caseOwner->save();

                                 $data = array(
                                        'name'   =>$thirdResponderUser->name,
                                        'caseID' =>$case->id,
                                        'caseDesc' => $case->description,
                                        'caseReporter' => $case->description,
                                    );

                                \Mail::send('emails.responder',$data, function($message) use ($thirdResponderUser)
                                {
                                    $message->from('info@siyaleader.co.za', 'Siyaleader');
                                    $message->to($thirdResponderUser->username)->subject("Siyaleader Notification - New Case Reported:");

                                });

                            }



                        }
                    }*/


                }

                return \Response::json($response,201);
             }

             else
             {

                $response['message'] = 'Access Denied. Invalid Api key';;
                $response['error']   = TRUE;
                return \Response::json($response,401);

             }

        }
        else
        {
             $response['message'] = 'Access Denied. Invalid Api key';;
             $response['error']   = TRUE;
             return \Response::json($response,401);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function myReport(Report $report)
    {
         $headers  = apache_request_headers();
         $response = array();

        if (isset($headers['api_key'])) {

             $user  = UserNew::where('api_key','=',$headers['api_key_new'])->first();

             if(sizeof($user) > 0)
             {


                 $myReports = \DB::table('cases')
                ->leftjoin('departments', 'cases.department', '=', 'departments.id')
                ->join('categories', 'cases.category', '=', 'categories.id')
                ->join('sub-categories', 'cases.sub_category', '=', 'sub-categories.id')
                ->leftjoin('sub-sub-categories', 'cases.sub_sub_category', '=', 'sub-sub-categories.id')
                ->join('users', 'cases.user', '=', 'users.id')
                ->where('cases.user','=',$user->id)
                ->select(\DB::raw("cases.id, cases.created_at,cases.status,cases.description,cases.priority,cases.img_url,cases.gps_lat,cases.gps_lng,categories.name as category,`sub-categories`.name as sub_category,`sub-sub-categories`.name as sub_sub_category"))
                ->get();

                $response["error"]   = FALSE;
                $response["reports"] = $myReports;
                return \Response::json($response,201);
             }
             else {

                $response['message'] = 'Access Denied. Invalid Api key';;
                $response['error']   = TRUE;
                return \Response::json($response,401);
             }
        }
        else
        {
            $response['message'] = 'Access Denied. Invalid Api key';;
            $response['error']   = TRUE;
            return \Response::json($response,401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function saveReportImage()
    {
         $category         = \Input::get('category');
         $sub_category     = \Input::get('sub_category');
         $sub_sub_category = \Input::get('sub_sub_category');
         $description      = \Input::get('description');
         $gps_lat          = \Input::get('gps_lat');
         $gps_lng          = \Input::get('gps_lng');
         $user_email       = \Input::get('user_email');
         $headers          = apache_request_headers();
         $response         = array();
         $files            = $_FILES['img'];
         $name             = uniqid('img-'.date('Ymd').'-');
         $temp             = explode(".",$files["name"]);
         $name             = $name . '.' .end($temp);


        if (file_exists("uploads/" . $name)) {
          echo $_FILES["img"]["name"] . " already exists. ";
        } else {
          move_uploaded_file($_FILES["img"]["tmp_name"],
          "uploads/" . $name);
          echo "Stored in: " . "uploads/" . $name;
          $img_url = "uploads/" . $name;
        }


         if (isset($headers['api_key'])) {

             $$user  = User::where('api_key','=',$headers['api_key'])->first();

             if(sizeof($user) > 0) {

                 $newReport = New Report();
                 $newReport->prob_category    = $category;
                 $newReport->prob_dis         = 'Durban';
                 $newReport->prob_mun         = 'Maydon Wharf';
                 $newReport->Province         = 'KZN';
                 $newReport->status           = 'Pending';
                 $newReport->prob_exp         = $description;
                 $newReport->img_url          = $img_url;
                 $newReport->ccg_nam          = $user->Fname;
                 $newReport->ccg_sur          = $user->Sname;
                 $newReport->ccg_pos          = $user->Position;
                 $newReport->prob_subcategory = $sub_sub_category['name'];
                 $newReport->GPS              = $gps_lat .', '.$gps_lng;
                 $newReport->gps_lat          = $gps_lat;
                 $newReport->gps_lng          = $gps_lng;
                 $newReport->submit_date      =  \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();
                 $newReport->user             = $user->ID;
                 $newReport->source           = 'M';
                 $newReport->save();
                 $response["message"]         = "Report created successfully";
                 $response['error']           = FALSE;
                 return \Response::json($response,201);
             }

             else
             {

                $response['message'] = 'Access Denied. Invalid Api key';;
                $response['error']   = TRUE;
                return \Response::json($response,401);

             }

        }
        else
        {
             $response['message'] = 'Access Denied. Invalid Api key';;
             $response['error']   = TRUE;
             return \Response::json($response,401);
        }
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
