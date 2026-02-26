<!DOCTYPE html>
<html>
<head>
  <script async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBYw_BYEFATw3z9lu6HsN2Ay6zQcGsbZbM&callback=initMap">
  </script>
  <script>
    function initMap() {
      new google.maps.Map(document.getElementById("map"), {
        center: { lat: 6.3350, lng: 6.8637 },
        zoom: 12,
      });
    }
  </script>
</head>
<body>
  <div id="map" style="height:100vh;"></div>
</body>
</html>