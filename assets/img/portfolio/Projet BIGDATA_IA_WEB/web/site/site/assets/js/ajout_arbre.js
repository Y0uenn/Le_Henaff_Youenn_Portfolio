"use strict";


async function make_inputs(parent, nom, type, required, autocomplete, data=null, placeholder=null){
	const label = document.createElement("label");
	 label.for = type;
	 label.innerText = nom;
	const input = document.createElement("input");
	 input.id = nom;
	 input.type = type;
	 input.name = nom;
	 if(data !== null) input.value = data;
	 if(placeholder !== null) input.placeholder = placeholder
	 if(required === true && type !== "checkbox") input.required = true;
	const div = document.createElement("div");
	 if(autocomplete) div.class = "autocomplete";
	 div.appendChild(label);
	 div.appendChild(input);
	parent.appendChild(div);
	if(autocomplete){
		const resp = await getEntry(nom);
		const resp_json = await resp.json();
		let data = Array();
		for(let el in resp_json){
			data.push(resp_json[el][nom]);
		}
		autocomplete_fun(input, data);
	}
}

async function add_to_form(action){
	const form = document.getElementById("formulaire");
	let arbre_id;
	if(action == "sup"){
		arbre_id = getCookie("arbre_id");
		const button = document.createElement("button");
		 button.type = "submit";
		 button.textContent = "Supprimer ?"
		 button.style.backgroundColor = "red";
		 button.style.color = "black";
		form.appendChild(button);
	}else{
		const resp = await getChamps();
		const resp_json = await resp.json();
		const type_map = {
			"int": "number",
			"float": "number",
			"tinyint": "checkbox",
			"varchar": "text"
		}
		let data = null
		console.log(action);
		if(action == "mod"){
			arbre_id = getCookie("arbre_id");
			const arbre_data = await getEntry("arbre", arbre_id);
			data = await arbre_data.json();
			data = data[0]
			console.log(data);
			console.log(data !== null);
			console.log(data["longitude"]);
		}

		for(let key in resp_json){
			if(["id", "age_estim"].includes(key))continue;
			let d = null;
			if(data !== null) d = data[key];
			console.log(d);
			make_inputs(
				form,
				key,
				type_map[resp_json[key][0]],
				!resp_json[key][1],
				resp_json[key][4],
				d
			);
		}
		const button = document.createElement("button");
		 button.type = "submit";
		 button.innerText = "Envoyer";
		form.appendChild(button);
	}
	form.addEventListener(
		"submit",
		(event)=>{
			event.preventDefault();
			const formData = new FormData(form);
			switch(action){
				case "add":
					addArbre(formData, getCookie("token"));
					break;
				case "mod":
					modArbre(arbre_id, formData, getCookie("token"));
					break;
				case "sup":
					supArbre(arbre_id, getCookie("token"));
					break;
				default:
					console.log("Cookie ERROR");
			}
		},
		false,
	);
}

add_to_form(getCookie("arbre_action"));
