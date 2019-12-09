<?php

namespace App\Http\Controllers;

use App\Photos;
use App\PhotosData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;


class MarsroverController extends Controller
{
    public function index(Request $request){
        $api_uri = "https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos";
        $query["api_key"] = "DEMO_KEY";

        if($request->filled("start_date") && $request->filled("end_date")){
            $query["earth_date"] = $request->input("start_date")." to ".$request->input("end_date");
        }

        if($request->filled("page")){
            $query["page"] = $request->input("page");
        }

        $query_param = array(
            "query" =>  $query
        );

        $client = new Client();
        $client = $client->request("GET",$api_uri, $query_param);
        $resp = $client->getBody();
        $resp = json_decode($resp);

        $ids = array();
        foreach ($resp->photos as $photo){
            $ids[] = $photo->id;
        }

        $pt = Photos::whereIn("photo_id",$ids)->select(array("photo_id"))->get();
        $ids = array();
        foreach ($pt as $p){
            $ids[] = $p->photo_id;
        }

        $data = array(
            "photos" => $resp,
            "inIds" => $ids,
            "request" => $request
        );

        return View::make("themes.default.index", $data);
    }

    public function importWorker(Request $request){
        $api_uri = "https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos";
        $query["api_key"] = "DEMO_KEY";

        if($request->filled("start_date") && $request->filled("end_date")){
            $query["earth_date"] = $request->input("start_date")." to ".$request->input("end_date");
        }

        if($request->filled("page")){
            $query["page"] = $request->input("page");
        }

        $query_param = array(
            "query" =>  $query
        );

        $client = new Client();
        $client = $client->request("GET",$api_uri, $query_param);
        $resp = $client->getBody();
        $resp = json_decode($resp);

        foreach ($resp->photos as $photo){
            $count = Photos::where("photo_id","=",$photo->id)->get()->count();
            if($count) continue;

            try {
                DB::beginTransaction();

                $transStatus = true;

                $photos = new Photos();
                $photos->photo_id = $photo->id;
                $photos->photo_sol = $photo->sol;
                $photos->rover_id = $photo->camera->rover_id;
                $photos->name = $photo->camera->full_name;
                $photos->earth_date = $photo->earth_date;
                $photos->save();

                //image data to base64 insert db
                $imgClient = new Client();
                $imgData = $imgClient->get($photo->img_src)->getBody();
                $imgData = base64_encode($imgData);

                $photosData = new PhotosData();
                $photosData->photos_id = $photos->id;
                $photosData->img_data = $imgData;
                $photosData->save();
            }catch (\PDOException $exception){
                $transStatus = false;
            }catch (ClientException $exception){
                $transStatus = false;
            }

            if($transStatus){
                DB::commit();
            }else{
                DB::rollBack();
            }
        }
        $resp_data = array(
            "mess" => "import completed"
        );
        return response()->json($resp_data)->header('Content-Type', 'application/json');
    }

    public function destroyWorker(Request $request){
        if($request->filled("start_date") && $request->filled("end_date")){
            //Subquery with Deleted
            PhotosData::whereIn("photos_id", function ($query) use ($request){
                $query->whereBetween("earth_date",array($request->input("start_date"), $request->input("end_date")))
                    ->select(array("id"))
                    ->from("photos");
            })->delete();
            Photos::whereBetween("earth_date",array($request->input("start_date"), $request->input("end_date")))->delete();
            $resp_data["mess"] = "deletion complete";
        }else{
            $resp_data["mess"] = "failed to delete data";
        }
        return response()->json($resp_data)->header('Content-Type', 'application/json');
    }

}
