<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;

class DroneController extends Controller {
    private int $owner;
    private float $damage,$droneHP,$droneEvasion,$droneSpeed;

    function __construct(FleetController $owner) {
        $this->owner = $owner->id;
        $this->damage = $owner->droneDamage;
        $this->droneHP = $owner->droneHP;
        $this->droneEvasion = $owner->droneEvasion;
        $this->droneSpeed = $owner->droneSpeed;
    }
    public function createBullet(): BulletController {
        return new BulletController($this, 'drone');
    }
}
