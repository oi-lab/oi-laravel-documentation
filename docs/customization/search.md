---
title: Search
description: Configure and customize search functionality
order: 2
---

# Search

OI Laravel Documentation includes powerful full-text search with relevance scoring. Learn how to configure, customize, and integrate search into your documentation.

## Search Architecture

The search system consists of:

1. **Search Index** - Pre-built JSON containing all searchable content
2. **Search API** - Backend endpoint that performs scoring and ranking
3. **Search UI** - React component for user interaction
4. **Search Scoring** - Relevance algorithm for result ranking

## Search Configuration

Configure search in `config/oi-documentation.php`:

```php
'search' => [
    /**
     * Minimum query length to perform a search.
     */
    'min_query_length' => 2,

    /**
     * Length of excerpt shown in results.
     */
    'excerpt_length' => 150,

    /**
     * Context characters around matched terms.
     */
    'excerpt_context' => 50,
],
```

### min_query_length

Minimum characters required before searching:

```php
'min_query_length' => 2,  // Search requires at least 2 characters
```

**Considerations:**
- Lower values (1-2) are more responsive
- Higher values (3+) reduce server load
- Common words like "php", "api" need at least 2 characters

### excerpt_length

Characters displayed in search results:

```php
'excerpt_length' => 150,  // Show 150 characters per result
```

**Examples:**
- 100 characters: Brief previews
- 150 characters: Balanced context (recommended)
- 250+ characters: Detailed previews

### excerpt_context

Characters shown before and after the matched term:

```php
'excerpt_context' => 50,  // Show 50 chars before and after match
```

If searching for "installation":

```
...for step-by-step installation and setup guide. The 
installation process typically takes about five minutes...
```

## Search Endpoint

The package provides a search API endpoint:

```
GET /documentation/search?q={query}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `q` | string | Yes | Search query |

### Request Example

```javascript
// JavaScript/Fetch
const query = 'installation';
const response = await fetch(`/documentation/search?q=${query}`);
const results = await response.json();
```

### Response Format

```json
{
    "results": [
        {
            "id": "getting-started",
            "title": "Getting Started",
            "section": "Getting Started",
            "url": "/documentation/getting-started",
            "excerpt": "OI Laravel Documentation is a comprehensive package for managing markdown-based documentation sites. Installation is straightforward...",
            "score": 26
        },
        {
            "id": "installation",
            "title": "Installation",
            "section": "Getting Started",
            "url": "/documentation/getting-started/installation",
            "excerpt": "Step-by-step installation and setup guide. Follow these steps to install and configure OI Laravel...",
            "score": 24
        }
    ],
    "totalResults": 2,
    "query": "installation"
}
```

### Response Fields

| Field | Description |
|-------|-------------|
| `id` | Unique page identifier |
| `title` | Page title |
| `section` | Section name |
| `url` | Full documentation URL |
| `excerpt` | Snippet of matching content |
| `score` | Relevance score (higher is more relevant) |

## Search Scoring Algorithm

Results are scored based on where matches occur. Results with higher scores appear first.

### Scoring Rules

```
Title match       → +10 points per occurrence
Description match → +5 points per occurrence
Heading match     → +3 points per occurrence
Content match     → +1 point per occurrence
```

### Scoring Example

Searching for "authentication":

**Page 1: "Authentication Guide"**
- Title contains "authentication": +10
- Description mentions "authentication": +5
- Heading "Setting up authentication": +3
- Content mentions "authentication" 5 times: +5
- **Total Score: 23**

**Page 2: "User Management"**
- Title: no match: 0
- Description: no match: 0
- Headings: no match: 0
- Content mentions "authentication" 2 times: +2
- **Total Score: 2**

Page 1 ranks much higher due to title and description matches.

## Using Search in React

### DocumentationSearch Component

Use the pre-built search component:

```typescript
import { DocumentationSearch } from '@/components/documentation/DocumentationSearch';

