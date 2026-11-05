import { expect, test } from '@playwright/test';

const meetingTypeName = 'Знакомство';
const meetingTypeDescription =
    'Короткий созвон, чтобы познакомиться и обсудить задачу';
const durationMinutes = 30;
const guestName = 'Анна';

/**
 * Adds minutes to a «HH:MM» time of day, e.g. «09:45» + 30 → «10:15».
 */
function addMinutes(timeOfDay: string, minutes: number): string {
    const [hours, mins] = timeOfDay.split(':').map(Number);
    const total = hours * 60 + mins + minutes;

    return [Math.floor(total / 60) % 24, total % 60]
        .map((part) => String(part).padStart(2, '0'))
        .join(':');
}

test('the README scenario: the owner creates a meeting type, a guest books it, the owner sees the meeting', async ({
    page,
}) => {
    await page.goto('/');

    await test.step('the owner creates the meeting type', async () => {
        await page.getByRole('link', { name: 'Управление' }).click();
        await page.getByLabel('Название').fill(meetingTypeName);
        await page
            .getByLabel('Описание (необязательно)')
            .fill(meetingTypeDescription);
        await page.getByLabel('Длительность').click();
        await page
            .getByRole('option', {
                name: `${durationMinutes} мин`,
                exact: true,
            })
            .click();
        await page.getByRole('button', { name: 'Создать' }).click();

        await expect(page.getByText('Тип встречи создан')).toBeVisible();
    });

    const { slotStartTime, slotDateLabel } =
        await test.step('the guest picks the type, the first date and the first slot', async () => {
            await page.getByRole('link', { name: 'Записаться' }).click();
            await page
                .getByRole('button', { name: new RegExp(meetingTypeName) })
                .click();

            const firstDate = page
                .getByRole('group', { name: 'Дата' })
                .getByRole('button')
                .first();
            await firstDate.click();
            const firstSlot = page
                .getByRole('group', { name: 'Время' })
                .getByRole('button')
                .first();
            await firstSlot.click();

            return {
                slotDateLabel: (await firstDate.innerText()).trim(),
                slotStartTime: (await firstSlot.innerText()).trim(),
            };
        });
    const slotTimeRange = `${slotStartTime}–${addMinutes(slotStartTime, durationMinutes)}`;
    const slotDayOfMonth = slotDateLabel.match(/\d+/)?.[0] ?? '';

    const confirmedDate =
        await test.step('the guest enters the name and sees the confirmation', async () => {
            await page.getByLabel('Ваше имя').fill(guestName);
            await page.getByRole('button', { name: 'Записаться' }).click();

            await expect(page.getByText('Вы записаны')).toBeVisible();
            const confirmation = page.getByRole('main');
            await expect(confirmation).toContainText(meetingTypeName);
            await expect(confirmation).toContainText(slotTimeRange);
            await expect(confirmation).toContainText(guestName);

            const date = confirmation.locator('dt:text-is("Дата") + dd');
            await expect(date).toHaveText(
                new RegExp(`(^|\\D)${slotDayOfMonth}(\\D|$)`),
            );

            return (await date.innerText()).trim();
        });

    await test.step('the owner sees the meeting among the upcoming ones', async () => {
        await page.getByRole('link', { name: 'Управление' }).click();
        await page.getByRole('link', { name: 'Предстоящие встречи' }).click();

        const meeting = page
            .getByRole('listitem')
            .filter({ hasText: guestName });
        await expect(meeting).toHaveCount(1);
        await expect(meeting).toContainText(
            `${confirmedDate}, ${slotTimeRange}`,
        );
        await expect(meeting).toContainText(meetingTypeName);
    });
});
