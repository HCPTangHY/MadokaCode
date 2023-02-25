<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Fleet;

class DroneController extends Controller {
    private int $owner;
    private float $droneDamage,$droneHP,$droneEvasion,$droneSpeed;
    public string $type;
    public int $creatTick;

    public function __construct(FleetController $owner,string $type, int $creatTick) {
        $this->owner = $owner->id;
        $this->type = $type;
        $this->droneDamage = $owner->droneDamage;
        $this->droneHP = $owner->droneHP;
        $this->droneEvasion = $owner->droneEvasion;
        $this->droneSpeed = $owner->droneSpeed;
        $this->creatTick = $creatTick;
    }
    public function chooseEnemy(array $fleets): int {
        foreach ($fleets as $ally=>$fleetsInAlly) {
            foreach ($fleetsInAlly as $fleetKey=>$fleet) {
                if ($fleet == $this->owner) {
                    break;
                }
            }
        }
        while (true) {
            $enemyAlly = array_rand($fleets);
            $enemyCountry = Fleet::where(["id"=>$fleets[$enemyAlly][0]])->first()->owner;
            $ownerCountry = Fleet::where(["id"=>$this->owner])->first()->owner;
            $atWar = json_decode(Country::where(["tag"=>$enemyCountry])->first()->atWarWith,true);
            if (in_array($ownerCountry, $atWar)) {
                $enemy = $fleets[$enemyAlly][array_rand($fleets[$enemyAlly])];
                break;
            } elseif ($enemyAlly == $ally) {
                continue;
            }
        }
        return $enemy;
    }
    public function createBullet(string $type,int $target,int $creatTick,QueueController $queue): BulletController {
        return new BulletController($this, $type, $target,$creatTick,$queue);
    }
    public function hitFleet(FleetController $enemy) {
        $damageHitChance = 1-$enemy->evasion;
        if ($enemy->armor > 0) {
            $enemy->armor -= ($this->droneDamage*1.5)*$damageHitChance;
            echo $enemy->name, '|', $enemy->shield, '|', $enemy->armor, '|', $enemy->hull, "<br>";
        } else {
            if ($enemy->hull > 0) {
                if ($enemy->hull <= 0.5 * $enemy->fullHull) {
                    $damage = ($this->droneDamage) * $damageHitChance;
                    if ($enemy->tryToDisengage($damage)) {
                        $enemy->disengage();
                    } else {
                        $enemy->hull -= $damage;
                        echo $enemy->name, '|', $enemy->shield, '|', $enemy->armor, '|', $enemy->hull, "<br>";
                    }
                }
            } else {
                $enemy->disengage();
            }
        }
    }
    public function hitDrone(DroneController $enemy) {
        if (random_int(0,100)>$enemy->droneEvasion) {
            $enemy->droneHP-=$this->droneDamage;
            if ($enemy->droneHP <= 0) {
                return false;
            } else {
                return true;
            }
        }
    }
}
