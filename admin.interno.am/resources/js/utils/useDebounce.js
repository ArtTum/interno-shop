import { watch } from 'vue';

/**
 * Creates a debounced watcher for auto-search functionality
 * @param {Ref} searchValue - The reactive search value to watch
 * @param {Function} callback - Function to call after debounce delay
 * @param {Number} delay - Delay in milliseconds (default: 500ms)
 * @returns {Function} stopWatch - Function to stop watching
 */
export function useDebouncedSearch(searchValue, callback, delay = 500) {
    let timeout = null;

    const stopWatch = watch(searchValue, (newValue, oldValue) => {
        // Clear existing timeout
        if (timeout) {
            clearTimeout(timeout);
        }

        // Set new timeout
        timeout = setTimeout(() => {
            // Only call callback if value actually changed
            if (newValue !== oldValue) {
                callback(newValue);
            }
        }, delay);
    });

    return stopWatch;
}

/**
 * Creates a debounced function
 * @param {Function} func - Function to debounce
 * @param {Number} delay - Delay in milliseconds (default: 500ms)
 * @returns {Function} Debounced function
 */
export function debounce(func, delay = 500) {
    let timeout = null;

    return function (...args) {
        if (timeout) {
            clearTimeout(timeout);
        }

        timeout = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}
