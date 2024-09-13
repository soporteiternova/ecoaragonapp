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
 * Access file to the app
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

declare( strict_types=0 );

// Includes
require __DIR__ . '/libs/composer/vendor/autoload.php';

session_start();

// Index
$zone = \ecoaragonapp\common\controller::get( 'zone' );
$js = \ecoaragonapp\common\controller::get( 'js' );
if ( $zone === 'map' ) {
    $controller = new \ecoaragonapp\common\map();
    echo $controller->actions();
} elseif ( $js === 'true' ) {
    if ( $zone === 'wind_farm' ) {
        $controller = new \ecoaragonapp\windfarm\controller();
        echo $controller->actions();
    } elseif ( $zone === 'solar_farm' ) {
        $controller = new \ecoaragonapp\solarfarm\controller();
        echo $controller->actions();
    }
} else {
    // Index
    \ecoaragonapp\common\controller::show_html_header();
    \ecoaragonapp\common\controller::show_html_body();
    \ecoaragonapp\common\controller::show_html_footer();
}
