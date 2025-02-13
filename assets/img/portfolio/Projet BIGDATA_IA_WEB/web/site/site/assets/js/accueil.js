const crea = document.getElementById("crea_compte");
if(crea !== null){
	crea.addEventListener(
		"click",
		()=>{setCookie("compte", "creation");}
	);
}
const conn = document.getElementById("conn_compte");
if(conn !== null){
	conn.addEventListener(
		"click",
		()=>{setCookie("compte", "connexion");}
	);
}
const add_a = document.getElementById("add_arbre");
if(add_a !== null){
	add_a.addEventListener(
		"click",
		()=>{setCookie("arbre_action", "add");}
	);
}
