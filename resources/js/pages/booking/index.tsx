import { Head, usePage } from '@inertiajs/react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import {
    createGuestMeetingMutation,
    listGuestMeetingTypesOptions,
    listGuestSlotsOptions,
    listGuestSlotsQueryKey,
    listOwnerMeetingsQueryKey,
} from '@/api/@tanstack/react-query.gen';
import type { Meeting, Slot } from '@/api/types.gen';
import { AppLayout } from '@/layouts/app-layout';
import { isSlotTakenError, isValidationError } from '@/lib/api-errors';
import { formatCalendarDate, formatDate, formatTime } from '@/lib/date-time';
import { FormField } from '@/components/form-field';
import { MeetingTypeCard } from '@/components/meeting-type-card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';

export default function BookingIndex() {
    const [guestName, setGuestName] = useState('');
    const [selectedMeetingTypeId, setSelectedMeetingTypeId] = useState<
        number | null
    >(null);
    const [selectedSlot, setSelectedSlot] = useState<Slot | null>(null);
    const [slotNotice, setSlotNotice] = useState<string | null>(null);
    const [bookedMeeting, setBookedMeeting] = useState<Meeting | null>(null);
    const queryClient = useQueryClient();
    const { mutate, isPending, error, reset } = useMutation(
        createGuestMeetingMutation(),
    );

    const fieldErrors = isValidationError(error) ? error.errors : {};
    const canBook =
        guestName.trim() !== '' &&
        selectedMeetingTypeId !== null &&
        selectedSlot !== null;

    function selectMeetingType(meetingTypeId: number) {
        if (meetingTypeId === selectedMeetingTypeId) {
            return;
        }

        setSelectedMeetingTypeId(meetingTypeId);
        setSelectedSlot(null);
        setSlotNotice(null);
    }

    function refetchSlots(meetingTypeId: number) {
        void queryClient.invalidateQueries({
            queryKey: listGuestSlotsQueryKey({ query: { meetingTypeId } }),
        });
    }

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (selectedMeetingTypeId === null || selectedSlot === null) {
            return;
        }

        const meetingTypeId = selectedMeetingTypeId;
        setSlotNotice(null);

        mutate(
            {
                body: {
                    meetingTypeId,
                    startsAt: selectedSlot.startsAt,
                    guestName,
                },
            },
            {
                onSuccess: (response) => {
                    setBookedMeeting(response.data);
                    refetchSlots(meetingTypeId);
                    void queryClient.invalidateQueries({
                        queryKey: listOwnerMeetingsQueryKey(),
                    });
                },
                onError: (bookingError) => {
                    const isSlotUnavailable =
                        isSlotTakenError(bookingError) ||
                        (isValidationError(bookingError) &&
                            bookingError.errors.startsAt !== undefined);

                    if (isSlotUnavailable) {
                        setSlotNotice(bookingError.message);
                        setSelectedSlot(null);
                        refetchSlots(meetingTypeId);
                    }
                },
            },
        );
    }

    function startOver() {
        reset();
        setGuestName('');
        setSelectedMeetingTypeId(null);
        setSelectedSlot(null);
        setSlotNotice(null);
        setBookedMeeting(null);
    }

    if (bookedMeeting !== null) {
        return (
            <BookingPage>
                <BookingConfirmation
                    meeting={bookedMeeting}
                    onDismiss={startOver}
                />
            </BookingPage>
        );
    }

    return (
        <BookingPage>
            <form onSubmit={handleSubmit} className="flex flex-col gap-6">
                {slotNotice !== null && (
                    <Alert variant="destructive">
                        <AlertDescription>{slotNotice}</AlertDescription>
                    </Alert>
                )}
                <FormField
                    id="guestName"
                    label="Ваше имя"
                    errors={fieldErrors.guestName}
                    control={(describedBy) => (
                        <Input
                            id="guestName"
                            name="guestName"
                            required
                            maxLength={100}
                            autoComplete="name"
                            value={guestName}
                            onChange={(event) =>
                                setGuestName(event.target.value)
                            }
                            aria-invalid={describedBy !== undefined}
                            aria-describedby={describedBy}
                        />
                    )}
                />
                <section
                    aria-labelledby="meeting-types-heading"
                    className="flex flex-col gap-4"
                >
                    <h2
                        id="meeting-types-heading"
                        className="text-xl font-medium"
                    >
                        Выберите тип встречи
                    </h2>
                    <MeetingTypeCatalog
                        selectedMeetingTypeId={selectedMeetingTypeId}
                        onSelect={selectMeetingType}
                    />
                </section>
                {selectedMeetingTypeId !== null && (
                    <section
                        aria-labelledby="slots-heading"
                        className="flex flex-col gap-4"
                    >
                        <h2 id="slots-heading" className="text-xl font-medium">
                            Выберите время
                        </h2>
                        <SlotPicker
                            key={selectedMeetingTypeId}
                            meetingTypeId={selectedMeetingTypeId}
                            selectedSlot={selectedSlot}
                            onSelect={setSelectedSlot}
                        />
                    </section>
                )}
                {error !== null &&
                    !isValidationError(error) &&
                    !isSlotTakenError(error) && (
                        <Alert variant="destructive">
                            <AlertDescription>
                                Не удалось записаться. Попробуйте ещё раз.
                            </AlertDescription>
                        </Alert>
                    )}
                <Button
                    type="submit"
                    disabled={!canBook || isPending}
                    className="self-start"
                >
                    {isPending ? 'Записываем…' : 'Записаться'}
                </Button>
            </form>
        </BookingPage>
    );
}

