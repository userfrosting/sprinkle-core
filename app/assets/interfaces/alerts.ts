import { Severity } from './severity'

export interface AlertInterface {
    title?: string
    description?: string
    style?: Severity | keyof typeof Severity
    closeBtn?: boolean
    hideIcon?: boolean
}
