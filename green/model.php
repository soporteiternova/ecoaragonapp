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
 * Renewable generation structure model
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package busstop
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\green;

class model extends \ecoaragonapp\common\model {
    public $_database_collection = 'renewable_structure';
    public $title = '';
    public $color = '';
    public $recordtime = '';
    public $values = [];
    public $last_value = 0.0;

    /**
     * Updates a glide object from open data api, and creates it if doesn't exist
     *
     * @param $api_object
     *
     * @return bool
     */
    public function update_from_api( $api_object_value, $api_object_structure ) {
        $this->_id = null;
        $ret = false;

        if( isset( $api_object_value->value)) {

            $recordtime_search = substr( $api_object_value->datetime,0,4);
            $array_criteria[] = [ 'recordtime', 'eq', $recordtime_search, 'string' ];
            $array_criteria[] = [ 'title', 'eq', $api_object_structure->title, 'string' ];

            $array_obj = $this->get_all( $array_criteria, [], 0, 1 );
            if ( !empty( $array_obj ) ) {
                $saved_obj = reset( $array_obj );
                $this->_id = $saved_obj->_id;
                $this->title = $saved_obj->title;
                $this->color = $saved_obj->color;
                $this->values = $saved_obj->values;
                $this->last_value = $saved_obj->last_value;
                $this->recordtime = $saved_obj->recordtime;
                $this->created_at = $saved_obj->created_at;
            } else {
                $this->created_at = date( 'Y-m-d H:i:s' );
                $this->recordtime = $recordtime_search;
                $this->title = $api_object_structure->title;
                $this->color = $api_object_structure->color;
            }

            $update = true;
            $value_date = substr($api_object_value->datetime,0,10);
            foreach( $this->values as $value){
                if( $value['recordtime'] === $value_date ){
                    $update = false;
                    break;
                }
            }

            if( $update ){
                $this->values[] = [ 'recordtime' => $value_date, 'value' => $api_object_value->value, 'percentage' => $api_object_value->percentage ];

                $last_recordtime = 0;
                foreach ( $this->values as $value ) {
                    $value_recordtime = strtotime( $value['recordtime'] . '  00:00:00' );
                    if ( $value_recordtime > $last_recordtime ) {
                        $last_recordtime = $value_recordtime;
                        $this->last_value = $value['value'];
                    }
                }
            }


            $ret = $this->store();
        }

        return $ret;
    }

    /**
     * Returns current price
     * @return array
     */
    public function get_structure() {
        $array_objs = $this->get_all();
        $return  = [];

        foreach( $array_objs as $obj ) {
            $title = \ecoaragonapp\common\utils::detect_utf8( $obj->title );
            $value = (int)$obj->last_value/1000;
            if( !isset( $return[ $title ] ) ) {
                $return[ $title] = ['year' => $obj->recordtime, 'value' => $value, 'color' => $obj->color];
            } else {
                if( $return[ $title ]['year'] < $obj->recordtime ) {
                    $return[ $title ]['year'] = $obj->recordtime;
                    $return[ $title ]['value'] = $value;
                }
            }
        }

        return $return;
    }

    /**
     * Sets collection indexes
     * @return bool Resultado de la operacion
     * @throws \Exception
     */
    protected function ensureIndex() {
        $array_indexes = [
            [ 'recordtime' => 1 ],
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
        $this->recordtime = (string)$this->recordtime;
        $this->title = (string) \call_user_func( $callback_function, $this->title);
        $this->color = (string)$this->color;
        $this->active = (boolean)$this->active;
        $this->last_value = (float)$this->last_value;

        foreach( $this->values as $key => $value ) {
            $this->values[$key]['value'] = (float)$value['value'];
            $this->values[$key]['percentage'] = (float)$value['percentage'];
            $this->values[$key]['recordtime'] = (string)$value['recordtime'];
        }
    }
}
