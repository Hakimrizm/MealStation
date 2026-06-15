import { createStore } from 'framework7';

const store = createStore({
  state: {
    notifications: [],
    chats: [],
    products: [
      {
        id: '1',
        title: 'Apple iPhone 8',
        description: 'Lorem ipsum dolor sit amet...'
      },
      {
        id: '2',
        title: 'Apple iPhone 8 Plus',
        description: 'Velit odit autem modi...'
      },
      {
        id: '3',
        title: 'Apple iPhone X',
        description: 'Expedita sequi perferendis...'
      },
    ]
  },

  getters: {
    notifications: ({ state }) => state.notifications,
    products: ({ state }) => state.products,
    chats: ({ state }) => state.chats,
  },

  setChats({ state }, chats) {
    state.chats = [...chats]; // FORCE REACTIVE
},
  actions: {

    // =========================
    // NOTIFICATION ACTIONS
    // =========================

    setNotifications({ state }, notifications) {
      state.notifications = notifications;
    },

    addNotification({ state }, order) {
      state.notifications.unshift({
        id: order.id || Date.now(),
        title: 'Pesanan Baru!',
        message: `Ada 1 pesanan baru: ${order.itemName}.`,
        time: new Date().toLocaleTimeString([], {
          hour: '2-digit',
          minute: '2-digit'
        }),

        // ⚠️ ini hanya default UI sementara
        read: false
      });
    },

    removeNotification({ state }, id) {
      state.notifications = state.notifications.filter(
        n => n.id !== id
      );
    },

    clearNotifications({ state }) {
      state.notifications = [];
    },


    // =========================
    // PRODUCT ACTIONS
    // =========================

    addProduct({ state }, product) {
      state.products = [...state.products, product];
    }

  }
});

export default store;