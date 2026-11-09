import { expect, test } from '@playwright/test';

/**
 * Written from the text of spec #15 before its implementation, independently
 * of the implementing session. The spec does not fix the UI copy, so the
 * cancel button, the confirmation dialog and the notification are located by
 * role and by loose patterns rather than by exact labels.
 *
 * The e2e database is shared by the whole run, so the test creates its own
 * meeting type and guests and does not depend on the other specs.
 */
const meetingTypeName = 'Разбор задачи';
const durationMinutes = 30;
const firstGuestName = 'Борис';
const secondGuestName = 'Вера';

test('the owner cancels an upcoming meeting and a guest books the freed time', async ({
    page,
}) => {
    await page.goto('/');

    await test.step('the owner creates the meeting type', async () => {
        await page.getByRole('link', { name: 'Управление' }).click();
        await page.getByLabel('Название').fill(meetingTypeName);
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

    const { slotDateLabel, slotStartTime } =
        await test.step('the first guest books the first free slot', async () => {
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
            const booked = {
                slotDateLabel: (await firstDate.innerText()).trim(),
                slotStartTime: (await firstSlot.innerText()).trim(),
            };

            await page.getByLabel('Ваше имя').fill(firstGuestName);
            await page.getByRole('button', { name: 'Записаться' }).click();
            await expect(page.getByText('Вы записаны')).toBeVisible();

            return booked;
        });

    await test.step('main scenario: the owner cancels the meeting through the confirmation dialog and it leaves the upcoming list', async () => {
        await page.getByRole('link', { name: 'Управление' }).click();
        await page.getByRole('link', { name: 'Предстоящие встречи' }).click();

        const meeting = page
            .getByRole('listitem')
            .filter({ hasText: firstGuestName });
        await expect(meeting).toHaveCount(1);

        const cancelButton = meeting.getByRole('button', { name: /отмен/i });
        await expect(cancelButton).toBeVisible();
        await cancelButton.click();

        const dialog = page
            .getByRole('alertdialog')
            .or(page.getByRole('dialog'));
        await expect(dialog).toBeVisible();
        await dialog
            .getByRole('button', { name: /отменить|подтвердить/i })
            .click();

        await expect(page.getByText(/отмен[её]н/i).first()).toBeVisible();
        await expect(meeting).toHaveCount(0);
    });

    await test.step('neighbouring scenario: a guest sees the freed time among the slots and books it', async () => {
        await page.getByRole('link', { name: 'Записаться' }).click();
        await page
            .getByRole('button', { name: new RegExp(meetingTypeName) })
            .click();
        await page
            .getByRole('group', { name: 'Дата' })
            .getByRole('button', { name: slotDateLabel, exact: true })
            .click();

        const freedSlot = page
            .getByRole('group', { name: 'Время' })
            .getByRole('button', { name: slotStartTime, exact: true });
        await expect(freedSlot).toBeVisible();
        await freedSlot.click();

        await page.getByLabel('Ваше имя').fill(secondGuestName);
        await page.getByRole('button', { name: 'Записаться' }).click();

        await expect(page.getByText('Вы записаны')).toBeVisible();
        const confirmation = page.getByRole('main');
        await expect(confirmation).toContainText(`${slotStartTime}–`);
        await expect(confirmation).toContainText(secondGuestName);
    });
});
