import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

export function useFlashMessages() {
    const page = usePage<SharedData>().props;
    const flash = page.flash;

    useEffect(() => {
        if (flash?.success) {
            toast.success(
                typeof flash.success === 'string'
                    ? flash.success
                    : flash.success.message,
                {
                    duration: 4000,
                },
            );
        }

        if (flash?.error) {
            toast.error(
                typeof flash.error === 'string'
                    ? flash.error
                    : flash.error.message,
                {
                    duration: 5000,
                },
            );
        }

        if (flash?.info) {
            toast.info(
                typeof flash.info === 'string'
                    ? flash.info
                    : flash.info.message,
                {
                    duration: 4000,
                },
            );
        }
    }, [flash]);
}
