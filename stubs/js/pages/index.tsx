import DocumentationNavigation from '@/components/documentation/documentation-navigation';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import DocumentationLayout from '@/layouts/documentation-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Book, Code, Shield } from 'lucide-react';

interface NavigationSection {
    title: string;
    slug: string;
    items: NavigationItem[];
    subsections?: NavigationSection[];
}

interface NavigationItem {
    title: string;
    slug: string;
    description: string;
}

interface IndexProps {
    navigation: {
        sections: NavigationSection[];
    };
}

export default function Index({ navigation }: IndexProps) {
    // Get the first few items from each section for quick access
    const getQuickStartItems = () => {
        const items: NavigationItem[] = [];

        navigation.sections.forEach((section) => {
            if (section.items.length > 0) {
                items.push(...section.items.slice(0, 3));
            }
            section.subsections?.forEach((subsection) => {
                if (subsection.items.length > 0) {
                    items.push(subsection.items[0]);
                }
            });
        });

        return items.slice(0, 6);
    };

    const quickStartItems = getQuickStartItems();

    return (
        <DocumentationLayout>
            <Head title="Documentation" />

            <div className="grid gap-8 lg:grid-cols-[250px_1fr]">
                {/* Navigation Sidebar */}
                <aside>
                    <DocumentationNavigation
                        sections={navigation.sections}
                        currentSlug=""
                    />
                </aside>

                {/* Main Content */}
                <div className="space-y-8">
                    {/* Header */}
                    <div className="space-y-4">
                        <h1 className="text-4xl font-bold tracking-tight">
                            Documentation
                        </h1>
                        <p className="text-lg text-muted-foreground">
                            Complete guide and reference for OiCms. Learn how to
                            build powerful content management solutions with our
                            headless CMS.
                        </p>
                    </div>

                    <Separator />

                    {/* Quick Start Section */}
                    <div className="space-y-4">
                        <h2 className="text-2xl font-semibold tracking-tight">
                            Quick Start
                        </h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            {quickStartItems.map((item) => (
                                <Link
                                    key={item.slug}
                                    href={`/documentation/${item.slug}`}
                                    className="group flex gap-4 rounded-lg border p-4 transition-colors hover:bg-accent"
                                >
                                    <div className="flex-1 space-y-1">
                                        <div className="font-medium group-hover:text-foreground">
                                            {item.title}
                                        </div>
                                        <div className="line-clamp-2 text-sm text-muted-foreground">
                                            {item.description}
                                        </div>
                                    </div>
                                    <div className="flex shrink-0 items-center">
                                        <ArrowRight className="size-4 text-muted-foreground transition-transform group-hover:translate-x-1" />
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>

                    <Separator />

                    {/* Feature Highlights */}
                    <div className="space-y-4">
                        <h2 className="text-2xl font-semibold tracking-tight">
                            Key Features
                        </h2>
                        <div className="grid gap-4 sm:grid-cols-3">
                            <div className="space-y-2 rounded-lg border p-4">
                                <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                                    <Book className="size-5 text-primary" />
                                </div>
                                <h3 className="font-semibold">
                                    Flexible Content
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Dynamic block system with custom field types
                                    and validation
                                </p>
                            </div>
                            <div className="space-y-2 rounded-lg border p-4">
                                <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                                    <Code className="size-5 text-primary" />
                                </div>
                                <h3 className="font-semibold">RESTful API</h3>
                                <p className="text-sm text-muted-foreground">
                                    Complete REST API with OpenAPI documentation
                                    and authentication
                                </p>
                            </div>
                            <div className="space-y-2 rounded-lg border p-4">
                                <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                                    <Shield className="size-5 text-primary" />
                                </div>
                                <h3 className="font-semibold">
                                    Security First
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    RBAC, rate limiting, audit logging, and
                                    comprehensive validation
                                </p>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    {/* Browse All Topics */}
                    <div className="space-y-4">
                        <h2 className="text-2xl font-semibold tracking-tight">
                            Browse All Topics
                        </h2>
                        <p className="text-muted-foreground">
                            Use the navigation menu on the left to explore all
                            available documentation topics organized by
                            category.
                        </p>
                        <div className="flex gap-4">
                            <Button variant="default" asChild>
                                <Link href="/documentation/introduction">
                                    Get Started
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href="/documentation/rest-api-swagger">
                                    API Reference
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </DocumentationLayout>
    );
}
