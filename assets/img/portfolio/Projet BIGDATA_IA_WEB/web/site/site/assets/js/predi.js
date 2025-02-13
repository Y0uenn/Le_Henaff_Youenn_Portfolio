// Fonction pour afficher la variable sur la page HTML
async function displayVariable(id, type){
	const resp = await getPrediction(id, type); // Exemple de valeur pour l'âge de l'arbre
	const resp_json = await resp.json(); // On récupère le JSON
	let treeVal = JSON.parse(resp_json)
	let content = "ERROR"
	if(type == "age"){
		treeVal = treeVal[0]['age_predicted'];
		content = `La prédiction de l'âge de l'abre est : ${treeVal} ans`;
	}else if(type = "dera"){
		treeVal = treeVal['risque'];
		content = `La probabilité de déracinement est: ${treeVal}`;
	}
	const displayElement = document.getElementById('variableDisplay');
	displayElement.textContent = content;
}

async function addMap(id){
	const resp = await getEntry("arbre", id);
	const resp_json = await resp.json();
	const arbre = resp_json[0];

	let data = [{
		type: "scattermapbox",
		lat: [arbre.latitude],
		lon: [arbre.longitude],
		mode: "markers",
		marker: {
			size: 14,
			color: "red",
		},
		text: [arbre.fk_nomtech] // Étiquettes des points
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

const id = getCookie("arbre_id");
const type = getCookie("predi");
// Appel de la fonction pour afficher la variable
displayVariable(id, type);
addMap(id);

const switch_predi = document.getElementById("switch_predi");
let content = "ERROR"
let cookie_content = "";
if(type === "age"){
	content = "Prédire le déracinement";
	cookie_content = "dera";
}else{
	content = "Prédire l'âge";
	cookie_content = "age";
}
switch_predi.children[0].textContent = content;
switch_predi.addEventListener(
	"click",
	()=>{
		setCookie("predi", cookie_content);
	}
);
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les données du localStorage
    const selectedTreeInfo = localStorage.getItem('selectedTreeInfo');

    // Vérifie si des données ont été trouvées
    if (selectedTreeInfo) {
        // Parse les données JSON
        const treeInfo = JSON.parse(selectedTreeInfo);

        // Récupère l'élément où tu veux afficher les informations
        const displayDiv = document.getElementById('variableDisplay');

        // Affichage de l'âge
        displayDiv.textContent = `L'âge Prédit est : `;

        // Exemple de logique conditionnelle pour afficher une autre valeur
        let additionalMessage = '';

        if (treeInfo.height < 7) {
            additionalMessage = ' 15 ans';
        } else if (treeInfo.height < 14) {
            additionalMessage = '50 ans';
        } else {
            additionalMessage = ' 80 ans';
        }

        // Ajouter le message additionnel au displayDiv
        displayDiv.textContent += ` ${additionalMessage}`;
    } else {
        // Affiche un message si aucune donnée n'est sélectionnée
        document.getElementById('variableDisplay').textContent = 'Aucune donnée sélectionnée.';
    }
});




