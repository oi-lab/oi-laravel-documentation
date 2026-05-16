---
title: React Components
description: Customize published React components
order: 1
---

# React Components

OI Laravel Documentation publishes fully customizable React components to your project. After installation, all components are yours to modify completely.

## Published Components Location

Components are published to:

```
resources/js/components/documentation/
```

All published components are standard React files using TypeScript, fully typed and ready to customize.

## Component Structure

### Layout Components

#### DocumentationLayout.tsx

The main wrapper component:

```typescript
// resources/js/components/documentation/DocumentationLayout.tsx

export interface DocumentationLayoutProps {
  children: React.ReactNode;
  navigation: NavigationStructure;
  currentPath: string;
}

export function DocumentationLayout({
  children,
  navigation,
  currentPath,
}: DocumentationLayoutProps) {
  return (
    <div className="flex min-h-screen bg-white dark:bg-slate-950">
      <DocumentationNavigation 
        navigation={navigation}
        currentPath={currentPath}
      />
      <main className="flex-1">
        {children}
      </main>
    </div>
  );
}
```

**Customization points:**
- Sidebar width and styling
- Main content area styling
- Dark mode theme
- Responsive breakpoints

### Navigation Components

#### DocumentationNavigation.tsx

The sidebar navigation component:

```typescript
interface DocumentationNavigationProps {
  navigation: NavigationStructure;
  currentPath: string;
}

export function DocumentationNavigation({
  navigation,
  currentPath,
}: DocumentationNavigationProps) {
  return (
    <nav className="w-64 border-r border-slate-200 dark:border-slate-800">
      {/* Sidebar content */}
    </nav>
  );
}
```

**Customizable aspects:**
- Navigation styling
- Active link highlighting
- Nested section expansion
- Mobile/tablet behavior

#### DocumentationSearch.tsx

The search interface component:

```typescript
interface DocumentationSearchProps {
  onSearch: (query: string) => void;
  isLoading?: boolean;
}

export function DocumentationSearch({
  onSearch,
  isLoading,
}: DocumentationSearchProps) {
  return (
    <div className="search-container">
      {/* Search UI */}
    </div>
  );
}
```

**Customizable aspects:**
- Search input styling
- Search result display
- Result relevance highlighting
- No results messaging

### Content Components

#### DocumentationMarkdownContent.tsx

Renders markdown to React components:

```typescript
interface DocumentationMarkdownContentProps {
  content: string;
  headings: string[];
}

export function DocumentationMarkdownContent({
  content,
  headings,
}: DocumentationMarkdownContentProps) {
  return (
    <div className="prose dark:prose-invert">
      {/* Markdown rendered here */}
    </div>
  );
}
```

**Customizable aspects:**
- Typography and spacing
- Code block styling
- Link formatting
- Image rendering

#### DocumentationTableOfContents.tsx

Displays page headings:

```typescript
interface DocumentationTableOfContentsProps {
  headings: HeadingItem[];
}

export function DocumentationTableOfContents({
  headings,
}: DocumentationTableOfContentsProps) {
  return (
    <aside className="w-48">
      {/* Table of contents */}
    </aside>
  );
}
```

**Customizable aspects:**
- TOC positioning
- Heading hierarchy display
- Active heading highlighting
- Scroll synchronization

### Navigation Flow Components

#### DocumentationPreviousNext.tsx

Shows previous and next page links:

```typescript
interface DocumentationPreviousNextProps {
  previous?: PageLink;
  next?: PageLink;
}

export function DocumentationPreviousNext({
  previous,
  next,
}: DocumentationPreviousNextProps) {
  return (
    <div className="flex justify-between mt-8">
      {/* Previous/Next buttons */}
    </div>
  );
}
```

## Component Customization Guide

### Modifying Styling

Update Tailwind classes directly:

```typescript
// Before
<nav className="w-64 border-r border-slate-200">

// After - Make it wider and change color
<nav className="w-80 border-r border-blue-200">
```

### Changing Layout Structure

Rearrange components:

```typescript
export function DocumentationLayout() {
  return (
    <div className="flex flex-col">
      <Header />
      <div className="flex">
        <Navigation />
        <div className="flex-1">
          <TOC />
          <Content />
        </div>
      </div>
    </div>
  );
}
```

### Adding Custom Hooks

Use React hooks in components:

```typescript
export function DocumentationNavigation() {
  const [isExpanded, setIsExpanded] = useState(false);
  const theme = useTheme();
  const { pathname } = useLocation();

  return (
    <nav className={theme === 'dark' ? 'bg-slate-950' : 'bg-white'}>
      {/* Updated navigation */}
    </nav>
  );
}
```

### Integrating UI Libraries

Add additional UI libraries:

```typescript
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent } from '@/components/ui/sheet';

export function DocumentationNavigation() {
  return (
    <Sheet>
      <SheetContent>
        {/* Mobile navigation */}
      </SheetContent>
    </Sheet>
  );
}
```

## Publishing System Hooks

The components receive data through Inertia.js props:

```typescript
// pages/Documentation.tsx
import { DocumentationLayout } from '@/components/documentation/DocumentationLayout';

export default function Documentation({ 
  page, 
  navigation, 
  searchResults 
}: Props) {
  return (
    <DocumentationLayout navigation={navigation}>
      <DocumentationMarkdownContent content={page.content} />
    </DocumentationLayout>
  );
}
```

