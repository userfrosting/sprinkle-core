import { ref, toValue, watchEffect, computed, type Ref, type ComputedRef } from 'vue'
import axios from 'axios'

interface Sprunjer {
    dataUrl: string
    size: Ref<number>
    page: Ref<number>
    totalPages: ComputedRef<number>
    countFiltered: ComputedRef<number>
    first: ComputedRef<number>
    last: ComputedRef<number>
    sorts: Ref<string>
    data: Ref<any>
    loading: Ref<boolean>
    count: ComputedRef<number>
    rows: ComputedRef<any>
    fetch: () => void
    downloadCsv: () => void
}

const useSprunjer = (dataUrl: string) => {
    // Sprunje parameters
    const size = ref<number>(10)
    const page = ref<number>(0)
    const sorts = ref<string>('[occurred_at]=desc')

    // Raw data
    const data = ref<any>({})

    // State
    const loading = ref<boolean>(false)

    /**
     * Api fetch function
     */
    async function fetch() {
        loading.value = true
        axios
            .get(
                toValue(dataUrl) +
                    '?size=' +
                    size.value +
                    '&page=' +
                    page.value +
                    '&sorts%5Boccurred_at%5D=desc'
            )
            .then((response) => {
                data.value = response.data
                loading.value = false
            })
            .catch((err) => {
                // TODO : User toast alert, or export alert
                console.error(err)
            })
    }

    /**
     * Computed properties
     */
    const totalPages = computed(() => {
        // Sprunjer page starts at 0, not 1
        return Math.ceil(data.value.count_filtered / size.value) - 1
    })

    const count = computed(() => {
        return data.value.count
    })

    const first = computed(() => {
        return page.value * size.value + 1
    })

    const last = computed(() => {
        return Math.min((page.value + 1) * size.value, count.value)
    })

    const countFiltered = computed(() => {
        return data.value.count_filtered
    })

    const rows = computed(() => {
        return data.value.rows
    })

    /**
     * Download the data as a CSV file
     */
    function downloadCsv() {
        console.log('Not yet implemented')
    }

    /**
     * Automatically fetch the data when any parameters change
     */
    watchEffect(() => {
        fetch()
    })

    /**
     * Export the functions and data
     */
    return {
        dataUrl,
        size,
        page,
        sorts,
        data,
        fetch,
        loading,
        downloadCsv,
        totalPages,
        countFiltered,
        count,
        rows,
        first,
        last
    }
}

export { useSprunjer, type Sprunjer }
