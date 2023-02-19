<?php

namespace App\Http\Controllers\Battle;

use App\Http\Controllers\Controller;

class QueueController extends Controller {
    protected int $front,$rear;//队尾
    protected array $queue=array('0'=>'队尾');//存储队列

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
        if($this->QIsFull())echo $data.":我一来咋就满了！（队满不能入队，请等待！）<br>";
        else {
            $this->front++;
            for($i=$this->front;$i>$this->rear;$i--){
                if($this->queue[$i])unset($this->queue[$i]);
                $this->queue[$i]=$this->queue[$i-1];
            }
            $this->queue[$this->rear+1]=new data($data);
            echo '入队成功！<br>';
        }

    }
    //出队
    public function OutQ(){
        if($this->QIsEmpty())echo "队空不能出队！<br>";
        else{
            unset($this->queue[$this->front]);
            $this->front--;
            echo "出队成功！<br>";
        }
    }
}
