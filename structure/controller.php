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
 * Generation structure controller
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package busstop
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\structure;

class controller {
    /**
     * Action controller for busstop class
     * @return bool
     */
    public function actions( $action = '', $debug = false ) {
        if ( $action === '' ) {
            $action = \ecoaragonapp\common\controller::get( 'action' );
        }
        switch ( $action ) {
            case 'crondaemon':
            default:
                return $this->crondaemon( $debug );
        }

        return true;
    }

    /**
     * Gets generation structure data from ree api.
     * @return bool
     */
    protected function crondaemon($debug = false) {
        // Loading of wind farms data
        $datetime = date( 'H' );
        if ( $debug || $datetime === '00' ) {
            $count = 0;
            $api_url = \ecoaragonapp\common\controller::get_endpoint_url( \ecoaragonapp\common\controller::ENDPOINT_GENERATION_STRUCTURE );
            $array_objs = json_decode( file_get_contents( $api_url ) );

            if ( isset( $array_objs->included  )) {
                foreach ( $array_objs->included as $structure_data ) {
                    if( !in_array($structure_data->id, ['10336','1']) && isset( $structure_data->attributes->values)) {
                        foreach( $structure_data->attributes->values as $value ) {
                            $obj_price = new model();
                            $obj_price->update_from_api( $value, $structure_data->attributes );
                            $count++;
                        }
                    }
                }
            }
            echo '<br/><br/><br/><br/><br/>Updated ' . $count . ' values of generation structure';
        }

        return true;
    }
}
