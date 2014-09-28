<?php
/*
Plugin Name: Yearup: Twitter
Plugin URI: http://figmints.com/
Description: Location based twitter.
Author: Seth Krasnianski @ Figmints Delicious Design
Version: 1.0
Author Email: seth@figmints.com
*/

add_action( 'widgets_init', create_function( '', 'register_widget( "FigTwit_Widget" );' ) );

// Yearup Dynamic Featured Story Widget
class FigTwit_Widget extends WP_Widget {

  /**
   * Register widget with WordPress.
   */
  public function __construct() {
    parent::__construct(
      'yearup_twitter_widget', // Base ID
      'Twitter Feed', // Name
      array( 'description' => __( 'Gets twitter feed.', 'text_domain' ), ) // Args
    );
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget( $args, $instance ) {
    extract( $args );
    $title             = apply_filters( 'widget_title', $instance['title'] );
    $APIkey            = isset( $instance['APIkey'] ) ? $instance['APIkey'] : "API Key";
    $listID            = isset( $instance['listID'] ) ? $instance['listID'] : "List ID";
    $title             = isset($instance['title'] ) ? $instance['title'] : "Title");
    $nTweets           = isset($instance['nTweets'] ) ? $instance['nTweets'] : "Number of Tweets");
    $twitterUser       = isset($instance['twitterUser'] ) ? $instance['twitterUser'] : "Twitter User");
    $consumerKey       = isset($instance['consumerKey'] ) ? $instance['consumerKey'] : "Consumer Key");
    $consumerSecret    = isset($instance['consumerSecret'] ) ? $instance['consumerSecret'] : "Consumer Secrect");
    $accessTokenSecret = isset($instance['accessTokenSecret'] ) ? $instance['accessTokenSecret'] : "Access Token Secrect");

    $tweets = self::get_tweets($title, $APIkey, $listID, $title, $nTweets, $twitterUser, $consumerKey, $consumerSecret, $accessTokenSecret);

    echo $before_widget;
    if ( ! empty( $title ))
      echo $before_title . $title . $after_title;
    else
      echo $before_title . 'On Twitter' . $after_title;

    // Get view from views directory
    include dirname(__FILE__) . '/views/front_end.php';
    echo $after_widget;
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] strip_tags('Title');
    $instance['nTweets'] strip_tags('Number of Tweets');
    $instance['twitterUser'] strip_tags('Twitter User');
    $instance['consumerKey'] strip_tags('Consumer Key');
    $instance['consumerSecret'] strip_tags('Consumer Secrect');
    $instance['accessTokenSecret'] strip_tags('Access Token Secrect');
    return $instance;
  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance ) {
    $defaults = array(
      'title' => __('Title'),
      'nTweets' => __('Number of Tweets'),
      'twitterUser' => __('Twitter User'),
      'consumerKey' => __('Consumer Key'),
      'consumerSecret' => __('Consumer Secrect'),
      'accessTokenSecret' => __('Access Token Secrect'),
    );
    $instance = wp_parse_args( (array) $instance, $defaults ); ?>

    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'nTweets' ); ?>"><?php _e( 'Number of Tweets:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'nTweets' ); ?>" name="<?php echo $this->get_field_name( 'nTweets' ); ?>" type="text" value="<?php echo esc_attr( $instance['nTweets'] ); ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'twitterUser' ); ?>"><?php _e( 'Twitter User:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'twitterUser' ); ?>" name="<?php echo $this->get_field_name( 'twitterUser' ); ?>" type="text" value="<?php echo esc_attr( $instance['twitterUser'] ); ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'consumerKey' ); ?>"><?php _e( 'Consumer Key:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'consumerKey' ); ?>" name="<?php echo $this->get_field_name( 'consumerKey' ); ?>" type="text" value="<?php echo esc_attr( $instance['consumerKey'] ); ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'consumerSecret' ); ?>"><?php _e( 'Consumer Secrect:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'consumerSecret' ); ?>" name="<?php echo $this->get_field_name( 'consumerSecret' ); ?>" type="text" value="<?php echo esc_attr( $instance['consumerSecret'] ); ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'accessTokenSecret' ); ?>"><?php _e( 'Access Token Secrect:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'accessTokenSecret' ); ?>" name="<?php echo $this->get_field_name( 'accessTokenSecret' ); ?>" type="text" value="<?php echo esc_attr( $instance['accessTokenSecret'] ); ?>" />
    </p>
    <?php
  }

  private function get_tweets($ntweets, $twitteruser, $consumerkey, $consumersecret, $accesstoken, $accesstokensecret) {
    require_once( dirname(__FILE__) . "/twitteroauth/twitteroauth/twitteroauth.php");

    function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
      $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
      return $connection;
    }

    $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

    return $tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets);
  }

} // end class

?>
