import HomePage from "../pages/home.f7";
import LoginPage from "../pages/login.f7";
import registerPage from "../pages/register.f7";
import ProfilePage from "../pages/profile.f7";
import riwayatPesananPage from "../pages/riwayat-pesanan.f7";
import RiwayatTransaksiPage from "../pages/riwayat-transaksi.f7";
import TenantPage from "../pages/tenant.f7";
import orderDetailPage from "../pages/order-detail.f7";
import keranjangPage from "../pages/keranjang.f7";
import TenantMenus from "../pages/menuTenant/tenant-menus.f7";
import TenantMenuCreate from "../pages/menuTenant/tenant-menu-create.f7";
import TenantMenuEdit from "../pages/menuTenant/tenant-menu-edit.f7";
import ScanQrPage from "../pages/scan-qr.f7";
import PaymentPage from "../pages/payment.f7";
import RiwayatDetailTransaksiPage from "../pages/riwayat-detail-transaksi.f7";
import TenantRiwayat from "../pages/tenant-riwayat.f7";

import DynamicRoutePage from "../pages/dynamic-route.f7";
import RequestAndLoad from "../pages/request-and-load.f7";
import NotFoundPage from "../pages/404.f7";
import { isLoggedIn, getRole } from "./auth";

function guestOnly({ resolve, router }) {
	if (isLoggedIn()) {
		router.navigate("/", { clearPreviousHistory: true });
		return;
	}
	resolve();
}

function authOnly({ resolve, router }) {
	if (!isLoggedIn()) {
		router.navigate("/login", { clearPreviousHistory: true });
		return;
	}
	resolve();
}

function authWithRole({ resolve, router, to }) {
	if (!isLoggedIn()) {
		router.navigate("/login", { clearPreviousHistory: true });
		return;
	}

	const role = getRole();

	// Tenant tidak boleh ke Home user
	if (role === "tenant" && to.path === "/") {
		router.navigate("/tenant", { clearPreviousHistory: true });
		return;
	}

	// User biasa tidak boleh ke halaman tenant
	if (role !== "tenant" && to.path === "/tenant") {
		router.navigate("/", { clearPreviousHistory: true });
		return;
	}

	resolve();
}

var routes = [
	{
		path: "/login",
		component: LoginPage,
		beforeEnter: guestOnly,
	},

	{
		path: "/register",
		component: registerPage,
		beforeEnter: guestOnly,
	},

	{
		path: "/",
		component: HomePage,
		beforeEnter: authWithRole,
	},

	{
		path: "/riwayat-pesanan",
		component: riwayatPesananPage,
		beforeEnter: authWithRole,
	},

	{
		path: "/riwayat-transaksi",
		component: RiwayatTransaksiPage,
		beforeEnter: authWithRole,
	},

	{
		path: "/riwayat-transaksi/:id",
		component: RiwayatDetailTransaksiPage,
		beforeEnter: authWithRole,
	},

	{
		path: "/profile",
		component: ProfilePage,
		beforeEnter: authWithRole,
	},

	{
		path: "/order-detail/:id/",
		component: orderDetailPage,
		beforeEnter: authWithRole,
	},

	{
		path: "/keranjang",
		component: keranjangPage,
		beforeEnter: authWithRole,
	},

	{
		path: "/scan-qr",
		component: ScanQrPage,
		beforeEnter: authOnly,
	},

	{
		path: "/payment/:id",
		component: PaymentPage,
		beforeEnter: authWithRole,
	},

	{
		path: "/tenant",
		component: TenantPage,
		beforeEnter: authWithRole,
	},

	{
		path: "/tenant/menus",
		component: TenantMenus,
		beforeEnter: authWithRole,
	},

	{
		path: "/tenant/menu/create",
		component: TenantMenuCreate,
		beforeEnter: authWithRole,
	},

	{
		path: "/tenant/menu/:id/edit",
		component: TenantMenuEdit,
		beforeEnter: authWithRole,
	},

	{
		path: "/tenant/riwayat",
		component: TenantRiwayat,
		beforeEnter: authWithRole,
	},

	{
		path: "(.*)",
		component: NotFoundPage,
	},

	{
		path: "/dynamic-route/blog/:blogId/post/:postId/",
		component: DynamicRoutePage,
	},

	{
		path: "/request-and-load/user/:userId/",
		async: function ({ router, to, resolve }) {
			// App instance
			var app = router.app;

			// Show Preloader
			app.preloader.show();

			// User ID from request
			var userId = to.params.userId;

			// Simulate Ajax Request
			setTimeout(function () {
				// We got user data from request
				var user = {
					firstName: "Vladimir",
					lastName: "Kharlampidi",
					about: "Hello, i am creator of Framework7! Hope you like it!",
					links: [
						{
							title: "Framework7 Website",
							url: "http://framework7.io",
						},
						{
							title: "Framework7 Forum",
							url: "http://forum.framework7.io",
						},
					],
				};
				// Hide Preloader
				app.preloader.hide();

				// Resolve route to load page
				resolve(
					{
						component: RequestAndLoad,
					},
					{
						props: {
							user: user,
						},
					}
				);
			}, 1000);
		},
	},
];

export default routes;
