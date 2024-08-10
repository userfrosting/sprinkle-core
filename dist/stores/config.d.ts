export declare const useConfigStore: import('pinia').StoreDefinition<"config", {
    config: {};
}, {
    get: (state: {
        config: {};
    } & import('pinia').PiniaCustomStateProperties<{
        config: {};
    }>) => (key: string, value?: any) => any;
}, {
    load(): Promise<void>;
}>;
