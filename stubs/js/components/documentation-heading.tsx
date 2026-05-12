import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { cva, type VariantProps } from 'class-variance-authority';
import { ArrowLeft } from 'lucide-react';
import React from 'react';

const headingTitleVariants = cva('font-semibold tracking-tight', {
    variants: {
        size: {
            lg: 'text-3xl',
            default: 'text-xl',
            sm: 'text-base font-medium tracking-normal',
            xs: 'text-sm tracking-normal',
        },
    },
    defaultVariants: {
        size: 'default',
    },
});

const headingDescriptionVariants = cva('text-muted-foreground', {
    variants: {
        size: {
            lg: 'text-base',
            default: 'text-sm',
            sm: 'text-sm',
            xs: 'text-xs',
        },
    },
    defaultVariants: {
        size: 'default',
    },
});

const headingTags = {
    lg: 'h1',
    default: 'h2',
    sm: 'h3',
    xs: 'h4',
} as const;

type DocumentationHeadingProps = React.ComponentProps<'div'> &
    VariantProps<typeof headingTitleVariants> & {
        title: string;
        description?: string;
        backToHref?: string;
    };

export default function DocumentationHeading({
    title,
    description,
    backToHref,
    size,
    className,
    ...props
}: DocumentationHeadingProps) {
    const HeadingTag = headingTags[size ?? 'default'];

    return (
        <div className={cn('space-y-0.5', className)} {...props}>
            <div className="flex items-center gap-2">
                {backToHref && (
                    <Button
                        variant="link"
                        size="icon"
                        className="-mt-0.5 self-start"
                        asChild
                    >
                        <Link href={backToHref}>
                            <ArrowLeft className="size-4" />
                            <span className="sr-only">Back to navigation</span>
                        </Link>
                    </Button>
                )}
                <HeadingTag
                    className={cn('flex-1', headingTitleVariants({ size }))}
                >
                    {title}
                </HeadingTag>
            </div>
            {description && (
                <p
                    className={cn(
                        headingDescriptionVariants({ size }),
                        backToHref && 'ml-11',
                    )}
                >
                    {description}
                </p>
            )}
        </div>
    );
}

export { headingTitleVariants };
