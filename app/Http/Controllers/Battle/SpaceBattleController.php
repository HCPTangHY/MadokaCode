<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Fleet;

class SpaceBattleController extends Controller {
    private array $fleets;

    public function __construct(Array $fleets) {
        $this->fleets = $fleets;
        $this->divideAlly();
    }
    private function divideAlly() {
        for ($i = 0; $i < count($this->fleets); $i++) {
            $this->fleets[$i] = [$this->fleets[$i],];
            for ($j = $i + 1; $j < count($this->fleets); $j++) {
                $id1 = $this->fleets[$i][0];
                $owner1 = Fleet::where(["id" => $id1])->first()->owner;
                $id2 = $this->fleets[$j];
                $owner2 = Fleet::where(["id" => $id2])->first()->owner;
                $ally = json_decode(Country::where(["tag" => $owner1])->first()->alliedWith,true);
                if (in_array($owner2, $ally)) {
                    array_push($fleets[$i], $fleets[$j]);
                    array_splice($fleets,$j,1);
                }
            }
        }
    }

}
