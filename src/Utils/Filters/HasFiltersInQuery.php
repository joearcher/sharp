<?php

namespace Code16\Sharp\Utils\Filters;

trait HasFiltersInQuery
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @param string $filterName
     * @return mixed|null
     */
    public function filterFor(string $filterName)
    {
        if(isset($this->filters["/forced/$filterName"])) {
            return $this->filterFor("/forced/$filterName");
        }

        if(!isset($this->filters[$filterName])) {
            return null;
        }

        return str_contains($this->filters[$filterName], ",")
            ? explode(",", $this->filters[$filterName])
            : $this->filters[$filterName];
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function setDefaultFilters($filters)
    {
        collect((array) $filters)->each(function($value, $filter) {
            $this->setFilterValue($filter, $value);
        });

        return $this;
    }

    /**
     * @param array $query
     */
    protected function fillFilterWithRequest(array $query = null)
    {
        collect($query)
            ->filter(function($value, $name) {
                return starts_with($name, "filter_");

            })->each(function($value, $name) {
                $this->setFilterValue(substr($name, strlen("filter_")), $value);
            });
    }

    /**
     * @param string $filter
     * @param string $value
     */
    public function forceFilterValue(string $filter, $value)
    {
        $this->filters["/forced/$filter"] = $value;
    }

    /**
     * @param string $filter
     * @param string $value
     */
    protected function setFilterValue(string $filter, $value)
    {
        if(is_array($value)) {
            // Force all filter values to be string, to be consistent with
            // all use cases (filter in EntityList or in Command)
            $value = empty($value) ? null : implode(',', $value);
        }

        $this->filters[$filter] = $value;

        event("filter-{$filter}-was-set", [$value, $this]);
    }
}