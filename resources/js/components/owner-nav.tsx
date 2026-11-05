import { Link, usePage } from '@inertiajs/react';
import { index as ownerMeetingTypesIndex } from '@/routes/owner/meeting-types';
import { index as ownerMeetingsIndex } from '@/routes/owner/meetings';
import { cn } from '@/lib/utils';

const ownerSections = [
    { title: 'Предстоящие встречи', route: ownerMeetingsIndex() },
    { title: 'Типы встреч', route: ownerMeetingTypesIndex() },
];

/**
 * Links between the pages of the owner section; the current page is marked.
 */
export function OwnerNav() {
    const { url } = usePage();
    const currentPath = url.split('?')[0];

    return (
        <nav aria-label="Раздел управления" className="flex gap-4 text-sm">
            {ownerSections.map(({ title, route }) => {
                const isCurrent = route.url === currentPath;

                return (
                    <Link
                        key={route.url}
                        href={route}
                        aria-current={isCurrent ? 'page' : undefined}
                        className={cn(
                            'underline-offset-4 hover:underline',
                            isCurrent
                                ? 'font-medium text-foreground'
                                : 'text-primary',
                        )}
                    >
                        {title}
                    </Link>
                );
            })}
        </nav>
    );
}
