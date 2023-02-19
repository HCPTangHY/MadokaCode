<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Fleet;

class FleetController extends Controller {
    public int $id;
    public string $name,$owner,$computer;
    public array $ships;
    public float $hull,$fullHull,$PDamage,$EDamage,$MDamage,$drone,$armor,$shield,$tracking,$evasion,$speed,$disengageChance;
    public float $droneHP,$droneDamage,$droneSpeed,$droneEvasion;

    public function __construct($id=-1) {
        if ($id != -1) {
            $f = Fleet::where(['id' => $id])->first()->toArray();
            $this->ships = json_decode($f['ships'],true);
            foreach ($f as $key => $value) {
                if ($key != 'ships') {
                    $this->$key = $value;
                }
            }
            $this->fullHull = $this->hull;
//            $this->hull = $f->hull;
//            $this->PDamage = $f->PDamage;
//            $this->EDamage = $f->EDamage;
//            $this->MDamage = $f->mission;
//            $this->drone = $f->drone;
//            $this->armor = $f->armor;
//            $this->shield = $f->shield;
//            $this->tracking = $f->tracking;
//            $this->evasion = $f->evasion;
//            $this->speed = $f->speed;
//            $this->disengageChance = $f->disengageChance;
        }
    }
    public function createBullet($type): BulletController {
        return new BulletController($this, $type);
    }
    public function createDrone(): DroneController {
        return new DroneController($this);
    }
    public function tryToDisengage($damage) {
        $disengage = ($damage / $this->hull * 1.5 * $this->disengageChance);
        echo '~', $disengage, '~';
        if ($disengage > 1) {
            return true;
        } else {
            $random = random_int(1, 100);
            if ($random <= 100 * $disengage) {
                return true;
            }
        }
    }
    public function disengage() {
        $owner = Fleet::where(["id"=>$this->id])->first()->owner;
        $cap = Country::where(["tag" => $owner])->first()->capital;
        Fleet::where(["id" =>$this->id])->update(["position" => $cap,"hull"=>$this->hull]);
        echo "!撤退".$this->name."撤退!";
    }
}

