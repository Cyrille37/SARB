<?php

namespace Twitter;

class ObjectBase {

	/**
	 * The integer representation of the unique identifier for this Object (Tweet/User).
	 * This number is greater than 53 bits and some programming languages may have difficulty/silent defects in interpreting it. Using a signed 64 bit integer for storing this identifier is safe. Use id_str for fetching the identifier to stay on the safe side.
	 *
	 * @var int64 The integer representation of the unique identifier for this Tweet
	 */
	protected $id;

	/**
	 * 
	 * @return int64
	 */
	public function getId(){
		return $this->id ;
	}

	/**
	 * The string representation of the unique identifier for this Object (Tweet/User).
	 * Implementations should use this rather than the large integer in id
	 *
	 * @var string The string representation of the unique identifier for this Tweet.
	 */
	protected $id_str;
	
	/**
	 * For Status : Nullable. When present, indicates a BCP 47 language identifier corresponding to the machine-detected language of the Tweet text, or "und" if no language could be detected.
	 * For User : The BCP 47 code for the user's self-declared user interface language. May or may not have anything to do with the content of their Tweets.
	 * 
	 * @var string
	 */
	protected $lang;
	
	/**
	 * 
	 * @return string
	 */
	public function getLang(){
		return $this->lang ;
	}
	
	/**
	 *
	 * @param mixed $object
	 * @return \Twitter\ObjectBase
	 */
	public static function initWith( $object, $data) {

		$vars = get_object_vars ( $object );
		if( is_array($data))
		foreach ( $vars as $k => $v ) {
			if (isset ( $data [$k] )) {
				$object->{$k} = $data [$k];
			}
		}
		else if( is_object($data))
		{
			foreach ( $vars as $k => $v ) {
				if (isset ( $data->{$k} )) {
					$object->{$k} = $data->{$k};
				}
			}
		}
	}
	
	
}