import Vue from 'vue'
export default {
    state: {
        login: localStorage.getItem('login') || false
    },
    mutations: {
        set_login(state, login) {
            localStorage.setItem('login', login);
            state.login = login;
        }
    }
}