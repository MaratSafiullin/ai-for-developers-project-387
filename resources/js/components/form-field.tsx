import type { ReactNode } from 'react';
import { Label } from '@/components/ui/label';

/**
 * A labelled form control with the server's validation messages below it.
 * The control receives the id of the messages for `aria-describedby` when
 * there are any.
 */
export function FormField({
    id,
    label,
    errors,
    control,
}: {
    id: string;
    label: string;
    errors: string[] | undefined;
    control: (describedBy: string | undefined) => ReactNode;
}) {
    const errorId = `${id}-error`;
    const hasErrors = errors !== undefined && errors.length > 0;

    return (
        <div className="flex flex-col gap-2">
            <Label htmlFor={id}>{label}</Label>
            {control(hasErrors ? errorId : undefined)}
            {hasErrors && (
                <p id={errorId} className="text-sm text-destructive">
                    {errors.join(' ')}
                </p>
            )}
        </div>
    );
}
