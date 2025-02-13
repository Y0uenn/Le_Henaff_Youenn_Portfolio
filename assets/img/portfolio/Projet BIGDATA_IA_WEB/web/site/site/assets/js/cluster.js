async function addMap(){
	const resp = await getCluster(3);
	const resp_json = await resp.json();
	let latitude = Array();
	let longitude= Array();
	let cluster  = Array();


	for(let el in resp_json){
		latitude.push(resp_json[el]["latitude"]);
		longitude.push(resp_json[el]["longitude"]);
		cluster.push(resp_json[el]["cluster"]);
	}
	let data = [{
		type: "scattermapbox",
		lat: latitude,
		lon: longitude,
		mode: "markers",
		marker: {
			size: 14,
			color: cluster
		}
	}];
	let layout = {
		mapbox: {
			center: {lon: 3.29326, lat: 49.8405},
			zoom: 12,
			style: "open-street-map"
		},
		margin: {r: 0, t: 0, b: 0, l: 0}
	};

	Plotly.newPlot("map", data, layout, {
		mapboxAccessToken: 'pk.eyJ1IjoibnVuYnVzIiwiYSI6ImNseGppanZjdDFxdGoyanFwaG4wNjVtaG4ifQ.BXNSajM0Roj6pVMoUZc39A'
	});
}

addMap();