function BookingPage({ children }: { children: ReactNode }) {
    return (
        <>
            <Head title="Запись на звонок" />
            <AppLayout>
                <main className="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6">
                    <h1 className="text-3xl font-semibold">Запись на звонок</h1>
                    {children}
                </main>
            </AppLayout>
        </>
    );
}

/**
 * The booked meeting as the server saved it, in the owner timezone.
 */
function BookingConfirmation({
    meeting,
    onDismiss,
}: {
    meeting: Meeting;
    onDismiss: () => void;
}) {
    const { timezone } = usePage().props;

    return (
        <Card>
            <CardHeader>
                <CardTitle>Вы записаны</CardTitle>
            </CardHeader>
            <CardContent>
                <dl className="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
                    <dt className="text-muted-foreground">Тип встречи</dt>
                    <dd>{meeting.meetingType.name}</dd>
                    <dt className="text-muted-foreground">Дата</dt>
                    <dd>{formatDate(meeting.startsAt, timezone)}</dd>
                    <dt className="text-muted-foreground">Время</dt>
                    <dd>
                        {formatTime(meeting.startsAt, timezone)}–
                        {formatTime(meeting.endsAt, timezone)} ({timezone})
                    </dd>
                    <dt className="text-muted-foreground">Имя</dt>
                    <dd>{meeting.guestName}</dd>
                </dl>
            </CardContent>
            <CardFooter>
                <Button type="button" onClick={onDismiss}>
                    Понятно
                </Button>
            </CardFooter>
        </Card>
    );
}

function MeetingTypeCatalog({
    selectedMeetingTypeId,
    onSelect,
}: {
    selectedMeetingTypeId: number | null;
    onSelect: (meetingTypeId: number) => void;
}) {
    const { data, isPending, isError } = useQuery(
        listGuestMeetingTypesOptions(),
    );

    if (isPending) {
        return <MeetingTypeCatalogSkeleton />;
    }

    if (isError) {
        return (
            <Alert variant="destructive">
                <AlertDescription>
                    Не удалось загрузить типы встреч. Обновите страницу.
                </AlertDescription>
            </Alert>
        );
    }

    if (data.data.length === 0) {
        return (
            <p className="text-muted-foreground">
                Пока нет доступных типов встреч.
            </p>
        );
    }

    return (
        <ul className="flex flex-col gap-3">
            {data.data.map((meetingType) => {
                const isSelected = meetingType.id === selectedMeetingTypeId;

                return (
                    <li key={meetingType.id}>
                        <button
                            type="button"
                            aria-pressed={isSelected}
                            onClick={() => onSelect(meetingType.id)}
                            className="w-full rounded-xl text-left outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <MeetingTypeCard
                                meetingType={meetingType}
                                className={
                                    isSelected
                                        ? 'border-primary ring-1 ring-primary'
                                        : 'hover:bg-accent'
                                }
                            />
                        </button>
                    </li>
                );
            })}
        </ul>
    );
}

