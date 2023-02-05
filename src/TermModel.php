<?php

namespace Vincentts\WpModels;

use WP_Term_Query;
use WP_Term;
use Vincentts\WpModels\Builder\RelationshipBuilder;

class TermModel {
    use RelationshipBuilder;

    protected $taxonomy;
    protected $object_ids;
    protected $order_by = 'name';
    protected $order = 'ASC';
    protected $hide_empty = true;
    protected $include = [];
    protected $exclude = [];
    protected $exclude_tree = [];
    protected $results_count = 0;
    protected $offset = 0;
    protected $with_count = false;
    protected $hierarchical = true;
    protected $search = [];
    protected $child_of = 0;
    protected $parent;
    protected $childless = false;
    protected $pad_counts = false;

    public function __construct( string $taxonomy = null )
    {
        if ( $taxonomy ) {
            $this->taxonomy = $taxonomy;
        }
    }

    public function for( array $object_ids ) {
        $this->object_ids = $object_ids;
        
        return $this;
    }

    public function orderBy( string $field = 'name', string $order = 'ASC' ) {
        $this->order_by = $field;
        $this->order = $order;

        return $this;
    }

    public function showEmpty() {
        $this->hide_empty = false;
        
        return $this;
    }

    public function include( array $term_ids = [] ) {
        $this->include = $term_ids;
        
        return $this;
    }

    public function exclude( array $term_ids = [] ) {
        $this->exclude = $term_ids;

        return $this;
    }

    public function excludeTree( array $term_ids = [] ) {
        $this->exclude_tree = $term_ids;

        return $this;
    }

    public function limit( int $limit = 0 ) {
        $this->results_count = $limit;

        return $this;
    }

    public function offset( int $offset ) {
        $this->offset = $offset;

        return $this;
    }

    public function withCount() {
        $this->with_count = true;

        return $this;
    }

    /**
     * @param string $parameter Allowed values: 'name', 'slug', 'term_taxonomy_id', 'name__like', 'description__like', 'search'.
     * Ref: https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
     */
    public function getBy( string $parameter, string|array $values ) {
        $this->search[ $parameter ] = $values;

        return $this;
    }

    public function withoutHierarchy() {
        $this->hierarchical = false;

        return $this;
    }
    
    public function childOf( int $term_id ) {
        $this->child_of = $term_id;

        return $this;
    }

    public function parent( int $term_id ) {
        $this->parent = $term_id;

        return $this;
    }

    public function childless() {
        $this->childless = true;

        return $this;
    }

    public function padCounts() {
        $this->pad_counts = true;

        return $this;
    }

    protected function getTermRelationships( WP_Term|int $term_object ) {
        $term = [];
        if ( $term_object instanceof WP_Term ) {
            $term = $term_object->to_array();
        }
        if ( is_int( $term_object ) ) {
            $term['term_id'] = $term_object;
        }
        $this->object_id = $term['term_id'];
        $meta_data = $this->getMetaData();
        $relationships = $this->getRelationships();
        return (object) array_merge( $term, $meta_data, $relationships );
    }

    public function get( string $field = 'all' ) {
        $terms = [];
        $args = [
            'taxonomy' => $this->taxonomy,
            'orderby' => $this->order_by,
            'order'=> $this->order,
            'hide_empty' => $this->hide_empty,
            'include' => $this->include,
            'exclude' => $this->exclude,
            'exclude_tree' => $this->exclude_tree,
            'number' => $this->results_count,
            'fields' => $field,
            'count' => $this->with_count,
            'hierharchical' => $this->hierarchical,
            'child_of' => $this->child_of,
            'childless' => $this->childless,
        ];

        if ( $this->object_ids ) {
            $args['object_ids'] = $this->object_ids;
        }

        if ( $this->offset > 0 ) {
            $args['offset'] = $this->offset;
        }

        if ( ! empty( $this->search ) ) {
            foreach( $this->search as $key => $values ) {
                $args[ $key ] = $values;
            }
        }

        if ( $this->parent ) {
            $args['parent'] = $this->parent;
        }

        $terms = ( new WP_Term_Query( $args ) )->get_terms();

        if ( count( $terms ) > 0 ) {
            if ( ! empty( $this->relations ) || ! empty( $this->meta ) ) {
                return array_map( function( $term ) {
                    return $this->getTermRelationships( $term );
                }, $terms );
            }

            return array_map( function( $term ) {
                return (object) $term->to_array();
            }, $terms );
        }

        return $terms;
    }
}