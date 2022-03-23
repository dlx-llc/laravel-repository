<?php

namespace Deluxetech\LaRepo;

use Deluxetech\LaRepo\Contracts\CriteriaContract;

class Criteria implements CriteriaContract
{
    use Traits\SupportsSorting;
    use Traits\SupportsTextSearch;
    use Traits\SupportsFiltration;
    use Traits\DefinesQueryContext;
}