function MeetingTypeCatalogSkeleton() {
    return (
        <div
            className="flex flex-col gap-3"
            aria-busy="true"
            aria-label="Загрузка типов встреч"
        >
            {[0, 1, 2].map((index) => (
                <Skeleton key={index} className="h-24 w-full" />
            ))}
        </div>
    );
}

/**
 * Dates of the booking window and the free slots of the chosen date. Mounted
 * with the meeting type as its key, so a type change starts from scratch.
 */
function SlotPicker({
    meetingTypeId,
    selectedSlot,
    onSelect,
}: {
    meetingTypeId: number;
    selectedSlot: Slot | null;
    onSelect: (slot: Slot) => void;
}) {
    const { timezone } = usePage().props;
    const [chosenDate, setChosenDate] = useState<string | null>(null);
    const { data, isPending, isError } = useQuery(
        listGuestSlotsOptions({ query: { meetingTypeId } }),
    );

    if (isPending) {
        return <SlotPickerSkeleton />;
    }

    if (isError) {
        return (
            <Alert variant="destructive">
                <AlertDescription>
                    Не удалось загрузить свободное время. Обновите страницу.
                </AlertDescription>
            </Alert>
        );
    }

    const { days } = data.data;
    const activeDate =
        chosenDate ??
        days.find((day) => day.slots.length > 0)?.date ??
        days[0]?.date;
    const activeDay = days.find((day) => day.date === activeDate);

    return (
        <div className="flex flex-col gap-4">
            <div
                role="group"
                aria-label="Дата"
                className="flex flex-wrap gap-2"
            >
                {days.map((day) => {
                    const isActive = day.date === activeDate;

                    return (
                        <Button
                            key={day.date}
                            type="button"
                            size="sm"
                            variant={isActive ? 'default' : 'outline'}
                            aria-pressed={isActive}
                            onClick={() => setChosenDate(day.date)}
                            className={
                                day.slots.length === 0 && !isActive
                                    ? 'text-muted-foreground'
                                    : undefined
                            }
                        >
                            {formatCalendarDate(day.date)}
                        </Button>
                    );
                })}
            </div>
            {activeDay === undefined || activeDay.slots.length === 0 ? (
                <p className="text-muted-foreground">
                    На эту дату свободного времени нет. Выберите другую дату.
                </p>
            ) : (
                <div
                    role="group"
                    aria-label="Время"
                    className="grid grid-cols-3 gap-2 sm:grid-cols-5"
                >
                    {activeDay.slots.map((slot) => {
                        const isSelected =
                            slot.startsAt === selectedSlot?.startsAt;

                        return (
                            <Button
                                key={slot.startsAt}
                                type="button"
                                variant={isSelected ? 'default' : 'outline'}
                                aria-pressed={isSelected}
                                onClick={() => onSelect(slot)}
                            >
                                {formatTime(slot.startsAt, timezone)}
                            </Button>
                        );
                    })}
                </div>
            )}
            <p className="text-sm text-muted-foreground">
                Время указано по часовому поясу {timezone}.
            </p>
        </div>
    );
}

function SlotPickerSkeleton() {
    return (
        <div
            className="flex flex-col gap-4"
            aria-busy="true"
            aria-label="Загрузка свободного времени"
        >
            <div className="flex flex-wrap gap-2">
                {Array.from({ length: 14 }, (_, index) => (
                    <Skeleton key={index} className="h-8 w-24" />
                ))}
            </div>
            <div className="grid grid-cols-3 gap-2 sm:grid-cols-5">
                {Array.from({ length: 10 }, (_, index) => (
                    <Skeleton key={index} className="h-9" />
                ))}
            </div>
        </div>
    );
}
