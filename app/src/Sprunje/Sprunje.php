<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Sprunje;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\Csv\Writer;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Support\Message\UserMessage;
use Valitron\Validator;

/**
 * Implements a versatile API for sorting, filtering, and paginating an Eloquent query builder.
 */
abstract class Sprunje
{
    /**
     * @var string Name of this Sprunje, used when generating output files.
     */
    protected string $name = 'export';

    /**
     * @var EloquentBuilder|QueryBuilder|Relation The base (unfiltered) query.
     */
    protected EloquentBuilder|QueryBuilder|Relation $query;

    /**
     * @var array{
     *  sorts: array<string, string>,
     *  filters: string[],
     *  lists: string[],
     *  size: string|int|null,
     *  page: ?int,
     *  format: 'csv'|'json',
     * } Default HTTP request parameters.
     */
    protected array $options = [
        'sorts'   => [],
        'filters' => [],
        'lists'   => [],
        'size'    => 'all',
        'page'    => null,
        'format'  => 'json',
    ];

    /**
     * @var string[] Fields to allow filtering upon.
     */
    protected array $filterable = [];

    /**
     * @var string[] Fields to allow listing (enumeration) upon.
     */
    protected array $listable = [];

    /**
     * @var string[] Fields to allow sorting upon.
     */
    protected array $sortable = [];

    /**
     * @var string[] List of fields to exclude when processing an "_all" filter.
     */
    protected array $excludeForAll = [];

    /**
     * @var non-empty-string Separator to use when splitting filter values to treat them as ORs.
     */
    protected string $orSeparator = '||';

    /**
     * @var string Array key for the total unfiltered object count.
     */
    protected string $countKey = 'count';

    /**
     * @var string Array key for the filtered object count.
     */
    protected string $countFilteredKey = 'count_filtered';

    /**
     * @var string Array key for the actual result set.
     */
    protected string $rowsKey = 'rows';

    /**
     * Array key for the list of enumerated columns and their enumerations.
     *
     * @var string
     */
    protected $listableKey = 'listable';

    /**
     * @var int CSV export split the request into multiple chunk to avoid memory overflow.
     *          Lower this value if you encounter memory issues when exporting large data sets.
     */
    protected int $csvChunk = 200;

    /**
     * @param array{
     *  sorts?: array<string, string>,
     *  filters?: array<string, mixed>,
     *  lists?: string[],
     *  size?: string|int|null,
     *  page?: ?int,
     *  format?: string
     * } $options DEPRECATED. Use `$this->setOptions()` instead.
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);

        // Start a new query on any Model instances
        if (is_a($query = $this->baseQuery(), Model::class)) {
            $this->query = $query->newQuery();
        } else {
            $this->query = $query;
        }
    }

    /**
     * Set Sprunje options.
     *
     * @param array{
     *  sorts?: array<string, string>,
     *  filters?: array<string, mixed>,
     *  lists?: string[],
     *  size?: string|int|null,
     *  page?: ?int,
     *  format?: string
     * } $options $options
     *
     * @return static
     */
    public function setOptions(array $options): static
    {
        $this->validateOptions($options);

        // @phpstan-ignore-next-line - Can't make array_replace_recursive hint at TOptions
        $this->options = array_replace_recursive($this->options, $options);

        return $this;
    }

    /**
     * Validate option using Validator.
     *
     * @param mixed[] $options
     *
     * @throws HttpBadRequestException
     */
    protected function validateOptions(array $options): void
    {
        // Validation on input data
        $v = new Validator($options);
        $v->rule('array', ['sorts', 'filters', 'lists']);
        $v->rule('regex', 'sorts.*', '/asc|desc/i');
        $v->rule('regex', 'size', '/all|[0-9]+/i');
        $v->rule('integer', 'page');
        $v->rule('regex', 'format', '/json|csv/i');

        if (!$v->validate()) {
            $e = new ValidationException();
            $e->addErrors($v->errors()); // @phpstan-ignore-line errors returns array with no arguments

            throw $e;
        }
    }

    /**
     * Extend the query by providing a callback.
     *
     * @param callable $callback A callback which accepts and returns a Builder instance.
     *
     * @return static
     */
    public function extendQuery(callable $callback): static
    {
        $this->query = $callback($this->query);

        return $this;
    }

    /**
     * Execute the query and build the results, and append them in the appropriate format to the response.
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function toResponse(ResponseInterface $response): ResponseInterface
    {
        $format = $this->options['format'];

        if ($format == 'csv') {
            // Prepare response
            $response = $response->withHeader('Content-Disposition', "attachment;filename={$this->name}.csv");
            $response = $response->withHeader('Content-Type', 'text/csv; charset=utf-8');
            $response->getBody()->write($this->getCsv()->toString());

            return $response;
        }

        // Default to JSON
        $payload = json_encode($this->getArray(), JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Executes the sprunje query, applying all sorts, filters, and pagination.
     *
     * Returns an array containing `count` (the total number of rows, before filtering), `count_filtered` (the total number of rows after filtering),
     * and `rows` (the filtered result set).
     *
     * @return array<string, mixed>
     */
    public function getArray(): array
    {
        list($count, $countFiltered, $rows) = $this->getModels();

        // Return sprunjed results
        return [
            $this->countKey           => $count,
            $this->countFilteredKey   => $countFiltered,
            $this->rowsKey            => $rows->values()->toArray(),
            $this->listableKey        => $this->getListable(),
        ];
    }

