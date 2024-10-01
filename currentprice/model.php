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
 * Current price model
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package busstop
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\currentprice;

class model extends \ecoaragonapp\common\model {
    public $_database_collection = 'currentprice';
    public $recordtime = '';
    public $values = [];

    /**
     * Updates a current price object from open data api, and creates it if doesn't exist
     *
     * @param $api_object
     *
     * @return bool
     */
    public function update_from_api( $api_object ) {
        $this->_id = null;
        $ret = false;

        if( isset( $api_object->value)) {

            $recordtime_search = date( 'Y-m-d', strtotime( $api_object->datetime ) );
            $array_criteria[] = [ 'recordtime', 'eq', $recordtime_search, 'string' ];

            $array_obj = $this->get_all( $array_criteria, [], 0, 1 );
            if ( !empty( $array_obj ) ) {
                $saved_obj = reset( $array_obj );
                $this->_id = $saved_obj->_id;
                $this->values = $saved_obj->values;
                $this->recordtime = $saved_obj->recordtime;
                $this->created_at = $saved_obj->created_at;
            } else {
                $this->created_at = date( 'Y-m-d H:i:s' );
                $this->recordtime = $recordtime_search;
            }

            $value_recordtime = date('Y-m-d H:i:s', strtotime($api_object->datetime));
            $updated = false;
            foreach( $this->values as $value ){
                if( $value['recordtime'] == $value_recordtime ) {
                    $value['value'] = $api_object->value;
                    $value['percentage'] = $api_object->percentage;
                    $updated = true;
                }
            }
            if( !$updated){
                $this->values[] = [ 'recordtime' => $value_recordtime, 'value' => $api_object->value, 'percentage' => $api_object->percentage ];
            }

            $ret = $this->store();
        }

        return $ret;
    }

    /**
     * Returns current price
     * @return int
     */
    public function get_current_price() {
        $array_criteria[] = [ 'recordtime', 'eq', gmdate( 'Y-m-d' ), 'string' ];
        $array_current_prices = $this->get_all( $array_criteria, [], 0, 1 );
        $array_current_prices = reset( $array_current_prices);
        $return  = 0.0;
        $date_now = gmdate('Y-m-d H:00:00');

        /** @var $array_current_prices \ecoaragonapp\currentprice\model */
        foreach( $array_current_prices->values as $key => $value ) {
            if( $value['recordtime'] === $date_now ) {
                $return = $value['value'];
                break;
            }
        }

        return ($return/1000) . ' &euro; kW/h';
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
        // Dates (format \MongoDate en UTC+0)
        $array_fields_datetime = [ 'updated_at', 'created_at' ];
        foreach ( $array_fields_datetime as $key ) {
            $this->{$key} = \ecoaragonapp\common\databasemongo::datetime_mongodate( $this->{$key}, $to_utf8, false );
        }
        $this->recordtime = (string)$this->recordtime;
        $this->active = (boolean)$this->active;
        foreach( $this->values as $key => $value ) {
            $this->values[$key]['value'] = (float)$value['value'];
            $this->values[$key]['percentage'] = (float)$value['percentage'];
            $this->values[$key]['recordtime'] = (string)$value['recordtime'];
        }
    }
}
