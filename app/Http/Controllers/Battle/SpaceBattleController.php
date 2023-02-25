<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Fleet;

class SpaceBattleController extends Controller {
    private array $fleets;
    private int $battlePosition;

    public function __construct(Array $fleets) {
        $this->fleets = $fleets;
        $this->battlePosition = Fleet::where(["id"=>$fleets[0]])->first()->position;
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
                    $pb = $f->createBullet('PDamage',$enemy,0,$q);
                }
                if ($f->EDamage != 0) {
                    $enemy = $f->chooseEnemy($this->fleets);
                    $eb = $f->createBullet('EDamage',$enemy,0,$q);
                }
                if ($f->MDamage != 0) {
                    $m = $f->createDrone('missile');
                }
                if ($f->drone != 0) {
                    $d = $f->createDrone('drone');
                }
            }
        }
        $tick = 0;
        while ($tick < 100) {
            $tick++;
            foreach ($q->queue as $key=>$data) {
                if ($data instanceof BulletController) {
                    if ($data->creatTick < $tick) {
                        $data->hit($this->fleets);
                        echo Fleet::where(["id" =>$data->target])->first()->position;
                        if (Fleet::where(["id"=>$data->target])->first()->position != $this->battlePosition) {
                            foreach ($this->fleets as $ally=>$fleetsInAlly) {
                                foreach ($fleetsInAlly as $fleetKey=>$fleet) {
                                    if ($fleet == $data->target) {
                                        array_splice($this->fleets[$ally], $fleetKey,1);
                                        if (count($this->fleets[$ally]) == 0) {
                                            array_splice($this->fleets,$ally,1);
                                        }
                                    }
                                }
                            }
                        }
                        if (count($this->fleets)<=1) {
                            break 2;
                        }
                        $f = new FleetController($data->owner);
                        $enemy = $f->chooseEnemy($this->fleets);
                        if ($data->damageType == 'PDamage') {
                            $f->createBullet('PDamage',$enemy,$tick+2,$q);
                        } elseif ($data->damageType == 'EDamage') {
                            $f->createBullet('EDamage',$enemy,$tick+5,$q);
                        }
                        $q->OutQ($key);
                    }
                } elseif ($data instanceof DroneController) {
                    if ($data->creatTick < $tick) {
                        $enemy = $data->chooseEnemy($this->fleets);
                        $data->createBullet($data->type,$enemy,$tick,$q);
                    }
                }
            }
//            var_dump($q->queue);
//            echo "<br>";
            var_dump($this->fleets);
        }
    }
}

