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
 * Wind farms data model
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package busstop
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\windfarm;

class model extends \ecoaragonapp\common\model {
    public $_database_collection = 'windfarm';
    public $objectid = -1;
    public $parque = '';
    public $titular = '';
    public $potencia = 0.0;

    /**
     * Updates a glide object from open data api, and creates it if doesn't exist
     *
     * @param $api_object
     *
     * @return bool
     */
    public function update_from_api( $api_object ) {
        /**
         * object(stdClass)#6 (3) {
         * ["type"]=> string(7) "Feature"
         * ["geometry"]=> object(stdClass)#7 (2) { ["type"]=> string(7) "Polygon" ["coordinates"]=> array(1) { [0]=> array(10) { [0]=> array(2) { [0]=> float(637445.9642) [1]=> float(4632463.0812) } [1]=> array(2) { [0]=> float(634456.9504) [1]=> float(4631100.1146) } [2]=> array(2) { [0]=> float(634412.9587) [1]=> float(4631833.1172) } [3]=> array(2) { [0]=> float(634515.9709) [1]=> float(4632925.1152) } [4]=> array(2) { [0]=> float(634712.9861) [1]=> float(4634351.1056) } [5]=> array(2) { [0]=> float(635155.9849) [1]=> float(4634322.0996) } [6]=> array(2) { [0]=> float(635876.9806) [1]=> float(4634012.0916) } [7]=> array(2) { [0]=> float(636553.9775) [1]=> float(4633780.0841) } [8]=> array(2) { [0]=> float(638107.9677) [1]=> float(4633080.0677) } [9]=> array(2) { [0]=> float(637445.9642) [1]=> float(4632463.0812) } } } }
         * ["properties"]=> object(stdClass)#8 (4) { ["objectid"]=> int(1) ["parque"]=> string(16) "Multitecnología" ["titular"]=> string(20) "Renovables ARA-IN SL" ["potencia"]=> int(32) } }
         */
        $this->_id = null;
        $ret = false;

        if( isset( $api_object->properties->objectid)) {
            $array_criteria[] = [ 'objectid', 'eq', $api_object->properties->objectid, 'int' ];

            $array_obj = $this->get_all( $array_criteria, [], 0, 1 );
            if ( !empty( $array_obj ) ) {
                $saved_obj = reset( $array_obj );
                $this->_id = $saved_obj->_id;
            } else {
                $this->created_at = date( 'Y-m-d H:i:s' );
            }

            $array_equivalence = [
                'objectid' => 'objectid',
                'parque' => 'parque',
                'titular' => 'titular',
                'potencia' => 'potencia',
            ];

            foreach ( $array_equivalence as $attr => $tag ) {
                $this->{$attr} = $api_object->properties->$tag;
            }

            $api_geometry = $api_object->geometry->coordinates;
            $count_array = 0;
            while( is_array( $api_geometry ) ){
                $api_geometry = reset( $api_geometry);
                $count_array++;
            }

            $api_geometry = $api_object->geometry->coordinates;
            $count_array-=2;
            for( $x = 0; $x<$count_array; $x++){
                $api_geometry = reset( $api_geometry );
            }
            foreach( $api_geometry as $key => $coords ){
                $coords = \ecoaragonapp\common\utils::OSGB36ToWGS84( $coords[ 1 ], $coords[ 0 ], 30 );
                $api_geometry[$key] = [$coords[1], $coords[0]];
            }

            for ( $x = 0; $x < $count_array; $x++ ) {
                $api_geometry = [$api_geometry];
            }

            $api_object->geometry->coordinates = $api_geometry;
            $this->geometry = json_encode([$api_object->geometry]);

            $ret = $this->store();
        }

        return $ret;
    }

    public function get_json( $date_min, $date_max ) {
        ini_set('memory_limit', '2G');
        $ret = [ 'type' => 'FeatureCollection', 'features' => [] ];
        $array_opts[] = [ 'recordtime', 'gte', $date_min, 'MongoDate' ];
        $array_opts[] = [ 'recordtime', 'lte', $date_max, 'MongoDate' ];

        $array_glides = $this->get_all( $array_opts, [], 0, 2000 );

        if( !empty( $array_glides ) ) {
            foreach( $array_glides as $glide ) {
                $ret['features'][] = ['type' => 'Feature', 'properties' => [ 'description' => $glide->parque ], 'geometry' => json_decode( $glide->geometry) ];
                //echo( $glide->geometry);
            }
        }
        return json_encode( $ret );
    }

    /**
     * Returns installed power
     * @return int
     */
    public function get_installed_power() {
        $array_wind_farm = $this->get_all( [['active', 'eq', true, 'bool']]);
        $return  = 0.0;
        foreach( $array_wind_farm as $wind_farm ) {
            $return+=$wind_farm->potencia;
        }

        return (int)round( $return );
    }

    /**
     * Sets collection indexes
     * @return bool Resultado de la operacion
     * @throws \Exception
     */
    protected function ensureIndex() {
        $array_indexes = [
            [ 'objectid' => 1 ],
            [ 'vertex' => '2dsphere' ],
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

        // Common attributes: integer
        $array_integer = [ 'objectid' ];
        foreach ( $array_integer as $key ) {
            $this->{$key} = (integer) $this->{$key};
        }

        // Common attributes: string
        $array_string = [ 'parque', 'titular' ];
        foreach ( $array_string as $key ) {
            $this->{$key} = (string) \call_user_func( $callback_function, $this->{$key} );
        }
        if ( !empty( $this->lat_lng ) ) {
            $this->lat_lng = [ (float) $this->lat_lng[ 0 ], (float) $this->lat_lng[ 1 ] ];
        }

        // Common attributes: float
        $array_float = [ 'potencia' ];
        foreach ( $array_float as $key ) {
            $this->{$key} = (float) $this->{$key};
        }

        // Common attributes: booleans
        $array_boolean = [ 'active' ];
        foreach ( $array_boolean as $key ) {
            $this->{$key} = (boolean) $this->{$key};
        }
    }

    public function get_feature_array(){
        $coordinates = json_decode( $this->geometry );
        return ['type' => 'Feature', 'properties' => [ 'description' => $this->parque, 'color' => 'red' ], 'geometry' => reset( $coordinates )];
    }
}
