import { cn } from '@/lib/utils';
import React from 'react';

interface Props extends React.ComponentProps<'div'> {
    title: string;
    description?: string;
}

export default function HeadingLarge({
    title,
    description,
    className,
    ...props
}: Props) {
    return (
        <div className={cn('space-y-0.5', className)} {...props}>
            <h1 className="text-3xl font-semibold tracking-tight">{title}</h1>
            {description && (
                <p className="text-muted-foreground">{description}</p>
            )}
        </div>
    );
}
