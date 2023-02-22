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
    private function createBattleQueue(): QueueController {
        return new QueueController();
    }
    public function spaceBattle() {
        $q = new QueueController();
        foreach ($this->fleets as $ally=>$fleetsInAlly) {
            foreach ($fleetsInAlly as $fleetKey=>$fleet) {
                $f = new FleetController($fleet);
                if ($f->PDamage != 0) {
                    $enemy = $f->chooseEnemy($this->fleets);
                    $pb = $f->createBullet('PDamage',$enemy,0);
                    $q->InQ($pb);
                }
                if ($f->EDamage != 0) {
                    $enemy = $f->chooseEnemy($this->fleets);
                    $eb = $f->createBullet('EDamage',$enemy,0);
                    $q->InQ($eb);
                }
                if ($f->MDamage != 0) {
                    $m = $f->createDrone('missile');
                    $q->InQ($m);
                }
                if ($f->drone != 0) {
                    $d = $f->createDrone('drone');
                    $q->InQ($d);
                }
            }
        }
        var_dump($q->queue);
        $tick = 0;
        while ($tick < 10) {
            $tick++;
            foreach ($q->queue as $key=>$data) {
                if ($data instanceof BulletController) {
                    if ($data->creatTick < $tick) {
                        $data->hit();
                        $f = new FleetController($data->owner);
                        $enemy = $f->chooseEnemy($this->fleets);
                        if ($data->damageType == 'PDamage') {
                            $f->createBullet('PDamage',$enemy,$tick+2);
                        } elseif ($data->damageType == 'EDamage') {
                            $f->createBullet('EDamage',$enemy,$tick+5);
                        }
                        $q->OutQ($key);
                    }
                } elseif ($data instanceof DroneController) {
                    if ($data->creatTick < $tick) {

                        $enemy = $data->chooseEnemy($this->fleets);
                        $data->createBullet($data->type,$enemy,$tick);
                    }
                }
            }
            var_dump($q->queue);
            break;
        }
    }
}

