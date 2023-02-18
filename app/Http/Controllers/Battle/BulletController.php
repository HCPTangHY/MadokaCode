<?php

namespace App\Http\Controllers;

class BulletController extends Controller {
    function __construct($fleet,$damageType) {
        $this->damage = $fleet->$damageType;
        $this->damageType = $damageType;
        $this->tracking = $fleet->tracking;
        $this->computer = $fleet->computer;
    }
}
