<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Battle\FleetController;
use App\Http\Controllers\Battle\SpaceBattleController;
use App\Models\Fleet;
use App\Models\Star;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MilitaryController extends Controller {
    public function militaryPage(Request $request){
        $privilege = Auth::user()->privilege;
        $country = Auth::user()->country;
        if($country!="" && $privilege == 2){
            $fleets = Fleet::where(["owner"=>$country])->get()->toArray();
        }else{
            $fleets = Fleet::get()->toArray();
        }
        foreach ($fleets as $key=>$fleet) {
            $fleets[$key]['position'] = Star::where(["id"=>$fleet['position']])->first()->name;
        }
        return view('military',["privilege"=>$privilege,"country"=>$country,
            "fleets"=>$fleets]);
    }
}
$s = new SpaceBattleController([38,39]);
$s->spaceBattle();
