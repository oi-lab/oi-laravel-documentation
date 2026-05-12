import DocumentationFooter from '@/components/documentation/documentation-footer';
import DocumentationHeader from '@/components/documentation/documentation-header';
import { useFlashMessages } from '@/hooks/use-flash-messages';
import { type PropsWithChildren } from 'react';

export default function DocumentationLayout({ children }: PropsWithChildren) {
    useFlashMessages();

    return (
        <div className="flex min-h-screen flex-col">
            <DocumentationHeader />

            <main className="container mx-auto flex-1 px-4 py-8">
                {children}
            </main>

            <DocumentationFooter />
        </div>
    );
}
