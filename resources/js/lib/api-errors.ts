import type { SlotTakenError, ValidationError } from '@/api/types.gen';

/**
 * Determines whether an SDK error is a 422 response with field errors.
 */
export function isValidationError(error: unknown): error is ValidationError {
    return typeof error === 'object' && error !== null && 'errors' in error;
}

/**
 * Reads the `message` of an SDK error response, or falls back to the given text.
 */
export function apiErrorMessage(error: unknown, fallback: string): string {
    return typeof error === 'object' &&
        error !== null &&
        'message' in error &&
        typeof error.message === 'string' &&
        error.message !== ''
        ? error.message
        : fallback;
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
