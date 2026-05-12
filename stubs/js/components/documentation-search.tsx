import { Link } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

interface SearchResult {
    id: string;
    title: string;
    description: string;
    section: string;
    excerpt: string;
    score: number;
}

export default function DocumentationSearch() {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<SearchResult[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [isOpen, setIsOpen] = useState(false);
    const searchRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (query.length < 2) {
            setResults([]);
            setIsOpen(false);
            return;
        }

        setIsLoading(true);

        const timeout = setTimeout(async () => {
            try {
                const response = await fetch(
                    `/documentation/search?q=${encodeURIComponent(query)}`,
                );
                const data = await response.json();
                setResults(data);
                setIsOpen(data.length > 0);
            } catch (error) {
                console.error('Search error:', error);
                setResults([]);
            } finally {
                setIsLoading(false);
            }
        }, 300);

        return () => clearTimeout(timeout);
    }, [query]);

    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (
                searchRef.current &&
                !searchRef.current.contains(event.target as Node)
            ) {
                setIsOpen(false);
            }
        }

        document.addEventListener('mousedown', handleClickOutside);
        return () =>
            document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleClear = () => {
        setQuery('');
        setResults([]);
        setIsOpen(false);
    };

    const handleResultClick = () => {
        setIsOpen(false);
        setQuery('');
    };

    const highlightMatch = (text: string, query: string) => {
        if (!query) return text;

        const parts = text.split(new RegExp(`(${query})`, 'gi'));
        return parts.map((part, index) =>
            part.toLowerCase() === query.toLowerCase() ? (
                <mark key={index} className="bg-yellow-200 dark:bg-yellow-800">
                    {part}
                </mark>
            ) : (
                part
            ),
        );
    };

    return (
        <div ref={searchRef} className="relative w-full">
            <div className="relative">
                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    type="search"
                    placeholder="Search documentation..."
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    className="pr-9 pl-9"
                />
                {query && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={handleClear}
                        className="absolute top-1/2 right-1 size-7 -translate-y-1/2 p-0"
                    >
                        <X className="size-4" />
                    </Button>
                )}
            </div>

            {isOpen && (
                <div className="absolute z-50 mt-2 max-h-96 w-full overflow-y-auto rounded-md border bg-popover shadow-lg">
                    {isLoading ? (
                        <div className="p-4 text-center text-sm text-muted-foreground">
                            Searching...
                        </div>
                    ) : results.length > 0 ? (
                        <div className="py-2">
                            {results.map((result) => (
                                <Link
                                    key={result.id}
                                    href={`/documentation/${result.id}`}
                                    onClick={handleResultClick}
                                    className="block border-b px-4 py-3 transition-colors last:border-b-0 hover:bg-accent"
                                >
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="flex-1 space-y-1">
                                            <div className="font-medium">
                                                {highlightMatch(
                                                    result.title,
                                                    query,
                                                )}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {result.section}
                                            </div>
                                            <div className="line-clamp-2 text-sm text-muted-foreground">
                                                {highlightMatch(
                                                    result.excerpt,
                                                    query,
                                                )}
                                            </div>
                                        </div>
                                        <div
                                            className={cn(
                                                'flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-medium',
                                                result.score >= 10
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                                    : result.score >= 5
                                                      ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                                                      : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                            )}
                                        >
                                            {result.score}
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <div className="p-4 text-center text-sm text-muted-foreground">
                            No results found for "{query}"
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
