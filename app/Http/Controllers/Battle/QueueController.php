<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;

class QueueController extends Controller {
    protected int $front,$rear;//队尾
    public array $queue=array();//存储队列

    public function __construct() {
        $this->front = 0;
        $this->rear = 0;
    }
    //判断队空
    public function QIsEmpty(): bool {
        return $this->front==$this->rear;
    }
    //获取队首数据
    public function getFrontDate(){
        return $this->queue[$this->front]->getData();
    }
    //入队
    public function InQ($data){
        $this->queue[$this->rear]=$data;
        $this->rear++;
    }
    //出队
    public function OutQ($key){
        if($this->QIsEmpty())echo "队空不能出队！<br>";
        else{
            array_splice($this->queue,$key,1);
//            $this->front++;
        }
    }
}
