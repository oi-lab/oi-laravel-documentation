import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { useState } from 'react';

import HeadingSmall from '@/components/heading-small';

interface NavigationItem {
    title: string;
    slug: string;
    description?: string;
}

interface NavigationSection {
    title: string;
    slug: string;
    items: NavigationItem[];
    subsections?: NavigationSection[];
}

interface DocumentationNavigationProps {
    sections: NavigationSection[];
    currentSlug: string;
}

function checkHasActiveItem(
    section: NavigationSection,
    currentSlug: string,
): boolean {
    // Check items in this section
    if (section.items?.some((item) => item.slug === currentSlug)) {
        return true;
    }

    // Recursively check subsections
    if (section.subsections) {
        return section.subsections.some((subsection) =>
            checkHasActiveItem(subsection, currentSlug),
        );
    }

    return false;
}

function NavigationItem({
    item,
    currentSlug,
}: {
    item: NavigationItem;
    currentSlug: string;
}) {
    const isActive = item.slug === currentSlug;

    return (
        <li>
            <Link
                href={`/documentation/${item.slug}`}
                className={`block rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-accent hover:text-accent-foreground ${
                    isActive
                        ? 'bg-accent font-medium text-accent-foreground'
                        : 'text-muted-foreground'
                }`}
            >
                {item.title}
            </Link>
        </li>
    );
}

function NavigationSubsection({
    subsection,
    currentSlug,
}: {
    subsection: NavigationSection;
    currentSlug: string;
}) {
    const hasActiveItem = checkHasActiveItem(subsection, currentSlug);
    const [isExpanded, setIsExpanded] = useState(hasActiveItem);

    return (
        <div className="space-y-1">
            <button
                type="button"
                onClick={() => setIsExpanded(!isExpanded)}
                className="flex w-full items-center gap-1.5 rounded-md px-2 py-1 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
            >
                {isExpanded ? (
                    <ChevronDown className="size-3.5 shrink-0" />
                ) : (
                    <ChevronRight className="size-3.5 shrink-0" />
                )}
                <span className="truncate">{subsection.title}</span>
            </button>

            {isExpanded && subsection.items && subsection.items.length > 0 && (
                <ul className="ml-4 space-y-0.5 border-l pl-2">
                    {subsection.items.map((item) => (
                        <NavigationItem
                            key={item.slug}
                            item={item}
                            currentSlug={currentSlug}
                        />
                    ))}
                </ul>
            )}
        </div>
    );
}

function NavigationMainSection({
    section,
    currentSlug,
}: {
    section: NavigationSection;
    currentSlug: string;
}) {
    const hasActiveItem = checkHasActiveItem(section, currentSlug);
    const [isExpanded, setIsExpanded] = useState(hasActiveItem);
    const hasSubsections =
        section.subsections && section.subsections.length > 0;
    const hasItems = section.items && section.items.length > 0;

    return (
        <div className="space-y-2">
            <button
                type="button"
                onClick={() => setIsExpanded(!isExpanded)}
                className="flex w-full items-center gap-1.5 text-sm font-semibold tracking-wide text-foreground uppercase transition-colors hover:text-foreground/80"
            >
                {isExpanded ? (
                    <ChevronDown className="size-4 shrink-0" />
                ) : (
                    <ChevronRight className="size-4 shrink-0" />
                )}
                <span className="truncate">{section.title}</span>
            </button>

            {isExpanded && (
                <div className="space-y-3 pl-1">
                    {/* Direct items (for sections without subsections like "Getting Started") */}
                    {hasItems && !hasSubsections && (
                        <ul className="space-y-0.5">
                            {section.items.map((item) => (
                                <NavigationItem
                                    key={item.slug}
                                    item={item}
                                    currentSlug={currentSlug}
                                />
                            ))}
                        </ul>
                    )}

                    {/* Subsections */}
                    {hasSubsections &&
                        section.subsections!.map((subsection) => (
                            <NavigationSubsection
                                key={subsection.slug}
                                subsection={subsection}
                                currentSlug={currentSlug}
                            />
                        ))}
                </div>
            )}
        </div>
    );
}

export default function DocumentationNavigation({
    sections,
    currentSlug,
}: DocumentationNavigationProps) {
    return (
        <div className="sticky top-20 space-y-4 p-4">
            <HeadingSmall title="Summary" />
            <nav className="space-y-4">
                {sections.map((section) => (
                    <NavigationMainSection
                        key={section.slug}
                        section={section}
                        currentSlug={currentSlug}
                    />
                ))}
            </nav>
        </div>
    );
}
