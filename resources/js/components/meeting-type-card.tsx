import type { MeetingType } from '@/api/types.gen';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDuration } from '@/lib/duration';

export function MeetingTypeCard({
    meetingType,
    className,
}: {
    meetingType: MeetingType;
    className?: string;
}) {
    return (
        <Card className={className}>
            <CardHeader>
                <CardTitle>{meetingType.name}</CardTitle>
                <p className="text-sm text-muted-foreground">
                    {formatDuration(meetingType.durationMinutes)}
                </p>
                {meetingType.description !== null && (
                    <CardDescription>{meetingType.description}</CardDescription>
                )}
            </CardHeader>
        </Card>
    );
}
