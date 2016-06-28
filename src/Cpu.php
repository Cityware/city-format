<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Format;

/**
 * Description of Cpu
 *
 * @author fsvxavier
 */
final class Cpu {
    
    /**
     * Formata os dados de Carga Média de CPU do servidor 
     * @param double $load
     * @return array
     */
    public static function loadAverage($load) {
        $loadReturn = Array();
        
        if($load > 1){
            $loadReturn['utilized'] = 100;
            $loadReturn['overload'] = ($load - 1) * 100;
        } else {
            $loadReturn['utilized'] = ($load * 100);
            $loadReturn['overload'] = 0;
        }
        
        return $loadReturn;
    }
}
