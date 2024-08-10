import { defineStore } from 'pinia'
import axios from 'axios'
import { getProperty } from 'dot-prop'

export const useConfigStore = defineStore('config', {
    persist: true,
    state: () => {
        return {
            config: {}
        }
    },
    getters: {
        get: (state) => {
            return (key: string, value?: any): any => getProperty(state.config, key, value)
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
