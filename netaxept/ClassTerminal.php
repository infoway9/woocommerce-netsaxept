<?php

class Terminal {
 public $AutoAuth;
 public $PaymentMethodList;
 public $Language;
 public $OrderDescription;
 public $RedirectOnError;
 public $RedirectUrl;
 
 function Terminal
   (
        $AutoAuth,
        $PaymentMethodList,
        $Language,
        $OrderDescription,
        $RedirectOnError,
        $RedirectUrl
   )
   {
        $this->AutoAuth           = $AutoAuth;
        $this->PaymentMethodList  = $PaymentMethodList;
        $this->Language           = $Language;
        $this->OrderDescription   = $OrderDescription;
        $this->RedirectOnError    = $RedirectOnError;
        $this->RedirectUrl        = $RedirectUrl;
   }
};

?>
