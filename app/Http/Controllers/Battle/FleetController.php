<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Fleet;
use App\Models\Ship;
use App\Models\ShipType;

class FleetController extends Controller {
    public int $id;
    public string $name,$owner,$computer;
    public array $ships;
    public float $hull,$fullHull,$PDamage,$EDamage,$MDamage,$pointDefense,$drone,$armor,$shield,$tracking,$evasion,$speed,$disengageChance;
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
    public function countFleet() {
        $f = Fleet::where(["id" => $this->id])->first();
        $ships = json_decode($f->ships,true);
        $hull = $PDamage = $EDamage = $MDamage = $pointDefense = $shield = $armor = 0;
        $drone = $evasion = $speed = $tracking = $commandPoints = $disengageChance = 0;
        foreach ($ships as $key => $value) {
            $shipType = Ship::where(["id" => $value])->first()->shipType;
            $data = ShipType::where(["type" => $shipType])->first()->toArray();
            $commandPoints += $data['commandPoints'];
            $hull += $data['baseHull'];
            $PDamage += $data['PDamage'];
            $EDamage += $data['EDamage'];
            $MDamage += $data['MDamage'];
            $pointDefense += $data['PointDefense'];
            $shield += $data['shield'];
            $armor += $data['armor'];
            $drone += $data['drone'];
            $evasion += $data['evasion']*$data['commandPoints'];
            $speed += $data['speed']*$data['commandPoints'];
            $tracking += $data['tracking']*$data['commandPoints'];
            $disengageChance += $data['disengageChance']*$data['commandPoints'];
        }
        $evasion = $evasion/($commandPoints+1);
        $speed = $speed/($commandPoints+1);
        $disengageChance = $disengageChance/($commandPoints+1);

        $f->owner = $this->owner;
        $f->hull = $hull;
        $f->PDamage = $PDamage;
        $f->EDamage = $EDamage;
        $f->MDamage = $MDamage;
        $f->pointDefense = $pointDefense;
        $f->shield = $shield;
        $f->armor = $armor;
        $f->drone = $drone;
        $f->evasion = $evasion;
        $f->speed = $speed;
        $f->tracking = $tracking;
        $f->disengageChance =$disengageChance;
        $weaponArr = array($f['weaponA'],$f['weaponB']);
        $weapon1 = $weapon2 = 0;
        foreach ($weaponArr as $key => $value) {
            if ($value == 1) {
                $weapon1 += 1;
            }
            else {
                $weapon2 += 1;
            }
        }
        $f->EDamage *= $weapon1;
        $f->armor *= 1+($weapon1*0.1);
        $f->PDamage *= $weapon2;
        $f->shield *= 1+($weapon2*0.1);
        $data = json_decode(Country::where(["tag" => $f->owner])->first()->fleetModifier,true);
        $f->hull *= 1+$data['hullModifier'];
        $f->PDamage *= 1+$data['PDamageModifier'];
        $f->EDamage *= 1+$data['EDamageModifier'];
        $f->MDamage *= 1+$data['MDamageModifier'];
        $f->shield *= 1+$data['shieldModifier'];
        $f->armor *= 1+$data['armorModifier'];
        $f->evasion *= 1+$data['evasionModifier'];
        $f->speed *= 1+$data['speedModifier'];
        $f->tracking *= 1+$data['trackingModifier'];
        $f->disengageChance *=1+$data['DisengageChanceModifier'];

        $f->power = ((0.25*($f->hull+$f->shield+$f->armor)/(1-$f->evasion)*
                    0.25*($f->PDamage+$f->EDamage))^0.25)*0.005;
        $f->save();
    }
    public function createBullet(string $type,int $target,int $creatTick,QueueController $queue): BulletController {
        return new BulletController($this, $type, $target,$creatTick,$queue);
    }
    public function createDrone($type,int $creatTick,QueueController $queue): DroneController {
        return new DroneController($this,$type,$creatTick,$queue);
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
        echo "!撤退".$this->name."撤退!";
        $ships = json_decode($f->ships,true);
        if ($f->hull <= 0 ) {
            $f->delete();
        } else {
            foreach ($ships as $key=>$ship) {
                $ship = Ship::where(["id"=>$ship])->first();
                $hull = ShipType::where(["type"=>$ship->type])->first()->hull;
                if ($this->fullHull - $hull > $f->hull) {
                    array_splice($ships,$key,1);
                    $ship->delete();
                    continue;
                }
                if ($this->fullHull - $hull <= $f->Hull) {
                    break;
                }
            }
        }
        $f->ships = json_encode($ships,JSON_UNESCAPED_UNICODE);
        $f->save();
        $this->countFleet();
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
        $i = 0;
        $enemy = 0;
        while ($i<10000) {
            $enemyAlly = array_rand($fleets);
            $enemyCountry = Fleet::where(["id"=>$fleets[$enemyAlly][0]])->first()->owner;
            $atWar = json_decode(Country::where(["tag"=>$enemyCountry])->first()->atWarWith,true);
            if (in_array($this->owner, $atWar)) {
                $enemy = $fleets[$enemyAlly][array_rand($fleets[$enemyAlly])];
                break;
            } elseif ($enemyAlly == $ally) {
                continue;
            }
            $i++;
        }
        return $enemy;
    }
}

