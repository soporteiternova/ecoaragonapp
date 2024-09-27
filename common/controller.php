<?php
/**
 * ECOAragon APP - ITERNOVA <info@iternova.net>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Basic actions controller for the app
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package common
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\common;

use Bbsnly\ChartJs\Chart;
use Bbsnly\ChartJs\Config\Data;
use Bbsnly\ChartJs\Config\Dataset;
use Bbsnly\ChartJs\Config\Options;

class controller {

    const ENDPOINT_WIND_FARM = 1;
    const ENDPOINT_SOLAR_FARM = 2;
    const ENDPOINT_COLAPSOS = 3;
    const ENDPOINT_CURRENT_PRICE = 4;
    const ENDPOINT_CURRENT_DEMAND = 5;
    const ENDPOINT_GENERATION_STRUCTURE = 6;
    const ENDPOINT_GREEN = 7;

    /**
     * Funcion para mostrar la cabecera html
     *
     * @param boolean $echo Lo muestra por pantalla si true
     * @param boolean $script Incluye scripts
     */
    public static function show_html_header( $echo = true, $script = true ) {
        /*
         *
            <script language="javascript" type="text/javascript">

                window.onload = function() {
                    var s1 = document.createElement("script");
                    s1.type = "text/javascript";
                    s1.src = "libs/js/ecoaragonapp.js";
                    document.getElementByTagName("head")[0].appendChild(s1);
                }

            </script>
         */
        $str = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<!--
			Twenty by HTML5 UP
			html5up.net | @ajlkn
			Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
		-->
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
		<head>
		    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<title>ECOArag&oacute;n APP</title>
			<meta charset="utf-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
			<link rel="stylesheet" href="css/main.css" />
			<noscript><link rel="stylesheet" href="css/noscript.css" /></noscript>			
			<link rel="shortcut icon" href="img/favicon.ico">
        
            <!-- Scripts -->
            <script src="libs/js/jquery.min.js"></script>
            <script src="libs/js/jquery-ui/jquery-ui.js"></script>
            <!-- DATATABLES -->
            <link href="//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
            <script src="libs/js/jquery.dataTables.min.js"></script>
            <script src="/libs/js/ecoaragonapp.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		</head>';

        if ( $echo ) {
            echo $str;
        }

        return $str;
    }

    /**
     * Funcion para mostrar el pie html
     */
    public static function show_html_footer( $echo = true ) {
        $str = '<!-- Footer -->
                        <footer id="footer">
                            <ul class="icons">
                                <li><a href="https://twitter.com/tecnocarreteras" target="_blank" class="icon brands circle fa-twitter"><span class="label">Twitter</span></a></li>
                                <li><a href="https://facebook.com/tecnocarreteras" target="_blank" class="icon brands circle fa-facebook-f"><span class="label">Facebook</span></a></li>
                                <li><a href="https://github.com/soporteiternova/ecoaragonapp" target="_blank" class="icon brands circle fa-github"><span class="label">Github</span></a></li>
                            </ul>
                            <ul class="copyright">
                                <li>Aplicaci&oacute;n subvencionada por el Gobierno de Arag&oacute;n con un importe de 6000&euro; - ' . date( 'Y' ) . ' <a href="https://www.iternova.net/" target="_blank">ITERNOVA</a></li>
                            </ul>
                        </footer>
                </div>
            <script src="libs/js/jquery.dropotron.min.js"></script>
            <script src="libs/js/jquery.scrolly.min.js"></script>
            <script src="libs/js/jquery.scrollgress.min.js"></script>
            <script src="libs/js/jquery.scrollex.min.js"></script>
            <script src="libs/js/browser.min.js"></script>
            <script src="libs/js/breakpoints.min.js"></script>
            <script src="libs/js/util.js"></script>
            <script src="libs/js/main.js"></script>
        
            </body>
        </html>';

        if ( $echo ) {
            echo $str;
        }
        return $str;
    }

    /**
     * Funcion para mostrar el cuerpo de la pagina
     */
    public static function show_html_body() {
        $zone = self::get( 'zone' );
        $class_start = '';
        $class_about = '';

        switch ( $zone ) {
            case 'about':
                $class_about = 'current';
                break;
            default:
                $class_start = 'current';
                break;
        }
        $str = '<body class="no-sidebar is-preload">
            <div id="page-wrapper">
    
                <!-- Header -->
                <header id="header">
                    <h1 id="logo"><a href="index.php">ECOArag&oacute;n <span>APP</span></a></h1>
                    <nav id="nav">
                        <ul>
                            <li class="' . $class_start . '"><a href="index.php">Inicio</a></li>
                            <li class="' . $class_about . '"><a href="?&amp;zone=about&amp;action=about">Sobre ECOArag&oacute;n APP</a></li>
                        </ul>
                    </nav>
                </header>
                
                <!-- Main -->
                <article id="main">
                    <section class="wrapper container">
                        <div style="width:100%; text-align:center"><a href="https://aragon.es/" target="_blank"><img src="img/logo_gobierno_aragon.png" alt="Gobierno de Arag&oacute;n" style="width:30%;"/></a></div>
                        <!--<span class="icon solid fa-car-crash"></span>-->
                        <h2 style="text-align:center; width:100%"><b>ECOArag&oacute;n APP</b></h2>
                        <p>Toda la informaci&oacute;n en tiempo real sobre la generaci&oacute;n de energ&iacute;as renovables en la Comunidad Aut&oacute;noma de Arag&oacute;n.</p>
                        <p>Este es un programa inform&aacute;tico de software libre denominado "ECOArag&oacute;n APP" que forma parte de la subvenci&oacute;n de software libre, seg&uacute;n ORDEN HAP/342/2024, de 25 de marzo, por la que se convocan subvenciones de apoyo al software libre dirigidas a microempresas y a trabajadores aut&oacute;nomos.</p>
                        <p>Proyecto financiado por el Gobierno de Arag&oacute;n.</p>
                    </section >

                    <!--One -->
                    <section class="wrapper style4 container">

                        <!--Content -->
                            <div class="content">';

        $controller = new self();
        switch ( controller::get( 'zone' ) ) {
            case 'about':
                $str .= $controller->about();
                break;
            case 'crondaemon':
                $controller->crondaemon(true);
                break;
            default:
                $str .= $controller->main();
        }
        $str .= '        </div >

                    </section >
                </article>';

        echo $str;
    }

    /**
     * Funcion para obtener datos de $_GET
     *
     * @param String $key Clave que queremos obtener
     */
    public static function get( $key ) {
        $return = '';
        if ( isset( $_GET[ $key ] ) ) {
            $return = trim( $_GET[ $key ] );
        }
        return $return;
    }

    /**
     * Funcion para obtener datos de $_POST
     */
    public static function post( $key ) {
        $return = '';
        if ( isset( $_POST[ $key ] ) ) {
            $return = trim( $_POST[ $key ] );
        }
        return $return;
    }

    /**
     * Proporciona la api key de google asociada al dominio
     */
    public static function google_key() {
        return file_get_contents( __DIR__ . '/../config/googlemaps.key' ); // Local
    }

    public static function get_endpoint_url( $endpoint ) {
        $url = '';
        switch ( $endpoint ) {
            case self::ENDPOINT_WIND_FARM:
                $url = 'https://opendata.aragon.es/GA_OD_Core/download?view_id=314&formato=json';
                break;
            case self::ENDPOINT_SOLAR_FARM:
                $url = 'https://opendata.aragon.es/GA_OD_Core/download?view_id=319&formato=json';
                break;
            case self::ENDPOINT_COLAPSOS:
                $url = 'https://opendata.aragon.es/GA_OD_Core/download?resource_id=212&formato=json&_pageSize=10000&_page=' . date( 'H' );
                break;
            case self::ENDPOINT_CURRENT_PRICE:
                $url = 'https://apidatos.ree.es/es/datos/mercados/precios-mercados-tiempo-real?start_date=' . date( 'Y-m-d\T00:00' ) . '&end_date=' . date( 'Y-m-d\T23:59' ) . '&time_trunc=hour';
                break;
            case self::ENDPOINT_CURRENT_DEMAND:
                $url = 'https://apidatos.ree.es/es/datos/demanda/demanda-tiempo-real?start_date=' . date( 'Y-m-d\T00:00' ) . '&end_date=' . date( 'Y-m-d\T23:59' ) . '&time_trunc=hour';
                break;
            case self::ENDPOINT_GENERATION_STRUCTURE:
                $url = 'https://apidatos.ree.es/es/datos/generacion/estructura-generacion?start_date=' . date( 'Y' ) . '-01-01T00:00&end_date=' . date( 'Y' ) . '-12-31T23:59&time_trunc=month&geo_trunc=electric_system&geo_limit=ccaa&geo_ids=5';
                break;
            case self::ENDPOINT_GREEN:
                $url = 'https://apidatos.ree.es/es/datos/generacion/estructura-renovables?start_date=' . date( 'Y' ) . '-01-01T00:00&end_date=' . date( 'Y' ) . '-12-31T23:59&time_trunc=month&geo_trunc=electric_system&geo_limit=ccaa&geo_ids=5';
                break;
        }
        return $url;
    }

    /**
     * Executes cron functions to load data from OpenData API
     * @return bool
     */
    public function crondaemon($debug = false) {
        $ret = true;

        $controller = new \ecoaragonapp\windfarm\controller();
        $ret &= $controller->actions( 'crondaemon', $debug );

        $controller = new \ecoaragonapp\solarfarm\controller();
        $ret &= $controller->actions( 'crondaemon', $debug );

        $controller = new \ecoaragonapp\currentprice\controller();
        $ret &= $controller->actions( 'crondaemon', $debug );

        $controller = new \ecoaragonapp\demand\controller();
        $ret &= $controller->actions( 'crondaemon', $debug );

        $controller = new \ecoaragonapp\structure\controller();
        $ret &= $controller->actions( 'crondaemon', $debug );

        $controller = new \ecoaragonapp\green\controller();
        $ret &= $controller->actions( 'crondaemon', $debug );

        return $ret;
    }

    public function about(){
        $str = '<h2>Sobre ECOArag&oacute;n APP</h2>';
        $str.= '<b>ECOArag&oacute;n APP</b> es una aplicaci&oacute;n multiplataforma, que mediante el uso de datos abiertos, procedentes de los repositorios OpenData del Gobierno de Arag&oacute;n, AEMET y Red El&eacute;ctrica, muestra al ciudadano informaci&oacute;n sobre la generaci&oacute; de energ&iacute;a e&oacute;lica y fotovoltaica en la Comunidad Aut&oacute;noma.';

        $str .= '<br/><br/><p>Este es un programa inform&aacute;tico de software libre denominado "COArag&oacute;n APP" que forma parte de la subvenci&oacute;n de software libre, seg&uacute;n ORDEN HAP/342/2024, de 25 de marzo, por la que se convocan subvenciones de apoyo al software libre dirigidas a microempresas y a trabajadores aut&oacute;nomos.</p>';
        return $str;
    }

    private function main() {
        $url_wind_farms = utils::get_server_url() . '?zone=wind_farm&action=show_in_map&js=true';
        $url_solar_farms = utils::get_server_url() . '?zone=solar_farm&action=show_in_map&js=true';

        $wind_farm_model = new \ecoaragonapp\windfarm\model();
        $solar_farm_model = new \ecoaragonapp\solarfarm\model();
        $current_price = new \ecoaragonapp\currentprice\model();
        $current_demand = new \ecoaragonapp\demand\model();

        $str = '<script src="/libs/js/ecoaragonapp.js"></script><div class="row" style="margin-left:0">
                    <div class="col-4 col-12-mobile" style="border-style: double;border-width:4px;border-color:#000000;padding:4px;"><b>Selecci&oacute;n de sistemas de generaci&oacute;n a mostrar en el mapa:</b><br/>
                        <label><input type="checkbox" id="wind_farm_checkbox" onchange="show_json_layer(\'' . $url_wind_farms . '\',\'wind_farm\');" checked/>Ver parques e&oacute;licos</label><br/>
                        <label><input type="checkbox" id="solar_farm_checkbox"  onchange="show_json_layer(\'' . $url_solar_farms . '\',\'solar_farm\');" checked/>Ver parques fotovoltaicos</label><br/>
                    </div>
                    <div class="col-4 col-12-mobile" style="border-style: double;border-width:4px;border-color:#000000;padding:4px;">
                        <span style="font-size:1.5em;font-weight: bold;">Potencia instalada en Arag&oacute;n:</span><br/>
                        <span style="font-size:1.5em;">E&oacute;lica: ' . $wind_farm_model->get_installed_power() . ' MW</span><br/>
                        <span style="font-size:1.5em;">Fotovoltaica:  ' . $solar_farm_model->get_installed_power() . ' MW</span>
                    </div>
                    <div class="col-4 col-12-mobile" style="border-style: double;border-width:4px;border-color:#000000;padding:4px;">
                        <span style="font-size:1.5em;">Precio actual: </span><br/><span style="font-size: 2em; font-weight: bold;">' . $current_price->get_current_price() . '</span><br/>
                        <span style="font-size:1.5em;">Demanda nacional actual: </span><br/><span style="font-size: 2em; font-weight: bold;">' . $current_demand->get_current_demand() . '</span>
                    </div>
                </div><br/>
                <div class="row" style="margin-left:0;margin-top:0">
                    <div class="col-6 col-12-mobile" style="border-style: double;border-width:4px;border-color:#000000;padding:4px;">
                        <span style="font-size:1.5em;font-weight: bold;">Estructura de generaci&oacute;n de energ&iacute;a en Arag&oacute;n:</span><br/>
                    ' . $this->generate_structure_chart() . '
                    </div>
                    <div class="col-6 col-12-mobile" style="border-style: double;border-width:4px;border-color:#000000;padding:4px;">
                        <span style="font-size:1.5em;font-weight: bold;">Estructura de generaci&oacute;n renovable en Arag&oacute;n:</span><br/>
                    ' . $this->generate_structure_chart(true) . '
                    </div>
                </div><br/>';
        $str.= map::create_map([], 600, 400, false);
        //$this->crondaemon(true);
        return $str;
    }

    private function generate_structure_chart($green=false){
        $obj_structure = $green ? new \ecoaragonapp\green\model() : new \ecoaragonapp\structure\model();
        $str_canvas_id = $green ? 'renewable_structure_chart' : 'generation_structure_chart';
        $js_element = $green ? 'gtx' : 'ctx';
        $array_structure = $obj_structure->get_structure();

        $array_labels = [];
        $array_data = [];
        $array_colors = [];

        foreach ( $array_structure as $title => $structure ) {
            $array_labels[] = $title;
            $array_data[] = $structure[ 'value' ];
            $array_colors[] = $structure[ 'color' ];
        }

        $str = "<canvas id='{$str_canvas_id}'></canvas><script>
              const {$js_element} = document . getElementById( '{$str_canvas_id}' );
            
              new Chart( {$js_element}, {
                        type: 'doughnut',
                data: {
                            labels:
                            " . json_encode( $array_labels ) . ",
                  datasets: [{label:'MWh', data:" . json_encode( $array_data ) . ",backgroundColor: " . json_encode( $array_colors ) . "}]
                },
                options: {
                            responsive: true,
                        }
              });
            </script>";


        return $str;
    }
}
