<?php

namespace DigitalUnited\WPElasticAPI;

/**
 * @SWG\Resource(
 *   basePath="{baseUrl}",
 *  resourcePath="/posts",
 * description="CRUD to manage posts"
 * )
 */
class Posts extends \DigitalUnited\WPElasticAPI\Application {

    static function createInstance( $app ) {
        $app->container->singleton('Posts', function () {
            return new Posts();
        });
        $app->Posts->routes( $app );
    }

    function routes( \Slim\Slim $app ) {

        $base = $this->getBasePath();

        $app->post( $base . '/posts/search', function () use ( $app ) {
            $app->Posts->search();
        } );

        $app->post( $base . '/posts/:id', function ( $id ) use ( $app ) {
            $app->Posts->getById( $id );
        } );

        $app->get( $base . '/posts/:id', function ( $id ) use ( $app ) {
            $app->Posts->getById( $id );
        } );

        $app->post( $base . '/posts', function () use ( $app ) {
            $app->Posts->save();
        } );

        $app->delete( $base . '/posts/:id', function ( $id ) use ( $app ) {
            $app->Posts->delete( $id );
        } );

    }

    /**
     * @SWG\Api(
     *   path="/posts",
     *   @SWG\Operation(
     *     method="POST",
     *     summary="Create or update a post and returns the data[] if success. This action requires valid IP-address.",
     *     nickname="posts_save",
     *      @SWG\Parameter(
     *           name="post_type",
     *           required=true,
     *           type="string"
     *         ),
     *      @SWG\Parameter(
     *           name="ID",
     *           required=true,
     *           type="integer"
     *         ),
     *      @SWG\Parameter(
     *           name="data",
     *           required=true,
     *           type="array[]"
     *         )
     *     )
     * )
     */
    function save() {

        $this->blockInvalidIPAddress();

        $body   = $this->getBodyAsArray();
        $required_parameters = array( 'post_type', 'ID','data' );
        $optional_parameters = array();
        $this->check_req_opt_param( $required_parameters , $optional_parameters , $body );

        $index = $this->elastica->getIndex( $this->getElasticsearchIndex() );
        $type  = $index->getType( $body['post_type'] );

        $doc = new \Elastica\Document( $body['ID'], json_encode( $body['data'] ) );
        $type->addDocument( $doc );

        $index->refresh();

        echo json_encode( $body['data'] );

    }

    /**
     * @SWG\Api(
     *   path="/posts/:id",
     *   @SWG\Operation(
     *     method="DELETE",
     *     summary="Deletes a post and returns true if success. This action requires valid IP-address.",
     *     nickname="posts_delete",
     *      @SWG\Parameter(
     *           name="ID",
     *           required=true,
     *           type="integer"
     *         )
     * )
     * )
     */
    function delete( $id ) {

        $this->blockInvalidIPAddress();

        $body                = $this->getBodyAsArray();
        $required_parameters = array( 'post_type' );
        $optional_parameters = array();
        $this->check_req_opt_param( $required_parameters, $optional_parameters, $body );

        $index = $this->elastica->getIndex( $this->getElasticsearchIndex() );
        $type  = $index->getType( $body['post_type'] );

        $type->deleteById( $id );
        $index->refresh();

        echo json_encode( true );

    }

