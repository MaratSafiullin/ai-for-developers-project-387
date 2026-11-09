import { Head, usePage } from '@inertiajs/react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import { toast } from 'sonner';
import {
    cancelOwnerMeetingMutation,
    listGuestSlotsQueryKey,
    listOwnerMeetingsOptions,
    listOwnerMeetingsQueryKey,
} from '@/api/@tanstack/react-query.gen';
import type { Meeting } from '@/api/types.gen';
import { AppLayout } from '@/layouts/app-layout';
import { apiErrorMessage } from '@/lib/api-errors';
import { formatDate, formatTime } from '@/lib/date-time';
import { OwnerNav } from '@/components/owner-nav';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
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
    const hasStarted = new Date(meeting.startsAt).getTime() <= Date.now();

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
                {hasStarted ? null : <CancelMeetingButton meeting={meeting} />}
            </CardHeader>
        </Card>
    );
}

/**
 * A button with a confirmation dialog that cancels the meeting. The list is
 * refreshed after both success and failure, so it never shows stale data.
 */
function CancelMeetingButton({ meeting }: { meeting: Meeting }) {
    const { timezone } = usePage().props;
    const [open, setOpen] = useState(false);
    const queryClient = useQueryClient();
    const { mutate, isPending } = useMutation({
        ...cancelOwnerMeetingMutation(),
        onSettled: async () => {
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey: listOwnerMeetingsQueryKey(),
                }),
                queryClient.invalidateQueries({
                    queryKey: listGuestSlotsQueryKey(),
                }),
            ]);
        },
    });

    function handleConfirm() {
        mutate(
            { path: { id: meeting.id } },
            {
                onSuccess: () => {
                    setOpen(false);
                    toast.success('Встреча отменена');
                },
                onError: (error) => {
                    setOpen(false);
                    toast.error(
                        apiErrorMessage(
                            error,
                            'Не удалось отменить встречу. Попробуйте ещё раз.',
                        ),
                    );
                },
            },
        );
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm" className="self-start">
                    Отменить встречу
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Отменить встречу?</DialogTitle>
                    <DialogDescription>
                        {meeting.guestName},{' '}
                        {formatDate(meeting.startsAt, timezone)},{' '}
                        {formatTime(meeting.startsAt, timezone)}–
                        {formatTime(meeting.endsAt, timezone)}. Встреча будет
                        удалена, время снова станет свободным. Действие нельзя
                        отменить.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        onClick={() => setOpen(false)}
                        disabled={isPending}
                    >
                        Закрыть
                    </Button>
                    <Button
                        variant="destructive"
                        onClick={handleConfirm}
                        disabled={isPending}
                    >
                        {isPending ? 'Отменяем…' : 'Отменить встречу'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
