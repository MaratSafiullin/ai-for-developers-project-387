import { createInertiaApp } from '@inertiajs/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { client } from '@/api/client.gen';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

client.setConfig({ baseUrl: '' });

const queryClient = new QueryClient();

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    strictMode: true,
    withApp(app) {
        return (
            <QueryClientProvider client={queryClient}>
                <TooltipProvider delayDuration={0}>
                    {app}
                    <Toaster />
                </TooltipProvider>
            </QueryClientProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
