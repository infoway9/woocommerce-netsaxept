<?php

class Environment {
  
 public $Language;
 public $OS;
 public $WebServicePlatform;
 
 function Environment
   (
        $Language,
        $OS,
        $WebServicePlatform
   )
   {
        $this->Language           = $Language;
        $this->OS                 = $OS;
        $this->WebServicePlatform = $WebServicePlatform;
   }
};

?>
