export function saveToken(t) {
	localStorage.setItem("token", t);
}

export function getToken() {
	return localStorage.getItem("token");
}

export function saveRole(r) {
	localStorage.setItem("role", r);
}

export function saveUser(r) {
	return localStorage.setItem("user", JSON.stringify(r));
}

export function getRole() {
	return localStorage.getItem("role");
}

export function getUser() {
	const user = localStorage.getItem("user");
	return user ? JSON.parse(user) : null;
}

export function logout() {
	localStorage.clear();
}

export function isLoggedIn() {
	return !!localStorage.getItem("token");
}
