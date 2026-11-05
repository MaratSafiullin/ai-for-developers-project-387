import { Head } from '@inertiajs/react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
import {
    createOwnerMeetingTypeMutation,
    listGuestMeetingTypesQueryKey,
    listOwnerMeetingTypesOptions,
    listOwnerMeetingTypesQueryKey,
} from '@/api/@tanstack/react-query.gen';
import { AppLayout } from '@/layouts/app-layout';
import { isValidationError } from '@/lib/api-errors';
import { formatDuration } from '@/lib/duration';
import { FormField } from '@/components/form-field';
import { MeetingTypeCard } from '@/components/meeting-type-card';
import { OwnerNav } from '@/components/owner-nav';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Textarea } from '@/components/ui/textarea';

export default function OwnerMeetingTypesIndex() {
    return (
        <>
            <Head title="Типы встреч" />
            <AppLayout>
                <main className="mx-auto flex w-full max-w-2xl flex-col gap-8 p-6">
                    <OwnerNav />
                    <h1 className="text-3xl font-semibold">Типы встреч</h1>
                    <section
                        aria-labelledby="create-meeting-type-heading"
                        className="flex flex-col gap-4"
                    >
                        <h2
                            id="create-meeting-type-heading"
                            className="text-xl font-medium"
                        >
                            Новый тип встречи
                        </h2>
                        <CreateMeetingTypeForm />
                    </section>
                    <section
                        aria-labelledby="meeting-types-heading"
                        className="flex flex-col gap-4"
                    >
                        <h2
                            id="meeting-types-heading"
                            className="text-xl font-medium"
                        >
                            Ваши типы встреч
                        </h2>
                        <OwnerMeetingTypeList />
                    </section>
                </main>
            </AppLayout>
        </>
    );
}

function textField(formData: FormData, name: string): string {
    const value = formData.get(name);

    return typeof value === 'string' ? value : '';
}

/**
 * Durations allowed by the contract: 15 to 240 minutes in steps of 15.
 */
const durationOptionsMinutes = Array.from(
    { length: 16 },
    (_, index) => (index + 1) * 15,
);

const defaultDurationMinutes = '30';

function CreateMeetingTypeForm() {
    const [durationMinutes, setDurationMinutes] = useState(
        defaultDurationMinutes,
    );
    const queryClient = useQueryClient();
    const { mutate, isPending, error } = useMutation({
        ...createOwnerMeetingTypeMutation(),
        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey: listOwnerMeetingTypesQueryKey(),
                }),
                queryClient.invalidateQueries({
                    queryKey: listGuestMeetingTypesQueryKey(),
                }),
            ]);
        },
    });

    const fieldErrors = isValidationError(error) ? error.errors : {};

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const form = event.currentTarget;
        const formData = new FormData(form);

        mutate(
            {
                body: {
                    name: textField(formData, 'name'),
                    description: textField(formData, 'description'),
                    durationMinutes: Number(durationMinutes),
                },
            },
            {
                onSuccess: () => {
                    form.reset();
                    setDurationMinutes(defaultDurationMinutes);
                    toast.success('Тип встречи создан');
                },
            },
        );
    }

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            <FormField
                id="name"
                label="Название"
                errors={fieldErrors.name}
                control={(describedBy) => (
                    <Input
                        id="name"
                        name="name"
                        required
                        maxLength={100}
                        aria-invalid={describedBy !== undefined}
                        aria-describedby={describedBy}
                    />
                )}
            />
            <FormField
                id="description"
                label="Описание (необязательно)"
                errors={fieldErrors.description}
                control={(describedBy) => (
                    <Textarea
                        id="description"
                        name="description"
                        maxLength={1000}
                        aria-invalid={describedBy !== undefined}
                        aria-describedby={describedBy}
                    />
                )}
            />
            <FormField
                id="durationMinutes"
                label="Длительность"
                errors={fieldErrors.durationMinutes}
                control={(describedBy) => (
                    <Select
                        name="durationMinutes"
                        required
                        value={durationMinutes}
                        onValueChange={setDurationMinutes}
                    >
                        <SelectTrigger
                            id="durationMinutes"
                            className="w-48"
                            aria-invalid={describedBy !== undefined}
                            aria-describedby={describedBy}
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {durationOptionsMinutes.map((minutes) => (
                                <SelectItem
                                    key={minutes}
                                    value={String(minutes)}
                                >
                                    {formatDuration(minutes)}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                )}
            />
            {error !== null && !isValidationError(error) && (
                <Alert variant="destructive">
                    <AlertDescription>
                        Не удалось создать тип встречи. Попробуйте ещё раз.
                    </AlertDescription>
                </Alert>
            )}
            <Button type="submit" disabled={isPending} className="self-start">
                {isPending ? 'Создаём…' : 'Создать'}
            </Button>
        </form>
    );
}

function OwnerMeetingTypeList() {
    const { data, isPending, isError } = useQuery(
        listOwnerMeetingTypesOptions(),
    );

    if (isPending) {
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
                Типов встреч пока нет. Создайте первый.
            </p>
        );
    }

    return (
        <ul className="flex flex-col gap-3">
            {data.data.map((meetingType) => (
                <li key={meetingType.id}>
                    <MeetingTypeCard meetingType={meetingType} />
                </li>
            ))}
        </ul>
    );
}
