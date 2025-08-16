<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_address') . " (" . $company->name . ")"; ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
				echo admin_form_open_multipart("customers/add_address/" . $company->id, $attrib); 
                ?>
                <div class="row">
                    <div class="col-lg-12">
						<div class="col-md-3">
							<div class="form-group">
								<?= lang('name', 'name'); ?>
								<input name="name" type="text"  class="form-control input-sm" id="name" required="required" />
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<?= lang('phone', 'phone'); ?>
								<?= form_input('phone', '', 'class="form-control" id="phone" required="required"'); ?>
							</div>
						</div>
						<div class="col-md-3 hide">
							<div class="form-group">
								<?= lang('contact_person', 'contact_person'); ?>
								<?= form_input('contact_person', '', 'class="form-control" id="contact_person"'); ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
				                <?= lang('address', 'address'); ?>
				                <?= form_input('line1', '', 'class="form-control" id="address" required="required"'); ?>
				            </div>
				        </div>
				        
				        <div class="col-md-3">
				            <div class="form-group">
				                <?= lang('line2', 'line2'); ?>
				                <?= form_input('line2', '', 'class="form-control" id="line2"'); ?>
				            </div>
						</div>

						
						<div class="col-md-3">
							<div class="form-group">
				                <?= lang('city', 'city'); ?>
				                <?= form_input('city', '', 'class="form-control" '); ?>
				            </div>
				        </div>
				        <div class="col-md-3">
				            <div class="form-group">
				                <?= lang('postal_code', 'postal_code'); ?>
				                <?= form_input('postal_code', '', 'class="form-control" id="postal_code"'); ?>
				            </div>
				        </div>

						<div class="col-md-3">
							<div class="form-group">
								<?= lang('kilometer', 'kilometer'); ?>
								<input name="kilometer" type="text"  class="form-control input-sm" id="kilometer" />
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<?= lang('color', 'color_marker'); ?>
								<select class="form-control" id="color_marker" name="color_marker">
									<option value="#e80000"><?= lang("red") ?></option>
									<option value="#00bd58"><?= lang("green") ?></option>
									<option value="#0404d1"><?= lang("blue") ?></option>
									<option value="#f478ff"><?= lang("pink") ?></option>
									<option value="#fff82e"><?= lang("yellow") ?></option>
									<option value="#5c0596"><?= lang("purple") ?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-12">
                            <div class="form-group">
								<?php echo form_submit('add_address', $this->lang->line("submit"), 'id="add_address" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
							</div>
                        </div>
						<div class="col-md-12">
							<div class="form-group">
								<div id="map" class="mapboxgl-map" style="height: 900px;"></div>
								<pre id="coordinates" class="coordinates"></pre>
							</div>
						</div>	
					</div>
				</div>
				<input type="hidden" name="longitude" class="form-control input-sm" id="longitude" value="104.91667" />
				<input type="hidden" name="latitude" class="form-control input-sm" id="latitude" value="11.55" />
				
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script src="https://api.mapbox.com/mapbox-gl-js/v2.9.2/mapbox-gl.js"></script>
<script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css" type="text/css">
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v2.9.2/mapbox-gl.css">
<script>
	$(document).ready(function () {
		
		$(document).on("change", '#color_marker', function () {
			var longitude = $("#longitude").val();
			var latitude = $("#latitude").val();
			marker.remove();
			marker = new mapboxgl.Marker({
				draggable: true,
				color: $('#color_marker').val()
			}).setLngLat([longitude,latitude]).addTo(map);
			marker.on('dragend', onDragEnd);
		});
		
		
		mapboxgl.accessToken = 'pk.eyJ1Ijoia2hlYW5naHVvIiwiYSI6ImNsNzRhMHM1NDFmenAzb29xM2F6MWwyOTEifQ.5uh1uctdWWotAFDP_Uz23w';
		const map = new mapboxgl.Map({
			container: 'map',
			style: 'mapbox://styles/mapbox/streets-v11', 
			center: [104.91667, 11.55], 
			zoom: 12
		});
		
		const coordinatesGeocoder = function (query) {
			const matches = query.match(
				/^[ ]*(?:Lat: )?(-?\d+\.?\d*)[, ]+(?:Lng: )?(-?\d+\.?\d*)[ ]*$/i
			);
			if (!matches) {
				return null;
			}
			 
			function coordinateFeature(lng, lat) {
				return {
					center: [lng, lat],
					geometry: {
					type: 'Point',
					coordinates: [lng, lat]
					},
					place_name: 'Lat: ' + lat + ' Lng: ' + lng,
					place_type: ['coordinate'],
					properties: {},
					type: 'Feature'
				};
			}
			 
			const coord1 = Number(matches[1]);
			const coord2 = Number(matches[2]);
			const geocodes = [];
			 
			if (coord1 < -90 || coord1 > 90) {
				geocodes.push(coordinateFeature(coord1, coord2));
			}
			 
			if (coord2 < -90 || coord2 > 90) {
				geocodes.push(coordinateFeature(coord2, coord1));
			}
			 
			if (geocodes.length === 0) {
				geocodes.push(coordinateFeature(coord1, coord2));
				geocodes.push(coordinateFeature(coord2, coord1));
			}
			 
			return geocodes;
		};

		
		const geocoder = new MapboxGeocoder({
			accessToken: mapboxgl.accessToken,
			localGeocoder: coordinatesGeocoder,
			zoom: 12,
			placeholder: '<?= lang("search") ?>',
			mapboxgl: mapboxgl,
			reverseGeocode: true,
			marker: {
				color: 'orange',
				draggable: true,
			}
		});
		map.addControl(geocoder);
		
		geocoder.on('result', function(e) {
			geocoder.clear();
			marker.remove();
			marker = new mapboxgl.Marker({
				draggable: true,
				color: $('#color_marker').val()
			}).setLngLat([e.result.center[0], e.result.center[1]]).addTo(map);
			onDragEnd();
			marker.on('dragend', onDragEnd);
		});
		
		var marker = new mapboxgl.Marker({
			draggable: true,
			color: $('#color_marker').val()
		}).setLngLat([104.91667, 11.55]).addTo(map);
		
		function onDragEnd() {
			const lngLat = marker.getLngLat();
			coordinates.style.display = 'block';
			coordinates.innerHTML = `Longitude: ${lngLat.lng}<br />Latitude: ${lngLat.lat}`;
			$("#longitude").val(lngLat.lng);
			$("#latitude").val(lngLat.lat);
		}
		marker.on('dragend', onDragEnd);
		
		map.addControl(new mapboxgl.FullscreenControl());

		var geolocate = new mapboxgl.GeolocateControl({
			positionOptions: {
				enableHighAccuracy: true
			},
			trackUserLocation: true,
			showUserHeading: true
		});
		
		map.addControl(geolocate);
		geolocate.on('geolocate', function(e) {
			var longitude = e.coords.longitude;
			var latitude = e.coords.latitude
			marker.remove();
			marker = new mapboxgl.Marker({
				draggable: true,
				color: $('#color_marker').val()
			}).setLngLat([longitude,latitude]).addTo(map);
			onDragEnd();
			marker.on('dragend', onDragEnd);
		});
		
	});
</script>

<style>
	.coordinates {
		background: rgba(0, 0, 0, 0.5);
		color: #fff;
		position: absolute;
		bottom: 40px;
		left: 10px;
		padding: 5px 10px;
		margin: 0;
		font-size: 11px;
		line-height: 18px;
		border-radius: 3px;
		display: none;
	}
</style>
