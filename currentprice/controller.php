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
 * Current price controller
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package busstop
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\currentprice;

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
     * Gets glide list from opendata repository.
     * @return bool
     */
    protected function crondaemon($debug = false) {
        // Loading of wind farms data
        $datetime = date( 'H' );
        if ( $debug || $datetime === '12' ) {
            $count = 0;
            $api_url = \ecoaragonapp\common\controller::get_endpoint_url( \ecoaragonapp\common\controller::ENDPOINT_CURRENT_PRICE );
            $array_objs = json_decode( file_get_contents( $api_url ) );

            if ( isset( $array_objs->included  )) {
                foreach ( $array_objs->included as $prices_data ) {
                    if( $prices_data->id==="1001" && isset( $prices_data->attributes)) {
                        foreach( $prices_data->attributes->values as $value) {
                            $obj_windfarm = new model();
                            $obj_windfarm->update_from_api( $value );
                            $count++;
                        }
                    }
                }
            }
            echo '<br/><br/><br/><br/><br/>Updated ' . $count . ' prices';
        }

        return true;
    }

    /**
     * Returns geojson data to load map layer
     * @return void
     */
    private function get_current_price() {

        $obj_model = new model();
        $array_obj = $obj_model->get_all( [], [], 0, 0, '_id', [ 'debug' => false ] );

        $return = \ecoaragonapp\common\utils::array_obj_to_geojson($array_obj);


        echo json_encode( $return );
    }

}
