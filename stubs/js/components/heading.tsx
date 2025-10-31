import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import React from 'react';

interface Props extends React.ComponentProps<'div'> {
    title: string;
    description?: string;
    backToHref?: string;
}

export default function Heading({
    title,
    description,
    backToHref,
    className,
    ...props
}: Props) {
    return (
        <div className={cn('space-y-0.5', className)} {...props}>
            <div className="flex items-center gap-2">
                {backToHref && (
                    <Button
                        variant={'link'}
                        size={'icon'}
                        className={'-mt-0.5 self-start'}
                        asChild
                    >
                        <Link href={backToHref}>
                            <ArrowLeft className="size-4" />
                            <span className={'sr-only'}>
                                Back to navigations
                            </span>
                        </Link>
                    </Button>
                )}
                <div className="flex-1 text-xl font-semibold tracking-tight">
                    {title}
                </div>
            </div>
            {description && (
                <p
                    className={cn(
                        'text-sm text-muted-foreground',
                        backToHref ? 'ml-11' : '',
                    )}
                >
                    {description}
                </p>
            )}
        </div>
    );
}
