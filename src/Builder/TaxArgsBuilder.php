<?php

namespace Vincentts\WpModels\Builder;

class TaxArgsBuilder {
    public function filterBy( string $taxonomy, int|string|array $terms = null, string $field = 'term_id', string $operator = 'IN', bool $include_children = true ) {
        return [
            'taxonomy' => $taxonomy,
            'terms' => $terms,
            'field' => $field,
            'operator' => $operator,
            'include_children' => $include_children,
        ];
    }
}