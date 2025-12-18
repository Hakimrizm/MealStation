import HomePage from "../pages/home.f7";
import LoginPage from "../pages/login.f7";
import registerPage from "../pages/register.f7";
import ProfilePage from "../pages/profile.f7";
import riwayatPesananPage from "../pages/riwayat-pesanan.f7";
import RiwayatTransaksiPage from "../pages/riwayat-transaksi.f7";


import DynamicRoutePage from "../pages/dynamic-route.f7";
import RequestAndLoad from "../pages/request-and-load.f7";
import NotFoundPage from "../pages/404.f7";

var routes = [

  {
    path: "/login",
    component: LoginPage,
  },

  {
    path: "/register",
    component: registerPage,
  },

  {
    path: "/",
    component: HomePage,
  },

  {
    path: '/riwayat-pesanan',
    component: riwayatPesananPage,
  },

  {
    path: '/riwayat-transaksi',
    component: RiwayatTransaksiPage,
  },

  
  {
    path: "/profile",
    component: ProfilePage
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