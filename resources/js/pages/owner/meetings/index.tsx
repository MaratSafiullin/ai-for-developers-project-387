import { Head, usePage } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { listOwnerMeetingsOptions } from '@/api/@tanstack/react-query.gen';
import type { Meeting } from '@/api/types.gen';
import { AppLayout } from '@/layouts/app-layout';
import { formatDate, formatTime } from '@/lib/date-time';
import { OwnerNav } from '@/components/owner-nav';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

export default function OwnerMeetingsIndex() {
    const { timezone } = usePage().props;

    return (
        <>
            <Head title="Предстоящие встречи" />
            <AppLayout>
                <main className="mx-auto flex w-full max-w-2xl flex-col gap-8 p-6">
                    <OwnerNav />
                    <div className="flex flex-col gap-2">
                        <h1 className="text-3xl font-semibold">
                            Предстоящие встречи
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Время указано по часовому поясу {timezone}.
                        </p>
                    </div>
                    <UpcomingMeetingList />
                </main>
            </AppLayout>
        </>
    );
}

function UpcomingMeetingList() {
    const { data, isPending, isError } = useQuery(listOwnerMeetingsOptions());

    if (isPending) {
        return (
            <div
                className="flex flex-col gap-3"
                aria-busy="true"
                aria-label="Загрузка встреч"
            >
                {[0, 1, 2].map((index) => (
                    <Skeleton key={index} className="h-24 w-full" />
                ))}
            </div>
        );
    }

    if (isError) {
        return (
            <Alert variant="destructive">
                <AlertDescription>
                    Не удалось загрузить встречи. Обновите страницу.
                </AlertDescription>
            </Alert>
        );
    }

    if (data.data.length === 0) {
        return (
            <p className="text-muted-foreground">
                Предстоящих встреч пока нет.
            </p>
        );
    }

    return (
        <ul className="flex flex-col gap-3">
            {data.data.map((meeting) => (
                <li key={meeting.id}>
                    <UpcomingMeetingCard meeting={meeting} />
                </li>
            ))}
        </ul>
    );
}

/**
 * A meeting with its date and time in the owner timezone.
 */
function UpcomingMeetingCard({ meeting }: { meeting: Meeting }) {
    const { timezone } = usePage().props;

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {formatDate(meeting.startsAt, timezone)},{' '}
                    {formatTime(meeting.startsAt, timezone)}–
                    {formatTime(meeting.endsAt, timezone)}
                </CardTitle>
                <dl className="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                    <dt className="text-muted-foreground">Тип встречи</dt>
                    <dd>{meeting.meetingType.name}</dd>
                    <dt className="text-muted-foreground">Гость</dt>
                    <dd>{meeting.guestName}</dd>
                </dl>
            </CardHeader>
        </Card>
    );
}
