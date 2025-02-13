"use strict";

async function fetchRequest(type, url, data=null, moreHeaders={"Cache-Control": "no-cache"}){
	let request = {
		method: type,
		headers: moreHeaders
	}
	if(data !== null){
		if(type === "GET"){
			url += "?"+new URLSearchParams(data);
		}else if(type !== "GET"){
			if(data instanceof FormData){ // is FormData: https://stackoverflow.com/a/46146638
				request.body = new URLSearchParams(data); // send FormData: https://stackoverflow.com/a/46642899
			}else{
				request.body = data;
			}
		}
	}
	const response = await fetch(url, request);
	return await response;
}
