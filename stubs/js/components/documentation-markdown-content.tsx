import { Link as InertiaLink } from '@inertiajs/react';
import { Check, Copy, ExternalLink, Hash, icons, Info, TriangleAlert } from 'lucide-react';
import React, { useEffect, useId, useState } from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeRaw from 'rehype-raw';
import rehypeSanitize, { defaultSchema } from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';
import slugify from 'slugify';

import { Button } from '@/components/ui/button';
import remarkCallouts, {
    type CalloutType,
    normalizeCallouts,
} from '@/lib/remark-callouts';
import remarkTableColumn from '@/lib/remark-table-column';
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
            'mt-6 text-2xl first:mt-0 md:mt-10 md:text-3xl': level === 2,
            'mt-5 text-xl md:mt-8 md:text-2xl': level === 3,
            'mt-4 text-lg md:mt-6 md:text-xl': level === 4,
            'mt-3 text-base md:mt-4 md:text-lg': level === 5,
            'mt-3 text-sm md:mt-4 md:text-base': level === 6,
        },
    );

    return (
        <Tag data-slot={'heading'} data-level={level} id={id} className={baseClasses}>
            {!isH1 && (
                <a
                    href={`#${id}`}
                    className="absolute top-0 -left-8 hidden size-8 items-center justify-center opacity-0 transition-opacity group-hover:opacity-100 md:flex"
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
                    className="ml-2 hidden size-6 opacity-0 transition-opacity group-hover:opacity-100 md:inline-flex"
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
            if (match[1] === 'mermaid') {
                const code = String(codeElement.props.children).replace(/\n$/, '');

                return <MermaidDiagram code={code} />;
            }

            return <CodeBlock {...codeElement.props} />;
        }
    }

    // Si ce n'est pas un bloc de code avec langage, retourner un <pre> simple
    return (
        <div className="overflow-x-auto rounded bg-muted">
            <pre data-slot={'pre'} className="p-4">
                {children}
            </pre>
        </div>
    );
}

// Composant pour afficher les blocs de code avec coloration syntaxique
function CodeBlock({ children, className }: React.ComponentProps<'code'>) {
    const [copied, setCopied] = useState(false);
    const [highlightedCode, setHighlightedCode] = useState('');
    const [bgColor, setBgColor] = useState('');
    const match = /language-(\w+)/.exec(className || '');
    const rawLanguage = match ? match[1] : '';
    const code = String(children).replace(/\n$/, '');

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
        if (!match || !code) {
            return;
        }

        const loadHighlightedCode = async () => {
            try {
                const { codeToHtml } = await import('shiki');
                const isDark = document.documentElement.classList.contains('dark');
                const theme = isDark ? 'github-dark' : 'github-light';

                const highlighted = await codeToHtml(code, {
                    lang: language,
                    theme,
                });

                const parser = new DOMParser();
                const doc = parser.parseFromString(highlighted, 'text/html');
                const preElement = doc.querySelector('pre');
                const codeElement = preElement?.querySelector('code');

                if (preElement?.style.backgroundColor) {
                    setBgColor(preElement.style.backgroundColor);
                }

                if (codeElement) {
                    setHighlightedCode(codeElement.innerHTML);
                } else {
                    setHighlightedCode(highlighted);
                }
            } catch (e) {
                console.error(`Language "${language}" could not be loaded.`, e);
                setHighlightedCode(
                    code
                        .split('\n')
                        .map((line) => `<span class="line">${escapeHtml(line)}</span>`)
                        .join('\n'),
                );
            }
        };

        loadHighlightedCode();
    }, [code, language, match]);

    return (
        <div data-slot={'code-block'} className="not-prose group relative my-4 w-full min-w-0 md:my-6">
            <div className="flex items-center justify-between rounded-t-lg border border-b-0 bg-muted px-3 py-2 md:px-4">
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
            <div
                className="overflow-x-auto rounded-b-lg border"
                style={{ backgroundColor: bgColor || undefined }}
            >
                <pre className="p-3 md:p-4">
                    <code
                        className="text-sm"
                        dangerouslySetInnerHTML={{
                            __html: highlightedCode || escapeHtml(code),
                        }}
                    />
                </pre>
            </div>
        </div>
    );
}

