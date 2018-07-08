<html>
  <head>
  <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Krtec</title>
    <style type="text/css">
    	#mapa{
	    	width:100%;
	    	height:100%;	
    	}
    </style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&key=AIzaSyDbkpbgrbuXs2bzZPJk5VdGMEKIUfLtvSY"></script>
	
    <script >
	var map = null;
	var infoWindow = null;
	var id;
	var markers = [];
	
	function MapaOnibus(){
	
		// Criando o mapa
		var latlng = new google.maps.LatLng(-22.904036, -43.445855);
		var myOptions = {
			zoom: 11,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("mapa"), myOptions);
		infoWindow = new google.maps.InfoWindow();
		
		var trafficLayer = new google.maps.TrafficLayer();
		trafficLayer.setMap(map);

		// Propriedades
		google.maps.event.addListener(map, 'click', function() {
			infoWindow.close();
		});
		gerarMarkers();
	}
	
	function gerarMarkers () {
		$.getJSON( "http://dadosabertos.rio.rj.gov.br/apiTransporte/apresentacao/rest/index.cfm/obterTodasPosicoes.json", function() {
		}).done(function( data ) {
			var aux = 0;
			var DateTimeAtual = new Date();
			var DateTimeRange = new Date(DateTimeAtual.getTime() - 2*60000);
			// Ajustes de zero ======================
			if ( (DateTimeRange.getMonth()+1) < 10) { var auxMes = "0"+(DateTimeRange.getMonth()+1); } else { var auxMes = (DateTimeRange.getMonth()+1);}
			if (DateTimeRange.getDate()<10){ var auxDia = "0"+DateTimeRange.getDate(); } else { var auxDia = DateTimeRange.getDate();  } 
			if (DateTimeRange.getHours()<10){ var auxHora = "0"+DateTimeRange.getHours(); } else { var auxHora = DateTimeRange.getHours();  } 
			if (DateTimeRange.getMinutes()<10){ var auxMinuto = "0"+DateTimeRange.getMinutes(); } else { var auxMinuto = DateTimeRange.getMinutes();  } 
			if (DateTimeRange.getSeconds()<10){ var auxSegundos = "0"+DateTimeRange.getSeconds(); } else { var auxSegundos = DateTimeRange.getSeconds();  } 
			// ======================
			var DateTimeRange = (auxMes)+"-"+auxDia+"-"+DateTimeRange.getFullYear()+" "+auxHora+":"+auxMinuto+":"+auxSegundos;
			
			$.each( data.DATA, function( i, item ) {
				  if (
				  aux < 100 && data.DATA[i][0] > DateTimeRange
				) { 
					// console.log(data.DATA[i][3], data.DATA[i][4], data.DATA[i][1], data.DATA[i][5]);
					createMarker(data.DATA[i][3], data.DATA[i][4], data.DATA[i][1], data.DATA[i][5]);
					aux++;
				}
			});
		});
	}
	// Função que cria os marcadores e define o conteúdo de cada Info Window.
	function createMarker(lat, lng, LINHA, VELOCIDADE){
		
		var myLatLng = new google.maps.LatLng(lat, lng);
		var marker = new google.maps.Marker({
			map: map,
			position: myLatLng,
			id: LINHA
		});
		if (VELOCIDADE > 0) {
			marker.setIcon('bus.png');
		} else {
			marker.setIcon('bus-2.png');
		}
	   // Evento que dá instrução à API para estar alerta ao click no marcador.
	   // Define o conteúdo e abre a Info Window.
		google.maps.event.addListener(marker, 'click', function() {
			// Variável que define a estrutura do HTML a inserir na Info Window.
			var iwContent = '<div id="iw_container">' +
			'<div class="iw_title"> Linha: ' + LINHA + '</div> ' +
			VELOCIDADE + ' Km/h</div></div>';
			// O conteúdo da variável iwContent é inserido na Info Window.
			infoWindow.setContent(iwContent);
			// A Info Window é aberta com um click no marcador.
			infoWindow.open(map, marker);
		});
		markers.push(marker);
	}
	
	function UpdateMarkers () {
		$.getJSON( "http://dadosabertos.rio.rj.gov.br/apiTransporte/apresentacao/rest/index.cfm/obterTodasPosicoes.json", function() {
		}).done(function( data ) {
			$.each( data.DATA, function( i, item ) {
				// console.log("Update: "+ data.DATA[i][3], data.DATA[i][4], data.DATA[i][1], data.DATA[i][5]);
				var myLatLng = new google.maps.LatLng(data.DATA[i][3], data.DATA[i][4]);
				UpdateMarkerPosition(myLatLng, data.DATA[i][1], data.DATA[i][5]);
			});
		});
	}
	
	function UpdateMarkerPosition(latLng, id, VELOCIDADE) {
		for(var i=0;i < markers.length; i++){
			if(markers[i].id == id){
				//console.log("Update: "+ latLng, id, VELOCIDADE);
				

    				
				if (VELOCIDADE > 0) {
					markers[i].setIcon('bus.png');
				} else {
					markers[i].setIcon('bus-2.png');
				}

google.maps.event.addListener(markers[i], 'click', function() {
				var iwContent = '<div id="iw_container">' +
				'<div class="iw_title"> Linha: ' + id+ '</div> ' +
				VELOCIDADE + ' Km/h</div></div>';
				infoWindow.setContent(iwContent);
				infoWindow.open(map, markers[i]);
});
markers[i].setPosition(latLng);
    				//var position0= markers[i].getPosition().lat();
    				//var position1= markers[i].getPosition().lng();
    				//console.log(latLng.lat(), latLng.lng(), position0, position1);
    				//transition(latLng, position0, position1, markers[i]);

				break;
			}
		}
	}
	// Sets the map on all markers in the array.
	function DeleteMarkers(map) {
		for (var i = 0; i < markers.length; i++) {
			markers[i].setMap(null);
		}
		markers = [];
	}



        var numDeltas = 1000;
        var delay = 60; //milliseconds
        var i = 0;
        var deltaLat;
        var deltaLng;
    				function transition(result, position0, position1, markers){

            				i = 0;
            				deltaLat = (result.lat() - position0)/numDeltas;
            				deltaLng = (result.lng() - position1)/numDeltas;

            				moveMarker(position0, position1, markers);
        			}
    
        			function moveMarker(position0, position1, markers ){
    				
            				position0 += deltaLat;
            				position1 += deltaLng;
            				var latlng = new google.maps.LatLng(position0, position1);

            				markers.setPosition(latlng);
            				if(i!=numDeltas){
                				i++;

                				setTimeout(moveMarker(position0, position1, markers), delay);
            				}
        			}


    

	setInterval(UpdateMarkers, 5000);
    </script>
    </head>
  	<body onload="MapaOnibus()">	
		<?php //echo "Tem ".$contOnibus." ônibus rodando no momento"; ?>
		<div id="mapa"></div> 
	</body>
</html>