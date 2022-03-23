import './bootstrap'
import Vue from 'vue'

import Player from '../js/Player.vue';

const app = new Vue({
    el: '#app',
    components: {
        Player
    },
    data() {
		return {
			deck: false,
			pot: 0,
			players: false,
			communityCards: false,
			winner: false,
			errors: {},
			loading: false,
			suitColours: {
				"Clubs": [
					"text-dark",
					"border border-2 border-dark"
				],
				"Diamonds": [
					"text-danger",
					"border border-2 border-danger"
				],
				"Hearts": [
					"text-danger",
					"border border-2 border-danger"
				],
				"Spades": [
					"text-dark",
					"border border-2 border-dark"
				]
			},
            actionBetAmounts: {
                "Fold": null,
                "Check": null,
                "Call": 50.0,
                "Bet": 50.0,
                "Raise": 50.0
            }
		}
	},
	computed: {
		payload(){
			return {
				//
			}
		}
	},
	methods: {
		action(action, player){

			let active = 1;
			if(action.id === 1){
				active = 0;
			}

			let payload = {
				deck: this.deck,
				player_id: player.player_id,
				action_id: action.id,
				table_seat_id: player.table_seat_id,
				hand_street_id: player.hand_street_id,
				active: active,
				bet_amount: this.actionBetAmounts[action.name]
			};

			this.loading = true
			window.axios.post('action', payload).then(response => {

				console.log(response);

				this.loading = false
				this.players = response.data.players;
				this.communityCards = response.data.communityCards;
				this.deck = response.data.deck;
				this.winner = response.data.winner ? response.data.winner : false;
                this.pot = response.data.pot;


			}).catch(error => {

				console.log(error);

				this.loading = false
				this.errors = error.response.data.errors

			});
		},
		gameData(){
			window.axios.get('play').then(response => {

                console.log(response.data);
				this.winner = false;
				this.players = response.data.players;
				this.communityCards = response.data.communityCards;
				this.deck = response.data.deck;
				this.pot = response.data.pot;

			});
		}
	},
    mounted() {
        this.gameData();
        this.$root.$on("action", function(action, player) {
            this.action(action, player);
        });
    }
});
