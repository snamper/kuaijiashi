import Vue from 'vue';
import is from 'is'
Storage.prototype.setObject = function(key, value) {
    this.setItem(key, JSON.stringify(value));
}
Storage.prototype.getObject = function(key) {
    var value = this.getItem(key);
    if (!is.empty(value) && value != 'undefined') {
        return JSON.parse(value);
    } else {
        return null;
    }
}
const app = {
    state: {
        token: localStorage.getObject('token'),
        userInfo: localStorage.getObject('userInfo'),
        audio_permission: localStorage.getObject('audio_permission'),
        location: localStorage.getObject('location'),
        coach_keywords: localStorage.getObject('coach_keywords'),
        audio_play: localStorage.getObject('audio_play')
    },
    mutations: {
        set_token(state, token) {
            localStorage.setObject('token', token);
            state.token = token;
        },
        set_userInfo(state, userInfo) {
            localStorage.setObject('userInfo', userInfo);
            state.userInfo = userInfo;
        },
        set_audio_permission(state, audio_permission) {
            localStorage.setObject('audio_permission', audio_permission);
            state.audio_permission = audio_permission;
        },
        set_location(state, location) {
            localStorage.setObject('location', location);
            console.log(location);
            state.location = location;
        },
        set_coach_keywords(state, coach_keywords) {
            localStorage.setObject('coach_keywords', coach_keywords);
            state.coach_keywords = coach_keywords;
        },
        push_coach_keywords(state, data) {
            console.log(data);
            let coach_keywords_old = data.coach_keywords;
            if (is.array(data.coach_keywords)) {
                coach_keywords_old.push(data.pushData);
            } else {
                coach_keywords_old = [data.pushData];
            }
            localStorage.setObject('coach_keywords', coach_keywords_old);
            state.coach_keywords = coach_keywords_old;
        },
        set_audio_play(state, audio_play) {
            localStorage.setObject('audio_play', audio_play);
            state.audio_play = audio_play;
        },
        push_audio_play(state, data) {
            let audio_play_old = data.urls;
            if (is.array(audio_play_old)) {
                audio_play_old.push(data.pushData);
            } else {
                audio_play_old = [data.pushData];
            }
            localStorage.setObject('audio_play', audio_play_old);
            state.audio_play = audio_play_old;
        }
    }
};
export default app;