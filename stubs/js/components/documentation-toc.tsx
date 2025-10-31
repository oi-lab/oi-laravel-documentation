import { Copy } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useCopyToClipboard } from 'usehooks-ts';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface TocItem {
    level: number;
    title: string;
    slug: string;
}

interface DocumentationTocProps {
    tableOfContents: TocItem[];
    markdown: string;
}

export default function DocumentationToc({
    tableOfContents,
    markdown,
}: DocumentationTocProps) {
    const [activeId, setActiveId] = useState<string>('');
    const [, copy] = useCopyToClipboard();
    const [copied, setCopied] = useState(false);

    const handleCopy = async () => {
        await copy(markdown);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    useEffect(() => {
        // Create an intersection observer to track which heading is in view
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        setActiveId(entry.target.id);
                    }
                });
            },
            {
                rootMargin: '-80px 0px -80% 0px',
                threshold: 1.0,
            },
        );

        // Observe all headings that are in the table of contents
        tableOfContents.forEach((item) => {
            const element = document.getElementById(item.slug);
            if (element) {
                observer.observe(element);
            }
        });

        return () => {
            observer.disconnect();
        };
    }, [tableOfContents]);

    return (
        <div className="sticky top-20">
            <div className={'p-4'}>
                <Button
                    variant={'link'}
                    size={'xs'}
                    className={'!p-0'}
                    onClick={handleCopy}
                >
                    <Copy /> {copied ? 'Copied!' : 'Copy as markdown'}
                </Button>
            </div>
            {/* Table of Contents */}
            {tableOfContents.length > 0 && (
                <div className="sticky top-20 space-y-4 p-4">
                    <HeadingSmall title={'On this page'} />
                    <nav className="border-l">
                        {tableOfContents
                            .filter((item) => item.level > 1 && item.level <= 3)
                            .map((item) => (
                                <a
                                    key={item.slug}
                                    href={`#${item.slug}`}
                                    className="relative block py-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                    style={{
                                        paddingLeft: `${(item.level - 1) * 1}rem`,
                                    }}
                                >
                                    <div
                                        data-slot="indicator"
                                        className={cn(
                                            'absolute top-0 bottom-0 left-0 w-1 transition-colors',
                                            activeId === item.slug
                                                ? 'bg-foreground'
                                                : 'bg-transparent',
                                        )}
                                    />
                                    {item.title}
                                </a>
                            ))}
                    </nav>
                </div>
            )}
        </div>
    );
}
