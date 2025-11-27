export function saveToken(t) {
	localStorage.setItem("token", t);
}

export function getToken() {
	return localStorage.getItem("token");
}

export function saveRole(r) {
	localStorage.setItem("role", r);
}

export function getRole() {
	return localStorage.getItem("role");
}

export function logout() {
	localStorage.clear();
}
