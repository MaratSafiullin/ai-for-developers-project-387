import type { ReactNode } from 'react';
import { AppHeader } from '@/components/app-header';

export function AppLayout({ children }: { children: ReactNode }) {
    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <AppHeader />
            {children}
        </div>
    );
}
