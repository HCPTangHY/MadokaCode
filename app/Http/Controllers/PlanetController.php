<?php

namespace App\Http\Controllers;

use App\Models\Planet;
use App\Models\Star;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanetController extends Controller
{
    public function planetPage(Request $request){
        $privilege = Auth::user()->privilege;
        $country = Auth::user()->country;
        if($country!="" && $privilege == 2){
            $planets = Planet::where(["owner"=>$country])->get()->toArray();
        }else{
            $planets = Planet::get()->toArray();
        }
        foreach ($planets as $key=>$planet) {
            $planets[$key]['position'] = Star::where(["id"=>$planet['position']])->first()->name;
            $planets[$key]['product'] = json_decode($planet['product'], true);
        }
        return view('planet',["privilege"=>$privilege,
            "planets"=>$planets]);
    }
}
