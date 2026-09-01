import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

import DocumentationHtmlContent from '@/components/documentation/documentation-html-content';
import DocumentationMarkdownContent from '@/components/documentation/documentation-markdown-content';
import DocumentationNavigation from '@/components/documentation/documentation-navigation';
import DocumentationToc from '@/components/documentation/documentation-toc';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import dashboard from '@/routes/dashboard';
import documentation, { show } from '@/routes/documentation';

interface NavigationItem {
    title: string;
    slug: string;
    description: string;
}

interface NavigationSection {
    title: string;
    slug: string;
    items: NavigationItem[];
    subsections?: NavigationSection[];
}

interface TocItem {
    level: number;
    title: string;
    slug: string;
}

interface ShowProps {
    document: {
        frontmatter: {
            title: string;
            description?: string;
        };
        markdown: string;
        html?: string;
        tableOfContents: TocItem[];
    };
    navigation: {
        sections: NavigationSection[];
    };
    slug: string;
    previousPage?: NavigationItem;
    nextPage?: NavigationItem;
}

export default function Show({
    document,
    navigation,
    slug,
    previousPage,
    nextPage,
}: ShowProps) {
    return (
        <>
            <Head title={document.frontmatter.title} />

            <div className="grid gap-8 lg:grid-cols-[--spacing(64)_1fr_--spacing(64)]">
                <aside>
                    <DocumentationNavigation
                        sections={navigation.sections}
                        currentSlug={slug}
                    />
                </aside>

                {/* Main Content */}
                <div className="space-y-6">
                    {/* Header */}
                    <div className="space-y-2">
                        <h1 className="text-4xl font-bold tracking-tight">
                            {document.frontmatter.title}
                        </h1>
                        {document.frontmatter.description && (
                            <p className="text-lg text-muted-foreground">
                                {document.frontmatter.description}
                            </p>
                        )}
                    </div>

                    <Separator />

                    {/* Content - rendered server-side to HTML (rendering.markdown_engine = "server")
                        or client-side from markdown (rendering.markdown_engine = "client", the default) */}
                    {document.html !== undefined ? (
                        <DocumentationHtmlContent html={document.html} />
                    ) : (
                        <DocumentationMarkdownContent content={document.markdown} />
                    )}

                    {/* Page Navigation */}
                    {(previousPage || nextPage) && (
                        <>
                            <div className="mt-12 grid gap-4 sm:grid-cols-2">
                                {previousPage ? (
                                    <Button
                                        variant={'outline'}
                                        className={
                                            'h-auto gap-4 p-4 whitespace-normal'
                                        }
                                        asChild
                                    >
                                        <Link
                                            href={`/documentation/${previousPage.slug}`}
                                        >
                                            <div className="flex shrink-0 items-center">
                                                <ChevronLeft className="size-5 text-muted-foreground transition-colors group-hover:text-foreground" />
                                            </div>
                                            <div className="flex-1 space-y-1">
                                                <div className="text-xs font-medium text-muted-foreground">
                                                    Previous
                                                </div>
                                                <div className="font-semibold">
                                                    {previousPage.title}
                                                </div>
                                                {previousPage.description && (
                                                    <div className="line-clamp-1 text-xs font-normal text-muted-foreground">
                                                        {
                                                            previousPage.description
                                                        }
                                                    </div>
                                                )}
                                            </div>
                                        </Link>
                                    </Button>
                                ) : (
                                    <div />
                                )}
                                {nextPage && (
                                    <Button
                                        variant={'outline'}
                                        className={
                                            'h-auto gap-4 p-4 whitespace-normal'
                                        }
                                        asChild
                                    >
                                        <Link
                                            href={`/documentation/${nextPage.slug}`}
                                        >
                                            <div className="flex-1 space-y-1">
                                                <div className="text-xs font-medium text-muted-foreground">
                                                    Next
                                                </div>
                                                <div className="font-semibold">
                                                    {nextPage.title}
                                                </div>
                                                {nextPage.description && (
                                                    <div className="line-clamp-1 text-xs font-normal text-muted-foreground">
                                                        {nextPage.description}
                                                    </div>
                                                )}
                                            </div>
                                            <div className="flex shrink-0 items-center">
                                                <ChevronRight className="size-5 text-muted-foreground transition-colors group-hover:text-foreground" />
                                            </div>
                                        </Link>
                                    </Button>
                                )}
                            </div>
                        </>
                    )}
                </div>

                {/* Sidebar */}
                <aside>
                    <DocumentationToc
                        tableOfContents={document.tableOfContents}
                        markdown={document.markdown}
                    />
                </aside>
            </div>
        </>
    );
}

Show.layout = ({ document, slug }: ShowProps) => ({
    breadcrumbs: [
        {
            title: 'Home',
            href: dashboard.board(),
        },
        {
            title: 'Documentation',
            href: documentation.index(),
        },
        {
            title: document.frontmatter.title,
            href: show(slug),
        },
    ],
});