export function MyDocumentation() {
  return (
    <DocumentationSearch onSearch={(query) => {
      // Handle search query
    }} />
  );
}
```

### Custom Search Implementation

Build your own search interface:

```typescript
import { useState, useEffect } from 'react';

export function CustomSearch() {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [isLoading, setIsLoading] = useState(false);

  const handleSearch = async (q: string) => {
    if (q.length < 2) {
      setResults([]);
      return;
    }

    setIsLoading(true);
    try {
      const response = await fetch(`/documentation/search?q=${encodeURIComponent(q)}`);
      const data = await response.json();
      setResults(data.results);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    const timer = setTimeout(() => {
      handleSearch(query);
    }, 300); // Debounce 300ms

    return () => clearTimeout(timer);
  }, [query]);

  return (
    <div>
      <input
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Search documentation..."
      />
      <div>
        {isLoading && <p>Searching...</p>}
        {results.map((result) => (
          <a key={result.id} href={result.url}>
            <h3>{result.title}</h3>
            <p>{result.excerpt}</p>
          </a>
        ))}
      </div>
    </div>
  );
}
```

## Highlighting Search Matches

Highlight matched terms in results:

```typescript
function highlightMatches(text: string, query: string): string {
  const regex = new RegExp(`(${query})`, 'gi');
  return text.replace(regex, '<mark>$1</mark>');
}

export function SearchResult({ result, query }: Props) {
  const highlightedExcerpt = highlightMatches(result.excerpt, query);

  return (
    <div>
      <h3>{result.title}</h3>
      <p dangerouslySetInnerHTML={{ __html: highlightedExcerpt }} />
    </div>
  );
}
```

## Customizing Search UI

### Search Result Card

Customize how results display:

```typescript
// resources/js/components/documentation/SearchResultCard.tsx
export function SearchResultCard({ result, query }: Props) {
  return (
    <div className="border rounded-lg p-4 hover:bg-slate-50">
      <div className="flex items-start justify-between">
        <div>
          <h3 className="font-semibold text-lg">
            {highlightMatches(result.title, query)}
          </h3>
          <p className="text-sm text-slate-600">{result.section}</p>
        </div>
        <span className="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
          Score: {result.score}
        </span>
      </div>
      <p className="mt-2 text-sm text-slate-700">
        {result.excerpt}...
      </p>
      <a
        href={result.url}
        className="mt-3 inline-flex items-center text-blue-600 hover:text-blue-800"
      >
        Read more →
      </a>
    </div>
  );
}
```

### Search Modal

Show search results in a modal:

```typescript
import { Dialog } from '@/components/ui/dialog';

export function SearchModal({ isOpen, onClose }: Props) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);

  const handleSearch = async (q: string) => {
    if (q.length < 2) return;
    const response = await fetch(`/documentation/search?q=${q}`);
    const data = await response.json();
    setResults(data.results);
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <input
        autoFocus
        value={query}
        onChange={(e) => {
          setQuery(e.target.value);
          handleSearch(e.target.value);
        }}
        className="w-full px-4 py-2 border rounded-lg"
        placeholder="Search documentation..."
      />
      <div className="mt-4">
        {results.map((result) => (
          <SearchResultCard key={result.id} result={result} query={query} />
        ))}
      </div>
    </Dialog>
  );
}
```

## Debouncing Search

Debounce search to reduce API calls:

```typescript
import { useMemo } from 'react';

function useDebounce(value: string, delay: number) {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => clearTimeout(handler);
  }, [value, delay]);

  return debouncedValue;
}

