<?php

namespace AppBundle\Twig;

class FilterVista extends \Twig_Extension {
    
    public function getFilters(){
        return array(
            new \Twig_SimpleFilter("addText", array($this, 'addText'))
        );
    }
    
    public function addText($string, $num){
        return $string. " TEXTO CONCATENADO .$num";
    }
    public function getName() {
        return "filter_vista";
    }

}
