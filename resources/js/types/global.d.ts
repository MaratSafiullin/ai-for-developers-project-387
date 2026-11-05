export {};

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            timezone: string;
            [key: string]: unknown;
        };
    }
}
