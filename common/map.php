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
 * Map generation function
 * @author ITERNOVA (info@iternova.net)
 * @version 1.0.0 - 20240904
 * @package common
 * @copyright 2024 ITERNOVA SL
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ecoaragonapp\common;

class map {

    /**
     * Returns google api key stored in config file
     * @return string
     */
    private static function google_key() {
        $file = __DIR__ . '/../config/googlemaps.key';
        return file_exists( __DIR__ . '/../config/googlemaps.key') ? trim( file_get_contents( $file ) ) : '';
    }

    /**
     * Generates a map with given markers
     *
     * @param array $array_markers marker array to be represented in map
     * @param int $sizex Ancho del mapa
     * @param int $sizey Alto del mapa
     *
     * @return string
     */
    public static function create_map( $array_markers, $sizex = 600, $sizey = 400, $set_center_user = false, $zoom = 8 ) {
        $rand = rand();

        // JS googlemaps
        $str = '<script>
                      (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
                        key: "' . self::google_key() . '",
                        v: "weekly",
                      });
                </script>';

        // Capas
        /*$arrayopts[ 'wms_layers' ][] = [
            'title' => 'Carreteras',
            'id' => 'roadslayer',
            'src' => 'https://servicios.idee.es/wms-inspire/transportes',
            'layers' => 'TN.RoadTransportNetwork.RoadLink',
            'index' => 1,
            'active' => true
        ];
        $arrayopts[ 'wms_layers' ][] = [
            'title' => 'Ferrocarril',
            'id' => 'railwayslayer',
            'src' => 'https://servicios.idee.es/wms-inspire/transportes',
            'layers' => 'TN.RailTransportNetwork.RailwayLink',
            'index' => 2,
            'active' => true
        ];*/
        $arrayopts=[];
        [ $str_layers, $map_layers ] = self::get_layers_config_wms( $rand, $arrayopts );

        $str_markers = '';

        if( is_array( $array_markers)){
            $str_markers.= 'const parser = new DOMParser();';
            foreach( $array_markers as $marker){
                $str_markers .= 'const pin_marker_' . $marker['id'] . ' = \'<svg xmlns="http://www.w3.org/2000/svg" width="60" height="40" viewBox="0 0 100 20" style="border:1px solid;background-color: #aaaaaa"><text font-weight="bold" font-size="2em" x="5" y="5" dominant-baseline="middle">' . $marker['title'] . '</text>></svg>\';
                                 const marker_' . $marker['id'].' = new google.maps.marker.AdvancedMarkerElement({
                                 
                                    map: window["map' . $rand . '"],
                                    position: {"lat": ' . $marker['lat'] . ', "lng": ' . $marker['lng'] . '},
                                    title: "' . $marker['title'] . '",
                                    content: parser.parseFromString(pin_marker_' . $marker[ 'id' ] . ', "image/svg+xml").documentElement,
                                });';
            }
        }

        // Generamos el mapa
        $str .= '<script type="text/javascript">
                window["map' . $rand . '"]=null;

                async function initialize' . $rand . '() {
                    const { Map } = await google.maps.importLibrary( "maps" );
                    const { Marker } = await google.maps.importLibrary( "marker" );
                    let center = { lat: 41.65606, lng: -0.87734 };
                    let zoom = 6;
                    let center_user = ' . json_encode($set_center_user ) . ';
                    const url_json_aragon = "' . utils::get_server_url() . '/common/files/comunidades.geojson";
                    const ARAGON_BOUNDS= {
                          north: 42.93,
                          south: 39.85,
                          west: -2.17,
                          east: 0.77,
                    };
                    
                    navigator.geolocation.getCurrentPosition(function (position) {
                        center = {lat: position.coords.latitude, lng: position.coords.longitude};
                        zoom = 12;
                    });
                     
                    window["map' . $rand . '"] = new Map(document.getElementById("ecoaragonapp_map' . $rand . '") ,{
                                zoom: zoom,
                                center: center,
                                backgroundColor: "hsla( 0, 0 %, 0 %, 0 )",
                                restriction: {
                                    latLngBounds: ARAGON_BOUNDS,
                                    strictBounds: false
                                },
                                mapId: "ecoaragonapp_map' . $rand . '",
                            });     
                    window["map_id"] = "map' . $rand . '";
                    window["layer_wind_farm"] = new google.maps.Data();
                    window["layer_solar_farm"] = new google.maps.Data();
                    $("#wind_farm_checkbox").trigger( "change" );
                    $("#solar_farm_checkbox").trigger( "change" );
                    
                    ' . $str_layers . '                     
                    window["map' . $rand . '"].data.loadGeoJson(url_json_aragon);
                    ' . $str_markers . '
                }
                
                initialize' . $rand . '();
       </script>';

        $str .= '<div class="ecoaragonapp_map" id="ecoaragonapp_map' . $rand . '" style="height:' . $sizey . 'px;width:100%;"></div>';

