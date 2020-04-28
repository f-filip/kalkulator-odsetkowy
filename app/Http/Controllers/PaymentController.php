<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public $interestrates = [
        1=>['start'=>'2005-10-15','end'=>'2008-12-14','interestrate'=>'11.5'], 
        2=>['start'=>'2008-12-15','end'=>'2014-12-22','interestrate'=>'13'], 
        3=>['start'=>'2014-12-23','end'=>'2015-12-31','interestrate'=>'8'], 
        4=>['start'=>'2016-01-01','end'=>'2020-04-30','interestrate'=>'7'], 
    ];

    /**
    * main claim
    */
    public $main;
    /** 
    * Interest calculated and sumed in specified interestrates period
    */
    public $periodInterest;
    /**
     * Costs with priority of satisfaction
     */
    public $cost;
    /**
     * Stores value of representation cost in  executive proceedings,
     * which are recovered in the same prioryty as main claim
     */
    public $executivecost;
    /**
     * Stores actual interest during calculation
     */
    public $interestcalculation;

    /** 
    *Stores on which interes rate period we start counting interest
    */
    public $startrange;
    /** 
    *Stores on which interes rate period we end counting interest
    */
    public $endrange;
    /** 
    *Stores amount of interest rate period used in currect calculation 
    */
    public $rangesamount;
    /** 
    *Date of payment due. Start calculating interest from day after
    */
    public $paymentdue;
     /** 
    *Date of payment. End calculating interest on that date
    */
    public $dateofpayment;
    /**
     * Stores result of calculation on given period, includes: Date from, date to, amount of days, interes rate, interest.
     */
    public $periodcalculationdata=[];

    public $dates;

    public $periods;

    public $beforedetails=[];

    public $allocation=[];

    public $payment;

    public $paymentdates;

    public $perioddata=[];

    public $afterdetails=[];

    public $overbalance=0;

    public function index()
    {
        return view('calculator_payment');
    }

    /**
     * Set start and end interes rate period, according to given date from and date to
     * Set amount of interest rate period 
     */
    public function setRanges($start, $end)
    {
        
        $rates = count($this->interestrates);

        for ($i=1; $i <= $rates;$i++){
            if($this->check_in_range($this->interestrates[$i]['start'],$this->interestrates[$i]['end'],$start)){
                $this->startrange = $i;
            }

            if($this->check_in_range($this->interestrates[$i]['start'],$this->interestrates[$i]['end'],$end)){
                $this->endrange = $i;
            }

        }
   
        $this->rangesamount=$this->endrange;
    }
    /**
     * Set result of calculation in given period
     * Including period, amoount of days, date from, date to, interest
     * Data is used only for calculation, array is cleared after calculation
     */
    public function setresult($period, $i,$days,$datestart,$dateend,$interest)
    {
        $this->periodcalculationdata[] = [$period =>  ['datestart'=>$datestart,'dateend'=>$dateend,'days'=>$days, 'interestrate'=>$this->interestrates[$i]['interestrate'],'interest'=>$interest]];
    }
    /**
     * According to given information about interestrate period calculate interest
     * Given dates can start and end in few interes rates
     */
    public function setperioddata($period){


        for ($i = $this->startrange; $i <= $this->endrange; $i++){
            //first loop
            if($i == $this->startrange && $i != $this->endrange)
            {        
                $data = $this->setDays($this->paymentdue,$this->interestrates[$i]['end']);
                $this->setresult($period,$i,$data['days']-1,date('Y-m-d',strtotime($data['datestart']."+1 days")),$data['dateend'],$this->calculateInterest($data['days']-1,$this->interestrates[$i]['interestrate']));
            }
            //just one loop
            if($i == $this->startrange && $i == $this->endrange)
            {       
                $data = $this->setDays($this->paymentdue,$this->dateofpayment);
                $this->setresult($period,$i,$data['days']-1,date('Y-m-d',strtotime($data['datestart']."+1 days")),$data['dateend'],$this->calculateInterest($data['days']-1,$this->interestrates[$i]['interestrate']));
            }
            //middle loop's
            if($i != $this->startrange && $i != $this->endrange){
                $data = $this->setDays($this->interestrates[$i]['start'],$this->interestrates[$i]['end']);
                $this->setresult($period,$i,$data['days'],$data['datestart'],$data['dateend'],$this->calculateInterest($data['days'],$this->interestrates[$i]['interestrate']));
            }
            //last loop
            if($i == $this->endrange && $i != $this->startrange){
                $data = $this->setDays($this->interestrates[$this->endrange]['start'],$this->dateofpayment);
                $this->setresult($period,$i,$data['days'],$data['datestart'],$data['dateend'],$this->calculateInterest($data['days'],$this->interestrates[$i]['interestrate']));
            }
        
        }


    }

    /**
     * Set days according to given date.
     * Return amount of days used to calculate interest, date from and date to
     * +1 to amount of days bacouse due to interest rise also in date to date
     */
    public function setDays($datefrom, $dateto)
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

    /**
     * Calculate interest for given amount of days and interest rate
     * recognize if given dates are in Leap year
     */
    public function calculateInterest($days, $interestrate)
    {
       return round(($days / 365) * $this->main * ($interestrate * 0.01),2);
            
    }
    /***
     * return sum interest on setted by setperiodddata period
     */
    public function interestSumInPeriod($i)
    {    
        foreach ($this->periodcalculationdata as $data)
        {  
        $this->periodInterest[] = $data[$i]['interest'];
        $interestSum = array_sum($this->periodInterest);
        }

        $this->periodInterest=[];
        return $interestSum;

    }

    public function daysInPeriod($i)
    {
        foreach ($this->periodcalculationdata as $data)
        {  
        $perioddays[] = $data[$i]['days'];
        $daysSum = array_sum($perioddays);
        }
        return $daysSum;
    }

    /**
     * Checks if cost with booking priority are paid
     * If yes, set proper cost and current payment amount
     */
    public function checkcost($i)
    {
        
        if(abs($this->cost) != $this->cost)
        {
           
            $this->payment[$i] = abs($this->cost);
            $this->cost=0;
            return false;
        }

        return true;
        
    }

    /**
     * checks if interest from recent period are paid after booking payment 
     * if yes, set proper current payment and interest
     */
    public function checkInterest($i){

        if(abs($this->interestcalculation)!= $this->interestcalculation)
        {
            $this->payment[$i] = abs($this->interestcalculation);
            $this->interestcalculation = 0;
            return false;
        }
        return true;

    }

  

    public function checkExecutiveCost($i)
    {
        
        if(abs($this->executivecost) != $this->executivecost)
        {   
            $this->payment[$i] = abs($this->executivecost);
            $this->executivecost = 0;
            return false;
        }
        return true;

    }

    /**
     * Checks if main claim is fully recovered
     * If so, die.
     */
    public function checkMain($i)
    {
        if(abs($this->main) != $this->main){
            $this->overbalance = abs($this->main) + $this->checkiflastpayment($i);
            $this->main = 0;
           // dd($this->allocation);
            return false;
        }

        return true;
    }
    public function checkiflastpayment($currentPayment)
    {
        $payments = count($this->payment);

        if($currentPayment != $payments)
        {
            for($i=$currentPayment+1;$i<=$payments;$i++)
            {
             $paymentleft[]=$this->payment[$i];
            }   
                return array_sum($paymentleft);
        }
    }

    public function setcalculatingdata($request)
    {
        $this->dates = $request->only('datepayment');
        $this->dates= array_filter($this->dates['datepayment']);
        $this->periods =count($this->dates);
        $this->main = $request->main;
        $this->cost = $request->cost;
        $this->executivecost = $request->executivecost;
        $this->payment = $request->only('payment');
        $this->payment=array_filter($this->payment['payment']);
        $this->paymentdates = $this->dates;
        array_splice($this->payment,0,0,0);
        array_splice($this->paymentdates,0,0,0);
        array_splice($this->beforedetails,0,0,0);
        array_splice($this->allocation,0,0,0);
        array_splice($this->perioddata,0,0,0);
        array_splice($this->afterdetails,0,0,0);
        unset($this->beforedetails[0]);
        unset($this->payment[0]);
        unset($this->allocation[0]);
        unset($this->paymentdates[0]);
        unset($this->perioddata[0]);
        unset($this->afterdetails[0]);
    }

    /**
     * Payment settlement with specified prioryty: 1-cost, 2-interest, 3-executive cost, 4- main claim
     */
    public function process(request $request){

        $this->setcalculatingdata($request);

        for($i=1; $i<$this->periods; $i++){
            $this->paymentdue = $this->dates[$i-1];
            $this->dateofpayment = $this->dates[$i]; 
            $this->setranges($this->paymentdue,$this->dateofpayment ); 
            $this->setperioddata($i);
            $this->interestcalculation = $this->interestcalculation + $this->interestSumInPeriod($i);    
            $this->beforedetails[] = [
            'main'=> $this->main,'cost'=>$this->cost,'executivecost'=>$this->executivecost,
            'interesttotal'=>$this->interestcalculation,'interestperiod'=>$this->interestSumInPeriod($i),
            'payment'=>$this->payment[$i], 'datefrom'=> date('Y-m-d',strtotime($this->paymentdue."+1 days")),'dateto'=>$this->dateofpayment,
            'days'=>$this->daysinPeriod($i)
            ];
            
            $this->perioddata[] = ['datefrom'=>$this->paymentdue,'dateto'=>$this->dateofpayment,'periodinterest'=>$this->interestSumInPeriod($i)];
            $this->cost = $this->cost - $this->payment[$i];
            if(!$this->checkCost($i)){
                $this->interestcalculation = $this->interestcalculation -  $this->payment[$i];
                if(!$this->checkInterest($i)){
                    $this->executivecost = $this->executivecost -  $this->payment[$i];
                    if(!$this->checkExecutiveCost($i)){
                        $this->main = $this->main -  $this->payment[$i];
                        if(!$this->checkMain($i)){
                        }
                    }
                }
            }
            $this->allocation[] = ['main'=> $this->main - $this->beforedetails[$i]['main'],'cost'=>$this->cost - $this->beforedetails[$i]['cost'],'executivecost'=>$this->executivecost - $this->beforedetails[$i]['executivecost'],'interest'=>$this->interestcalculation - $this->beforedetails[$i]['interesttotal']];
            $this->afterdetails[]=['main'=>$this->main,'cost'=>$this->cost,'executivecost'=>$this->executivecost,'interest'=>$this->interestcalculation, 'overbalance' => $this->overbalance];
            
            $finaldata[]=$this->periodcalculationdata;
            $this->periodcalculationdata =[];

            if($this->overbalance != 0)
            {
                break;
            }
            
        }
            
            return view('result_payment',[
                'befores'=>$this->beforedetails,
                'allocations'=>$this->allocation,
                'payments'=>$this->payment, 
                'after'=>$this->afterdetails

            ]);

 
 
    }

    

    public function check_in_range($start_date, $end_date, $date_from_user)
    {
        return (($date_from_user >= $start_date) && ($date_from_user <= $end_date));
    }

}


/**
 * TODO
 * Many interest rates
 * kze with 9th category                                                                     +
 * Also add few interest rate types in one executive title
 * Koszty z odsetkami są takie tytuły
 * PRIMO ULTIMO Store counting data, to show exctaly what happend behind the scenes in view  +
 * Enable to show 
 * 
 */