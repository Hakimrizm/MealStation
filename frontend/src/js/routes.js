import HomePage from "../pages/home.f7";
import AboutPage from "../pages/about.f7";
import FormPage from "../pages/form.f7";
import LoginPage from "../pages/login.f7";
import ProfilePage from "../pages/profile.f7";


import DynamicRoutePage from "../pages/dynamic-route.f7";
import RequestAndLoad from "../pages/request-and-load.f7";
import NotFoundPage from "../pages/404.f7";

var routes = [

  {
    path: "/",
    component: HomePage,
  },

  {
    path: "/login",
    component: LoginPage,
  },
  
  {
    path: "/profile/",
    component: ProfilePage
  },

  {
    path: "/about/",
    component: AboutPage,
  },

  {
    path: "/form/",
    component: FormPage,
  },

  {
    path: "(.*)",
    component: NotFoundPage,
  },
];

export default routes;
