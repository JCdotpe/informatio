<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>Resultados Georeferenciados</title>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
	<meta name="viewport" content="width=device-width">

	<link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>css/bootstrap.spacelab.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>css/bootstrap-responsive.min.css">
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans">
	<link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>css/maps.css">

	<script type="text/javascript" src="<?php echo $ruta ?>js/general/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<script type="text/javascript" src="http://geoxml3.googlecode.com/svn/branches/polys/geoxml3.js"></script>


	<script type="text/javascript">
		
		google.load('visualization', '1', {'packages':['corechart', 'table', 'geomap']});

		var kmlArray = [];
		var maploaded = false;
		var layer;
		var capaKml;
		var table_dpto = '1GpIA0mBHMTame6QFenQeQCazLW4NiLciy3lfLvSZ';
		var table_prov = '1tmpbIqHGt8ymHU_L_qTEOpzcMHTOh3i_zzvWB7ZQ';
		var table_dist = '1Qvu7A-6HA7TCPVTAJ6xgld_3J7UFBr2SIlbQBz4w';

		function checkGoogleMap() {
			
			//specify the target element for our messages
			var msg = document.getElementById('msg');

			if (maploaded == false) {
				//if we dont have a fully loaded map - show the message
				msg.innerHTML = '<b><center><em><font face="Brush Script Std">Cargando Puntos </em></b></font><img src="<?php echo $ruta; ?>img/294.gif"></center>';
				$("#msg").slideDown("fast");

			} else {
				//otherwise, show 'loaded' message then hide the message after a second
				msg.innerHTML = '<b><center><font face="Brush Script Std">Puntos Cargados</b></font><img src="<?php echo $ruta; ?>img/08.gif"></center>';
				$("#msg").slideUp(1950);
			} 
		}

		function initialize() {
			var myOptions = {
				zoom: 6,
				center: new google.maps.LatLng(-10.089204, -69.802552),
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				zoomControl: true,
				zoomControlOptions: {
					style: google.maps.ZoomControlStyle.LARGE,
					position: google.maps.ControlPosition.RIGHT_CENTER
				},
				streetViewControl: true,
				streetViewControlOptions:{
					position: google.maps.ControlPosition.RIGHT_CENTER
				},
				panControl: false,
				panControlOptions: {
					position: google.maps.ControlPosition.RIGHT_CENTER
				},
				scaleControl: false,
				scaleControlOptions: {
					position: google.maps.ControlPosition.RIGHT_CENTER
				},
				mapTypeControl: true,
				mapTypeControlOptions: {
					style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
					position: google.maps.ControlPosition.RIGHT_CENTER
				}
			}

			map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);

			capaKml = new google.maps.FusionTablesLayer({
				query: {
					select: "geometry",
					from: table_dpto,
					where: "Departamento IN ('Ica', 'Piura', 'Tacna', 'Ancash', 'Callao', 'Tumbes', 'Arequipa', 'La Libertad', 'Lambayeque', 'Moquegua')"
				},
				options: {
					styleId: 2,
					templateId: 2
				}
			});
			capaKml.setMap(map);
			
		}

		google.maps.event.addDomListener(window, 'load', initialize);
	</script>
	<script type="text/javascript">
		$(document).ready(function () {
			
			initialize();
			depa_region();

			$('#region').change(function(event){

				if ( layer != undefined ) layer.setMap(null);
				$('#cat').val(-1);
				depa_region();
				if ( $('#region option:selected').attr('id') == 3 ) load_ubigeo('PROV');
			});


			$('#cat').change(function(event){
				load_fusiontable( );
			});


			$('#depa').change(function(event){

				$('#prov').empty();
				$("#prov").append('<option value="0">TODOS...</option>');
				if ( $(this).val() != 0 )  load_ubigeo('PROV');
				$('#dist').html('<option value="0">TODOS...</option>');

				if ( $(this).val() != 0 ){
					cod_ubigeo = $(this).val();
					load_kml_ft( table_dpto, cod_ubigeo );
				}else{
					depa_region();
				}
				
				load_fusiontable();

			});

			$('#prov').change(function(event){

				$('#dist').empty();
				$("#dist").append('<option value="0">TODOS...</option>');
				if ( $(this).val() != 0 ) load_ubigeo('DIST');

				if ( $(this).val() != 0 )
				{
					cod_ubigeo =  $('#depa').val() + $(this).val();
					load_kml_ft( table_prov, cod_ubigeo );
				}else{
					load_kml_ft( table_dpto, $('#depa').val() );
				}
				
				load_fusiontable( );

			});

			$('#dist').change(function(event){

				if ( $(this).val() != 0 )
				{
					cod_ubigeo = $('#depa').val() + $('#prov').val() + $(this).val();
					load_kml_ft( table_dist, cod_ubigeo );
				}else{
					cod_ubigeo = $('#depa').val() + $('#prov').val();
					load_kml_ft( table_prov, cod_ubigeo );
				}

				load_fusiontable( );
			});


		});

		function load_fusiontable( ) {

			if ( layer != undefined ) layer.setMap(null);

			if ( $('#cat').val() != -1 ) {

				maploaded = false;
				checkGoogleMap();

				tabla = $('#region').val();

				condicion = ( $('#cat').val() > 0 ) ? 'Categoria = '+$('#cat').val()+'' : 'Categoria > 0';
				condicion += ( $('#depa').val() != 0 && $('#depa').val() != 15  ) ? " AND CCDD = '"+$('#depa').val()+"'" : '';
				condicion += ( $('#prov').val() != 0 ) ? " AND CCPP = '"+$('#prov').val()+"'" : '';
				condicion += ( $('#dist').val() != 0 ) ? " AND CCDI = '"+$('#dist').val()+"'" : '';

				var interval = setInterval(function(){
						clearInterval(interval);
						
						layer = new google.maps.FusionTablesLayer({
							query: {
								select: " * ",
								from: tabla,
								where: condicion
							},
							options: {
								styleId: 2,
								templateId: 2
							}
						});

						layer.setMap(map);

						maploaded = true;
						setTimeout('checkGoogleMap()',1000);
				}, 3000);

			}
			
		}

		// clean para cmb dinamico
		function clean_kml_dpto(){

			ckb = ($('#ckb_kml').is(':checked')) ? 0 : 1;
			code = parseInt( $('#depa').val() );

			for (var i = 0; i < kmlArray.length; i++) {
				kmlArray[i].nomkml.setMap(null);
			}
			if ( ckb == 1 ){
				kmlArray[code].nomkml.setMap(map);
				map.setCenter(new google.maps.LatLng(kmlArray[code].lat,kmlArray[code].lng));
			}
		}

		function depa_region() {

			$('#depa').empty();
			$('#depa').append('<option value="0">TODOS...</option>');
			$('#prov').html('<option value="0">TODOS...</option>');
			$('#dist').html('<option value="0">TODOS...</option>');

			zomCenter = new google.maps.LatLng(-10.089204, -69.802552);
			zom = 6;

			posicion = $('#region option:selected').attr('id');

			switch ( posicion ){
				case '0':
					condicion = "Departamento IN ('Ica', 'Piura', 'Tacna', 'Ancash', 'Callao', 'Tumbes', 'Arequipa', 'La Libertad', 'Lambayeque', 'Moquegua')";
					break;
				case '1':
					condicion = "Departamento IN ('Puno', 'Cusco', 'Junin', 'Pasco', 'Ayacucho', 'Apurimac', 'Cajamarca', 'Huancavelica', 'Huanuco')";
					break;
				case '2':
					condicion = "Departamento IN ('MadredeDios', 'Ucayali', 'Amazonas', 'Loreto', 'SanMartin')";
					break;
				case '3':
					condicion = "Departamento = 'Lima'";
					$('#depa').empty();
					zomCenter = new google.maps.LatLng(-11.7866731456649,-76.6324097107669);
					zom = 8;
					break;
			}

			if ( capaKml != undefined ) capaKml.setMap(null);
			capaKml = new google.maps.FusionTablesLayer({
				query: {
					select: "geometry",
					from: table_dpto,
					where: condicion
				},
				options: {
					styleId: 2,
					templateId: 2
				}
			});
			capaKml.setMap(map);

			map.setCenter( zomCenter );
			map.setZoom( zom );

			load_ubigeo('DEP');
		}

		function load_ubigeo(name) {

			indice1 = $('#region option:selected').attr('id');
			indice2 = ( indice1 != '3' ) ? $('#depa option:selected').attr('id') : 0;
			indice3 = $('#prov option:selected').attr('id');

			
			$.ajax({
				type: "POST",
				url: "<?php echo $ruta; ?>json/region.json",
				dataType:'json',
				success: function(json_data){

					if ( name == 'DEP' ){
						for (var k in json_data.Region[indice1].Departamento) {
							$("#depa").append('<option id="' + k + '" value="' + json_data.Region[indice1].Departamento[k].CCDD + '" >' + json_data.Region[indice1].Departamento[k].Nombre + '</option>');
						}

					}else if ( name == 'PROV'){
						for (var k in json_data.Region[indice1].Departamento[indice2].PROVINCIA) {
							$("#prov").append('<option id="' + k + '" value="' + json_data.Region[indice1].Departamento[indice2].PROVINCIA[k].CCPP + '" >' +json_data.Region[indice1].Departamento[indice2].PROVINCIA[k].Nombre + '</option>');
						}

					}else if ( name == 'DIST'){
						for (var k in json_data.Region[indice1].Departamento[indice2].PROVINCIA[indice3].DISTRITO) {
							$("#dist").append('<option id="' + k + '" value="' + json_data.Region[indice1].Departamento[indice2].PROVINCIA[indice3].DISTRITO[k].CCDI + '" >' +json_data.Region[indice1].Departamento[indice2].PROVINCIA[indice3].DISTRITO[k].Nombre + '</option>');
						}

					}
				}
			});

		}

		function load_kml_ft( tabla, code ) {

			capaKml.setMap(null);

			capaKml = new google.maps.FusionTablesLayer({
				query: {
					select: "geometry",
					from: tabla,
					where: 'Ubigeo = ' + code,
				},
				options: {
					styleId: 2,
					templateId: 2
				}
			});
			capaKml.setMap(map);


			var queryText = "SELECT Ubigeo, geometry FROM " + tabla + " Where Ubigeo = " + code;
			var encodedQuery = encodeURIComponent(queryText);

			var query = new google.visualization.Query('http://www.google.com/fusiontables/gvizdata?tq=' + queryText);

			query.send(zoomTo);
		}

		function zoomTo(response) {
			if (!response) {
				alert('no response');
				return;
			}
			if (response.isError()) {
				alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
				return;
			} 
			FTresponse = response;

			var kml =  FTresponse.getDataTable().getValue(0,1);
			// create a geoXml3 parser for the click handlers
			var geoXml = new geoXML3.parser({
				map: map,
				zoom: false
			});

			geoXml.parseKmlString("<Placemark>"+kml+"</Placemark>");
			geoXml.docs[0].gpolygons[0].setMap(null);
			map.fitBounds(geoXml.docs[0].gpolygons[0].bounds);
		}

	</script>

