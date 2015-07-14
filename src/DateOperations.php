<?php

namespace Cityware\Format;

/**
 * Description of Date2
 *
 * @author fabricio.xavier
 */
class DateOperations {

    private $dateTime, $dateTimeDiff;

    public function __construct() {
        $this->dateTime = new \DateTime();
        $this->dateTimeDiff = new \DateTime();
    }

    /**
     * Função de envio de data baseado em string no formado Y-m-d
     * @param string $date
     * @return \Cityware\Format\DateOperations
     */
    public function setDate($date, $diff = false) {
        $dateTemp = str_replace('/', '-', $date);
        $formatedDate = date('Y-m-d', strtotime($dateTemp));
        list($year, $month, $day) = explode("-", $formatedDate);
        if ($diff) {
            $this->dateTimeDiff->setDate($year, $month, $day);
        } else {
            $this->dateTime->setDate($year, $month, $day);
        }
        return $this;
    }

    /**
     * 
     * @return type
     */
    public function parseDate() {
        return \date_parse($this->format());
    }

    /**
     * 
     * @param type $modify
     * @return type
     */
    public function dateModify($modify) {
        $modified = (array) $this->dateTime->modify($modify);
        $date = explode(' ', $modified['date']);

        return $this->setDate($date[0])->parseDate();
    }

    /**
     * Pega o primeiro dia do mês corrente
     * @return \Cityware\Format\DateOperations
     */
    public function firstDayOfThisMonth() {
        $this->dateTime->modify('first day of this month');
        return $this;
    }

    /**
     * Pega o ultimo dia do mês corrente
     * @return \Cityware\Format\DateOperations
     */
    public function lastDayOfThisMonth() {
        $this->dateTime->modify('last day of this month');
        return $this;
    }

    /**
     * 
     * @param type $date
     * @return string
     */
    private function convertDate($date) {
        $return = Array();
        $return['dateFormated'] = str_replace(Array('-', '/', '.', '_'), '-', $date);
        if (preg_match('/^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/', $return['dateFormated'])) {
            $return['format'] = 'Y-m-d';
        } else if (preg_match('/^(0[1-9]|[12][0-9]|3[01])[\-\/.](0[1-9]|1[012])[\-\/.](19|20)\d\d$/', $return['dateFormated'])) {
            $return['format'] = 'd-m-Y';
        } else {
            throw new \Exception('Necessário usilizar data nos formatos ddmmYYYY ou YYYYmmdd e separadores podendo ser ".", "-", "/", "_" sem as aspas');
        }
        return $return;
    }

    /**
     * Função de conversão de tipo
     * @param string $type
     * @return string
     */
    private function converType($type) {
        switch (strtolower($type)) {
            case 'd':
                $typeSum = 'day';
                break;
            case 'm':
                $typeSum = 'month';
                break;
            case 'y':
                $typeSum = 'year';
                break;
        }
        return $typeSum;
    }

    /**
     * Função de soma com datas
     * @param integer $num Número que será adicionado
     * @param string $type Tipo de adição (Dia = d, Mês = m, Ano = y)
     * @return \Cityware\Format\DateOperations
     */
    public function sum($num, $type = 'd') {
        $typeSum = $this->converType($type);
        $this->dateTime->modify('+' . $num . ' ' . \Cityware\Format\Inflector::pluralize($typeSum));
        return $this;
    }

    /**
     * Função de subtração com datas
     * @param integer $num Número que será adicionado
     * @param string $type Tipo de adição (Dia = d, Mês = m, Ano = y)
     * @return \Cityware\Format\DateOperations
     */
    public function sub($num, $type = 'd') {
        $typeSum = $this->converType($type);
        $this->dateTime->modify('-' . $num . ' ' . \Cityware\Format\Inflector::pluralize($typeSum));
        return $this;
    }

    /**
     * Função de calculo de diferença de dias entre datas
     * @param string $date
     * @return integer
     */
    public function difference($date = null) {
        if (!empty($date)) {
            $this->setDate($date, true);
        }
        $interval = (array) $this->dateTime->diff($this->dateTimeDiff);
        return $interval['days'];
    }

