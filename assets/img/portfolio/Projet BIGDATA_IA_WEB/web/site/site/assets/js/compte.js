"use strict";


async function make_inputs(parent, nom, type, required){
	const label = document.createElement("label");
	 label.for = type;
	 label.innerText = nom;
	const input = document.createElement("input");
	 input.id = nom;
	 input.type = type;
	 input.name = nom;
	 if(required === true) input.required = true;
	const div = document.createElement("div");
	 div.appendChild(label);
	 div.appendChild(input);
	parent.appendChild(div);
}

async function add_to_form(method){
	const resp = await getChamps("utilisateur");
	const resp_json = await resp.json();
	const form = document.getElementById("formulaire");
	const type_map = {
		"int": "number",
		"float": "number",
		"tinyint": "checkbox",
		"varchar": "text"
	}

	for(let key in resp_json){
		if(method == "connexion" && key == "nom") continue;
		let type = type_map[resp_json[key][0]];
		if(key === "mdp") type = "password";
		make_inputs(
			form,
			key,
			type,
			!resp_json[key][1]
		);
		if(method == "creation" && key == "mdp"){
		make_inputs(
			form,
			"conf_m",
			type,
			!resp_json[key][1]
		);
		}
	}
	const button = document.createElement("button");
	 button.type = "submit";
	 button.innerText = "Envoyer";
	form.appendChild(button);
	form.addEventListener(
		"submit",
		async (event)=>{
			event.preventDefault();
			const formData = new FormData(form);
			console.log(formData);
			let tok = "";
			let r;
			if(method == "connexion"){
				r = await conCompte(formData.get("mail"), formData.get("mdp"));
			}else if(method == "creation"){
				r = await addCompte(formData);
			}
			tok = await r.json();
			console.log(tok);
			setCookie("token", tok);
			document.location.href = "index.html";
		},
		false,
	);
}

add_to_form(getCookie("compte"));