    /**
     * Run the query and build a CSV object by flattening the resulting collection.  Ignores any pagination.
     *
     * @return Writer
     */
    public function getCsv(): Writer
    {
        $filteredQuery = clone $this->query;

        // Apply filters
        $this->applyFilters($filteredQuery);

        // Apply sorts
        $this->applySorts($filteredQuery);

        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        $columnNames = [];
        $rows = [];

        $filteredQuery->chunk($this->csvChunk, function ($models) use (&$columnNames, &$rows) {

            // Perform any additional transformations on the dataset
            $collection = $this->applyTransformations($models);

            // Flatten collection while simultaneously building the column names from the union of each element's keys
            $collection->transform(function ($item, $key) use (&$columnNames) {
                $item = Arr::dot($item->toArray());
                foreach ($item as $itemKey => $itemValue) {
                    if (!in_array($itemKey, $columnNames, true)) {
                        $columnNames[] = $itemKey;
                    }
                }

                return $item;
            });

            // Insert the data as rows in the CSV document
            $collection->each(function ($item) use ($columnNames, &$rows) {
                $row = [];
                foreach ($columnNames as $itemKey) {
                    // Only add the value if it is set and not an array. Laravel's array_dot sometimes creates empty child arrays :(
                    // See https://github.com/laravel/framework/pull/13009
                    if (isset($item[$itemKey]) && !is_array($item[$itemKey])) {
                        $row[] = $item[$itemKey];
                    } else {
                        $row[] = '';
                    }
                }

                $rows[] = $row;
            });
        });

        $csv->insertOne($columnNames);
        $csv->insertAll($rows);

        return $csv;
    }

    /**
     * Executes the sprunje query, applying all sorts, filters, and pagination.
     *
     * Returns the filtered, paginated result set and the counts.
     *
     * @return array{int, int, Collection}
     */
    public function getModels(): array
    {
        // Count unfiltered total
        $count = $this->count($this->query);

        // Clone the Query\Builder, Eloquent\Builder, or Relation
        $filteredQuery = clone $this->query;

        // Apply filters
        $this->applyFilters($filteredQuery);

        // Count filtered total
        $countFiltered = $this->countFiltered($filteredQuery);

        // Apply sorts
        $this->applySorts($filteredQuery);

        // Paginate
        $this->applyPagination($filteredQuery);

        $collection = collect($filteredQuery->get());

        // Perform any additional transformations on the dataset
        $collection = $this->applyTransformations($collection);

        return [$count, $countFiltered, $collection];
    }

    /**
     * Get lists of values for specified fields in 'lists' option, calling a custom lister callback when appropriate.
     *
     * @return array<string,mixed>
     */
    public function getListable()
    {
        $result = [];
        foreach ($this->listable as $name) {

            // Determine if a custom filter method has been defined
            $methodName = 'list' . Str::studly($name);

            if (method_exists($this, $methodName)) {
                // @phpstan-ignore-next-line Allow variable method call, since we know it exists
                $result[$name] = $this->$methodName();
            } else {
                $result[$name] = $this->getColumnValues($name);
            }
        }

        return $result;
    }

    /**
     * Get the underlying queriable object in its current state.
     *
     * @return EloquentBuilder|QueryBuilder|Relation
     */
    public function getQuery(): EloquentBuilder|QueryBuilder|Relation
    {
        return $this->query;
    }