    /**
     * Função de renderização do resultado
     * @param string $format
     */
    public function render($format = 'Y-m-d') {
        echo $this->dateTime->format($format);
    }

    /**
     * Função de formatação do resultado
     * @param string $format
     */
    public function format($format = 'Y-m-d') {
        return $this->dateTime->format($format);
    }

    /**
     * Função de conversão de segundos para Array(Hora, Minuto, Segundo)
     * @param  integer $seconds
     * @return array
     */
    public static function secondsToTime($seconds) {
        // extract hours
        $hours = floor($seconds / (60 * 60));

        // extract minutes
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);

        // extract the remaining seconds
        $divisor_for_seconds = $divisor_for_minutes % 60;
        $seconds = ceil($divisor_for_seconds);

        // return the final array
        $arrayReturn = array(
            "h" => (int) $hours,
            "m" => (int) $minutes,
            "s" => (int) $seconds,
        );

        return $arrayReturn;
    }

    /**
     * Conversão de hora para o formato que definir (Hora, Minuto, Segundo)
     * @param  time    $time
     * @param  string  $conversion
     * @param  boolean $debug
     * @return double
     */
    public static function convertTime($time, $conversion, $debug = false) {
        list ($hora, $minuto, $segundo) = explode(":", $time);
        switch (strtolower($conversion)) {
            case 'h':
                $resultH = $hora * 1;
                $resultM = $minuto / 60;
                $resultS = $segundo / 60;
                break;
            case 'm':
                $resultH = $hora * 60;
                $resultM = $minuto * 1;
                $resultS = $segundo / 60;
                break;
            case 's':
                $resultH = $hora * 60;
                $resultM = $minuto * 60;
                $resultS = $segundo * 1;
                break;
        }

        $result = $resultH + $resultM + $resultS;

        if ($debug) {
            echo "Hora: " . $resultH . " Minuto: " . $resultM . " Segundo: " . $resultS . " Total: " . $result . "<br>";
        }

        return $result;
    }

    /**
     * Cria um intervalo de Meses de acordo com a data inicial e final
     * @param  date  $startDate
     * @param  date  $endDate
     * @return array
     */
    public static function getMonthsRange($startDate, $endDate) {
        
        $startDateTemp = str_replace('/', '-', $startDate);
        $endDateTemp = str_replace('/', '-', $endDate);
        $time1 = strtotime($startDateTemp);
        $time2 = strtotime($endDateTemp);

        $year1 = date('Y', $time1);
        $year2 = date('Y', $time2);
        $years = range($year1, $year2);
        $months = Array();
        foreach ($years as $year) {
            $months[$year] = array();
            while ($time1 < $time2) {
                if (date('Y', $time1) == $year) {
                    $months[$year][] = date('m', $time1);
                    $time1 = strtotime(date('Y-m-d', $time1) . ' +1 month');
                } else {
                    break;
                }
            }
            continue;
        }

        return $months;
    }

    /**
     * Função de geração de intervalo de semana
     * @param  type $datestr
     * @return type
     */
    public static function rangeWeek($datestr) {
        date_default_timezone_set(date_default_timezone_get());
        $dt = strtotime(str_replace('/', '-', $datestr));
        $res['start'] = date('N', $dt) == 1 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last monday', $dt));
        $res['end'] = date('N', $dt) == 7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next sunday', $dt));

        return $res;
    }

    /**
     * Função para criação de intervalo de datas
     * @param  type  $strDateFrom
     * @param  type  $strDateTo
     * @return array
     */
    public static function createDateRangeArray($strDateFrom, $strDateTo) {
        $aryRange = array();
        
        $startDateTemp = str_replace('/', '-', $strDateFrom);
        $endDateTemp = str_replace('/', '-', $strDateTo);
        $iDateFrom = strtotime($startDateTemp);
        $iDateTo = strtotime($endDateTemp);

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry

            while ($iDateFrom < $iDateTo) {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }

        return $aryRange;
    }
    
    public static function extensionDateNow() {
        $hoje = getdate();

        // Nessa parte do código foi criada a variável $hoje, que receberá os valores da data.
        switch ($hoje['wday']) {
            case 0:
                $diaSemana = "Domingo, ";
                break;
            case 1:
                $diaSemana = "Segunda-Feira, ";
                break;
            case 2:
                $diaSemana = "Terça-Feira, ";
                break;
            case 3:
                $diaSemana = "Quarta-Feira, ";
                break;
            case 4:
                $diaSemana = "Quinta-Feira, ";
                break;
            case 5:
                $diaSemana = "Sexta-Feira, ";
                break;
            case 6:
                $diaSemana = "Sábado, ";
                break;
        }

        // Acima foi utilizada a instrução switch para que o dia da semana possa ser apresentado por
        // extenso, já que o PHP retorna em números. Perceba que dentro de cada instrução case tem uma
        // instrução echo que escreve o dia da semana na tela.

        $dia = $hoje['mday'];

        // A instrução echo $hoje[‘mday’]; escreve na tela o data em número,
        // conforme retorna o PHP, não precisando de conversão.

        switch ($hoje['mon']) {
            case 1:
                $mes = " de Janeiro de ";
                break;
            case 2:
                $mes = " de Fevereiro de ";
                break;
            case 3:
                $mes = " de Março de ";
                break;
            case 4:
                $mes = " de Abril de ";
                break;
            case 5:
                $mes = " de Maio de ";
                break;
            case 6:
                $mes = " de Junho de ";
                break;
            case 7:
                $mes = " de Julho de ";
                break;
            case 8:
                $mes = " de Agosto de ";
                break;
            case 9:
                $mes = " de Setembro de ";
                break;
            case 10:
                $mes = " de Outubro de ";
                break;
            case 11:
                $mes = " de Novembro de ";
                break;
            case 12:
                $mes = " de Dezembro de ";
                break;
        }

        // A parte do código acima tem a mesma função que o primeiro switch utilizado,
        // só que agora ele é usado para apresentar o mês.

        $ano = $hoje['year'];

        return $diaSemana . $dia . $mes . $ano;
    }

    /**
     * Retorna o mês por extenso
     * @param  integer $month
     * @return string
     */
    public static function extensionMonth($month) {
        $mes = null;
        switch ($month) {
            case '01':
            case 1:
                $mes = "Janeiro";
                break;
            case '02':
            case 2:
                $mes = "Fevereiro";
                break;
            case '03':
            case 3:
                $mes = "Março";
                break;
            case '04':
            case 4:
                $mes = "Abril";
                break;
            case '05':
            case 5:
                $mes = "Maio";
                break;
            case '06':
            case 6:
                $mes = "Junho";
                break;
            case '07':
            case 7:
                $mes = "Julho";
                break;
            case '08':
            case 8:
                $mes = "Agosto";
                break;
            case '09':
            case 9:
                $mes = "Setembro";
                break;
            case '10':
            case 10:
                $mes = "Outubro";
                break;
            case '11':
            case 11:
                $mes = "Novembro";
                break;
            case '12':
            case 12:
                $mes = "Dezembro";
                break;
        }

        return $mes;
    }
    
    /**
     * Retorna o mês por extenso em formato reduzido
     * @param  integer $month
     * @return string
     */
    public static function extensionShortMonth($month) {
        $mes = null;
        switch ($month) {
            case '01':
            case 1:
                $mes = "Jan";
                break;
            case '02':
            case 2:
                $mes = "Fev";
                break;
            case '03':
            case 3:
                $mes = "Mar";
                break;
            case '04':
            case 4:
                $mes = "Abr";
                break;
            case '05':
            case 5:
                $mes = "Mai";
                break;
            case '06':
            case 6:
                $mes = "Jun";
                break;
            case '07':
            case 7:
                $mes = "Jul";
                break;
            case '08':
            case 8:
                $mes = "Ago";
                break;
            case '09':
            case 9:
                $mes = "Set";
                break;
            case '10':
            case 10:
                $mes = "Out";
                break;
            case '11':
            case 11:
                $mes = "Nov";
                break;
            case '12':
            case 12:
                $mes = "Dez";
                break;
        }

        return $mes;
    }

}
