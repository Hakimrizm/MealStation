const API = "http://your-laravel-domain/api";

export async function api(endpoint, method = "GET", body = null) {
	const token = localStorage.getItem("token");

	const options = {
		method,
		headers: {
			"Content-Type": "application/json",
			...(token ? { Authorization: "Bearer " + token } : {}),
		},
	};

	if (body) options.body = JSON.stringify(body);

	const res = await fetch(API + endpoint, options);
	return res.json();
}
