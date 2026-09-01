import { router } from '@inertiajs/react';
import { type MouseEvent } from 'react';

import { DOCUMENTATION_TYPOGRAPHY_CLASS } from '@/lib/documentation-typography';
import { cn } from '@/lib/utils';

interface DocumentationHtmlContentProps {
    html: string;
    className?: string;
}

export default function DocumentationHtmlContent({
    html,
    className,
}: DocumentationHtmlContentProps) {
    const handleClick = (event: MouseEvent<HTMLDivElement>) => {
        const anchor = (event.target as HTMLElement).closest('a');

        if (!anchor) {
            return;
        }

        const href = anchor.getAttribute('href');

        if (
            !href ||
            href.startsWith('#') ||
            anchor.target === '_blank' ||
            /^[a-z]+:/i.test(href)
        ) {
            return;
        }

        event.preventDefault();
        router.visit(href);
    };

    return (
        <div
            data-slot={'documentation-html'}
            className={cn(DOCUMENTATION_TYPOGRAPHY_CLASS, className)}
            onClick={handleClick}
            dangerouslySetInnerHTML={{ __html: html }}
        />
    );
}