</head>
<body>

	<div id="header" class="container-fluid" style="border-top:5px solid #00A1C7 !important; background: url(img/bannerinei.png) no-repeat scroll right 0 #EFF7FA !important;" >
		<a id="logo" href="#">
			<img border="0" style="CURSOR: hand" src="img/inei.jpg" width='60' height='20'>
			</a>
				<div id="titulo">
					<td><b><font FACE="Aharoni"><big><big><big>P</big></big></big>UNTOS <big><big><big>I</big></big></big>NFORMATIO</b></font><td>
				</div>
				<div id="oted">
					<tr> Oficina Técnica de Estadísticas Departamentales - OTED</tr>
				</div>	
	</div>

	<div id="cuerpo" >
		<div id="msg"></div>
		<div class="map_container">
			<div id="map-canvas"></div>
		</div>

		<div class="filtro_map preguntas_sub2 span2">
			<!-- <div class="row-fluid control-group span9">
				<input type="checkbox" name="ckb_kml" id="ckb_kml" onclick="clean_kml_dpto();" > Ocultar KML
			</div> -->

			<div class="row-fluid control-group span9">
				<label class="preguntas_sub2" for="region">REGION</label>
				<div class="controls span">
					<select id="region" class="span12" name="region">
						<option id="0" value="1XKxUzwHeO0mrxwXoIhliYKBtWIj2Q_NHSKZbhnrX">COSTA</option>
						<option id="1" value="1wJ-5f5BeI_n0qH3OeyMxKcO90-8eCNgSFnvtzs1x">SIERRA</option>
						<option id="2" value="1CMrmsdHyYXCx3Jepdnede8pwZZy0qiMvYVqT75aj">SELVA</option>
						<option id="3" value="1YvW7aDv4CXq_hz2japbXTyMvgscLMnCJXz8V7z29">LIMA</option>
					</select>
				</div>
			</div>

			<div id="dv_cat" class="row-fluid control-group span9">
				<label class="preguntas_sub2" for="cat">CATEGORIA</label>
				<div class="controls">
					<select id="cat" class="span12" name="cat">
						<option value="-1">SELECCIONE...</option>
						<option value="0">TODOS</option>
						<option value="1">CENTRO POBLADO</option>
						<option value="2">ESTABLECIMIENTO DE SALUD</option>
						<option value="3">INSTITUCION EDUCATIVA</option>
					</select>
				</div>
			</div>

			<div id="dv_dep" class="row-fluid control-group span9">
				<label class="preguntas_sub2" for="depa">DEPARTAMENTO</label>
				<div class="controls">
					<select id="depa" class="span12" name="depa">
						<!-- ajax -->
					</select>
				</div>
			</div>

			<div id="dv_prov" class="row-fluid control-group span9">
				<label class="preguntas_sub2" for="prov">PROVINCIA</label>
				<div class="controls">
					<select id="prov" class="span12" name="prov">
						<option id="0" value="0">TODOS...</option>
						<!-- ajax -->
					</select>
				</div>
			</div>

			<div id="dv_dist" class="row-fluid control-group span9">
				<label class="preguntas_sub2" for="dist">DISTRITO</label>
				<div class="controls">
					<select id="dist" class="span12" name="dist">
						<option id="0" value="0">TODOS...</option>
						<!-- ajax -->
					</select>
				</div>
			</div>
			
	</div>

	<div id="footer">
		<div class="container-fluid">
			<div class="row-fluid">
				<div id="geo_leyenda" class="span9">
					<img src="img/ama.jpg" width='50' height='40'> 
					<b><small><em>CENTRO POBLADO</em></b>
					<img src="img/rojo.png" width='50' height='40'> 
					<b><em>INSTITUCIÓN EDUCATIVA</em></b>
					<img src="img/pur.png" width='50' height='40'> 
					<b><em>ESTABLECIMIENTO DE SALUD</em></small></b>		
				</div>
				<div id="subtitulo" class="span3">
					<!-- ajax -->
				</div>
			</div>
		</div>
	</div>

	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-47317828-1', 'inei.gob.pe');
	  ga('send', 'pageview');

	</script>
	
</body>
</html>
