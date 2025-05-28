<?php
  $apiKey = getenv('Key');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mapa com sua Localização Atual</title>
  <style>
    #map {
      height: 500px;
      width: 100%;
    }
  </style>
</head>
<body>
  <h1>Mapa</h1>
  <div id="map"></div>

  <script>
    function initMap() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function (position) {
            const userLocation = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            const map = new google.maps.Map(document.getElementById("map"), {
              center: userLocation,
              zoom: 15
            });

            const marker = new google.maps.Marker({
              position: userLocation,
              map: map,
              title: "Sua Localização"
            });

            const infoWindow = new google.maps.InfoWindow({
              content: "<p>Você está aqui!</p>"
            });

            marker.addListener("click", () => {
              infoWindow.open(map, marker);
            });
          },
          function () {
            alert("Permissão negada ou erro ao obter localização.");
          }
        );
      } else {
        alert("Geolocalização não suportada neste navegador.");
      }
    }

    window.initMap = initMap;
  </script>

  <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&callback=initMap"></script>
</body>
</html>

