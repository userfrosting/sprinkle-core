import { useConfigStore } from './stores/config'

export default {
    install: () => {
        const config = useConfigStore()
        config.load()
    }
}
