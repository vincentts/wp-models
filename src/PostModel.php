<?php

namespace Vincentts\WpModels;

use WP_Query;
use WP_Post;
use Vincentts\WpModels\Builder\TaxArgsBuilder;
use Vincentts\WpModels\Builder\MetaArgsBuilder;
use Vincentts\WpModels\Builder\PostArgsBuilder;
use Vincentts\WpModels\Builder\PaginationArgsBuilder;
use Vincentts\WpModels\Builder\RelationshipBuilder;

class PostModel {
    use PostArgsBuilder, PaginationArgsBuilder, RelationshipBuilder;

    protected $query_method = 'get';
    protected $post_type = 'post';
    protected $keyword = '';
    protected $post_status = 'publish';
    protected $meta_query = [];
    protected $tax_query = [];
    protected $orderby = [];
    protected $tax_filter_builder;
    protected $meta_filter_builder;

    public function __construct( string $post_type = null )
    {
        $this->tax_filter_builder = new TaxArgsBuilder();
        $this->meta_filter_builder = new MetaArgsBuilder();

        if ( $post_type ) {
            $this->post_type = $post_type;
        }
    }

    public function method( string $query_method ) {
        $this->query_method = $query_method;

        return $this;
    }

    public function status( string|array $status ) {
        $this->post_status = $status;

        return $this;
    }

    public function search( string $keyword ) {
        $this->keyword = $keyword;

        return $this;
    }

    public function taxFilter( string $taxonomy, int|string|array $terms = null, string $field = 'term_id', string $operator = 'IN', bool $include_children = true) {
        $this->tax_query[] = $this->tax_filter_builder->filterBy( $taxonomy, $terms, $field, $operator, $include_children );
        
        return $this;
    }

    public function taxFilters( callable $filter_callback ) {
        $this->tax_query = $filter_callback( $this->tax_filter_builder, $this->tax_query );
        
        return $this;
    }

    public function metaFilter( string $key, string|array $value = null, string $type = 'string', string $compare = '=' ) {
        $meta_filter = $this->meta_filter_builder->filterBy( $key, $value, $type, $compare );

        if ( count( array_keys( $meta_filter ) ) > 1 ) {
            $this->meta_query[] = $meta_filter;

            return $this;
        }

        $this->meta_query = array_merge( $this->meta_query, $meta_filter );

        return $this;
    }

    public function metaFilters( callable $filter_callback ) {
        $this->meta_query = $filter_callback( $this->meta_filter_builder, $this->meta_query );

        return $this;
    }

    public function orderBy( $key, $sort = 'DESC' ) {
        $this->orderby[ $key ] = $sort;
        
        return $this;
    }

    public function getArgs() {
        $args = [
            'post_type' => $this->post_type,
            'post_status' => $this->post_status,
            'tax_query' => $this->tax_query,
            'meta_query' => $this->meta_query,
            'orderby' => $this->orderby,
        ];

        $post_args = $this->getPostArgs();
        if ( ! empty( $post_args ) ) {
            $args = array_merge( $args, $post_args );
        }

        $pagination_args = $this->getPaginationArgs();
        if ( ! empty( $pagination_args ) ) {
            $args = array_merge( $args, $pagination_args );
        }

        if ( $this->keyword !== '' ) {
            $args['s'] = $this->keyword;
        }

        return $args;
    }

    protected function getPostRelationships( WP_Post|int $post_object ) {
        $post = [];
        if ( $post_object instanceof WP_Post ) {
            $post = $post_object->to_array();
        }
        
        if ( is_int( $post_object ) ) {
            $post = [
                'ID' => $post_object,
            ];
        }
        
        $this->object_id = $post['ID'];
        $meta_data = $this->getMetaData();
        $relationships = $this->getRelationships();
        
        return (object) array_merge( $post, $meta_data, $relationships );
    }

    public function get( string $field = 'all' ) {
        $posts = [];
        $args = $this->getArgs();
        $args['fields'] = $field;

        if ( $this->query_method === 'get' ) {
            $posts = get_posts( $args );
        }

        if ( $this->query_method === 'query' ) {
            $wp_query = new WP_Query( $args );
            
            if ( $wp_query->have_posts() ) {
                $posts = $wp_query->get_posts();
            }
        }
       
        if ( count( $posts ) > 0 ) {
            if ( ! empty( $this->relations ) || ! empty( $this->meta ) ) {
                if ( $this->single ) {
                    return $this->getPostRelationships( $posts[0] );
                } else {
                    return array_map( function( $post ) {
                        return $this->getPostRelationships( $post );
                    }, $posts );
                }
            }

            return array_map( function( $post ) {
                return (object) $post->to_array();
            }, $posts );
        }

        return $posts;
    }
}