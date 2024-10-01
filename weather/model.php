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
 * Weather data model
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package busstop
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\weather;

class model extends \ecoaragonapp\common\model {
    public $_database_collection = 'stations';
    public $idema = '';
    public $altitud = 0.0;
    public $nombre = '';
    public $wind_speed = 0.0;
    public $wind_direction = 0.0;
    public $recordtime = '';
    public $values = [];

    /**
     * Updates a weather object from open data api, and creates it if doesn't exist
     *
     * @param $api_object
     *
     * @return bool
     */
    public function update_from_api( $api_object ) {
        $this->_id = null;
        $ret = false;

        if( isset( $api_object['idema'])) {
            $obj_recordtime_search = substr($api_object['fint'],0, 7 );

            $array_criteria[] = [ 'idema', 'eq', $api_object[ 'idema' ], 'string' ];
            $array_criteria[] = [ 'recordtime', 'eq', $obj_recordtime_search, 'string' ];

            $array_obj = $this->get_all( $array_criteria, [], 0, 1 );
            if ( !empty( $array_obj ) ) {
                $saved_obj = reset( $array_obj );
                $this->_id = $saved_obj->_id;
                $this->idema = $saved_obj->idema;
                $this->altitud = $saved_obj->altitud;
                $this->nombre = $saved_obj->nombre;
                $this->lat_lng = $saved_obj->lat_lng;
                $this->created_at = $saved_obj->created_at;
                $this->wind_speed = $saved_obj->wind_speed;
                $this->wind_direction = $saved_obj->wind_direction;
                $this->values = $saved_obj->values;
                $this->recordtime = $saved_obj->recordtime;
            } else {
                $this->created_at = date( 'Y-m-d H:i:s' );
                $this->idema = $api_object['idema'];
                $this->altitud = $api_object[ 'alt' ];
                $this->nombre = $api_object[ 'ubi' ];
                $this->lat_lng = [ $api_object['lat'], $api_object['lon']];
                $this->recordtime = $obj_recordtime_search;
            }

            $value_recordtime = str_replace('T', ' ', substr( $api_object[ 'fint' ], 0, 19 ));
            $this->values[] = ['recordtime' => $value_recordtime, 'wind_speed' => $api_object['vv'], 'wind_direction' => $api_object['dv'] ];


            // Sort values
            $array_sort = [];
            foreach( $this->values as $value ){
                $array_sort[strtotime( $value['recordtime'] )] = $value;
            }
            ksort( $array_sort );
            $this->values = array_values( $array_sort );
            $last_value = end( $this->values );
            $this->wind_speed = $last_value['wind_speed'];
            $this->wind_direction = $last_value['wind_direction'];

            $ret = $this->store();
        }

        return $ret;
    }

    /**
     * Returns array of marker to be shown in map
     * @return array
     * @throws \Exception
     */
    public function get_array_markers( ) {
        ini_set('memory_limit', '1G');
        $array_return = [];
        $array_opts[] = [ 'recordtime', 'eq', date('Y-m' ), 'string' ];

        $array_weather = $this->get_all( $array_opts );
        if( !empty( $array_weather ) ) {
            foreach( $array_weather as $weather ) {
                if( $weather->wind_speed!=='') {
                    $array_return[] = [
                        'id' => $weather->_id,
                        'lat' => $weather->lat_lng[ 0 ],
                        'lng' => $weather->lat_lng[ 1 ],
                        'title' => $weather->wind_speed . ' km/h',
                    ];
                }
            }
        }
        return $array_return;
    }

    /**
     * Sets collection indexes
     * @return bool Resultado de la operacion
     * @throws \Exception
     */
    protected function ensureIndex() {
        $array_indexes = [
            [ 'indicativo' => 1 ],
            [ 'lat_lng' => '2d' ],
        ];
        foreach ( $array_indexes as $index ) {
            $this->_database_controller->ensureIndex( $this->_database_collection, $index );
        }
        return true;
    }

    /**
     * Cofieds object to utf8/iso8859-1
     *
     * @param boolean $to_utf8 if true, converts to utf8, if false, converts to iso8859-1
     *
     * @return void
     */
    public function object_encode_data( $to_utf8 = false ) {
        $callback_function = \ecoaragonapp\common\utils::class . ( $to_utf8 ? '::detect_utf8' : '::detect_iso8859_1' );

        // Dates (format \MongoDate en UTC+0)
        $array_fields_datetime = [ 'updated_at', 'created_at' ];
        foreach ( $array_fields_datetime as $key ) {
            $this->{$key} = \ecoaragonapp\common\databasemongo::datetime_mongodate( $this->{$key}, $to_utf8, false );
        }


        // Common attributes: string
        $array_string = [ 'indicativo', 'nombre' ];
        foreach ( $array_string as $key ) {
            $this->{$key} = (string) \call_user_func( $callback_function, $this->{$key} );
        }
        if ( !empty( $this->lat_lng ) ) {
            $this->lat_lng = [ (float) $this->lat_lng[ 0 ], (float) $this->lat_lng[ 1 ] ];
        }

        // Common attributes: float
        $this->altitud = (float)$this->altitud;
        $this->wind_speed = (float)$this->wind_speed;
        $this->wind_direction = (float)$this->wind_direction;
        $this->lat_lng[0] = (float)$this->lat_lng[0];
        $this->lat_lng[1] = (float)$this->lat_lng[1];

        // Common attributes: booleans
        $array_boolean = [ 'active' ];
        foreach ( $array_boolean as $key ) {
            $this->{$key} = (boolean) $this->{$key};
        }
    }
}
