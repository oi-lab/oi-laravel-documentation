import { Link as InertiaLink } from '@inertiajs/react';
import { Check, Copy, ExternalLink, Hash } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeRaw from 'rehype-raw';
import rehypeSanitize from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';
import slugify from 'slugify';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

// Helper pour échapper le HTML
function escapeHtml(text: string): string {
    const map: Record<string, string> = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
}

interface MarkdownContentProps {
    content: string;
    className?: string;
}

interface HeadingProps {
    level: 1 | 2 | 3 | 4 | 5 | 6;
    children: React.ReactNode;
}

function Heading({ level, children }: HeadingProps) {
    const [copied, setCopied] = useState(false);
    const text = children?.toString() || '';
    const id = slugify(text.replaceAll('/', '-'), {
        lower: true,
        strict: true,
        remove: /[*+~.()'"!:@]/g,
    });

    const handleCopyLink = async () => {
        await navigator.clipboard.writeText(
            `${window.location.origin}${window.location.pathname}#${id}`,
        );
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const Tag = `h${level}` as const;
    const isH1 = level === 1;

    const baseClasses = cn(
        'group relative scroll-mt-20 font-bold tracking-tight',
        {
            hidden: isH1,
            'mt-10 text-3xl first:mt-0': level === 2,
            'mt-8 text-2xl': level === 3,
            'mt-6 text-xl': level === 4,
            'mt-4 text-lg': level === 5,
            'mt-4 text-base': level === 6,
        },
    );

    return (
        <Tag id={id} className={baseClasses}>
            {!isH1 && (
                <a
                    href={`#${id}`}
                    className="absolute top-0 -left-8 flex size-8 items-center justify-center opacity-0 transition-opacity group-hover:opacity-100"
                    aria-label="Link to this section"
                >
                    <Hash className="size-4 text-muted-foreground" />
                </a>
            )}
            {children}
            {!isH1 && (
                <Button
                    variant="ghost"
                    size="icon"
                    className="ml-2 inline-flex size-6 opacity-0 transition-opacity group-hover:opacity-100"
                    onClick={handleCopyLink}
                    aria-label="Copy link"
                >
                    {copied ? (
                        <Check className="size-3 text-green-600" />
                    ) : (
                        <Copy className="size-3" />
                    )}
                </Button>
            )}
        </Tag>
    );
}

// Composant pour les blocs de code (fenced code blocks)
function PreBlock({ children }: React.ComponentProps<'pre'>) {
    const childrenArray = React.Children.toArray(children);
    const codeElement = childrenArray[0];

    // Vérifier si c'est un élément code avec une classe language-*
    if (
        React.isValidElement<React.ComponentProps<'code'>>(codeElement) &&
        codeElement.props.className
    ) {
        const match = /language-(\w+)/.exec(codeElement.props.className);
        if (match) {
            return <CodeBlock {...codeElement.props} />;
        }
    }

    // Si ce n'est pas un bloc de code avec langage, retourner un <pre> simple
    return (
        <pre className="overflow-x-auto rounded bg-muted p-4">{children}</pre>
    );
}

// Composant pour afficher les blocs de code avec coloration syntaxique
function CodeBlock({ children, className }: React.ComponentProps<'code'>) {
    const [copied, setCopied] = useState(false);
    const [highlightedCode, setHighlightedCode] = useState('');
    const match = /language-(\w+)/.exec(className || '');
    const rawLanguage = match ? match[1] : '';
    const code = String(children).replace(/\n$/, '');

    // Mapper les alias de langages vers les langages Shiki supportés
    const languageMap: Record<string, string> = {
        env: 'dotenv',
        envrc: 'dotenv',
        config: 'properties',
        conf: 'properties',
    };

    const language = languageMap[rawLanguage.toLowerCase()] || rawLanguage;

    const handleCopy = async () => {
        await navigator.clipboard.writeText(code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    useEffect(() => {
        if (!match || !code) return;

        const loadHighlightedCode = async () => {
            try {
                const { codeToHtml } = await import('shiki');
                const isDark =
                    document.documentElement.classList.contains('dark');

                const highlighted = await codeToHtml(code, {
                    lang: language,
                    themes: {
                        light: 'github-light',
                        dark: 'github-dark',
                    },
                    defaultColor: isDark ? 'dark' : 'light',
                });

                // Extraire uniquement le contenu du <code> sans le <pre> wrapper
                const parser = new DOMParser();
                const doc = parser.parseFromString(highlighted, 'text/html');
                const codeElement = doc.querySelector('pre code');
                if (codeElement) {
                    setHighlightedCode(codeElement.innerHTML);
                } else {
                    setHighlightedCode(highlighted);
                }
            } catch (e) {
                console.error(`Language "${language}" could not be loaded.`, e);
                // Fallback: afficher le code brut sans coloration
                setHighlightedCode(
                    code
                        .split('\n')
                        .map(
                            (line) =>
                                `<span class="line">${escapeHtml(line)}</span>`,
                        )
                        .join('\n'),
                );
            }
        };

        loadHighlightedCode();
    }, [code, language, match]);

    return (
        <div className="not-prose group relative my-6">
            <div className="flex items-center justify-between rounded-t-lg border border-b-0 bg-muted px-4 py-2">
                <span className="text-xs font-medium text-muted-foreground">
                    {rawLanguage}
                </span>
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-6 opacity-0 transition-opacity group-hover:opacity-100"
                    onClick={handleCopy}
                    aria-label="Copy code"
                >
                    {copied ? (
                        <Check className="size-3 text-green-600" />
                    ) : (
                        <Copy className="size-3" />
                    )}
                </Button>
            </div>
            <pre className="overflow-x-auto rounded-b-lg border bg-slate-950 p-4 dark:bg-slate-900">
                <code
                    className="text-sm"
                    dangerouslySetInnerHTML={{
                        __html: highlightedCode || escapeHtml(code),
                    }}
                />
            </pre>
        </div>
    );
}

// Composant pour le code inline
function InlineCode({ children }: React.ComponentProps<'code'>) {
    return (
        <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-sm text-foreground">
            {children}
        </code>
    );
}

function LinkComponent({ href, children }: React.ComponentProps<'a'>) {
    const isExternal = href?.startsWith('http');
    const isAnchor = href?.startsWith('#');

    if (isAnchor) {
        return (
            <a
                href={href}
                className="font-medium text-primary underline decoration-primary/30 underline-offset-4 transition-colors hover:decoration-primary"
            >
                {children}
            </a>
        );
    }

    if (isExternal) {
        return (
            <a
                href={href}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-1 font-medium text-primary underline decoration-primary/30 underline-offset-4 transition-colors hover:decoration-primary"
            >
                {children}
                <ExternalLink className="size-3" />
            </a>
        );
    }

    return (
        <InertiaLink
            href={href}
            className="font-medium text-primary underline decoration-primary/30 underline-offset-4 transition-colors hover:decoration-primary"
        >
            {children}
        </InertiaLink>
    );
}

function BlockquoteComponent({ children }: React.ComponentProps<'blockquote'>) {
    return (
        <blockquote className="my-6 border-l-4 border-primary bg-muted/50 py-2 pr-4 pl-4 italic">
            {children}
        </blockquote>
    );
}

function TableComponent({ children }: React.ComponentProps<'table'>) {
    return (
        <div className="my-6 overflow-x-auto">
            <table className="w-full border-collapse">{children}</table>
        </div>
    );
}

function ImageComponent({ src, alt }: React.ComponentProps<'img'>) {
    return (
        <span className="my-6 block">
            <img
                src={src}
                alt={alt}
                className="rounded-lg border shadow-sm"
                loading="lazy"
            />
        </span>
    );
}

export default function DocumentationMarkdownContent({
    content,
    className,
}: MarkdownContentProps) {
    return (
        <div className={cn('typography', className)}>
            <ReactMarkdown
                remarkPlugins={[remarkGfm]}
                rehypePlugins={[rehypeRaw, rehypeSanitize]}
                components={{
                    h1: ({ children }) => (
                        <Heading level={1}>{children}</Heading>
                    ),
                    h2: ({ children }) => (
                        <Heading level={2}>{children}</Heading>
                    ),
                    h3: ({ children }) => (
                        <Heading level={3}>{children}</Heading>
                    ),
                    h4: ({ children }) => (
                        <Heading level={4}>{children}</Heading>
                    ),
                    h5: ({ children }) => (
                        <Heading level={5}>{children}</Heading>
                    ),
                    h6: ({ children }) => (
                        <Heading level={6}>{children}</Heading>
                    ),
                    pre: PreBlock,
                    code: InlineCode,
                    a: LinkComponent,
                    blockquote: BlockquoteComponent,
                    table: TableComponent,
                    img: ImageComponent,
                    ul: ({ children }) => (
                        <ul className="my-6 space-y-2">{children}</ul>
                    ),
                    ol: ({ children }) => (
                        <ol className="my-6 space-y-2">{children}</ol>
                    ),
                    li: ({ children }) => (
                        <li className="leading-7">{children}</li>
                    ),
                    p: ({ children }) => <p className="my-4">{children}</p>,
                    hr: () => <hr className="my-8" />,
                }}
            >
                {content}
            </ReactMarkdown>
        </div>
    );
}
