# GeoRiesgos Aragón

La aplicación ECOAragón, permite a los usuarios ver la localización de todos los sistemas de generación de energías renovables, situados en el territorio de la comunidad autónoma.

**Aplicación subvencionada por el Gobierno de Aragón**

## Crondaemon

Se debe configurar la siguiente orden en crontab para poder cargar los datos de la aplicación de forma automática:

`* * * * * php path_to_prorject/common/crondaemon.php`

Donde `path_to_prorject` será el directorio de instalación.

## Configuración

En el directorio `config` se deben crear dos ficheros de texto plano, que incluirán lo siguiente:

* `googlemaps.key` clave de API para GoogleMaps
* `mongodb.key` contraseña para la base de datos MongoDB. La base de datos deberá llamarse `ecoaragonapp` y el usuario que acceda deberá llamarse `ecoaragonapp`.
* `aemet.key` clave para acceso a la API de AEMET open data.
