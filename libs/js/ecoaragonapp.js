$(document).ready(function () {
});

function check_layers(){
    window[window['map_id']].overlayMapTypes.clear();
    enable_disable_layer('layer_wind_farm', $('#wind_farm_checkbox').prop('checked'));
    enable_disable_layer('layer_solar_farm', $('#solar_farm_checkbox').prop('checked'));
}
function enable_disable_layer(layer_id, show) {

    if (window[layer_id ] !== undefined) {
        if(show) {
            window[ layer_id].setMap(window[map_id]);
        }else{
            window[ layer_id].setMap(null);
        }
    }
}

function show_json_layer(url,type){
    var map_id = window['map_id'];

    if( window['layer_'+type].style===undefined) {
        var zoom = parseInt(window[map_id].getZoom());
        //url += '&sw_lat=' + southWest.lat() + '&sw_lng=' + southWest.lng() + '&ne_lat=' + northEast.lat() + '&ne_lng=' + northEast.lng() + '&zoom=' + zoom;
        window['layer_' + type].setMap(null);
        window['layer_' + type] = null;
        window['layer_' + type] = new google.maps.Data();

        var color = 'grey';
        if (type === 'wind_farm') {
            color = '#074965';
        } else if (type === 'solar_farm') {
            color = 'yellow';
        }

        window['layer_' + type].loadGeoJson(url);
        window['layer_' + type].setStyle({fillColor: color, strokeColor: color, fillOpacity: 0.5, strokeOpacity: 0.5});
    }
    check_layers();
}

function disable_json_layers(){
    var types = ['glides', 'floods', 'collapses'];
    types.forEach(function(v){
        if(window['layer_' + v]!==undefined) {
            window['layer_' + v].setMap(null);
            window['layer_' + v] = null;
            window['layer_' + v] = new google.maps.Data();
        }
    });
}
