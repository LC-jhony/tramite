# filament-tables (v5)

Generate complete **Filament PHP v5** table configurations — ready-to-paste PHP `table()` method bodies with columns, filters, actions, bulk actions, layout, summaries, grouping, empty state, and custom-data support.

---

## IMPORTANT: v5 API CHANGES vs v4

Before generating any code, internalize these breaking changes:

| Feature | v4 | v5 |
|---|---|---|
| Row actions | `->actions([])` | `->recordActions([])` |
| Bulk actions | `->bulkActions([])` | `->toolbarActions([])` or `->groupedBulkActions([])` |
| Header actions | `->headerActions([])` | `->headerActions([])` *(unchanged)* |
| Bulk action class | `Filament\Tables\Actions\BulkAction` | `Filament\Actions\BulkAction` |
| Bulk action group | `Filament\Tables\Actions\BulkActionGroup` | `Filament\Actions\BulkActionGroup` |
| Action class | `Filament\Tables\Actions\Action` | `Filament\Actions\Action` |
| Delete bulk | `Filament\Tables\Actions\DeleteBulkAction` | `Filament\Actions\DeleteBulkAction` |

---

## WORKFLOW

1. **Identify requirements** from user description:
   - Which columns to display (type, searchable, sortable, toggleable)
   - Which filters (checkbox, select, ternary, date range, query builder)
   - Which actions (record, header, toolbar/bulk)
   - Layout needs (responsive, grid, collapsible)
   - Summaries, grouping, empty state customizations
   - Data source (Eloquent model or custom/API)

2. **Choose column types** from the list below.

3. **Generate the complete `table(Table $table): Table` method** with all requested features.

4. **Always include** the correct `use` imports at the top.

---

## COLUMN TYPES

### Built-in columns (namespace: `Filament\Tables\Columns\`)
- `TextColumn` — text, numbers, dates, badges, descriptions
- `IconColumn` — boolean icons, status icons
- `ImageColumn` — avatar/thumbnail images
- `ColorColumn` — color swatches
- `SelectColumn` — editable select dropdown (inline edit)
- `ToggleColumn` — editable boolean toggle (inline edit)
- `TextInputColumn` — editable text field (inline edit)
- `CheckboxColumn` — editable checkbox (inline edit)

### Common TextColumn modifiers
```php
TextColumn::make('name')
    ->label('Full Name')           // custom header label
    ->searchable()                 // adds to global search
    ->searchable(isIndividual: true) // per-column search field
    ->sortable()                   // sortable column header
    ->toggleable()                 // user can hide/show
    ->toggleable(isToggledHiddenByDefault: true) // hidden by default
    ->limit(50)                    // truncate text
    ->tooltip(fn ($record) => $record->name) // hover tooltip
    ->headerTooltip('Full name of user') // header hover tooltip
    ->money('USD')                 // currency format
    ->numeric(decimalPlaces: 2)    // number format
    ->dateTime()                   // format as datetime
    ->date()                       // format as date
    ->since()                      // "3 hours ago"
    ->copyable()                   // copy-to-clipboard icon
    ->badge()                      // render as badge
    ->color(fn ($state) => match($state) { 'active' => 'success', default => 'gray' })
    ->icon('heroicon-m-check')     // prepend icon
    ->description(fn ($record) => $record->email) // secondary line
    ->placeholder('N/A')           // shown when null
    ->default('Unknown')           // value when null
    ->url(fn ($record) => route('x', $record)) // clickable link
    ->openUrlInNewTab()
    ->alignEnd()                   // right-align
    ->alignCenter()
    ->grow(false)                  // don't stretch in split layout
    ->width('1%')                  // fixed width
    ->hidden(fn () => !auth()->user()->isAdmin()) // conditional hide
    ->visible(fn () => auth()->user()->isAdmin())
    ->state(fn ($record) => $record->computed_value) // custom state
    ->formatStateUsing(fn ($state) => strtoupper($state))
    ->counts('comments')           // count related records
    ->exists('subscription')       // boolean existence check
    ->sum('orderItems', 'quantity')// aggregate
    ->avg('reviews', 'rating')
    ->prefix('$')
    ->suffix(' items')
    ->wrapHeader()                 // wrap long header text
    ->extraAttributes(['class' => 'font-bold']) // extra HTML attrs
    // Relationship dot-notation:
TextColumn::make('author.name')    // eager-loaded automatically
TextColumn::make('category.parent.name') // nested relations
```

