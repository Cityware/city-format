<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Format;

/**
 * Description of Number
 *
 * @author fabricio.xavier
 */
class Number {

    /**
     * Formatação e numero inteiro
     * @param float/integer $value
     * @param string $language
     * @return integer
     */
    public static function integerNumber($value, $language = 'pt_BR') {
        $valInteger = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
        $valInteger->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
        $valInteger->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
        return $valInteger->format((float) $value, \NumberFormatter::TYPE_INT64);
    }

    /**
     * Formatação de numero formato moeda
     * @param float/integer $value
     * @param integer $precision
     * @param string $language
     * @return float
     */
    public static function currency($value, $precision = 2, $language = 'pt_BR') {
        $valCurrency = new \NumberFormatter($language, \NumberFormatter::CURRENCY);
        $valCurrency->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
        $valCurrency->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
        return $valCurrency->format((float) $value, \NumberFormatter::TYPE_DOUBLE);
    }

    /**
     * Formatação de numero decimal
     * @param float/integer $value
     * @param integer $precision
     * @param string $language
     * @return float
     */
    public static function decimalNumber($value, $precision = 2, $language = 'pt_BR') {
        $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
        $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
        $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
        return $valDecimal->format((float) $value, \NumberFormatter::TYPE_DOUBLE);
    }

    /**
     * Formatação de numero em bytes para o formato de tamanho
     * @param int $bytes
     * @param string $unit
     * @param int $precision
     * @return string
     */
    public static function byteFormat($bytes, $unit = "B", $precision = 2) {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;
        $petabyte = $terabyte * 1024;
        $exabyte = $petabyte * 1024;
        $zettabyte = $exabyte * 1024;
        $yottabyte = $zettabyte * 1024;

        $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);

        if ($unit != 'B') {
            $value = ($bytes * pow(1024, floor($units[$unit])));
        } else {
            $value = $bytes;
        }

        if (($value >= 0) && ($value < $kilobyte)) {
            return $value . ' B';
        } elseif (($value >= $kilobyte) && ($value < $megabyte)) {
            return round($value / $kilobyte, $precision) . ' KB';
        } elseif (($value >= $megabyte) && ($value < $gigabyte)) {
            return round($value / $megabyte, $precision) . ' MB';
        } elseif (($value >= $gigabyte) && ($value < $terabyte)) {
            return round($value / $gigabyte, $precision) . ' GB';
        } elseif ($value >= $terabyte) {
            return round($value / $terabyte, $precision) . ' TB';
        } elseif ($value >= $petabyte) {
            return round($value / $petabyte, $precision) . ' PB';
        } elseif ($value >= $exabyte) {
            return round($value / $exabyte, $precision) . ' EB';
        } elseif ($value >= $zettabyte) {
            return round($value / $zettabyte, $precision) . ' ZB';
        } elseif ($value >= $yottabyte) {
            return round($value / $yottabyte, $precision) . ' YB';
        } else {
            return $value . ' B';
        }
    }

}
