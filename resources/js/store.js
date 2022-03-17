import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export const store = new Vuex.Store({
    state: {
        players: null,
        communityCards: null,
    },
    mutations: {
        setPlayers(state, payload) {
            state.players = payload.players
        },
        setCommunityCards(state, payload) {
            state.communityCards = payload.communityCards
        },
        updateDirectory(state, payload){
            state.players = payload.players
        },
        addItemToDirectory(state, payload){
            state.communityCards = payload.communityCards
        },
    },
    getters: {
        getPlayers: state => {
            return state.players
        },
        getCommunityCards: state => {
            return state.communityCards
        }
    }
})