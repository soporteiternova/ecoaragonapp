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
 * Solar farms data model
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package busstop
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\solarfarm;

class model extends \ecoaragonapp\common\model {
    public $_database_collection = 'solarfarm';
    public $objectid = -1;
    public $parque = '';
    public $titular = '';
    public $potencia_placas = 0.0;
    public $potencia_inversor = 0.0;

    /**
     * Updates a solar farm object from open data api, and creates it if doesn't exist
     *
     * @param $api_object
     *
     * @return bool
     */
    public function update_from_api( $api_object ) {
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
                'parque' => 'nombre',
                'titular' => 'promotor',
                'potencia_placas' => 'potencia_placas_mwp',
                'potencia_inversor' => 'potencia_inversior_mwp',
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

        $array_solar_farms = $this->get_all( $array_opts, [], 0, 2000 );

        if( !empty( $array_solar_farms ) ) {
            foreach( $array_solar_farms as $solar_farm ) {
                $ret['features'][] = ['type' => 'Feature', 'properties' => [ 'description' => $solar_farm->parque ], 'geometry' => json_decode( $solar_farm->geometry) ];
            }
        }
        return json_encode( $ret );
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
        $array_float = [ 'potencia_placas', 'potencia_inversor' ];
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

    /**
     * Returns installed power
     * @return int
     */
    public function get_installed_power() {
        $array_solar_farm = $this->get_all( [ [ 'active', 'eq', true, 'bool' ] ] );
        $return = 0.0;
        foreach ( $array_solar_farm as $solar_farm ) {
            $return += $solar_farm->potencia_placas;
        }

        return (int) round( $return );
    }
}
