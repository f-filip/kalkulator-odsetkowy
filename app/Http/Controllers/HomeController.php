<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    public $interestrates = [
        1=>['start'=>'2005-10-15','end'=>'2008-12-14','interestrate'=>'11.5'], 
        2=>['start'=>'2008-12-15','end'=>'2014-12-22','interestrate'=>'13'], 
        3=>['start'=>'2014-12-23','end'=>'2015-12-31','interestrate'=>'8'], 
        4=>['start'=>'2016-01-01','end'=>'2020-04-30','interestrate'=>'7'], 
    ];

    public $main;
    public $startrange;
    public $endrange;
    public $rangesamount;
    public $paymentdue;
    public $dateofpayment;
    public $calcdata;

    public function index()
    {
        return view('calculator');
    }

    public function setRanges($start, $end)
    {
   
        //check start
        if($this->check_in_range($this->interestrates['1']['start'],$this->interestrates['1']['end'],$start)){
            $this->startrange = '1';
        }
      
        if($this->check_in_range($this->interestrates['2']['start'],$this->interestrates['2']['end'],$start)){
            $this->startrange = '2';
        }

        if($this->check_in_range($this->interestrates['3']['start'],$this->interestrates['3']['end'],$start)){
            $this->startrange = '3';
        }
        if($this->check_in_range($this->interestrates['4']['start'],$this->interestrates['4']['end'],$start)){
            $this->startrange = '4';
        }

        //check end
        if($this->check_in_range($this->interestrates['1']['start'],$this->interestrates['1']['end'],$end)){
            $this->endrange = '1';
        }

        if($this->check_in_range($this->interestrates['2']['start'],$this->interestrates['2']['end'],$end)){
            $this->endrange = '2';
        }
    
        if($this->check_in_range($this->interestrates['3']['start'],$this->interestrates['3']['end'],$end)){
            $this->endrange = '3';
        }
        if($this->check_in_range($this->interestrates['4']['start'],$this->interestrates['4']['end'],$end)){
            $this->endrange = '4';
        }

        $this->rangesamount=$this->endrange;
    }

    public function setresult($i,$days,$datestart,$dateend,$interest)
    {
        $this->calcdata[$i] = ['datestart'=>$datestart,'dateend'=>$dateend,'days'=>$days, 'interestrate'=>$this->interestrates[$i]['interestrate'],'interest'=>$interest];
    }

    public function daysinranges(){

        for ($i = $this->startrange; $i <= $this->endrange; $i++){

            //first loop
            if($i == $this->startrange)
            {        
            $data = $this->setData($this->paymentdue,$this->interestrates[$i]['end']);
            $this->setresult($i,$data['days']-1,date('Y-m-d',strtotime($data['datestart']."+1 days")),$data['dateend'],$this->calculate($data['days']-1,$this->interestrates[$i]['interestrate']));

            }
            //middle loop's
            if($i != $this->startrange && $i != $this->endrange){
                $data = $this->setData($this->interestrates[$i]['start'],$this->interestrates[$i]['end']);
                $this->setresult($i,$data['days'],$data['datestart'],$data['dateend'],$this->calculate($data['days'],$this->interestrates[$i]['interestrate']));
            }
            //last loop
            if($i == "$this->endrange"){
                $data = $this->setData($this->interestrates[$this->endrange]['start'],$this->dateofpayment);
                $this->setresult($i,$data['days'],$data['datestart'],$data['dateend'],$this->calculate($data['days'],$this->interestrates[$i]['interestrate']));
            }
        }
        echo "<pre>";
        print_r($this->calcdata);
        echo "</pre>";
    }
    public function setData($datefrom, $dateto)
    {   
        $datetime1 = new \DateTime($datefrom);
        $datetime2 = new \DateTime($dateto);
        $interval = $datetime1->diff($datetime2);
        $datestart = $datetime1->format("Y-m-d");
        $dateend = $datetime2->format("Y-m-d");
        return ['days'=>$interval->format('%a') +1,'datestart'=>$datestart,'dateend'=>$dateend];
    }
    
    public function getcalcdata()
    {
        return $this->calcdata;
    }
    public function calculate($days, $interestrate)
    {
       
       return round(($days / 365) * $this->main * ($interestrate * 0.01),2);
            
        
    }

    
    public function process(request $request){
        $this->paymentdue = $request->start;
        $this->dateofpayment = $request->end;
        $this->main = $request->main;
        $this->setranges($this->paymentdue,$this->dateofpayment );
        $this->daysinranges();
    }

    public function check_in_range($start_date, $end_date, $date_from_user)
    {
        return (($date_from_user >= $start_date) && ($date_from_user <= $end_date));
    }

}


/** 
*2014.12.23 - 2015.12.31 8%
*2016.01.01 - 2019.12.31 7%
*2020.01.01 - 2020.02.28 9%
*/

/**
 * ustalić stawkę % dla daty początkowej
 * sprawdzić czy endtate jest z tej stawki
 * ustalić ilość dni w tej stawce
 * ustalić czy enddata wchodzi do następnej stawki
 * jeśli wchodzi ustalić ilość dni w tej stawce, jeśłi nie to finito 
 */