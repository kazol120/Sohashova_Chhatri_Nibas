import { createStore } from 'vuex';
import axios from 'axios';
// Automatically detect local vs live server URL


const getBaseUrl = () => {
    const hostname = window.location.hostname;
    if (hostname === 'localhost' || hostname === '127.0.0.1' || hostname.endsWith('.test')) {
        return window.location.origin + '/';
    }
    return 'https://tssvilla.nsmlimited.com/';
};

const store = createStore({
    state: {
    url: getBaseUrl(),
    },
    mutations: {
    },
    actions: {
    },
    getters: {
    },
});

export default store;

