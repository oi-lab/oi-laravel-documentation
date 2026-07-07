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
 * Matches a trailing column directive appended to a header cell, e.g.
 * `Libellé ::width=5rem`. The value runs up to the next whitespace (the cell's
 * surrounding pipes are already stripped by the GFM table parser), so any CSS
 * length is accepted: `5rem`, `120px`, `20%`, `10ch`… The leading whitespace is
 * captured too so it can be trimmed away with the directive.
 */
const WIDTH_DIRECTIVE = /\s*::width=(\S+)\s*$/i;

function isParent(node: MdNode): node is MdParent {
    return Array.isArray(node.children);
}

/**
 * Push a fixed `width` onto a cell's `hProperties.style`, merging with any style
 * already present so the rehype layer renders `<th style="width: …">` /
 * `<td style="width: …">`. Applying the width to every cell in the column makes
 * it effective under the default `table-layout: auto`, not only `fixed`.
 */
function applyWidth(cell: MdNode, width: string): void {
    cell.data = cell.data ?? {};
    const hProperties = (cell.data.hProperties = cell.data.hProperties ?? {});
    const existing =
        typeof hProperties.style === 'string' ? hProperties.style.trim() : '';
    const separator = existing && !existing.endsWith(';') ? '; ' : ' ';

    hProperties.style = `${existing}${existing ? separator : ''}width: ${width}`;
}

/**
 * Read and strip a `::width=…` directive from a header cell, returning the
 * captured width (or `null` when the cell carries no directive). The directive
 * lives in the cell's last text node; that node is trimmed of the directive and
 * dropped entirely when nothing readable remains.
 */
function extractWidth(cell: MdParent): string | null {
    for (let index = cell.children.length - 1; index >= 0; index--) {
        const child = cell.children[index];

        if (child.type !== 'text' || typeof child.value !== 'string') {
            continue;
        }

        const match = WIDTH_DIRECTIVE.exec(child.value);

        if (!match) {
            // Only the trailing text node can hold the directive; once we hit a
            // non-empty text node without one, there is nothing to find.
            if (child.value.trim().length > 0) {
                return null;
            }

            continue;
        }

        const stripped = child.value.slice(0, match.index);

        if (stripped.length > 0) {
            child.value = stripped;
        } else {
            cell.children.splice(index, 1);
        }

        return match[1];
    }

    return null;
}

/**
 * Resolve fixed column widths from a table's header row and apply each to every
 * cell sharing its column index. The first row of a GFM table is the header;
 * its cells are matched positionally against the body rows.
 */
function transformTable(table: MdParent): void {
    const [headerRow, ...bodyRows] = table.children;

    if (!headerRow || !isParent(headerRow)) {
        return;
    }

    headerRow.children.forEach((cell, columnIndex) => {
        if (!isParent(cell)) {
            return;
        }

        const width = extractWidth(cell);

        if (!width) {
            return;
        }

        applyWidth(cell, width);

        for (const row of bodyRows) {
            if (isParent(row) && isParent(row.children[columnIndex])) {
                applyWidth(row.children[columnIndex], width);
            }
        }
    });
}

function transformChildren(parent: MdParent): void {
    for (const child of parent.children) {
        if (child.type === 'table' && isParent(child)) {
            transformTable(child);

            continue;
        }

        if (isParent(child)) {
            transformChildren(child);
        }
    }
}

/**
 * Remark plugin letting authors fix a table column's width from Markdown by
 * appending a `::width=<length>` directive to its header cell:
 *
 * ```markdown
 * | Produit ::width=8rem | Description | Prix ::width=6rem |
 * | -------------------- | ----------- | ----------------- |
 * | …                    | …           | …                 |
 * ```
 *
 * The directive is stripped from the rendered header, and the resolved width is
 * surfaced as an inline `width` style on every `<th>` / `<td>` of that column.
 * Pairs with `remarkGfm`, which must run first to parse the table itself.
 */
export default function remarkTableColumn() {
    return (tree: MdNode): void => {
        if (isParent(tree)) {
            transformChildren(tree);
        }
    };
}