    /**
     * @SWG\Api(
     *   path="/posts/search",
     *   @SWG\Operation(
     *     method="POST",
     *     summary="Search items in posts",
     *     nickname="posts_search",
     *      @SWG\Parameter(
     *           name="post_type",
     *           required=false,
     *           type="string"
     *         ),
     *      @SWG\Parameter(
     *           name="filters",
     *           description="filter values, eg. [{my_parameter: 'mustmatchthis'}]
     *           required=false,
     *           type="array[]"
     *         ),
     *      @SWG\Parameter(
     *           name="search_phrase",
     *           required=false,
     *           type="string"
     *         ),
     *      @SWG\Parameter(
     *           name="sort",
     *           description="sort configuration, eg [{'post_name':{'order':'asc'}}]",
     *           required=false,
     *           type="array[]"
     *         ),
     *      @SWG\Parameter(
     *           name="limit",
     *           description="Max number of documents in return",
     *           required=false,
     *           type="integer"
     *         )
     *   )
     * )
     */
    function search() {

        $result = array();

        $body   = $this->getBodyAsArray();
        $required_parameters = array();
        $optional_parameters = array( 'post_type', 'filters', 'search_phrase', 'sort', 'limit' );
        $this->check_req_opt_param( $required_parameters , $optional_parameters , $body );

        $index = $this->elastica->getIndex( $this->getElasticsearchIndex() );

        $query = new \Elastica\Query();
        $boolean = new \Elastica\Query\Bool();
        $added = false;

        $type  = isset( $body['post_type'] ) ? $body['post_type'] : null;
        if($type) {
            $q = new \Elastica\Query\Term(array('_type' => $type));
            $boolean->addMust($q);
            $added = true;
        }

        $filters = isset($body['filters']) ? (array)$body['filters'] : array();
        $filters = array_filter($filters);

        $filterAnd = '';
        if ($filters) {
            $filterAnd = new \Elastica\Filter\BoolAnd();
            foreach($filters as $key => $val) {
                $filter = new \Elastica\Filter\Term();
                $filter->setTerm($key, $val);
                $filterAnd->addFilter($filter);
            }
        }

        $search_phrase  = isset( $body['search_phrase'] ) ? $body['search_phrase'] : null;
        if ( isset( $search_phrase ) && ! empty( $search_phrase ) ) {
            $word                = strtolower( $search_phrase ) . '*';
            $elasticaQueryString = new \Elastica\Query\SimpleQueryString( $word );
            $boolean->addMust( $elasticaQueryString );
            $added = true;
        }

        if ( $added ) {
            $query->setQuery( $boolean );
        }

        if ($filterAnd) {
            $query->setFilter($filterAnd);
        }

        $limit  = isset( $body['limit'] ) ? (int)$body['limit'] : null;
        if( $limit ) {
            $query->setSize( $limit );
        }

        $sort  = isset( $body['sort'] ) ? $body['sort'] : null;
        if($sort ) {
            $query->setSort( $sort ); // example: array( 'post_date' => array( 'order' => 'desc' ) )
        }

        $elasticaResultSet = $index->search( $query );

        $elasticaResults = $elasticaResultSet->getResults();

        foreach ( $elasticaResults as $elasticaResult ) {
            $result[] = $elasticaResult->getData();
        }

        echo json_encode( $result );

    }

    /**
     * @SWG\Api(
     *   path="/posts/:id",
     *   @SWG\Operation(
     *     method="GET",
     *     summary="Gets a post",
     *     nickname="posts_get"
     *   )
     * )
     */
    function getById( $id ) {

        $result = array();

        $body   = $this->getBodyAsArray();
        $required_parameters = array();
        $optional_parameters = array( 'post_type' );
        $this->check_req_opt_param( $required_parameters , $optional_parameters , $body );

        $index = $this->elastica->getIndex( $this->getElasticsearchIndex() );

        $query = new \Elastica\Query();
        $boolean = new \Elastica\Query\Bool();
        $added = false;

        $type  = isset( $body['post_type'] ) ? $body['post_type'] : null;
        if ( $type ) {
            $q = new \Elastica\Query\Term( array( "_type" => $type ) );
            $boolean->addMust( $q );
        }

        $q = new \Elastica\Query\Term( array( "_id" => $id ) );
        $boolean->addMust( $q );

        $query->setQuery( $boolean );
        $query->setSize( 1 );

        $elasticaResultSet = $index->search( $query );

        $elasticaResults = $elasticaResultSet->getResults();

        foreach ( $elasticaResults as $elasticaResult ) {
            $result[] = $elasticaResult->getData();
        }

        echo json_encode( $result );

    }

}
