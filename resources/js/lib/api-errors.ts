import type { SlotTakenError, ValidationError } from '@/api/types.gen';

/**
 * Determines whether an SDK error is a 422 response with field errors.
 */
export function isValidationError(error: unknown): error is ValidationError {
    return typeof error === 'object' && error !== null && 'errors' in error;
}

/**
 * Determines whether an SDK error is a 409 response for a taken time.
 */
export function isSlotTakenError(error: unknown): error is SlotTakenError {
    return (
        typeof error === 'object' &&
        error !== null &&
        'code' in error &&
        error.code === 'slot_taken'
    );
}
