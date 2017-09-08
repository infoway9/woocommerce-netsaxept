<?php

class RegisterRequest {
  
 public $AvtaleGiro;
 public $CardInfo;
 public $Customer;
 public $Description;
 public $DnBNorDirectPayment;
 public $Environment;
 public $MicroPayment;
 public $Order;
 public $Recurring;
 public $ServiceType;
 public $Terminal;
 public $TransactionId;
 public $TransactionReconRef;
 
 function RegisterRequest
   (
        $AvtaleGiro,
        $CardInfo,
        $Customer,
        $Description,
        $DnBNorDirectPayment,
        $Environment,
        $MicroPayment,
        $Order,
        $Recurring,
        $ServiceType,
        $Terminal,
        $TransactionId,
        $TransactionReconRef
   )
   {
        $this->AvtaleGiro                 = $AvtaleGiro;
        $this->CardInfo                   = $CardInfo;
        $this->Customer                   = $Customer;
        $this->Description                = $Description;
        $this->DnBNorDirectPayment        = $DnBNorDirectPayment;
        $this->Environment                = $Environment;
        $this->MicroPayment               = $MicroPayment;
        $this->Order                      = $Order;
        $this->Recurring                  = $Recurring;
        $this->ServiceType                = $ServiceType;
        $this->Terminal                   = $Terminal;
        $this->TransactionId              = $TransactionId;
        $this->TransactionReconRef        = $TransactionReconRef;
   }
   
};
?>
