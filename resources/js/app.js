import './bootstrap'
import Vue from 'vue'

const app = new Vue({
    el: '#app',
    data() {
		return {
			deck: false,
			game_play: false,
			players: false,
			communityCards: false,
			errors: {},
			loading: false
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

			let payload = {
				deck: this.deck,
				game_play: this.game_play,
				player_id: player.player_id,
				action_id: action,
				table_seat_id: player.table_seat_id,
				hand_street_id: player.hand_street_id,
				active: player.active,
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


			}).catch(error => {

				console.log(error);

				this.loading = false
				this.errors = error.response.data.errors

			});
		},
		gameData(){
			window.axios.get('hand').then(response => {

				console.log(response.data);
				this.players = response.data.players;
				this.communityCards = response.data.communityCards;
				this.game_play = response.data.game_play;
				this.deck = response.data.deck;
				
			});
		}
	},
    mounted() {
        console.log('mounted')
        this.gameData();
    }
});
