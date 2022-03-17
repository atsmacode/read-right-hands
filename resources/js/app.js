import './bootstrap'
import Vue from 'vue'

const app = new Vue({
    el: '#app',
    data() {
		return {
			count: 0
		}
	},
    mounted() {
        console.log('mounted')
    }
});
