<?php

namespace Twitter ;

class SearchMetaData {

	protected $completed_in ;
	protected $max_id ;
    protected $max_id_str ;
    protected $next_results ;
    protected $query ;
    protected $refresh_url ;
    protected $count;
    protected $since_id;
    protected $since_id_str;

    public static function createFromArray( Array $object )
    {
    	$smd = new SearchMetaData();
    	$vars = get_object_vars($smd);
    	foreach( $vars as $k => $v )
    	{
    		if( isset($object[$k]) )
    		{
    			$smd->{$k} = $object[$k] ;
    		}
    	}
    	return $smd ;
    }

    public function asMoreResults()
    {
    	return $this->next_results!=null ? true : false ;
    }
}
