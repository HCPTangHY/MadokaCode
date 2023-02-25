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
    public function save() {
        if ($this->id != -1) {
            $f = Fleet::where(['id'=>$this->id])->first();
            $f->ships = json_encode($this->ships,JSON_UNESCAPED_UNICODE);
            foreach ($this as $key => $value) {
                if ($key != 'ships' && $key != 'middleware' && $key != 'fullHull' && $key != 'position') {
                    $f->$key = $value;
                }
            }
            $f->save();
        }
    }
    public function createBullet(string $type,int $target,int $creatTick,QueueController $queue): BulletController {
        return new BulletController($this, $type, $target,$creatTick,$queue);
    }
    public function createDrone($type): DroneController {
        return new DroneController($this,$type);
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
        $f = Fleet::where(["id" =>$this->id])->first();
        $cap = Country::where(["tag" => $f->owner])->first()->capital;
        $f->position = intval($cap);
        $f->save();
        echo "!撤退".$this->name."撤退!";
    }
    public function countComputerModifier() {

    }
    public function chooseEnemy(array $fleets): int {
        foreach ($fleets as $ally=>$fleetsInAlly) {
            foreach ($fleetsInAlly as $fleetKey=>$fleet) {
                if ($fleet == $this->id) {
                    break;
                }
            }
        }
        while (true) {
            $enemyAlly = array_rand($fleets);
            $enemyCountry = Fleet::where(["id"=>$fleets[$enemyAlly][0]])->first()->owner;
            $atWar = json_decode(Country::where(["tag"=>$enemyCountry])->first()->atWarWith,true);
            if (in_array($this->owner, $atWar)) {
                $enemy = $fleets[$enemyAlly][array_rand($fleets[$enemyAlly])];
                break;
            } elseif ($enemyAlly == $ally) {
                continue;
            }
        }
        return $enemy;
    }
}