### IconColumn
```php
IconColumn::make('is_active')
    ->boolean()                   // green check / red cross
    ->icon(fn ($state) => $state ? 'heroicon-m-check' : 'heroicon-m-x-mark')
    ->color(fn ($state) => $state ? 'success' : 'danger')
    ->summarize(Count::make()->icons()) // count icons in summary
```

### ImageColumn
```php
ImageColumn::make('avatar')
    ->circular()                  // round avatar
    ->size(40)
    ->stacked()                   // overlapping stack for HasMany
    ->limit(3)
    ->grow(false)
```

### ColumnGroup (group headers)
```php
use Filament\Tables\Columns\ColumnGroup;

ColumnGroup::make('Contact Info', [
    TextColumn::make('email'),
    TextColumn::make('phone'),
])->alignCenter()->wrapHeader()
```

---

## FILTERS

Namespace: `Filament\Tables\Filters\`

### Checkbox filter (default)
```php
Filter::make('is_featured')
    ->label('Featured only')
    ->query(fn (Builder $query) => $query->where('is_featured', true))
    ->toggle()  // render as toggle instead of checkbox
    ->default() // active by default
```

### SelectFilter
```php
SelectFilter::make('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ])
    ->multiple()    // allow multiple selections
    ->searchable()  // searchable dropdown

// From relationship:
SelectFilter::make('author')
    ->relationship('author', 'name')
    ->preload()
    ->searchable()
```

### TernaryFilter
```php
use Filament\Tables\Filters\TernaryFilter;

TernaryFilter::make('is_verified')
    ->label('Email verified')
    ->trueLabel('Verified')
    ->falseLabel('Unverified')
    ->queries(
        true: fn (Builder $q) => $q->whereNotNull('email_verified_at'),
        false: fn (Builder $q) => $q->whereNull('email_verified_at'),
    )
// Soft-delete built-in ternary:
TernaryFilter::make('trashed')
    ->placeholder('Without trashed')
    ->trueLabel('With trashed')
    ->falseLabel('Only trashed')
    ->queries(
        true: fn (Builder $q) => $q->withTrashed(),
        false: fn (Builder $q) => $q->onlyTrashed(),
    )
    ->baseQuery(fn (Builder $q) => $q->withoutGlobalScopes([SoftDeletingScope::class]))
```

### Custom filter with form fields
```php
use Filament\Forms\Components\DatePicker;

Filter::make('created_between')
    ->schema([
        DatePicker::make('created_from')->label('From'),
        DatePicker::make('created_until')->label('Until'),
    ])
    ->query(function (Builder $query, array $data): Builder {
        return $query
            ->when($data['created_from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($data['created_until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    })
    ->columns(2)
```

### Filter table-level options
```php
->deferFilters(false)           // live filters (no "Apply" button)
->persistFiltersInSession()     // remember filters across page loads
->deselectAllRecordsWhenFiltered(false) // keep selection when filter changes
->filtersTriggerAction(fn ($action) => $action->button()->label('Filter'))
```

---

## ACTIONS (v5 API — critical)

### Imports
```php
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
```

### Record actions (per-row, v5: `recordActions`)
```php
->recordActions([
    ViewAction::make(),
    EditAction::make(),
    DeleteAction::make(),
    ReplicateAction::make()
        ->requiresConfirmation(),

    // Custom action
    Action::make('feature')
        ->label('Feature')
        ->icon('heroicon-m-star')
        ->color('warning')
        ->requiresConfirmation()
        ->modalHeading('Feature this post?')
        ->action(fn (Post $record) => $record->update(['is_featured' => true]))
        ->hidden(fn (Post $record) => $record->is_featured)
        ->visible(fn (Post $record) => !$record->is_featured),

    // Grouped record actions dropdown
    ActionGroup::make([
        ViewAction::make(),
        EditAction::make(),
        DeleteAction::make(),
    ]),
])
```

### Position record actions before columns
```php
use Filament\Tables\Enums\RecordActionsPosition;

->recordActions([...], position: RecordActionsPosition::BeforeColumns)
->recordActions([...], position: RecordActionsPosition::BeforeCells)
```

### Toolbar/bulk actions (v5: `toolbarActions` or `groupedBulkActions`)
```php
// Grouped (dropdown) — most common
->toolbarActions([
    BulkActionGroup::make([
        DeleteBulkAction::make(),
        ForceDeleteBulkAction::make(),
        RestoreBulkAction::make(),
        BulkAction::make('approve')
            ->label('Approve Selected')
            ->icon('heroicon-m-check')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records) => $records->each->update(['status' => 'approved'])),
    ]),
])

// Shorthand when all bulk actions are grouped:
->groupedBulkActions([
    DeleteBulkAction::make(),
    BulkAction::make('export')->action(fn (Collection $records) => ...),
])
```

### Header actions (above table)
```php
->headerActions([
    CreateAction::make(),
    ImportAction::make()->importer(PostImporter::class),
    ExportAction::make()->exporter(PostExporter::class),
    Action::make('sync')
        ->label('Sync from API')
        ->icon('heroicon-m-arrow-path')
        ->action(fn () => SyncService::run()),
])
```

### Action with form modal
```php
Action::make('updateStatus')
    ->schema([
        Select::make('status')->options(['draft'=>'Draft','published'=>'Published']),
        Textarea::make('note')->label('Internal note'),
    ])
    ->action(function (Post $record, array $data): void {
        $record->update(['status' => $data['status']]);
    })
    ->fillForm(fn (Post $record) => ['status' => $record->status])
```

### Bulk action with individual record authorization
```php
BulkAction::make('delete')
    ->requiresConfirmation()
    ->authorizeIndividualRecords('delete')
    ->deselectRecordsAfterCompletion()
    ->successNotificationTitle('Deleted records')
    ->failureNotificationTitle(fn (int $s, int $t) => "{$s} of {$t} deleted")
    ->action(fn (Collection $records) => $records->each->delete())
```

### Accessing selected rows in a record action
```php
->selectable()
->recordActions([
    Action::make('copyToSelected')
        ->accessSelectedRecords()
        ->action(function (Model $record, Collection $selectedRecords) {
            $selectedRecords->each->update(['status' => $record->status]);
        }),
])
```

---

## LAYOUT (responsive)

### Simple stacking on mobile
```php
->stackedOnMobile()  // auto-stacks all cells vertically on mobile
```

### Split + Stack layout
```php
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Grid;

->columns([
    Split::make([
        ImageColumn::make('avatar')->circular()->grow(false),
        TextColumn::make('name')->weight(FontWeight::Bold)->searchable()->sortable(),
        Stack::make([
            TextColumn::make('phone')->icon('heroicon-m-phone'),
            TextColumn::make('email')->icon('heroicon-m-envelope'),
        ])->visibleFrom('md'),
    ])->from('md'),
    Panel::make([
        Split::make([
            TextColumn::make('address'),
            TextColumn::make('city'),
        ])->from('md'),
    ])->collapsible(),
])
```

### Grid layout
```php
use Filament\Tables\Columns\Layout\Grid;

Grid::make(['lg' => 2, '2xl' => 4])
    ->schema([
        TextColumn::make('name'),
        TextColumn::make('email'),
        TextColumn::make('phone'),
        TextColumn::make('city'),
    ])
```

### Content grid (card view)
```php
->contentGrid(['md' => 2, 'xl' => 3])
```

### Responsive visibility
```php
TextColumn::make('slug')->visibleFrom('md')  // hide on mobile
TextColumn::make('id')->hiddenFrom('lg')      // hide on desktop
```

---

## SUMMARIES

Attach to columns using `->summarize()`. Cannot use on the first column.

```php
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;

TextColumn::make('price')
    ->summarize([
        Sum::make()->label('Total')->money('USD'),
        Average::make()->label('Average')->numeric(decimalPlaces: 2),
        Range::make()->label('Range'),
    ])

// Count with scope
IconColumn::make('is_published')->boolean()
    ->summarize(Count::make()->query(fn (Builder $q) => $q->where('is_published', true)))

// Count icons visually
IconColumn::make('status')
    ->summarize(Count::make()->icons())

// Custom summarizer
TextColumn::make('notes')
    ->summarize(Summarizer::make()
        ->label('Most recent')
        ->using(fn (Builder $query): string => $query->max('updated_at') ?? 'N/A'))

// Groups-only reporting view
->defaultGroup('category')
->groupsOnly()

// Hide summary rows
->summaries(pageCondition: false, allTableCondition: true)
```

---

## GROUPING

```php
use Filament\Tables\Grouping\Group;

// Simple
->defaultGroup('status')

// With user-selectable groups
->groups(['status', 'category'])
->defaultGroup('status')

// Full Group object
->groups([
    Group::make('status')
        ->label('Publication status')
        ->getTitleFromRecordUsing(fn (Post $r) => ucfirst($r->status->getLabel()))
        ->getDescriptionFromRecordUsing(fn (Post $r) => $r->status->getDescription())
        ->titlePrefixedWithLabel(false)
        ->collapsible(),

    Group::make('created_at')
        ->label('Date')
        ->date()
        ->collapsible(),

    Group::make('author.name')
        ->label('Author'),
])
->collapsedGroupsByDefault()
->groupingSettingsInDropdownOnDesktop()

// Custom ordering/scoping
Group::make('status')
    ->orderQueryUsing(fn (Builder $q, string $dir) => $q->orderBy('status', $dir))
    ->scopeQueryByKeyUsing(fn (Builder $q, string $key) => $q->where('status', $key))
```

---

## EMPTY STATE

```php
->emptyStateHeading('No posts yet')
->emptyStateDescription('Once you write your first post, it will appear here.')
->emptyStateIcon('heroicon-o-document-text')
->emptyStateActions([
    Action::make('create')
        ->label('Create post')
        ->url(route('posts.create'))
        ->icon('heroicon-m-plus')
        ->button(),
])
// Or fully custom:
->emptyState(view('tables.posts.empty-state'))
```

---

## PAGINATION

```php
->paginated([10, 25, 50, 100, 'all'])
->defaultPaginationPageOption(25)
->extremePaginationLinks()
->paginationMode(PaginationMode::Cursor)   // or Simple
->queryStringIdentifier('users')           // prevent conflicts on multi-table pages
->paginated(false)                         // disable pagination
->deferLoading()                           // async load
->poll('10s')                              // auto-refresh
```

---

## TABLE-LEVEL FEATURES

```php
// Row behavior
->recordUrl(fn (Model $record) => route('posts.edit', $record))
->openRecordUrlInNewTab()
->striped()
->recordClasses(fn (Post $record) => match($record->status) {
    'draft' => 'opacity-50',
    default => null,
})

// Reordering
->reorderable('sort')
->reorderable('sort', auth()->user()->isAdmin())

// Header
->heading('Posts')
->description('Manage blog posts.')

// Search
->searchPlaceholder('Search (ID, title, author...)')
->searchDebounce('750ms')
->searchOnBlur()
->persistSearchInSession()
->persistColumnSearchesInSession()
->splitSearchTerms(false)

// Columns manager
->reorderableColumns()
->deferColumnManager(false) // live column manager

// Default sort
->defaultSort('created_at', 'desc')
->persistSortInSession()

// Performance
->deferLoading()
->selectCurrentPageOnly()  // prevent bulk-select all pages
->maxSelectableRecords(100)
```

---

## CUSTOM DATA (non-Eloquent)

Use `->records()` instead of Eloquent when data comes from arrays, APIs, etc.

```php
->records(fn (
    ?string $sortColumn,
    ?string $sortDirection,
    ?string $search,
    array $filters,
    int $page,
    int $recordsPerPage
): LengthAwarePaginator|Collection|array => ...)
```

### Example: API-backed table
```php
->records(function (
    ?string $search,
    ?string $sortColumn,
    ?string $sortDirection,
    int $page,
    int $recordsPerPage
): LengthAwarePaginator {
    $response = Http::get('https://api.example.com/products', [
        'search' => $search,
        'sort' => $sortColumn,
        'order' => $sortDirection,
        'page' => $page,
        'per_page' => $recordsPerPage,
    ])->collect();

    return new LengthAwarePaginator(
        items: $response['data'],
        total: $response['total'],
        perPage: $recordsPerPage,
        currentPage: $page,
    );
})
```

- Column names map to **array keys**, not model attributes
- Use `$record` typed as `array` inside column closures
- Implement sorting, searching, filtering manually inside `records()`
- For bulk actions across pages: use `->resolveSelectedRecordsUsing(fn (array $keys) => ...)`
- Filter values: checkbox→`$filters['name']['isActive']`, select→`$filters['name']['value']`, custom→`$filters['name']['fieldName']`

---

## GLOBAL SETTINGS (AppServiceProvider)

```php
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;

Table::configureUsing(function (Table $table): void {
    $table
        ->paginationPageOptions([10, 25, 50])
        ->filtersLayout(FiltersLayout::AboveContentCollapsible)
        ->reorderableColumns()
        ->striped();
});

// Push global columns (e.g. timestamps) to all tables
Table::configureUsing(function (Table $table): void {
    $table->pushColumns([
        TextColumn::make('created_at')->sortable()->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')->sortable()->toggleable(isToggledHiddenByDefault: true),
    ]);
});
```

---

## COMPLETE EXAMPLE (Post table, Filament v5)

```php
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

public function table(Table $table): Table
{
    return $table
        ->heading('Blog Posts')
        ->description('Manage all blog content.')
        ->columns([
            ImageColumn::make('featured_image')
                ->circular()
                ->grow(false),
            TextColumn::make('title')
                ->searchable()
                ->sortable()
                ->weight(\Filament\Support\Enums\FontWeight::Bold)
                ->description(fn (Post $record) => $record->excerpt),
            TextColumn::make('author.name')
                ->sortable()
                ->searchable()
                ->toggleable(),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'published' => 'success',
                    'draft' => 'gray',
                    'reviewing' => 'warning',
                })
                ->sortable(),
            IconColumn::make('is_featured')
                ->boolean()
                ->label('Featured')
                ->toggleable(),
            TextColumn::make('views_count')
                ->numeric()
                ->sortable()
                ->summarize(Sum::make()->label('Total views'))
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('published_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'reviewing' => 'Reviewing',
                    'published' => 'Published',
                ])
                ->multiple(),
            SelectFilter::make('author')
                ->relationship('author', 'name')
                ->preload()
                ->searchable(),
            Filter::make('is_featured')
                ->label('Featured only')
                ->query(fn (Builder $q) => $q->where('is_featured', true))
                ->toggle(),
            Filter::make('published_between')
                ->schema([
                    \Filament\Forms\Components\DatePicker::make('published_from')->label('From'),
                    \Filament\Forms\Components\DatePicker::make('published_until')->label('Until'),
                ])
                ->query(function (Builder $q, array $data): Builder {
                    return $q
                        ->when($data['published_from'], fn ($q, $v) => $q->whereDate('published_at', '>=', $v))
                        ->when($data['published_until'], fn ($q, $v) => $q->whereDate('published_at', '<=', $v));
                })
                ->columns(2),
            TernaryFilter::make('trashed')
                ->placeholder('Without trashed')
                ->trueLabel('With trashed')
                ->falseLabel('Only trashed')
                ->queries(
                    true: fn (Builder $q) => $q->withTrashed(),
                    false: fn (Builder $q) => $q->onlyTrashed(),
                )
                ->baseQuery(fn (Builder $q) => $q->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class]))
                ->excludeWhenResolvingRecord(),
        ])
        ->recordActions([
            ViewAction::make(),
            EditAction::make(),
            Action::make('feature')
                ->icon('heroicon-m-star')
                ->color('warning')
                ->requiresConfirmation()
                ->action(fn (Post $record) => $record->update(['is_featured' => true]))
                ->hidden(fn (Post $record) => $record->is_featured),
            Action::make('unfeature')
                ->icon('heroicon-m-star')
                ->color('gray')
                ->action(fn (Post $record) => $record->update(['is_featured' => false]))
                ->visible(fn (Post $record) => $record->is_featured),
            ActionGroup::make([
                DeleteAction::make(),
                \Filament\Actions\ForceDeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),
            ]),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
                \Filament\Actions\ForceDeleteBulkAction::make(),
                \Filament\Actions\RestoreBulkAction::make(),
                \Filament\Actions\BulkAction::make('markFeatured')
                    ->label('Mark as Featured')
                    ->icon('heroicon-m-star')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(fn (Collection $records) => $records->each->update(['is_featured' => true])),
            ]),
        ])
        ->headerActions([
            \Filament\Actions\CreateAction::make(),
        ])
        ->groups([
            Group::make('status')->label('Status')->collapsible(),
            Group::make('author.name')->label('Author'),
            Group::make('created_at')->label('Date')->date()->collapsible(),
        ])
        ->defaultSort('created_at', 'desc')
        ->striped()
        ->deferLoading()
        ->persistFiltersInSession()
        ->persistSearchInSession()
        ->searchPlaceholder('Search title, author...')
        ->emptyStateHeading('No posts found')
        ->emptyStateDescription('Try adjusting your filters or create a new post.')
        ->emptyStateIcon('heroicon-o-document-text')
        ->emptyStateActions([
            \Filament\Actions\Action::make('create')
                ->label('Create post')
                ->url(route('filament.admin.resources.posts.create'))
                ->icon('heroicon-m-plus')
                ->button(),
        ]);
}
```

---

## PERFORMANCE TIPS

- Use `with()` in the resource's `getEloquentQuery()` to eager-load relationships accessed in columns
- Dot-notation columns (`author.name`) are auto-eager-loaded by Filament
- Use `->toggleable(isToggledHiddenByDefault: true)` for rarely-needed columns to keep page load fast
- Use `->deferLoading()` for tables with heavy queries
- Use `->chunkSelectedRecords(250)` in bulk actions that process large datasets
- Use `->searchable(isGlobal: false, isIndividual: true)` to keep global search fast
- Use `->counts()`, `->exists()`, `->sum()`, `->avg()` instead of loading full relationships just for aggregates
- Avoid `->state(fn ($record) => ...)` with N+1-prone logic — move computation to Eloquent accessors or query scopes

---

## DOCUMENTATION REFERENCES (Filament v5)

- Overview: https://filamentphp.com/docs/5.x/tables/overview
- Columns: https://filamentphp.com/docs/5.x/tables/columns/overview
- Filters: https://filamentphp.com/docs/5.x/tables/filters/overview
- Actions: https://filamentphp.com/docs/5.x/tables/actions
- Layout: https://filamentphp.com/docs/5.x/tables/layout
- Summaries: https://filamentphp.com/docs/5.x/tables/summaries
- Grouping: https://filamentphp.com/docs/5.x/tables/grouping
- Empty state: https://filamentphp.com/docs/5.x/tables/empty-state
- Custom data: https://filamentphp.com/docs/5.x/tables/custom-data