        return $str;
    }

    /**
     * Genera configuracion de capas de tipo WMS
     *
     * @param string $rand Id. de div de mapa
     * @param array $arrayopts Array de configuracion de mapas
     *
     * @return array [$arrayopts, $str_layers, $map_layers] actualizados
     */
    private static function get_layers_config_wms( $rand, $arrayopts ) {
        $str_layers = '';
        $map_layers = [];
        if ( isset( $arrayopts[ 'wms_layers' ] ) && is_array( $arrayopts[ 'wms_layers' ] ) && !empty( $arrayopts[ 'wms_layers' ] ) ) {
            $index = 0;
            foreach ( $arrayopts[ 'wms_layers' ] as $layer ) {
                if ( isset( $layer[ 'title' ], $layer[ 'id' ], $layer[ 'src' ] ) && ( isset( $layer[ 'layer' ] ) || isset( $layer[ 'layers' ] ) ) ) {
                    $layer_id = $layer[ 'id' ] . '_' . $rand . '_layer';

                    $array_config_default_values = [
                        'version' => '1.3.0',
                        'request' => 'GetMap',
                        'service' => 'WMS',
                        'format' => 'image/png',
                        'projection' => 'EPSG:4326',
                        'layer' => '',
                        'layers' => '',
                        'width' => '256',
                        'height' => '256',
                        'style' => '',
                        'geojson_mode' => true,
                        'opacity' => 1.0,
                        'active' => true,
                    ];
                    $layer = utils::initialize_array_config( $layer, $array_config_default_values );
                    $str_layers .= 'var ' . $layer_id . '_wmsOptions = ' . self::get_wms_config_str( $layer, $rand ) . ';';

                    $str_layers .= 'window["' . $layer['id'] . '"] = new google.maps.ImageMapType(' . $layer_id . '_wmsOptions);';
                    $str_layers .= 'window["' . $layer[ 'id' ] . '"].setOpacity(' . $layer[ 'opacity' ] . ');';
                    if ( $layer[ 'active' ] ) {
                        $str_layers .= 'map' . $rand . '.overlayMapTypes.setAt(' . $layer['index'].',window["' . $layer[ 'id' ] . '"]);';
                    }

                    $index += 2; // Reservamos el siguiente indice para capas temporales
                    $map_layers[ $layer_id ] = $layer;

                }
            }

        }

        return [ $str_layers, $map_layers ];
    }

    /**
     * @param array $layer Array configuracion de capa WMS
     * @param string $div_id Id. de div de mapa
     * @param string $str_time_param Parametro para datos variantes en el tiempo
     *
     * @return string
     */
    public static function get_wms_config_str( $layer, $div_id, $str_time_param = '' ) {
        $array_config_default_values = [
            'version' => '1.3.0',
            'request' => 'GetMap',
            'service' => 'WMS',
            'format' => 'image/png',
            'projection' => 'EPSG:4326',
            'width' => '256',
            'height' => '256',
            'style' => '',
            'geojson_mode' => true,
            'opacity' => 1.0,
            'layers' => '',
            'layer' => '',
        ];
        $layer = utils::initialize_array_config( $layer, $array_config_default_values );
        $layer_wms_with_get_attributes = strpos( $layer[ 'src' ], '?' );
        return '{
                getTileUrl: function(coord, zoom) {
                  var projection = map' . $div_id . '.getProjection();
                  var zpow = Math.pow(2, zoom);
                  var ul = new google.maps.Point(coord.x * 256.0 / zpow, (coord.y + 1) * 256.0 / zpow);
                  var lr = new google.maps.Point((coord.x + 1) * 256.0 / zpow, (coord.y) * 256.0 / zpow);
                  var ulw = projection.fromPointToLatLng(ul);
                  var lrw = projection.fromPointToLatLng(lr);
                  var bbox = ' . ( !$layer[ 'geojson_mode' ] ? 'ulw.lng() + "," + ulw.lat() + "," + lrw.lng() + "," + lrw.lat()' : 'ulw.lat() + "," + ulw.lng() + "," + lrw.lat() + "," + lrw.lng()' ) . ';
                  var url = "' . $layer[ 'src' ] . ( $layer_wms_with_get_attributes ? '&' : '?' ) .
               'SERVICE=' . $layer[ 'service' ] .
               '&VERSION=' . $layer[ 'version' ] .
               '&REQUEST=' . $layer[ 'request' ] .
               '&BBOX=" + bbox + "&WIDTH=' . $layer[ 'width' ] .
               ( $layer[ 'layers' ] !== '' ? '&LAYERS=' . $layer[ 'layers' ] : '&LAYER=' . $layer[ 'layer' ] ) .
               '&STYLES=' . $layer[ 'style' ] .
               ( $layer[ 'geojson_mode' ] ? '&CRS=' : '&SRS=' ) . $layer[ 'projection' ] .
               '&HEIGHT=' . $layer[ 'height' ] .
               '&FORMAT=' . $layer[ 'format' ] .
               '&TRANSPARENT=true' . $str_time_param . '";
                  return url;
              },
              tileSize: new google.maps.Size(256, 256)
              }';
    }

    /**
     * Controlador de acciones
     * @return void
     */
    public function actions() {
        $action = controller::get( 'action' );
        $ret = '';
        switch ($action){
            default:
        }

        return $ret;
    }

}
