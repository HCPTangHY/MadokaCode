<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;
use App\Models\Fleet;

class FleetController extends Controller {
    private $owner,$ships,$computer,$hull,$PDamage,$EDamage,$MDamage,$drone,$armor,$shield,$tracking,$evasion,$speed,$disengageChance;
    public function __construct($id=-1) {
        if ($id != -1) {
            $f = Fleet::where(['id' => $id])->first();
            $this->owner = $f->owner;
            $this->ships = json_decode($f->ship,true);
            $this->computer = $f->computer;
            $this->hull = $f->hull;
            $this->PDamage = $f->PDamage;
            $this->EDamage = $f->EDamage;
            $this->MDamage = $f->mission;
            $this->drone = $f->drone;
            $this->armor = $f->armor;
            $this->shield = $f->shield;
            $this->tracking = $f->tracking;
            $this->evasion = $f->evasion;
            $this->speed = $f->speed;
            $this->disengageChance = $f->disengageChance;
        }
    }
}
