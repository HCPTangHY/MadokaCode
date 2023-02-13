<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Star;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller
{
    public function mapPage(Request $request){
        if (!is_null(Auth::user()->privilege)) {
            return view("mappage");
        }
//        else {
//            return redirect("https://madoka.leftsunion.org");
//        }
    }
    public function getData(Request $request) {
        $privilege = Auth::user()->privilege;
        $country = Auth::user()->country;
        if (!is_null($privilege)) {
            $stars = Star::get()->toArray();
            foreach ($stars as $key => $value) {
                $stars[$key]['resource'] = json_decode($value['resource'], true);
            }
            $countries = Country::get()->toArray();
//            $stations = Station::get()->toArray();
//            $planets = Planet::get()->toArray();
//            $planetTypes = PlanetType::get()->toArray();
//            if ($privilege <= 1) {
//                $fleets = Fleet::get()->toArray();
//                $armys = Army::get()->toArray();
//            } elseif ($privilege >=2) {
//                $fleets = Fleet::where(["owner"=>$country])->get()->toArray();
//                $armys = Army::where(["owner"=>$country])->get()->toArray();
//            }
            return view("map",["stars"=>$stars,"countrys"=>$countries,
//                "stations"=>$stations,"planets"=>$planets,
//                "planetTypes"=>$planetTypes,
//                "fleets"=>$fleets,
//                "armys"=>$armys,
                "privilege"=>$privilege]);
        }else {
//            return redirect("https://kanade.nbmun.cn");
        }
    }
}