    /**
     * Set the underlying QueryBuilder object.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     *
     * @return static
     */
    public function setQuery(EloquentBuilder|QueryBuilder|Relation $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Apply any filters from the options, calling a custom filter callback when appropriate.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     *
     * @return static
     */
    public function applyFilters(EloquentBuilder|QueryBuilder|Relation $query): static
    {
        foreach ($this->options['filters'] as $name => $value) {
            // Check that this filter is allowed
            if (($name != '_all') && !in_array($name, $this->filterable, true)) {
                $e = new SprunjeException("Bad filter: $name");
                $message = new UserMessage('VALIDATE.SPRUNJE.BAD_FILTER', ['name' => $name]);
                $e->setDescription($message);

                throw $e;
            }
            // Since we want to match _all_ of the fields, we wrap the field callback in a 'where' callback
            $query->where(function ($fieldQuery) use ($name, $value) {
                $this->buildFilterQuery($fieldQuery, $name, $value);
            });
        }

        return $this;
    }

    /**
     * Apply any sorts from the options, calling a custom sorter callback when appropriate.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     *
     * @return static
     */
    public function applySorts(EloquentBuilder|QueryBuilder|Relation $query): static
    {
        foreach ($this->options['sorts'] as $name => $direction) {
            // Check that this sort is allowed
            if (!in_array($name, $this->sortable, true)) {
                $e = new SprunjeException("Bad sort: $name");
                $message = new UserMessage('VALIDATE.SPRUNJE.BAD_SORT', ['name' => $name]);
                $e->setDescription($message);

                throw $e;
            }

            // Determine if a custom sort method has been defined
            $methodName = 'sort' . Str::studly($name);

            if (method_exists($this, $methodName)) {
                // @phpstan-ignore-next-line Allow variable method call, since we know it exists
                $this->$methodName($query, $direction);
            } else {
                $query->orderBy($name, $direction);
            }
        }

        return $this;
    }

    /**
     * Apply pagination based on the `page` and `size` options.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     *
     * @return static
     */
    public function applyPagination(EloquentBuilder|QueryBuilder|Relation $query): static
    {
        if (
            (is_int($this->options['page'])) &&
            (is_int($this->options['size']))
        ) {
            $offset = $this->options['size'] * $this->options['page'];
            $query->skip($offset)
                  ->take($this->options['size']);
        }

        return $this;
    }

    /**
     * Set CSV export chunk parameters.
     *
     * @param int $csvChunk
     *
     * @return static
     */
    public function setCsvChunk(int $csvChunk): static
    {
        $this->csvChunk = $csvChunk;

        return $this;
    }

    /**
     * Match any filter in `filterable`.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param mixed                                 $value
     *
     * @return static
     */
    protected function filterAll(EloquentBuilder|QueryBuilder|Relation $query, mixed $value): static
    {
        foreach ($this->filterable as $name) {
            if (Str::studly($name) != 'all' && !in_array($name, $this->excludeForAll, true)) {
                // Since we want to match _any_ of the fields, we wrap the field callback in a 'orWhere' callback
                $query->orWhere(function ($fieldQuery) use ($name, $value) {
                    $this->buildFilterQuery($fieldQuery, $name, $value);
                });
            }
        }

        return $this;
    }

    /**
     * Build the filter query for a single field.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $name
     * @param mixed                                 $value
     *
     * @return static
     */
    protected function buildFilterQuery(EloquentBuilder|QueryBuilder|Relation $query, string $name, mixed $value): static
    {
        $methodName = 'filter' . Str::studly($name);

        // Determine if a custom filter method has been defined
        if (method_exists($this, $methodName)) {
            // @phpstan-ignore-next-line Allow variable method call, since we know it exists
            $this->$methodName($query, $value);
        } else {
            $this->buildFilterDefaultFieldQuery($query, $name, $value);
        }

        return $this;
    }

    /**
     * Perform a 'like' query on a single field, separating the value string on the or separator and
     * matching any of the supplied values.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $name
     * @param mixed                                 $value
     *
     * @return static
     */
    protected function buildFilterDefaultFieldQuery(EloquentBuilder|QueryBuilder|Relation $query, string $name, mixed $value): static
    {
        if (is_bool($value)) {
            // Bool doesn't behave correctly when cast to string (false is empty instead of 0).
            $query->orWhere($name, $value);
        } elseif (is_scalar($value)) {
            // Default filter - split value on separator for OR queries
            // and search by column name
            $values = explode($this->orSeparator, (string) $value);
            foreach ($values as $val) {
                $query->orWhere($name, 'LIKE', "%$val%");
            }
        }

        return $this;
    }

    /**
     * Set any transformations you wish to apply to the collection, after the query is executed.
     * This method is meant to be customized in child class.
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    protected function applyTransformations(Collection $collection): Collection
    {
        return $collection;
    }

    /**
     * Set the initial query used by your Sprunje.
     *
     * @return EloquentBuilder|QueryBuilder|Relation|Model
     */
    abstract protected function baseQuery(): EloquentBuilder|QueryBuilder|Relation|Model;

    /**
     * Returns a list of distinct values for a specified column.
     * Formats results to have a "value" and "text" attribute.
     *
     * @param string $column
     *
     * @return array{value: mixed, text: mixed}[]
     */
    protected function getColumnValues(string $column): array
    {
        $rawValues = $this->query->select($column)
                                 ->distinct()
                                 ->orderBy($column, 'asc')
                                 ->get();

        return $rawValues->map(function ($item, $key) use ($column) {
            return [
                'value' => $item[$column],
                'text'  => $item[$column],
            ];
        })->all();
    }

    /**
     * Get the unpaginated count of items (before filtering) in this query.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     *
     * @return int
     */
    protected function count(EloquentBuilder|QueryBuilder|Relation $query): int
    {
        return $query->count();
    }

    /**
     * Get the unpaginated count of items (after filtering) in this query.
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     *
     * @return int
     */
    protected function countFiltered(EloquentBuilder|QueryBuilder|Relation $query): int
    {
        return $query->count();
    }
}
