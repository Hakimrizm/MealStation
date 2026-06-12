import $ from "dom7";
import Framework7, { getDevice } from "framework7/bundle";

// Import F7 Styles
import "framework7/css/bundle";
import "../css/icons.css";
import "../css/app.css";

// Import Store, Routes, Cordova, dan App
import store from "./store.js";
import routes from "./routes.js";
import cordovaApp from "./cordova-app.js";
import App from "../app.f7";

var device = getDevice();

var app = new Framework7({
  name: "kantin", // App name
  theme: "auto", // Automatic theme detection

  el: "#app", // App root element
  component: App, // App main component
  // App store
  store: store,
  // App routes
  routes: routes,

  // Register service worker (only on production build)
  serviceWorker:
    process.env.NODE_ENV === "production"
      ? {
          path: "/service-worker.js",
        }
      : {},

  // Input settings
  input: {
    scrollIntoViewOnFocus: device.cordova,
    scrollIntoViewCentered: device.cordova,
  },
  // Cordova Statusbar settings
  statusbar: {
    iosOverlaysWebView: true,
    androidOverlaysWebView: false,
  },
  on: {
    init: function () {
      var f7 = this;
      if (f7.device.cordova) {
        // Init cordova APIs (see cordova-app.js)
        cordovaApp.init(f7);
      }
    },
  },
});

window.addEventListener("new-order-received", (event) => {
  const notif = event.detail;

  app.notification
    .create({
      icon: '<i class="f7-icons">bell_fill</i>',
      title: notif.title || "Pesanan Baru",
      text: notif.message || "Ada pesanan baru",
      closeButton: true,
      closeTimeout: 5000,

      on: {
        click() {
          app.views.main.router.navigate("/notifikasi_tenant");
        },
      },
    })
    .open();
});
