<!DOCTYPE html>
<html>
<head>
    <title>Teste Google Maps</title>
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
</head>
<body>

<h2>Mapa de Teste</h2>
<div id="map"></div>

<script>
function initMap() {
    var local = { lat: -23.5505, lng: -46.6333 }; // São Paulo
    var mapa = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: local
    });
    var marcador = new google.maps.Marker({
        position: local,
        map: mapa
    });
}
</script>

<script async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCAYL87be8vkNduKrolC_micH2ADQu0xHI&callback=initMap">
</script>

</body>
</html>