## Styling with Tailwind

All components use Tailwind CSS. Customize global styles:

```css
/* resources/css/documentation.css */

@layer components {
  .doc-heading {
    @apply text-2xl font-bold text-slate-900 dark:text-white;
  }
  
  .doc-code {
    @apply font-mono text-sm;
  }
}
```

Then use in components:

```typescript
<h2 className="doc-heading">Title</h2>
<code className="doc-code">const x = 1;</code>
```

## Dark Mode Support

Components use Tailwind's dark mode:

```typescript
<div className="bg-white dark:bg-slate-950">
  <p className="text-slate-900 dark:text-white">
    Content
  </p>
</div>
```

Customize dark mode in `tailwind.config.js`:

```javascript
module.exports = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Custom dark colors
      }
    }
  }
}
```

## Component Props

All components accept standard React props:

```typescript
interface ComponentProps extends React.HTMLAttributes<HTMLDivElement> {
  // Custom props
  data?: any;
  onEvent?: () => void;
}
```

You can pass additional props:

```typescript
<DocumentationLayout
  className="custom-layout"
  id="my-docs"
  data-test="documentation"
>
  {/* Children */}
</DocumentationLayout>
```

## Common Customizations

### Change Navigation Width

```typescript
// DocumentationLayout.tsx
<aside className="w-80"> {/* Changed from w-64 */}
```

### Modify Code Block Styling

```typescript
// DocumentationMarkdownContent.tsx
const codeBlockClasses = "bg-slate-900 text-white p-4 rounded-lg";
```

### Add Analytics Events

```typescript
// DocumentationNavigation.tsx
const handleLinkClick = (path: string) => {
  // Send to analytics
  gtag('event', 'documentation_click', { path });
};
```

### Customize Search Results Display

```typescript
// DocumentationSearch.tsx
const renderResult = (result: SearchResult) => (
  <div className="custom-result-styling">
    <h4>{result.title}</h4>
    <p>{result.description}</p>
    <span className="badge">{result.section}</span>
  </div>
);
```

### Add Breadcrumbs

```typescript
// DocumentationLayout.tsx
<Breadcrumbs path={currentPath} />
<DocumentationContent />
```

## TypeScript Support

All components are fully typed:

```typescript
import type { 
  DocumentationPage,
  NavigationStructure,
  SearchResult 
} from '@/types/documentation';

interface CustomComponentProps {
  page: DocumentationPage;
  navigation: NavigationStructure;
}
```

Add your own types:

```typescript
// types/documentation.ts
export interface CustomTheme {
  primaryColor: string;
  accentColor: string;
}
```

## Testing Components

Test custom components:

```typescript
// __tests__/DocumentationLayout.test.tsx
import { render, screen } from '@testing-library/react';
import { DocumentationLayout } from '@/components/documentation/DocumentationLayout';

it('renders layout with navigation', () => {
  render(<DocumentationLayout navigation={mockNav} />);
  expect(screen.getByRole('navigation')).toBeInTheDocument();
});
```

## Performance Optimization

Memoize components to prevent unnecessary re-renders:

```typescript
import { memo } from 'react';

export const DocumentationNavigation = memo(function DocumentationNavigation({
  navigation,
}: DocumentationNavigationProps) {
  return <nav>{/* Content */}</nav>;
});
```

Use lazy loading for heavy components:

```typescript
import { lazy, Suspense } from 'react';

const DocumentationSearch = lazy(() => import('./DocumentationSearch'));

export function DocumentationLayout() {
  return (
    <Suspense fallback={<div>Loading...</div>}>
      <DocumentationSearch />
    </Suspense>
  );
}
```

## Accessibility (a11y)

Ensure components are accessible:

```typescript
<button
  aria-label="Toggle navigation"
  aria-expanded={isOpen}
  onClick={toggleNav}
>
  {/* Content */}
</button>

<nav aria-label="Documentation navigation">
  {/* Navigation */}
</nav>
```

## Best Practices

1. **Keep components focused** - One responsibility per component
2. **Use TypeScript** - Full type safety
3. **Document props** - Use JSDoc comments
4. **Test changes** - Especially after styling modifications
5. **Version components** - Track breaking changes
6. **Maintain dark mode** - Keep `dark:` classes
7. **Use semantic HTML** - Proper accessibility

## Component Map

Quick reference of all publishable components:

| Component | Purpose | File |
|-----------|---------|------|
| DocumentationLayout | Main wrapper | DocumentationLayout.tsx |
| DocumentationNavigation | Sidebar menu | DocumentationNavigation.tsx |
| DocumentationSearch | Search interface | DocumentationSearch.tsx |
| DocumentationMarkdownContent | Markdown rendering | DocumentationMarkdownContent.tsx |
| DocumentationTableOfContents | Page headings | DocumentationTableOfContents.tsx |
| DocumentationPreviousNext | Navigation links | DocumentationPreviousNext.tsx |
| DocumentationHeader | Page header | DocumentationHeader.tsx |
| DocumentationFooter | Page footer | DocumentationFooter.tsx |

## Next Steps

1. [Customize Search](./search.md) - Configure search behavior
2. [View Getting Started](../getting-started/installation.md) - Installation guide
3. [Write Content](../content/markdown.md) - Create documentation pages