export function SearchWithDebounce() {
  const [query, setQuery] = useState('');
  const debouncedQuery = useDebounce(query, 300);
  const [results, setResults] = useState([]);

  useEffect(() => {
    if (debouncedQuery.length >= 2) {
      performSearch(debouncedQuery);
    }
  }, [debouncedQuery]);

  return (
    // Your search UI
  );
}
```

## Pagination

Handle large result sets:

```typescript
export function SearchWithPagination() {
  const [query, setQuery] = useState('');
  const [page, setPage] = useState(1);
  const [results, setResults] = useState([]);
  const resultsPerPage = 10;

  const paginatedResults = results.slice(
    (page - 1) * resultsPerPage,
    page * resultsPerPage
  );
  const totalPages = Math.ceil(results.length / resultsPerPage);

  return (
    <div>
      {paginatedResults.map((result) => (
        <SearchResultCard key={result.id} result={result} />
      ))}
      <div className="flex gap-2 mt-4">
        {Array.from({ length: totalPages }, (_, i) => (
          <button
            key={i + 1}
            onClick={() => setPage(i + 1)}
            className={page === i + 1 ? 'font-bold' : ''}
          >
            {i + 1}
          </button>
        ))}
      </div>
    </div>
  );
}
```

## Advanced Features

### Typeahead/Autocomplete

Suggest as user types:

```typescript
export function SearchAutocomplete() {
  const [suggestions, setSuggestions] = useState([]);

  const handleInputChange = async (value: string) => {
    if (value.length < 2) {
      setSuggestions([]);
      return;
    }

    const response = await fetch(`/documentation/search?q=${value}`);
    const data = await response.json();
    
    // Take first 5 results as suggestions
    setSuggestions(data.results.slice(0, 5).map(r => r.title));
  };

  return (
    <div>
      <input onChange={(e) => handleInputChange(e.target.value)} />
      <ul>
        {suggestions.map((suggestion, i) => (
          <li key={i}>{suggestion}</li>
        ))}
      </ul>
    </div>
  );
}
```

### Filter Search Results

Add filtering to search:

```typescript
export function FilteredSearch() {
  const [query, setQuery] = useState('');
  const [section, setSection] = useState('');
  const [results, setResults] = useState([]);

  const handleSearch = async () => {
    const params = new URLSearchParams({
      q: query,
      section: section,
    });
    
    const response = await fetch(`/documentation/search?${params}`);
    const data = await response.json();
    setResults(data.results);
  };

  return (
    <div>
      <input
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Search..."
      />
      <select value={section} onChange={(e) => setSection(e.target.value)}>
        <option value="">All Sections</option>
        <option value="getting-started">Getting Started</option>
        <option value="configuration">Configuration</option>
      </select>
      <button onClick={handleSearch}>Search</button>
    </div>
  );
}
```

## Performance Optimization

### Cache Search Results

```typescript
const searchCache = new Map<string, any>();

async function searchWithCache(query: string) {
  if (searchCache.has(query)) {
    return searchCache.get(query);
  }

  const response = await fetch(`/documentation/search?q=${query}`);
  const data = await response.json();
  searchCache.set(query, data);
  return data;
}
```

### Lazy Load Search Component

```typescript
import { lazy, Suspense } from 'react';

const SearchComponent = lazy(() => import('./Search'));

export function Documentation() {
  return (
    <Suspense fallback={<div>Loading search...</div>}>
      <SearchComponent />
    </Suspense>
  );
}
```

## Testing Search

```typescript
// __tests__/search.test.ts
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Search } from '@/components/documentation/Search';

it('shows results when user types', async () => {
  const user = userEvent.setup();
  render(<Search />);

  const input = screen.getByPlaceholderText('Search...');
  await user.type(input, 'installation');

  await waitFor(() => {
    expect(screen.getByText('Installation')).toBeInTheDocument();
  });
});
```

## Troubleshooting

### Search Returns No Results

Check:
1. Query is at least 2 characters
2. Search index has been generated: `php artisan doc:gen-index`
3. Content exists in documentation
4. Try different search terms

### Search Is Slow

Optimize:
1. Increase `min_query_length` to reduce queries
2. Add debouncing in React component
3. Cache frequently searched terms
4. Check server performance

### Results Not Relevant

Improve by:
1. Ensuring good titles and descriptions
2. Checking search scoring algorithm
3. Using meaningful headings in content

## Next Steps

1. [Customize Components](./react-components.md) - Style search interface
2. [Configuration](../configuration/configuration.md) - Adjust search settings
3. [Write Content](../content/markdown.md) - Create searchable documentation

