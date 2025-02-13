"use strict";

const path = "assets/php/request.php/";

// --- POST --- \\

// Fonction pour ajouter un arbre à la bdd
// Paramètres :
// - formData	: Données du formulaire
// - token		: Jeton d'authentification
function addArbre(formData, token){
	return fetchRequest("POST", path+"bdd/", formData, {
		"Content-Type": "application/x-www-form-urlencoded",
		"Authorization_": "Bearer "+token
	});
}

// Fonction pour créer un compte utilisateur
// Paramètres :
// - formData	: Données du formulaire
//		mail	Mail
//		nom		Nom
//		mdp		Mot de passe
//		conf_m	Confirmation du mot de passe
function addCompte(formData){
	return fetchRequest("POST", path+"account/", formData);
}
// --- POST --- \\


// --- GET --- \\

// Fonction pour obtenir une carte des arbres colorés en fonction
//	de leur cluster d'appartenance
// - start		: Page du tableau
// - amount		: Nombre de lignes
// - sort		: Colonne par laquelle trier
// - ordre		: Croissant / Décroissant
// - filtre		: Objet contenant les valeurs qu'on veut garder
function getTableau(start=1, amount=10, sort="id", ordre="croissant", filtre="all"){
	const data = {
		"start": start,
		"amount": amount,
		"sort": sort,
		"ordre": ordre
	};
	if(filtre !== "all"){
		data["filtre"] = new URLSearchParams(filtre);
	}else{
		data["filtre"] = filtre;
	}
	return fetchRequest("GET", path+"arbres/tableau", data, {"Content-Type": "application/x-www-form-urlencoded"});
}

// Fonction pour obtenir une carte des arbres
function getCarte(){
	return fetchRequest("GET", path+"arbres/carte");
}

// Fonction pour obtenir une carte des arbres colorés en fonction
//	de leur cluster d'appartenance
// - nb_cluster	: Nombre de cluster à former
// - model		: Modèle à utiliser pour former les clusters
function getCluster(nb_cluster=2, model=null){
	const data = {
		"nb_clust": nb_cluster
	};
	if(model !== null) data["model"] = model;
	return fetchRequest("GET", path+"arbres/cluster", data);
}

// Fonction pour obtenir une prédiction de l'âge
// - id			: ID de l'arbre à prédire
// - model		: Model à utiliser pour faire la prédiction
function getAge(id, model=null){
	return getPrediction(id, "age", model);
}

// Fonction générique pour obtenir une prédiction
//	du risque de déracinement
// - id			: ID de l'arbre à prédire
// - model		: Model à utiliser pour faire la prédiction
function getDera(id, model=null){
	return getPrediction(id, "dera", model);
}

// Fonction générique pour obtenir une prédiction
// - id			: ID de l'arbre à prédire
// - type		: Type de prédiction
// - model		: Model à utiliser pour faire la prédiction
function getPrediction(id, type, model=null){ // -> getAge / getRisque
	const data = {
		"type": type
	};
	if(model !== null) data["model"] = model;
	return fetchRequest("GET", path+"predict/"+id, data, {"Content-Type": "application/x-www-form-urlencoded"});
}

// Fonction pour se connecter à un compte
// Paramètres :
// - mail		: Mail
// - mdp		: Mot de passe
function conCompte(mail, mdp){
	return fetchRequest("GET", path+"account/", null, {"Authorization_": "Basic "+mail+":"+mdp});
}

// Fonction pour obtenir les champs d'une table
// Paramètres :
// - table		: Table dont on veut les champs
function getChamps(table="arbre"){
	return fetchRequest("GET", path+"champs/"+table);
}

// Fonction pour obtenir les données d'une table
// Paramètres :
// - entry		: Table dont on veut les données
// - id			: Identifiant si on veut seulement 1 élément
function getEntry(entry, id=null){
	let data;
	if(id !== null){
		data = {"id": id};
	}else{
		data = null;
	}
	return fetchRequest("GET", path+"entry/"+entry, data, {"Content-Type": "application/x-www-form-urlencoded"});
}
// --- GET --- \\


// --- PUT --- \\

// Fonction pour modifier un arbre dans la bdd
// Paramètres :
// - id			: ID de l'arbre à modifier
// - formData	: Données du formulaire
// - token		: Jeton d'authentification
function modArbre(id, formData, token){
	return fetchRequest("PUT", path+"bdd/"+id, formData, {
		"Content-Type": "application/x-www-form-urlencoded",
		"Authorization_": "Bearer "+token
	});
}

// Fonction pour modifier un compte dans la bdd
// Paramètres :
// - mail		: Mail
// - nom		: Mot de passe
// - token		: Jeton d'authentification
function modCompte(mail, nom, token){
	return fetchRequest("PUT", path+"account/"+mail, {"nom":nom}, {
		"Content-Type": "application/x-www-form-urlencoded",
		"Authorization_": "Bearer "+token
	});
}
// --- PUT --- \\


// --- DELETE --- \\

// Fonction pour supprimer un arbre de la bdd
// Paramètres :
// - id			: ID de l'arbre à modifier
// - token		: Jeton d'authentification
function supArbre(id, token){
	return fetchRequest("DELETE", path+"bdd/"+id, null, {
		"Content-Type": "application/x-www-form-urlencoded",
		"Authorization_": "Bearer "+token
	});
}
// --- DELETE --- \\


// --- COOKIES --- \\
function setCookie(cname, cvalue){ // https://www.w3schools.com/js/js_cookies.asp
	document.cookie = cname+"="+cvalue+";path=/";
}

function getCookie(cname){
	let name = cname+"=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let ca = decodedCookie.split(';');
	for(let i=0; i<ca.length; i++){
		let c = ca[i];
		while(c.charAt(0) == ' '){
			c = c.substring(1);
		}
		if(c.indexOf(name) == 0){
			return c.substring(name.length, c.length);
		}
	}
	return "";
}
// --- COOKIES --- \\
