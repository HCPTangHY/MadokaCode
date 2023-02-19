<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;

class DroneController extends Controller {
    private int $owner;
    private float $droneDamage,$droneHP,$droneEvasion,$droneSpeed;
    private string $type;

    public function __construct(FleetController $owner,$type) {
        $this->owner = $owner->id;
        $this->type = $type;
        $this->droneDamage = $owner->droneDamage;
        $this->droneHP = $owner->droneHP;
        $this->droneEvasion = $owner->droneEvasion;
        $this->droneSpeed = $owner->droneSpeed;
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
