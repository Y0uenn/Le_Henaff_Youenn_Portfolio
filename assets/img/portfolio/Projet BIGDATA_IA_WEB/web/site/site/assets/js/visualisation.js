// Définir le JSON directement dans le fichier
const jsonData = [
    { "Longitude": 3.3000, "Latitude": 49.8600, "Espèce": "Bouleau", "Hauteur totale (m)": 6, "Hauteur du tronc (m)": 2, "Diamètre du tronc (cm)": 37, "Etat de l'arbre": "En place", "Stade de developpement de l'arbre": "jeune", "Type de port": "semi libre", "Type de pied": "gazon", "Remarquable": "non" },
    { "Longitude": 3.3000, "Latitude": 49.8500, "Espèce": "Chêne", "Hauteur totale (m)": 13, "Hauteur du tronc (m)": 7, "Diamètre du tronc (cm)": 160, "Etat de l'arbre": "En place", "Stade de developpement de l'arbre": "adulte", "Type de port": "semi libre", "Type de pied": "gazon", "Remarquable": "non" },
    { "Longitude": 3.3100, "Latitude": 49.8400, "Espèce": "Bouleau", "Hauteur totale (m)": 26, "Hauteur du tronc (m)": 11, "Diamètre du tronc (cm)": 225, "Etat de l'arbre": "En place", "Stade de developpement de l'arbre": "adulte", "Type de port": "semi libre", "Type de pied": "gazon", "Remarquable": "non" },
    
];

// Fonction pour générer le tableau HTML à partir du JSON
function generateTable(data) {
    const tableHeader = document.getElementById('tableHeader');
    const tableBody = document.getElementById('tableBody');

    // Vider les en-têtes et le corps du tableau
    tableHeader.innerHTML = '';
    tableBody.innerHTML = '';

    // Créer les en-têtes de colonne
    const headers = Object.keys(data[0]);

    // Ajouter une colonne pour les boutons au début
    const actionTh = document.createElement('th');
    actionTh.textContent = 'Action';
    tableHeader.appendChild(actionTh);

    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header.charAt(0).toUpperCase() + header.slice(1);
        tableHeader.appendChild(th);
    });

    // Créer les lignes de données
    data.forEach((item, index) => {
        const tr = document.createElement('tr');

        // Ajouter le bouton de sélection au début
        const actionTd = document.createElement('td');
        const button = document.createElement('button');
        button.textContent = 'Sélectionner';
        button.onclick = () => selectRow(index); // Appel de la fonction avec l'index
        actionTd.appendChild(button);
        tr.appendChild(actionTd);

        headers.forEach(header => {
            const td = document.createElement('td');
            td.textContent = item[header];
            tr.appendChild(td);
        });

        tableBody.appendChild(tr);
    });
}

// Fonction pour gérer la sélection de ligne
function selectRow(index) {
    const selectedData = jsonData[index];

    // Par exemple, tu veux stocker plusieurs attributs
    const selectedInfo = {
        height: selectedData["Hauteur totale (m)"],
        species: selectedData["Espèce"],
        // Ajoute d'autres propriétés si nécessaire
    };

    // Stocke les données dans le localStorage sous forme de chaîne JSON
    localStorage.setItem('selectedTreeInfo', JSON.stringify(selectedInfo));

    // Ne rien faire d'autre
}

document.addEventListener("DOMContentLoaded", function () {
    // Extraire les coordonnées et autres informations des arbres
    const latitudes = jsonData.map(item => item.Latitude);
    const longitudes = jsonData.map(item => item.Longitude);
    const texts = jsonData.map(item => item.Espèce);
    
    var data = [{
      type: 'scattermapbox',
      lat: ['49.86', '49.85', '49.84'], // Latitude des points
      lon: ['3.3', '3.3', '3.31'], // Longitude des points
      mode: 'markers',
      marker: {
          size: 14
      },
      text: ['Bouleau', 'Chène', 'Bouleau'] // Étiquettes des points
    }];
    
    // Mise en page de la carte
    var layout = {
      mapbox: {
          center: {lon: 2.3522, lat: 48.8566}, // Centre initial de la carte
          zoom: 2,
          style: 'open-street-map' // Style de la carte
      },
      margin: {r: 0, t: 0, b: 0, l: 0} // Marges de la carte
    };
    
    // Initialisation de la carte avec Plotly
    Plotly.newPlot('map', data, layout, {
      mapboxAccessToken: 'pk.eyJ1IjoibnVuYnVzIiwiYSI6ImNseGppanZjdDFxdGoyanFwaG4wNjVtaG4ifQ.BXNSajM0Roj6pVMoUZc39A'
    });
    });
// Appeler la fonction pour générer le tableau à partir du JSON
generateTable(jsonData);
