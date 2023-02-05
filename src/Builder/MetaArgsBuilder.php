<?php

namespace Vincentts\WpModels\Builder;

class MetaArgsBuilder {
    /**
     * @param string $key Use ":" to create alias e.g. "key:alias".
     */
    public function filterBy( string $key, string|array $value = null, string $type = 'CHAR', string $compare = '=' ) {
        $key_alias = explode( ':', $key);
        if ( count( $key_alias ) === 1 ) {
            $meta_query = [
                'key' => $key,
                'compare' => $compare,
                'type' => $type,
            ];

            if ( ! is_null( $value ) ) {
                $meta_query['value'] = $value;
            }

            return $meta_query;
        }

        $key = $key_alias[0];
        $alias = $key_alias[1];
        $meta_query[ $alias ] = [
            'key' => $key,
            'compare' => $compare,
            'type' => $type,
        ];
        
        if ( ! is_null( $value ) ) {
            $meta_query[ $alias ]['value'] = $value;
        }

        return $meta_query;
    }
}