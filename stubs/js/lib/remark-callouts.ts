/**
 * Minimal structural mdast typings—kept local so the plugin does not depend on
 * `@types/mdast`, which is not a direct dependency of this project.
 */
interface MdNode {
    type: string;
    children?: MdNode[];
    value?: string;
    data?: {
        hProperties?: Record<string, unknown>;
        [key: string]: unknown;
    };
}

interface MdParent extends MdNode {
    children: MdNode[];
}

/**
 * Callout flavours derived from the leading marker of a block:
 * - `quote`  → classic citation (`>`)
 * - `info`   → tip / information (`i>`)
 * - `danger` → forbidden / error (`x>`)
 */
export type CalloutType = 'quote' | 'info' | 'danger';

/** Matches a line-level `i>` / `x>` marker, capturing the remaining content. */
const LINE_MARKER = /^([ix])>[ \t]?(.*)$/;

/** Matches an opening/closing fenced code block delimiter. */
const CODE_FENCE = /^\s*(`{3,}|~{3,})/;

/** Sentinel tag emitted per marker so the tree transform can resolve the type. */
const TYPE_TAG: Record<string, string> = {
    i: '[!INFO]',
    x: '[!DANGER]',
};

/** Reads back the callout type from an alert sentinel tag. */
const ALERT_TAG = /^\[!(INFO|DANGER)\]\s*/;

function tagToType(tag: string): CalloutType {
    return tag === 'INFO' ? 'info' : 'danger';
}

/**
 * Rewrite runs of `i>` / `x>` lines into genuine Markdown blockquotes carrying
 * an alert sentinel (`> [!INFO]` / `> [!DANGER]`). This must happen on the raw
 * source—before parsing—so the blockquote body (paragraphs, lists, emphasis…)
 * is interpreted normally by the Markdown parser.
 *
 * Lines inside fenced code blocks are left untouched.
 */
export function normalizeCallouts(content: string | null | undefined): string {
    if (!content) {
        return content ?? '';
    }

    const lines = content.split('\n');
    const out: string[] = [];
    let index = 0;
    let inFence = false;

    while (index < lines.length) {
        const line = lines[index];

        if (CODE_FENCE.test(line)) {
            inFence = !inFence;
            out.push(line);
            index++;
            continue;
        }

        const match = inFence ? null : LINE_MARKER.exec(line);

        if (!match) {
            out.push(line);
            index++;
            continue;
        }

        const marker = match[1];
        const body: string[] = [];

        // Gather the contiguous run of same-marker lines.
        while (index < lines.length) {
            const next = LINE_MARKER.exec(lines[index]);

            if (!next || next[1] !== marker) {
                break;
            }

            body.push(next[2]);
            index++;
        }

        // Keep the blockquote detached from any preceding block.
        if (out.length > 0 && out[out.length - 1].trim() !== '') {
            out.push('');
        }

        out.push(`> ${TYPE_TAG[marker]}`);
        out.push('>');

        for (const bodyLine of body) {
            out.push(bodyLine.length > 0 ? `> ${bodyLine}` : '>');
        }

        out.push('');
    }

    return out.join('\n');
}

/**
 * Expose the callout type to the rehype layer as a `data-callout` attribute on
 * the `<blockquote>` element so the renderer can pick a colour and icon.
 */
function applyCallout(node: MdNode, type: CalloutType): void {
    node.data = node.data ?? {};
    node.data.hProperties = {
        ...(node.data.hProperties ?? {}),
        dataCallout: type,
    };
}

function isParent(node: MdNode): node is MdParent {
    return Array.isArray(node.children);
}

/**
 * Resolve a blockquote's callout type from its leading alert sentinel and strip
 * that sentinel from the tree. Returns `quote` for plain blockquotes.
 */
function resolveBlockquoteType(blockquote: MdParent): CalloutType {
    const firstParagraph = blockquote.children.find(
        (node): node is MdParent =>
            node.type === 'paragraph' && isParent(node),
    );

    if (!firstParagraph) {
        return 'quote';
    }

    const firstText = firstParagraph.children[0];

    if (
        !firstText ||
        firstText.type !== 'text' ||
        typeof firstText.value !== 'string'
    ) {
        return 'quote';
    }

    const match = ALERT_TAG.exec(firstText.value);

    if (!match) {
        return 'quote';
    }

    const rest = firstText.value.slice(match[0].length);

    if (rest.length === 0 && firstParagraph.children.length === 1) {
        const paragraphIndex = blockquote.children.indexOf(firstParagraph);
        blockquote.children.splice(paragraphIndex, 1);
    } else {
        firstText.value = rest;
    }

    return tagToType(match[1]);
}

function transformChildren(parent: MdParent): void {
    for (const child of parent.children) {
        if (child.type === 'blockquote' && isParent(child)) {
            applyCallout(child, resolveBlockquoteType(child));
        }

        if (isParent(child)) {
            transformChildren(child);
        }
    }
}

/**
 * Remark plugin that types blockquotes carrying an alert sentinel injected by
 * {@link normalizeCallouts}: `[!INFO]` (info) and `[!DANGER]` (danger). Plain
 * `>` blockquotes resolve to classic quotes. The resolved type is surfaced via
 * a `data-callout` attribute on the rendered `<blockquote>`.
 */
export default function remarkCallouts() {
    return (tree: MdNode): void => {
        if (isParent(tree)) {
            transformChildren(tree);
        }
    };
}