// Composant pour rendre les diagrammes Mermaid
function MermaidDiagram({ code }: { code: string }) {
    const reactId = useId();
    const diagramId = `mermaid-${reactId.replace(/[^a-zA-Z0-9]/g, '')}`;
    const [svg, setSvg] = useState('');
    const [hasError, setHasError] = useState(false);
    const [isDark, setIsDark] = useState(
        () =>
            typeof document !== 'undefined' &&
            document.documentElement.classList.contains('dark'),
    );

    useEffect(() => {
        const observer = new MutationObserver(() => {
            setIsDark(document.documentElement.classList.contains('dark'));
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        let cancelled = false;

        const renderDiagram = async () => {
            try {
                const mermaid = (await import('mermaid')).default;

                mermaid.initialize({
                    startOnLoad: false,
                    theme: isDark ? 'dark' : 'default',
                    securityLevel: 'strict',
                    fontFamily: 'inherit',
                });

                const { svg: renderedSvg } = await mermaid.render(diagramId, code);

                if (!cancelled) {
                    setSvg(renderedSvg);
                    setHasError(false);
                }
            } catch (e) {
                if (!cancelled) {
                    console.error('Mermaid diagram could not be rendered.', e);
                    setHasError(true);
                }
            }
        };

        renderDiagram();

        return () => {
            cancelled = true;
        };
    }, [code, diagramId, isDark]);

    if (hasError) {
        return (
            <div
                data-slot={'mermaid-error'}
                className="my-4 overflow-x-auto rounded-lg border border-destructive/50 bg-destructive/10 p-4 md:my-6"
            >
                <p className="mb-2 text-sm font-medium text-destructive">
                    Diagram could not be rendered
                </p>
                <pre className="text-sm">
                    <code>{code}</code>
                </pre>
            </div>
        );
    }

    return (
        <div
            data-slot={'mermaid-diagram'}
            className="not-prose my-4 flex justify-center overflow-x-auto rounded-lg border bg-muted/30 p-4 md:my-6"
            dangerouslySetInnerHTML={{ __html: svg }}
        />
    );
}

// Composant pour le code inline
function InlineCode({ children }: React.ComponentProps<'code'>) {
    return (
        <code
            data-slot={'inline-code'}
            className="rounded bg-muted px-1.5 py-0.5 font-mono text-sm text-foreground"
        >
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
                data-slot={'link-anchor'}
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
                data-slot={'link-external'}
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
            data-slot={'link-inertia'}
            href={href}
            className="font-medium text-primary underline decoration-primary/30 underline-offset-4 transition-colors hover:decoration-primary"
        >
            {children}
        </InertiaLink>
    );
}


const CALLOUT_VARIANTS: Record<
    Exclude<CalloutType, 'quote'>,
    { icon: typeof Info; container: string; iconWrapper: string }
> = {
    info: {
        icon: Info,
        container: 'border-sky-500 bg-sky-500',
        iconWrapper: 'text-background',
    },
    danger: {
        icon: TriangleAlert,
        container: 'border-red-500 bg-red-500',
        iconWrapper: 'text-background',
    },
};

const calloutContentClasses =
    'p-4 [&_p]:mt-0! [&_p]:mb-2! [&_p]:last:mb-0! [&_ul]:mt-0! [&_ul]:mb-2! [&_ul]:last:mb-0!';

function BlockquoteComponent({
                                 node,
                                 children,
                             }: React.ComponentProps<'blockquote'> & {
    node?: { properties?: Record<string, unknown> };
}) {
    const rawType = node?.properties?.dataCallout;
    const type: CalloutType =
        rawType === 'info' || rawType === 'danger' ? rawType : 'quote';

    // Classic citation: plain accent rule, no icon.
    if (type === 'quote') {
        return (
            <blockquote
                data-slot={'blockquote'}
                data-callout={'quote'}
                className="border-border text-muted-foreground my-6 border-l-4 pl-4 italic [&_p]:my-2 [&_p]:first:mt-0 [&_p]:last:mb-0"
            >
                {children}
            </blockquote>
        );
    }

    const variant = CALLOUT_VARIANTS[type];
    const Icon = variant.icon;

    return (
        <blockquote
            data-slot={'callout'}
            data-callout={type}
            className={cn(
                'my-6 grid grid-cols-[2.5rem_1fr] items-stretch overflow-hidden rounded-lg border-2 not-italic',
                variant.container,
            )}
        >
            <div
                className={cn(
                    'flex items-start justify-center pt-4',
                    variant.iconWrapper,
                )}
            >
                <Icon className={'size-5'} />
            </div>
            <div
                className={cn(
                    'bg-background rounded-l-md',
                    calloutContentClasses,
                )}
            >
                {children}
            </div>
        </blockquote>
    );
}

function TableComponent({ children }: React.ComponentProps<'table'>) {
    return (
        <div className="my-6 overflow-x-auto">
            <table className="[&_td]:border-muted [&_th]:border-muted [&_th]:text-muted-foreground w-full border-collapse [&_td]:border-b [&_td]:py-1.5 [&_td]:text-sm [&_th]:border-b [&_th]:py-1.5 [&_th]:text-left [&_th]:text-xs [&_th]:font-medium">
                {children}
            </table>
        </div>
    );
}


function ImageComponent({ src, alt }: React.ComponentProps<'img'>) {
    return (
        <span data-slot={'image'} className="my-6 block">
            <img
                src={src}
                alt={alt}
                className="max-w-full rounded-lg border shadow-sm"
                loading="lazy"
            />
        </span>
    );
}

type IconName = keyof typeof icons;

interface IconComponentProps {
    name?: string;
    className?: string | string[];
}

function IconComponent({ name, className }: IconComponentProps) {
    if (!name) {
        return null;
    }

    // rehype-sanitize prefixes `name` attribute values with `user-content-`
    // to prevent DOM clobbering. Strip it to recover the original icon key.
    const iconName = name.replace(/^user-content-/, '') as IconName;
    const LucideIcon = icons[iconName];

    if (!LucideIcon) {
        console.warn(`<Icon name="${iconName}" /> not found in lucide-react`);

        return null;
    }

    const resolvedClassName = Array.isArray(className)
        ? className.join(' ')
        : className;

    return (
        <span
            className={
                '-mb-1 inline-flex size-6 items-center justify-center rounded-sm border align-text-bottom'
            }
        >
            <LucideIcon
                className={cn('inline-block size-4', resolvedClassName)}
            />
        </span>
    );
}

const sanitizeSchema = {
    ...defaultSchema,
    tagNames: [...(defaultSchema.tagNames ?? []), 'icon'],
    attributes: {
        ...defaultSchema.attributes,
        icon: ['name', 'className'],
        // Surface the callout type `remarkCallouts` emits as `data-callout`,
        // which `defaultSchema` would otherwise strip from the blockquote.
        blockquote: [
            ...(defaultSchema.attributes?.blockquote ?? []),
            'dataCallout',
        ],
        // Preserve the inline width `remarkTableColumn` emits on table cells,
        // which `defaultSchema` would otherwise strip along with all `style`.
        th: [...(defaultSchema.attributes?.th ?? []), 'style'],
        td: [...(defaultSchema.attributes?.td ?? []), 'style'],
    },
};

export default function DocumentationMarkdownContent({
                                                         content,
                                                         className,
                                                     }: MarkdownContentProps) {
    return (
        <div data-slot={'documentation-markdown'} className={cn('typography', className)}>
            <ReactMarkdown
                remarkPlugins={[remarkGfm, remarkCallouts, remarkTableColumn]}
                rehypePlugins={[rehypeRaw, [rehypeSanitize, sanitizeSchema]]}
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
                    icon: IconComponent
                }}
            >
                {normalizeCallouts(content)}
            </ReactMarkdown>
        </div>
    );
}
