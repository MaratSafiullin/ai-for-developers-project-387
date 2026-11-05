import { Head, Link } from '@inertiajs/react';
import { AppLayout } from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { index as bookingIndex } from '@/routes/booking';

export default function Home() {
    return (
        <>
            <Head title="Календарь звонков" />
            <AppLayout>
                <main className="flex flex-1 flex-col items-center justify-center gap-6 p-6 text-center">
                    <h1 className="text-3xl font-semibold">
                        Календарь звонков
                    </h1>
                    <p className="max-w-md text-muted-foreground">
                        Выберите тип встречи и удобное время, чтобы записаться
                        на звонок. Регистрация не нужна.
                    </p>
                    <Button asChild size="lg">
                        <Link href={bookingIndex()}>Записаться на звонок</Link>
                    </Button>
                </main>
            </AppLayout>
        </>
    );
}
