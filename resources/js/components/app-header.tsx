import { Link } from '@inertiajs/react';
import { home } from '@/routes';
import { index as bookingIndex } from '@/routes/booking';
import { index as ownerMeetingTypesIndex } from '@/routes/owner/meeting-types';

export function AppHeader() {
    return (
        <header className="border-b bg-background">
            <nav
                aria-label="Основная навигация"
                className="mx-auto flex w-full max-w-2xl flex-wrap items-center justify-between gap-4 px-6 py-4"
            >
                <Link href={home()} className="font-semibold">
                    Календарь звонков
                </Link>
                <div className="flex items-center gap-4 text-sm">
                    <Link
                        href={bookingIndex()}
                        className="text-primary underline-offset-4 hover:underline"
                    >
                        Записаться
                    </Link>
                    <Link
                        href={ownerMeetingTypesIndex()}
                        className="text-primary underline-offset-4 hover:underline"
                    >
                        Управление
                    </Link>
                </div>
            </nav>
        </header>
    );
}
