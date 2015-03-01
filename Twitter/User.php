<?php
namespace Twitter;

require_once (__DIR__ . '/ObjectBase.php');

/**
 * https://dev.twitter.com/docs/platform-objects/users
 */
class User extends ObjectBase
{

    /**
     * Indicates that the user has an account with "contributor mode" enabled,
     * allowing for Tweets issued by the user to be co-authored by another account.
     * Rarely true.
     *
     * @var boolean
     */
    protected $contributors_enabled;

    /**
     * The UTC datetime that the user account was created on Twitter.
     *
     * @var string
     */
    protected $created_at;

    /**
     * When true, indicates that the user has not altered the theme or background of their user profile.
     *
     * @var boolean
     */
    protected $default_profile;

    /**
     * When true, indicates that the user has not uploaded their own avatar and a default egg avatar is used instead.
     *
     * @var boolean
     */
    protected $default_profile_image;

    /**
     * Nullable.
     * The user-defined UTF-8 string describing their account.
     *
     * @var string
     */
    protected $description;

    /**
     * Nullable.
     * The user-defined UTF-8 string describing their account.
     *
     * @return string
     */
    public function getDescription()
    {
    	return $this->description;
    }

    /**
     * Entities which have been parsed out of the url or description fields defined by the user.
     *
     * @var Entities
     */
    protected $entities;

    /**
     * The number of tweets this user has favorited in the account's lifetime.
     * British spelling used in the field name for historical reasons.
     *
     * @var int
     */
    protected $favourites_count;

    /**
     * Nullable.
     * Perspectival. When true, indicates that the authenticating user has issued a follow request to this protected user account.
     *
     * @var boolean
     */
    protected $follow_request_sent;

    /**
     * Nullable.
     * Perspectival. Deprecated.
     * When true, indicates that the authenticating user is following this user.
     * Some false negatives are possible when set to "false," but these false negatives are increasingly being represented as "null" instead
     *
     * @var boolean
     */
    protected $following;

    /**
     * The number of followers this account currently has.
     * Under certain conditions of duress, this field will temporarily indicate "0."
     *
     * @var int
     */
    protected $followers_count;

    /**
     * The number of followers this account currently has.
     * Under certain conditions of duress, this field will temporarily indicate "0."
     *
     * @return int
     */
    public function getFollowersCount()
    {
    	return $this->followers_count;
    }

    /**
     * The number of users this account is following (AKA their "followings").
     * Under certain conditions of duress, this field will temporarily indicate "0."
     *
     * @var int
     */
    protected $friends_count;

    /**
     * The number of users this account is following (AKA their "followings").
     * Under certain conditions of duress, this field will temporarily indicate "0."
     *
     * @return int
     */
    public function getFriendsCount()
    {
    	return $this->friends_count;
    }

    /**
     * When true, indicates that the user has enabled the possibility of geotagging their Tweets.
     * This field must be true for the current user to attach geographic data when using POST statuses/update.
     *
     * @var boolean
     */
    protected $geo_enabled;

    /**
     * When true, indicates that the user is a participant in Twitter's translator community.
     *
     * @var boolean
     */
    protected $is_translator;

    /**
     * The number of public lists that this user is a member of.
     *
     * @var int
     */
    protected $listed_count;

    /**
     * Nullable.
     * The user-defined location for this account's profile.
     * Not necessarily a location nor parseable.
     * This field will occasionally be fuzzily interpreted by the Search service.
     *
     * @var string
     */
    protected $location;

    /**
     * The name of the user, as they've defined it.
     * Not necessarily a person's name.
     * Typically capped at 20 characters, but subject to change.
     *
     * @var string
     */
    protected $name;

    /**
     * The name of the user, as they've defined it.
     * Not necessarily a person's name.
     * Typically capped at 20 characters, but subject to change.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Nullable.
     * Deprecated. May incorrectly report "false" at times.
     * Indicates whether the authenticated user has chosen to receive this user's tweets by SMS.
     *
     * @var boolean
     */
    protected $notifications;

    /**
     * When true, indicates that this user has chosen to protect their Tweets.
     *
     * @var boolean
     */
    protected $protected;

    /**
     * The screen name, handle, or alias that this user identifies themselves with.
     * screen_names are unique but subject to change.
     * Use id_str as a user identifier whenever possible.
     * Typically a maximum of 15 characters long, but some historical accounts may exist with longer names.
     *
     * @var string
     */
    protected $screen_name;

    /**
     * The screen name, handle, or alias that this user identifies themselves with.
     * screen_names are unique but subject to change.
     * Use id_str as a user identifier whenever possible.
     * Typically a maximum of 15 characters long, but some historical accounts may exist with longer names.
     *
     * @return string
     */
    public function getScreenName()
    {
        return $this->screen_name;
    }

    /**
     * Nullable.
     * If possible, the user's most recent tweet or retweet.
     * In some circumstances, this data cannot be provided and this field will be omitted, null, or empty.
     * Perspectival attributes within tweets embedded within users cannot always be relied upon.
     *
     * @var Status
     */
    protected $status;

    /**
     * The number of tweets (including retweets) issued by the user.
     *
     * @var int
     */
    protected $statuses_count;

    /**
     * Nullable.
     * A string describing the Time Zone this user declares themselves within.
     *
     * @var string
     */
    protected $time_zone;

    /**
     * Nullable.
     * A URL provided by the user in association with their profile.
     *
     * @var string
     */
    protected $url;

    /**
     * Nullable.
     * The offset from GMT/UTC in seconds.
     *
     * @var int
     */
    protected $utc_offset;

    /**
     * When true, indicates that the user has a verified account.
     *
     * @var boolean
     */
    protected $verified;

    /**
     * When present, indicates a textual representation of the two-letter country codes this user is withheld from.
     *
     * @var string
     */
    protected $withheld_in_countries;

    /**
     * When present, indicates whether the content being withheld is the "status" or a "user."
     *
     * @var string
     */
    protected $withheld_scope;

    /**
     * Create an User object form a Json object.
     * 
     * @param mixed $data            
     * @return \Twitter\User
     */
    public static function createFrom($data)
    {
        $user = new User();
        parent::initWith($user, $data);
        return $user;
    }
}