import './bootstrap'
import Vue from 'vue'

const app = new Vue({
    el: '#app',
    data() {
		return {
			deck: false,
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
			actionColours: {
				"Fold": [
					"bg-info"
				],
				"Check": [
					"bg-info"
				],
				"Call": [
					"bg-success"
				],
				"Bet": [
					"bg-warning"
				],
				"Raise": [
					"bg-danger"
				]
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
        showOptions(action_on){
            return action_on === true && this.winner === false;
        },
		setSuitColour(suit){
			return this.suitColours.suit;
		},
		action(action, player){

			let active = 1;
			if(action === 1){
				active = 0;
			}

			let payload = {
				deck: this.deck,
				player_id: player.player_id,
				action_id: action,
				table_seat_id: player.table_seat_id,
				hand_street_id: player.hand_street_id,
				active: active,
				bet_amount: null
			};

			console.log(payload);

			this.loading = true
			window.axios.post('action', payload).then(response => {

				console.log(response);

				this.loading = false
				this.players = response.data.players;
				this.communityCards = response.data.communityCards;
				this.deck = response.data.deck;
				this.winner = response.data.winner ? response.data.winner : false;


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

			});
		}
	},
    mounted() {
        console.log('mounted')
        this.gameData();
    }
});
