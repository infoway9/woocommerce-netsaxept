<?php

class Order {
  
 public $Amount;
 public $CurrencyCode;
 public $Force3DSecure;
 public $Goods;
 public $OrderNumber;
 public $UpdateStoredPaymentInfo;

 
 function Order
   (
        $Amount,
        $CurrencyCode,
        $Force3DSecure,
        $Goods,
        $OrderNumber,
        $UpdateStoredPaymentInfo
   )
   {
        $this->Amount                   = $Amount;
        $this->CurrencyCode             = $CurrencyCode;
        $this->Force3DSecure            = $Force3DSecure;
        $this->Goods                    = $Goods;
        $this->OrderNumber              = $OrderNumber;
        $this->UpdateStoredPaymentInfo  = $UpdateStoredPaymentInfo;
   }
};

?>
