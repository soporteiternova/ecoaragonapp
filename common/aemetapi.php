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
 * AEMET API client
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package common
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\common;

class aemetapi {
    public function do_request( $url){
        $curl = curl_init();
        $key = file_get_contents( __DIR__ . '/../config/aemet.key' );

        curl_setopt_array( $curl, [
            CURLOPT_URL => $url . '?api_key='.rtrim($key),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "cache-control: no-cache"
            ],
        ] );

        $response = curl_exec( $curl );
        $err = curl_error( $curl );

        curl_close( $curl );

        if ( $err ) {
            return "Error #:" . $err;
        } else {
            $obj_response = json_decode( $response );
            $data = file_get_contents( $obj_response->datos );
            $metadata = file_get_contents( $obj_response->metadatos );
            $data =  json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $data ), true );
            $metadata =  json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $metadata ), true );

            return ['data' => $data, 'metadata' => $metadata ];
        }
    }
}
