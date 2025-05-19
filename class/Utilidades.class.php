<?php

class Utilidades {

    public static function nicevar($var, $title = '') {
        if (is_array($var)) {
            $table = '<table class="table table-striped table-hover">';
            if ($title) {
                $table .= '<tr><th colspan=2>' . $title . '</th></tr>';
            }
            foreach ($var as $k => $v) {
                $table .= '<tr>';
                $table .= '<td><b>' . $k . '</b></td>';
                $table .= '<td>';
                if (is_array($v)) {
                    $table .= self::nicevar($v);
                } else {
                    $table .= $v;
                }
                $table .= '</td>';
                $table .= '</tr>';
            }
            $table .= '</table>';
        } else {
            $table = $var;
        }
        return $table;
    }

    public static function limpiar_campos($dato) {
        $dato = trim($dato);
        $dato = strval($dato);
        $dato = stripslashes($dato);
        $dato = htmlspecialchars($dato);
        $dato = "'" . $dato . "'";
        return $dato;
    }
   
}
?>