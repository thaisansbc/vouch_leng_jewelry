<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-content">
		<div class="box-header">
			<h2 class="blue"><i class="fa-fw fa fa-bars"></i><?= lang('view_address'). ($company ? ' ('.$company->name.')' : '') ?></h2>
		</div>
		<?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("customers/view_address" , $attrib); ?>
        <div class="modal-body">
            <p><?= lang('customize_report'); ?></p>

			<div class="col-md-4">
				<div class="form-group">
					<?= lang('customer', 'customer'); ?>
					<?php 
						$customer_opt = "<option value='false'>".lang("select")." ".lang("customer")."</option>";
						if ($customers){
							foreach($customers as $customer){
								$customer_opt .= "<option ".($customer_id == $customer->id ? "selected" : "")." value='".$customer->id."'>".$customer->company."</option>";
							}
						} 
					?>
					<select class="form-control customer" name="customer">
						<?= $customer_opt ?>
					</select>
				</div>
			</div>
			
			<div class="col-md-4">
				<div class="form-group">
					<?= lang('address', 'address'); ?>
					<?php
					$opt_address[''] = lang("select")." ".lang("address");
					if($all_addresses){
						foreach ($all_addresses as $all_addresse) {
							$opt_address[$all_addresse->id] = $all_addresse->address_name;
						}
					}
					echo form_dropdown('address', $opt_address, ($address_id ? $address_id : ""), 'id="address" class="form-control input-tip select" style="width:100%;" ');
					?>
				</div>
			</div>
			
			<div class="form-group">
				<div class="box">
					<div class="box-content">
						<div class="row">
							<div class="clearfix"></div>
							<div class="col-lg-12" style="margin-top:5px;">
								<div id="map" class="mapboxgl-map" style="height: 900px;"></div>
								<pre id="coordinates" class="coordinates"></pre>
							</div>
						</div>
					</div>
				</div>
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
		$('.customer').on('change',function(){
			var customer_id = $(this).val();
			location.replace(site.base_url+"customers/view_address/false/"+customer_id);
		});
		
		$('#address').on('change',function(){
			var address_id = $(this).val();
			var customer_id = $(".customer").find(":selected").val();
			if(address_id){
				location.replace(site.base_url+"customers/view_address/"+address_id+"/"+customer_id);
			}else{
				location.replace(site.base_url+"customers/view_address/false/"+customer_id);
			}
		});
		
		mapboxgl.accessToken = 'pk.eyJ1Ijoia2hlYW5naHVvIiwiYSI6ImNsNzRhMHM1NDFmenAzb29xM2F6MWwyOTEifQ.5uh1uctdWWotAFDP_Uz23w';
		const map = new mapboxgl.Map({
			container: 'map',
			style: 'mapbox://styles/mapbox/streets-v11', 
			center: [<?= ($addresses ? $addresses[0]->longitude : 104.91667) ?>, <?= ($addresses ? $addresses[0]->latitude : 11.55) ?>], 
			zoom: 11
		});
		
		var customer_id = $(".customer").find(":selected").val();
		<?php if($addresses) { foreach($addresses as $addresse){ ?>
			if(customer_id && customer_id != "false"){
				var customer_info = "";
			}else{
				var customer_info = "<?= lang("customer") ?>: <?= $addresse->company ?><br>";
			}
			var google_url = "https://maps.google.com/maps?q=<?= $addresse->latitude ?>,<?= $addresse->longitude ?>"; 
			var label = new mapboxgl.Popup().setHTML('<a target="_blank" href="'+google_url+'">'+customer_info+'<?= lang("address").": ".$addresse->address_name ?><?= ($addresse->contact_person ? "<br>".lang("contact_person").": ".$addresse->contact_person : "").($addresse->address_phone ? "<br>".lang("contact_number").": ".$addresse->address_phone : "") ?></a>').addTo(map);
			new mapboxgl.Marker({ color: '<?= $addresse->color_marker ?>'}).setLngLat([<?= $addresse->longitude ?>, <?= $addresse->latitude ?>]).addTo(map).setPopup(label);	
		<?php } } ?>
		
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
		
		map.addControl(new mapboxgl.FullscreenControl());
		
		var geolocate = new mapboxgl.GeolocateControl({
			positionOptions: {
				enableHighAccuracy: true
			},
			trackUserLocation: true,
			showUserHeading: true
		});

		map.addControl(geolocate);
	});
</script>




