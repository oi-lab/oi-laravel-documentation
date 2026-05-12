import AppLogoIcon from '@/components/app-logo-icon';
import DocumentationSearch from '@/components/documentation/documentation-search';
import { dashboard, home } from '@/routes';
import documentation from '@/routes/documentation';
import { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export default function DocumentationHeader() {
    const { auth } = usePage<SharedData>().props;

    return (
        <header className="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div className="container mx-auto grid h-16 items-center gap-8 px-4 lg:grid-cols-[250px_1fr_250px]">
                <div className="flex shrink-0 items-center gap-2">
                    <Link href={home()}>
                        <AppLogoIcon className="size-6 fill-current" />
                    </Link>
                    <Link
                        href={documentation.index()}
                        className="flex items-center gap-1.5 font-semibold"
                    >
                        <span className="font-normal text-muted-foreground">
                            Documentation
                        </span>
                    </Link>
                </div>

                <div className="hidden flex-1 md:block">
                    <DocumentationSearch />
                </div>

                <nav className="flex shrink-0 items-center justify-end gap-6">
                    {auth?.user && (
                        <Link
                            href={dashboard()}
                            className="text-sm font-medium transition-colors hover:text-foreground/80"
                        >
                            Dashboard
                        </Link>
                    )}
                    <Link
                        href={home()}
                        className="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground/80"
                    >
                        Home
                    </Link>
                </nav>
            </div>
        </header>
    );
}
