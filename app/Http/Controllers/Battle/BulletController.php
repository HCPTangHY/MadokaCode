<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Battle\FleetController;
use App\Http\Controllers\Controller;

class BulletController extends Controller {
    public int $owner;
    public float $damage;
    public string $damageType;
    public int $target;
    public int $creatTick;

    function __construct(FleetController|DroneController $owner, string $damageType, int $target, int $creatTick) {
        if ($owner instanceof FleetController) {
            $this->owner = $owner->id;
            $this->damage = $owner->$damageType;
            $this->damageType = $damageType;
            $this->target = $target;
            $this->creatTick = $creatTick;
        } else {
            $this->owner = $owner->id;
            $this->damage = $owner->droneDamage;
            $this->damageType = $owner->type;
            $this->target = $target;
            $this->creatTick = $creatTick;
        }
    }
    public function hit() {
        $enemy = new FleetController($this->target);
        if ($this->damageType == 'energy') {
            $accuracy = 0.9;
            $damageShield = 0.5;
            $damageArmor = 1.5;
            $damageHull = 1.25;
        }
//        elseif ($this->damageType == 'missile') {
//            $accuracy = 1;
//            $damageShield = 0;
//            $damageArmor = 1;
//            $damageHull = 1;
//        } elseif ($this->damageType == 'torpedo') {
//            $accuracy = 0.5;
//            $damageShield = 0;
//            $damageArmor = 1.5;
//            $damageHull = 1;
//        }
        else {
            $accuracy = 0.75;
            $damageShield = 1.5;
            $damageArmor = 0.5;
            $damageHull = 1.;
        }
        $damageHitChance = max(0,$accuracy-$enemy->evasion);
        if ($enemy->shield > 0 && $this->damageType != 'missile' && $this->damageType != 'torpedo') {
            $enemy->shield -= ($this->damage*$damageShield)*$damageHitChance;
            echo $enemy->name, '|', $enemy->shield, '|', $enemy->armor, '|', $enemy->hull, "<br>";
        } else {
            if ($enemy->armor > 0) {
                $enemy->armor -= ($this->damage*$damageArmor)*$damageHitChance;
                echo $enemy->name, '|', $enemy->shield, '|', $enemy->armor, '|', $enemy->hull, "<br>";
            } else {
                if ($enemy->hull > 0) {
                    if ($enemy->hull <= 0.5 * $enemy->fullHull) {
                        $damage = ($this->damage * $damageHull) * $damageHitChance;
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
        $enemy->save();
    }
}
