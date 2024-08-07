import { defineStore } from 'pinia'
import axios from 'axios'

export const useConfigStore = defineStore('config', {
    persist: true,
    state: () => {
        return {
            config: {}
        }
    },
    actions: {
        async load() {
            axios.get('/api/config').then((response) => {
                this.config = response.data
            })
        }
    }
})
