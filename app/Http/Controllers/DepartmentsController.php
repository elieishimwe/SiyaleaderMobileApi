<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Department;
use App\DepartmentCategory;
use App\DepartmentSubCategory;
use App\DepartmentSubSubCategory;
use App\UserNew;

class DepartmentsController extends Controller
{



    private $category;


    public function __construct(DepartmentCategory $category)
    {

        $this->category = $category;

    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
     public function index()
    {
        $headers  = apache_request_headers();
        $response = array();

        if (isset($headers['api_key'])) {
            $apiKey = UserNew::where('api_key','=',$headers['api_key'])->first();

            if (sizeof($apiKey) > 0) {

                    $categories          = $this->category->get();
                    $subCategories       = array();

                    foreach ($categories as $category) {

                        $subCategories['id']     = $category['id'];
                        $subCategories['name']   = $category['name'];
                        $subCats                 = DepartmentSubCategory::where('category','=',$category['id'])->get();
                        $tmpArrayAll             = [];

                        foreach ($subCats as $subCat) {

                            $tmpArray['cat_id'] = $category['id'];
                            $tmpArray['id']     = $subCat['id'];
                            $tmpArray['name']   = $subCat['name'];
                            $subsubCats         = DepartmentSubSubCategory::where('sub_category','=',$subCat['id'])->get();
                            $tmpArrayAll2       = [];

                            foreach ($subsubCats as $subsubCat) {

                                $tmpA['cat_id'] = $subCat['id'];
                                $tmpA['id']     = $subsubCat['id'];
                                $tmpA['name']   = $subsubCat['name'];
                                $tmpArrayAll2[] = $tmpA;

                            }
                            $tmpArray['subs'] = $tmpArrayAll2;
                            $tmpArrayAll[]    = $tmpArray;
                        }
                        $subCategories['subs'] = $tmpArrayAll;
                        $tmp[]                 = $subCategories;
                    }
                    $response['categories'] = $tmp;
                    $response['error'] = FALSE;
                    return \Response::json($response,201);
            }
            else {
                $response['message'] = 'Access Denied. Invalid Api key';;
                $response['error'] = TRUE;
                return \Response::json($response,401);
        }
    }else {
        $response['message'] = 'Access Denied. Invalid Api key';;
        $response['error'] = TRUE;
        return \Response::json($response,401);
    }
}



}
