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
 * Stations controller
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package busstop
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\weather;

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
                return $this->crondaemon( $debug );
                break;
            case 'get_historic':
                return $this->get_historic();
                break;
            case 'show_in_map':
                return $this->get_map_json();
                break;
            case 'listing':
            default:
                return $this->listing();
                break;
        }

        return true;
    }

    /**
     * Gets stations list from aemet
     * @return bool
     */
    protected function crondaemon($debug = false) {
        // Loading of solar farms data
        $datetime = date( 'H' ) % 2;
        if ( $debug || $datetime === 0 ) {
            $count = 0;
            $obj_api = new \ecoaragonapp\common\aemetapi();
            $api_url = \ecoaragonapp\common\controller::get_endpoint_url( \ecoaragonapp\common\controller::ENDPOINT_AEMET_STATIONS_LIST );
            $response = $obj_api->do_request($api_url);

            // Stations list to get identification of stations located in Aragon
            $array_stations_indicatives = [];
            if ( isset( $response['data']  )) {
                foreach ( $response[ 'data' ] as $station ) {
                    if( in_array( $station['provincia'], ['HUESCA', 'TERUEL', 'ZARAGOZA' ] ) ){
                        $array_stations_indicatives[] = $station['indicativo'];
                    }
                }
            }

            // Observations
            $api_url = \ecoaragonapp\common\controller::get_endpoint_url( \ecoaragonapp\common\controller::ENDPOINT_AEMET_OBSERVATION_ALL );
            $response = $obj_api->do_request( $api_url );

            if( isset( $response[ 'data' ] ) ) {
                $obj_model = new model();

                foreach( $response[ 'data' ] as $station ) {
                    if( in_array( $station['idema'], $array_stations_indicatives ) ) {
                        $obj_model->update_from_api( $station );
                        $count++;
                    }
                }
            }
            echo '<br/><br/><br/><br/><br/>Updated ' . $count . ' solar farms';
        }

        return true;
    }

    /**
     * Returns geojson data to load map layer
     * @return void
     */
    private function get_map_json() {

        $obj_model = new model();
        $array_obj = $obj_model->get_all( [], [], 0, 0, '_id', [ 'debug' => false ] );

        $return = \ecoaragonapp\common\utils::array_obj_to_geojson($array_obj);


        echo json_encode( $return );
    }

}
